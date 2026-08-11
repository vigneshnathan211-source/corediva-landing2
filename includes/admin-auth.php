<?php
/**
 * Admin authentication: passwordless email OTP + a DB-backed session
 * (admin_sessions), separate from the public site's PHP session.
 *
 * A DB-backed session, rather than plain $_SESSION, is what lets a
 * session be individually revoked (logout, or an admin disabling another
 * user) and audited -- that's the whole reason admin_sessions exists as
 * its own table instead of relying on PHP's file-based session store.
 * The short-lived step between "code emailed" and "code entered" is the
 * one place this flow still uses the public $_SESSION, purely to carry
 * the pending user ID across the two requests.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mailer.php';

const ADMIN_SESSION_COOKIE = 'cd_admin_session';

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

/**
 * The signed-in admin (id, email, name, role_id, role_slug, role_name),
 * or null. Reads the admin_sessions cookie -- entirely separate from the
 * public CSRF session.
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

/** Issues a fresh DB-backed session and sets its cookie. */
function admin_create_session(int $adminUserId): void
{
    $token = bin2hex(random_bytes(32));
    $hours = (int) cfg('security.session_hours', 8);

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
// OTP issuance + verification
// =====================================================================

/**
 * Requests a login code for $email.
 *
 * Always returns the same "ok" shape and message whether or not $email
 * belongs to an active admin -- an attacker submitting a guessed address
 * gets no signal either way. A code is only generated and sent, and the
 * pending-user marker only set, when the account actually exists.
 */
function admin_otp_request(string $email): array
{
    ensure_session();

    $generic = ['ok' => true, 'message' => "If that email has admin access, we've sent a login code."];

    $ip         = client_ip_binary();
    $maxPerHour = (int) cfg('security.otp_max_per_hour', 5);
    if ($ip !== null) {
        $recent = (int) db_value(
            'SELECT COUNT(*) FROM admin_otp_codes WHERE request_ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)',
            [$ip]
        );
        if ($recent >= $maxPerHour) {
            return ['ok' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
    }

    // The pending email is remembered even when no account matches, so
    // the next step (admin_otp_verify) can fail with the same "incorrect
    // code" message a real account with a mistyped code would get,
    // rather than a distinguishable "no such session" message.
    $_SESSION['admin_otp_pending_email'] = $email;
    unset($_SESSION['admin_otp_pending_user_id'], $_SESSION['admin_dev_code']);

    $user = db_one('SELECT * FROM admin_users WHERE email = ? AND is_active = 1', [$email]);
    if ($user === null) {
        return $generic;
    }

    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $ttl  = (int) cfg('security.otp_ttl_minutes', 10);

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

    $result = $generic;
    // Only reachable when app.debug is true (dev machines only) and the
    // real send failed -- the default state until config.local.php's
    // mail.* placeholders are filled in with real SMTP credentials. Lets
    // the flow be tested end-to-end without them.
    if (!$sent && cfg('app.debug', false)) {
        error_log("[DEV] Admin OTP for {$email}: {$code}");
        $_SESSION['admin_dev_code'] = $code;
    }

    return $result;
}

/** Verifies $code against the pending login started by admin_otp_request(). */
function admin_otp_verify(string $code): array
{
    ensure_session();

    $pendingEmail = $_SESSION['admin_otp_pending_email'] ?? null;
    if ($pendingEmail === null) {
        return ['ok' => false, 'message' => 'Your login session expired. Please start again.'];
    }

    $userId = $_SESSION['admin_otp_pending_user_id'] ?? null;
    if ($userId === null) {
        // No account behind this email -- fail exactly like a wrong code
        // would, so this step can't be used to confirm which emails have
        // admin access.
        return ['ok' => false, 'message' => 'That code is incorrect.'];
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

    $maxAttempts = (int) cfg('security.otp_max_attempts', 5);
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
