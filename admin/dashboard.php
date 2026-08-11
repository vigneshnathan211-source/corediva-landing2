<?php
/**
 * Admin dashboard. Deliberately blank -- the modules that will fill it
 * (leads, services, products, admin users) aren't built yet.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

ensure_session();
$admin = require_admin_login();

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/admin-header.php';
?>

<header class="cd-admin-topbar">
    <a href="<?= esc(admin_url('dashboard.php')) ?>" class="cd-admin-topbar-logo">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" width="150" height="19">
    </a>

    <div class="cd-admin-topbar-user">
        <span class="cd-admin-topbar-name"><?= esc($admin['name']) ?></span>
        <span class="cd-admin-topbar-role"><?= esc($admin['role_name']) ?></span>
        <form method="post" action="<?= esc(admin_url('logout.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <button type="submit" class="cd-admin-logout">Log out</button>
        </form>
    </div>
</header>

<main class="cd-admin-main">
    <h1>Dashboard</h1>
    <p class="cd-admin-lede">Welcome back, <?= esc($admin['name']) ?>. Nothing's been built here yet.</p>
</main>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
