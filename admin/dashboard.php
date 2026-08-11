<?php
/**
 * Admin dashboard. Content area is deliberately blank -- the modules
 * that will fill it (leads, services, products) aren't built yet. The
 * Admin Users / Roles & Permissions module is, and is linked from the
 * topbar nav below, gated behind the admins.manage permission.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();

$canManageAdmins = in_array('admins.manage', $admin['permissions'], true);

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/layout-header.php';
?>

<header class="cd-admin-topbar">
    <a href="<?= esc(admin_url('dashboard.php')) ?>" class="cd-admin-topbar-logo">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" width="150" height="19">
    </a>

    <nav class="cd-admin-nav" aria-label="Admin">
        <a href="<?= esc(admin_url('dashboard.php')) ?>" class="cd-admin-nav-link is-active">Dashboard</a>
<?php if ($canManageAdmins): ?>
        <a href="<?= esc(admin_url('users.php')) ?>" class="cd-admin-nav-link">Admin Users</a>
        <a href="<?= esc(admin_url('roles.php')) ?>" class="cd-admin-nav-link">Roles &amp; Permissions</a>
<?php endif; ?>
    </nav>

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

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
