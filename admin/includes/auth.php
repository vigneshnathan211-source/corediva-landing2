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

function admin_otp_email_html(string $name, string $code, int $ttlMinutes): string
{
    $safeName = esc($name);
    $safeCode = esc($code);
    return <<<HTML
        <p>Hi {$safeName},</p>
        <p>Your Corediva admin login code is:</p>
        <p style="font-size:28px;font-weight:700;letter-spacing:6px;">{$safeCode}</p>
        <p>This code expires in {$ttlMinutes} minutes. If you didn't request this, you can ignore this email.</p>
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
