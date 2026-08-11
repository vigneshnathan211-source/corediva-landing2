<?php
/**
 * Admin login, step 2: enter the code that was emailed after the
 * email + password step.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();

if (admin_current_user() !== null) {
    header('Location: ' . admin_url('dashboard.php'));
    exit;
}

$pendingEmail = $_SESSION['admin_otp_pending_email'] ?? null;
if ($pendingEmail === null) {
    header('Location: ' . admin_url('login.php'));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $code   = trim((string) ($_POST['code'] ?? ''));
        $result = admin_otp_verify($code);
        if ($result['ok']) {
            header('Location: ' . admin_url('dashboard.php'));
            exit;
        }
        $errors[] = $result['message'];
    }
}

// Only ever set in debug mode, and only when the real send failed --
// see admin_login_step1(). Never present in production.
$devCode = cfg('app.debug', false) ? ($_SESSION['admin_dev_code'] ?? null) : null;

$pageTitle = 'Enter your code';
require __DIR__ . '/includes/layout-header.php';
?>

<main class="cd-admin-auth">
    <div class="cd-admin-card">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" class="cd-admin-logo" width="180" height="23">

        <h1>Enter your code</h1>
        <p class="cd-admin-lede">We sent a 6-digit code to <strong><?= esc($pendingEmail) ?></strong>.
           It expires in <?= esc((string) admin_cfg('otp.ttl_minutes', 10)) ?> minutes.</p>

<?php if ($devCode): ?>
        <div class="cd-admin-alert cd-admin-alert-dev">
            <p><strong>Dev mode</strong> — SMTP send failed, so the code wasn't actually
               emailed. Your code: <strong><?= esc($devCode) ?></strong></p>
        </div>
<?php endif; ?>

<?php if ($errors): ?>
        <div class="cd-admin-alert cd-admin-alert-error" role="alert">
<?php foreach ($errors as $e): ?>
            <p><?= esc($e) ?></p>
<?php endforeach; ?>
        </div>
<?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

            <label for="code">6-digit code</label>
            <input type="text" id="code" name="code" required autofocus
                   inputmode="numeric" pattern="[0-9]*" maxlength="6"
                   autocomplete="one-time-code" class="cd-admin-otp-input">

            <button type="submit" class="cd-admin-btn">Verify &amp; sign in</button>
        </form>

        <a class="cd-admin-resend" href="<?= esc(admin_url('login.php?email=' . urlencode($pendingEmail))) ?>">
            Didn't get it? Sign in again to resend
        </a>
    </div>
</main>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
