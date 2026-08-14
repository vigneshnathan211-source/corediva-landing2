<?php
/**
 * Shared admin sidebar nav. Expects $admin (current admin row, with
 * permissions) and $activeNav (nav item key, e.g. 'services') already in
 * scope. Grouped rather than a flat list so a new module joins an
 * existing group instead of just adding another item to one long row --
 * that's what let the previous topbar grow past being usable.
 */

declare(strict_types=1);

$adminNavGroups = [
    null => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => admin_url('dashboard.php'), 'show' => true],
    ],
    'Content' => [
        ['key' => 'services', 'label' => 'Services', 'href' => admin_url('services.php'), 'show' => in_array('services.view', $admin['permissions'], true)],
        ['key' => 'products', 'label' => 'Products', 'href' => admin_url('products.php'), 'show' => in_array('products.view', $admin['permissions'], true)],
        ['key' => 'blog', 'label' => 'Blog', 'href' => admin_url('blog.php'), 'show' => in_array('blog.view', $admin['permissions'], true)],
        ['key' => 'case_studies', 'label' => 'Case Studies', 'href' => admin_url('case-studies.php'), 'show' => in_array('case_studies.view', $admin['permissions'], true)],
        ['key' => 'seo', 'label' => 'SEO', 'href' => admin_url('seo.php'), 'show' => in_array('seo.view', $admin['permissions'], true)],
    ],
    'Engagement' => [
        ['key' => 'ai_chat', 'label' => 'AI Chat', 'href' => admin_url('ai-chat.php'), 'show' => in_array('ai_chat.view', $admin['permissions'], true)],
        ['key' => 'leads', 'label' => 'Leads', 'href' => admin_url('leads.php'), 'show' => in_array('leads.view', $admin['permissions'], true)],
    ],
    'Administration' => [
        ['key' => 'settings', 'label' => 'Settings', 'href' => admin_url('settings.php'), 'show' => in_array('settings.view', $admin['permissions'], true)],
        ['key' => 'users', 'label' => 'Admin Users', 'href' => admin_url('users.php'), 'show' => in_array('admins.manage', $admin['permissions'], true)],
        ['key' => 'roles', 'label' => 'Roles & Permissions', 'href' => admin_url('roles.php'), 'show' => in_array('admins.manage', $admin['permissions'], true)],
    ],
];
?>
<aside class="cd-admin-sidebar">
    <a href="<?= esc(admin_url('dashboard.php')) ?>" class="cd-admin-sidebar-logo">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" width="150" height="19">
    </a>

    <nav class="cd-admin-sidebar-nav" aria-label="Admin">
<?php foreach ($adminNavGroups as $groupLabel => $items): ?>
<?php
    $visible = array_filter($items, static fn ($item) => $item['show']);
    if (!$visible) {
        continue;
    }
?>
<?php if ($groupLabel !== null): ?>
        <p class="cd-admin-sidebar-group"><?= esc($groupLabel) ?></p>
<?php endif; ?>
<?php foreach ($visible as $item): ?>
        <a href="<?= esc($item['href']) ?>" class="cd-admin-sidebar-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"><?= esc($item['label']) ?></a>
<?php endforeach; ?>
<?php endforeach; ?>
    </nav>

    <div class="cd-admin-sidebar-user">
        <p class="cd-admin-sidebar-name"><?= esc($admin['name']) ?></p>
        <p class="cd-admin-sidebar-role"><?= esc($admin['role_name']) ?></p>
        <form method="post" action="<?= esc(admin_url('logout.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <button type="submit" class="cd-admin-logout">Log out</button>
        </form>
    </div>
</aside>
