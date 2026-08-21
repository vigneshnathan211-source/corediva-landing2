<?php
/**
 * Admin authentication: email + password (first factor), then an emailed
 * OTP (second factor), then a DB-backed session (admin_sessions) --
 * separate from the public site's PHP session.
 *
 * A DB-backed session, rather than plain $_SESSION, is what lets a
 * session be individually revoked (logout, or an admin disabling another
 * user) and audited -- that's the whole reason admin_sessions exists as
 * its own table instead of relying on PHP's file-based session store.
 * The short-lived step between "password verified, code emailed" and
 * "code entered" still uses the public $_SESSION, purely to carry the
 * pending user ID across those two requests.
 *
 * Enumeration protection lives entirely in the password step: a wrong
 * email and a wrong password return the exact same message, and take
 * about the same time either way (password_verify always runs, against
 * a dummy hash when no such user exists). Because the OTP step can only
 * be reached after a correct password, it no longer needs its own
 * generic-response trick -- reaching it already proves the account
 * exists.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/mailer.php';
require_once __DIR__ . '/uploads.php';

const ADMIN_SESSION_COOKIE = 'cd_admin_session';

/** Admin-panel-only config: password policy, OTP and session lifetimes. */
function admin_cfg(string $path, $default = null)
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }

    $node = $config;
    foreach (explode('.', $path) as $part) {
        if (!is_array($node) || !array_key_exists($part, $node)) {
            return $default;
        }
        $node = $node[$part];
    }
    return $node;
}

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

// =====================================================================
// Session
// =====================================================================

/**
 * The signed-in admin (id, email, name, role_id, role_slug, role_name,
 * permissions), or null. Reads the admin_sessions cookie -- entirely
 * separate from the public CSRF session.
 */
function admin_current_user(): ?array
{
    static $cached = false;
    if ($cached !== false) {
        return $cached;
    }

    $token = $_COOKIE[ADMIN_SESSION_COOKIE] ?? '';
    if ($token === '') {
        $cached = null;
        return null;
    }

    $row = db_one(
        'SELECT u.id, u.email, u.name, u.role_id, r.slug AS role_slug, r.name AS role_name
           FROM admin_sessions s
           JOIN admin_users u ON u.id = s.admin_user_id
           JOIN roles r ON r.id = u.role_id
          WHERE s.token_hash = ?
            AND s.revoked_at IS NULL
            AND s.expires_at > NOW()
            AND u.is_active = 1',
        [hash('sha256', $token)]
    );

    if ($row !== null) {
        $row['permissions'] = admin_role_permissions((int) $row['role_id']);
    }

    $cached = $row;
    return $row;
}

/** Redirects to the login page and exits if no admin is signed in. */
function require_admin_login(): array
{
    $user = admin_current_user();
    if ($user === null) {
        header('Location: ' . admin_url('login.php'));
        exit;
    }
    return $user;
}

/**
 * Redirects to the dashboard (with nothing revealed about why) and exits
 * if the signed-in admin's role lacks $slug. Call after require_admin_login().
 */
function require_permission(array $admin, string $slug): void
{
    if (!in_array($slug, $admin['permissions'], true)) {
        header('Location: ' . admin_url('dashboard.php'));
        exit;
    }
}

/** Permission slugs granted to $roleId. */
function admin_role_permissions(int $roleId): array
{
    return array_column(
        db_all(
            'SELECT p.slug FROM role_permissions rp
               JOIN permissions p ON p.id = rp.permission_id
              WHERE rp.role_id = ?',
            [$roleId]
        ),
        'slug'
    );
}

