<?php
/**
 * Admin dashboard. Content area is deliberately blank -- most modules
 * that will fill it (products, blog, case studies) aren't built yet.
 * The sidebar nav (admin-nav.php) gates each link on its own permission.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'dashboard';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main">
            <h1>Dashboard</h1>
            <p class="cd-admin-lede">Welcome back, <?= esc($admin['name']) ?>. Nothing's been built here yet.</p>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
