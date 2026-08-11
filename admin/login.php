<?php
/**
 * Admin login, step 1: request a one-time code.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

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
        $email = trim((string) ($_POST['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } else {
            $result = admin_otp_request($email);
            if (!$result['ok']) {
                $errors[] = $result['message'];
            } else {
                header('Location: ' . admin_url('verify.php'));
                exit;
            }
        }
    }
}

$prefillEmail = $_GET['email'] ?? ($_SESSION['admin_otp_pending_email'] ?? '');

$pageTitle = 'Admin sign in';
require __DIR__ . '/../includes/admin-header.php';
?>

<main class="cd-admin-auth">
    <div class="cd-admin-card">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" class="cd-admin-logo" width="180" height="23">

        <h1>Admin sign in</h1>
        <p class="cd-admin-lede">Enter your admin email and we'll send you a one-time login code.</p>

<?php if ($errors): ?>
        <div class="cd-admin-alert cd-admin-alert-error" role="alert">
<?php foreach ($errors as $e): ?>
            <p><?= esc($e) ?></p>
<?php endforeach; ?>
        </div>
<?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

            <div class="cd-admin-hp" aria-hidden="true">
                <label>Website
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </label>
            </div>

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required autofocus
                   autocomplete="username" value="<?= esc($prefillEmail) ?>">

            <button type="submit" class="cd-admin-btn">Send login code</button>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