/** Issues a fresh DB-backed session and sets its cookie. */
function admin_create_session(int $adminUserId): void
{
    $token = bin2hex(random_bytes(32));
    $hours = (int) admin_cfg('session.hours', 8);

    db_exec(
        'INSERT INTO admin_sessions (admin_user_id, token_hash, ip, user_agent, expires_at)
         VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR))',
        [
            $adminUserId,
            hash('sha256', $token),
            client_ip_binary(),
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
            $hours,
        ]
    );

    setcookie(ADMIN_SESSION_COOKIE, $token, [
        'expires'  => time() + $hours * 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
}

/** Revokes the current admin session (DB row) and clears its cookie. */
function admin_destroy_session(): void
{
    $token = $_COOKIE[ADMIN_SESSION_COOKIE] ?? '';
    if ($token !== '') {
        db_exec('UPDATE admin_sessions SET revoked_at = NOW() WHERE token_hash = ?', [hash('sha256', $token)]);
    }

    setcookie(ADMIN_SESSION_COOKIE, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
}

function admin_audit(?int $adminUserId, string $action, ?string $entity = null, ?int $entityId = null, ?string $detail = null): void
{
    db_exec(
        'INSERT INTO admin_audit_log (admin_user_id, action, entity, entity_id, detail, ip) VALUES (?,?,?,?,?,?)',
        [$adminUserId, $action, $entity, $entityId, $detail, client_ip_binary()]
    );
}

// =====================================================================
// Step 1: email + password
// =====================================================================

/**
 * Verifies $email/$password. On success, starts the OTP step (generates
 * and emails a code, marks the pending session) and returns ['ok' => true].
 * On failure, returns a generic message that never reveals which of
 * email/password/account-status was wrong.
 */
function admin_login_step1(string $email, string $password): array
{
    ensure_session();

    $generic = ['ok' => false, 'message' => 'Incorrect email or password.'];

    $ip         = client_ip_binary();
    $maxPerHour = (int) admin_cfg('password.max_attempts_per_hour', 10);
    if ($ip !== null) {
        $recent = (int) db_value(
            "SELECT COUNT(*) FROM admin_audit_log
              WHERE action = 'login_failed' AND ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)",
            [$ip]
        );
        if ($recent >= $maxPerHour) {
            return ['ok' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
    }

    $user = db_one('SELECT * FROM admin_users WHERE email = ? AND is_active = 1', [$email]);

    // Verify against a real hash when one exists, a well-formed dummy one
    // otherwise (a malformed string gets rejected by password_verify()
    // almost instantly, which would defeat the point -- this needs a real
    // bcrypt hash so the computation actually runs). A wrong email can't
    // then be told apart from a wrong password by response timing.
    $hash = $user['password_hash'] ?? '$2y$12$ohR15DosAoG0Xpa/QQVVy.dXE.EIjviKcJ8neKh2D/8dRywZjb/x6';
    $passwordOk = password_verify($password, $hash);

    if ($user === null || $user['password_hash'] === null || !$passwordOk) {
        admin_audit(null, 'login_failed', 'admin_users', $user['id'] ?? null, $email);
        return $generic;
    }

    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $ttl  = (int) admin_cfg('otp.ttl_minutes', 10);

    db_exec(
        'INSERT INTO admin_otp_codes (admin_user_id, code_hash, expires_at, request_ip)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), ?)',
        [$user['id'], password_hash($code, PASSWORD_DEFAULT), $ttl, $ip]
    );

    $sent = send_mail(
        $user['email'],
        $user['name'],
        'Your Corediva admin login code',
        admin_otp_email_html($user['name'], $code, $ttl),
        "Your login code is {$code}. It expires in {$ttl} minutes."
    );

    $_SESSION['admin_otp_pending_user_id'] = $user['id'];
    $_SESSION['admin_otp_pending_email']   = $user['email'];
    unset($_SESSION['admin_dev_code']);

    // Only reachable when app.debug is true (dev machines only) and the
    // real send failed. Lets the flow be tested end-to-end without SMTP
    // configured; unreachable in production, where app.debug is false.
    if (!$sent && cfg('app.debug', false)) {
        error_log("[DEV] Admin OTP for {$user['email']}: {$code}");
        $_SESSION['admin_dev_code'] = $code;
    }

    return ['ok' => true, 'message' => ''];
}

// =====================================================================
// Step 2: OTP
// =====================================================================

/** Verifies $code against the pending login started by admin_login_step1(). */
function admin_otp_verify(string $code): array
{
    ensure_session();

    $userId = $_SESSION['admin_otp_pending_user_id'] ?? null;
    if ($userId === null) {
        return ['ok' => false, 'message' => 'Your login session expired. Please start again.'];
    }

    $otp = db_one(
        'SELECT * FROM admin_otp_codes
          WHERE admin_user_id = ? AND consumed_at IS NULL AND expires_at > NOW()
          ORDER BY id DESC LIMIT 1',
        [$userId]
    );

    if ($otp === null) {
        return ['ok' => false, 'message' => 'That code has expired. Request a new one.'];
    }

    $maxAttempts = (int) admin_cfg('otp.max_attempts', 5);
    if ((int) $otp['attempts'] >= $maxAttempts) {
        return ['ok' => false, 'message' => 'Too many incorrect attempts. Request a new code.'];
    }

    if (!password_verify($code, $otp['code_hash'])) {
        db_exec('UPDATE admin_otp_codes SET attempts = attempts + 1 WHERE id = ?', [$otp['id']]);
        return ['ok' => false, 'message' => 'That code is incorrect.'];
    }

    db_exec('UPDATE admin_otp_codes SET consumed_at = NOW() WHERE id = ?', [$otp['id']]);
    db_exec('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?', [$userId]);

    admin_create_session((int) $userId);
    admin_audit((int) $userId, 'login');

    unset(
        $_SESSION['admin_otp_pending_user_id'],
        $_SESSION['admin_otp_pending_email'],
        $_SESSION['admin_dev_code']
    );

    return ['ok' => true, 'message' => ''];
}

/**
 * The OTP email, built as a real table-based transactional template
 * (inline styles only, no <style> block, no flexbox/grid, no CSS
 * variables) rather than the bare <p> tags this used to be. Table
 * layout + inline styles is the boring, correct choice for email --
 * Outlook desktop renders with Word's engine and ignores most modern
 * CSS, so anything not inlined-on-the-element is a gamble.
 */
function admin_otp_email_html(string $name, string $code, int $ttlMinutes): string
{
    $safeName = esc($name);
    $safeCode = esc($code);
    $siteName = esc(setting('site_name', 'Corediva Tech Solutions'));
    $logoUrl  = esc(public_asset('imgs/corediva-logo.png'));

    return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="light">
        <title>Your admin sign-in code</title>
        </head>
        <body style="margin:0; padding:0; background-color:#F4F6FB; -webkit-text-size-adjust:100%;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F4F6FB;">
        <tr>
        <td align="center" style="padding:40px 16px;">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:440px; background-color:#FFFFFF; border-radius:16px; border:1px solid #E4E7F0;">
                <tr>
                    <td style="padding:32px 36px 0;">
                        <img src="{$logoUrl}" width="140" height="18" alt="{$siteName}" style="display:block; border:0; outline:0;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 36px 0; font-family:'DM Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0 0 6px; font-size:12.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#1351D8;">Admin sign-in</p>
                        <h1 style="margin:0 0 12px; font-size:21px; line-height:1.35; font-weight:700; color:#1C1C1C;">Hi {$safeName}, here's your code</h1>
                        <p style="margin:0; font-size:14.5px; line-height:1.6; color:#454545;">Enter this code to finish signing in. It expires in {$ttlMinutes} minutes.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 36px 4px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" style="background-color:#F3F6FD; border:1px solid #DCE4F7; border-radius:12px; padding:20px 24px;">
                                    <span style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace; font-size:32px; font-weight:700; letter-spacing:.3em; color:#1351D8;">{$safeCode}</span>
                                </td>
                            </tr>
                        </table>
                        <!-- Plain text, one selectable run -- a per-digit tile layout copies as
                             several separate clipboard lines instead of one pasteable code. -->
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 36px 32px; font-family:'DM Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0; font-size:13px; line-height:1.6; color:#8A8F9C;">Didn't request this? You can safely ignore this email. No changes were made to your account.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 36px; border-top:1px solid #EDEFF5; background-color:#FAFBFD; border-radius:0 0 16px 16px; font-family:'DM Sans',Arial,Helvetica,sans-serif;">
                        <p style="margin:0; font-size:12px; color:#9198A6;">{$siteName} &middot; This is an automated message, please don't reply.</p>
                    </td>
                </tr>
            </table>

        </td>
        </tr>
        </table>
        </body>
        </html>
    HTML;
}

// =====================================================================
// Password helpers (used by admin/users.php too)
// =====================================================================

function admin_validate_password(string $password): ?string
{
    $min = (int) admin_cfg('password.min_length', 8);
    if (mb_strlen($password) < $min) {
        return "Password must be at least {$min} characters.";
    }
    return null;
}
