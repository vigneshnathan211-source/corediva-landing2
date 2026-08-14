<?php
/**
 * Admin login, step 1: email + password.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();

if (admin_current_user() !== null) {
    header('Location: ' . admin_url('dashboard.php'));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } elseif (!empty($_POST['website'])) {
        // Honeypot: real users never fill a hidden field. Pretend success
        // so bots don't learn they were caught.
        header('Location: ' . admin_url('verify.php'));
        exit;
    } else {
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $errors[] = 'Enter your email and password.';
        } else {
            $result = admin_login_step1($email, $password);
            if (!$result['ok']) {
                $errors[] = $result['message'];
            } else {
                header('Location: ' . admin_url('verify.php'));
                exit;
            }
        }
    }
}

$prefillEmail = $_GET['email'] ?? '';

$pageTitle = 'Admin sign in';
require __DIR__ . '/includes/layout-header.php';
?>

<main class="cd-admin-auth">
    <div class="cd-admin-card">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" class="cd-admin-logo" width="180" height="23">

        <div class="cd-admin-auth-steps" aria-hidden="true">
            <span class="is-active"></span>
            <span></span>
        </div>

        <h1>Admin sign in</h1>
        <p class="cd-admin-lede">Enter your email and password. We'll then send a one-time code to confirm it's you.</p>

<?php if ($errors): ?>
        <div class="cd-admin-alert cd-admin-alert-error" role="alert">
<?php foreach ($errors as $e): ?>
            <p><?= esc($e) ?></p>
<?php endforeach; ?>
        </div>
<?php endif; ?>

        <form method="post" novalidate id="login-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

            <div class="cd-admin-hp" aria-hidden="true">
                <label>Website
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </label>
            </div>

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required autofocus
                   autocomplete="username" value="<?= esc($prefillEmail) ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required
                   autocomplete="current-password">

            <button type="submit" class="cd-admin-btn" id="login-submit">
                <span class="cd-admin-btn-spinner" aria-hidden="true"></span>
                <span class="cd-admin-btn-label">Continue</span>
            </button>
        </form>
    </div>
</main>

<script>
(function () {
    var form = document.getElementById('login-form');
    var btn = document.getElementById('login-submit');
    if (!form || !btn) { return; }
    form.addEventListener('submit', function () {
        if (btn.hasAttribute('data-pending')) { return; }
        btn.setAttribute('data-pending', '');
        btn.querySelector('.cd-admin-btn-label').textContent = 'Signing in…';
    });
})();
</script>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
