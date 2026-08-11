<?php
/**
 * Roles & Permissions: a permission matrix (roles x permissions) plus
 * creating/deleting custom roles. Part of the Roles & Access module --
 * gated behind admins.manage.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'admins.manage');

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'save_matrix') {
            $roles = db_all('SELECT id, slug FROM roles');
            $perms = db_all('SELECT id FROM permissions');
            $permIds = array_column($perms, 'id');

            $checked = $_POST['perm'] ?? []; // [roleId => [permId => '1', ...]]

            db()->beginTransaction();
            try {
                foreach ($roles as $role) {
                    // super_admin always has every permission -- editable
                    // here would risk one misclick locking every admin
                    // out of the module that fixes it.
                    if ($role['slug'] === 'super_admin') {
                        continue;
                    }
                    $roleChecked = array_map('intval', array_keys($checked[$role['id']] ?? []));
                    $roleChecked = array_values(array_intersect($roleChecked, $permIds));

                    db_exec('DELETE FROM role_permissions WHERE role_id = ?', [$role['id']]);
                    foreach ($roleChecked as $permId) {
                        db_exec('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)', [$role['id'], $permId]);
                    }
                }
                db()->commit();
                admin_audit((int) $admin['id'], 'update', 'role_permissions', null, 'permission matrix saved');
                $notice = 'Permissions updated.';
            } catch (\Throwable $e) {
                db()->rollBack();
                error_log('role_permissions save failed: ' . $e->getMessage());
                $errors[] = 'Could not save permissions. Nothing was changed.';
            }
        } elseif ($action === 'create_role') {
            $name        = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            if ($name === '') {
                $errors[] = 'Enter a role name.';
            } else {
                $slug = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower($name)), '_');
                if ($slug === '' || db_value('SELECT COUNT(*) FROM roles WHERE slug = ?', [$slug]) > 0) {
                    $errors[] = 'A role with that name already exists.';
                } else {
                    db_exec(
                        'INSERT INTO roles (slug, name, description, is_system) VALUES (?,?,?,0)',
                        [$slug, $name, $description !== '' ? $description : null]
                    );
                    $newId = (int) db()->lastInsertId();
                    admin_audit((int) $admin['id'], 'create', 'roles', $newId, $name);
                    $notice = "Created the {$name} role. Set its permissions below.";
                }
            }
        } elseif ($action === 'delete_role') {
            $roleId = (int) ($_POST['role_id'] ?? 0);
            $role   = db_one('SELECT * FROM roles WHERE id = ?', [$roleId]);

            if ($role === null) {
                $errors[] = 'That role no longer exists.';
            } elseif ((int) $role['is_system'] === 1) {
                $errors[] = 'System roles can\'t be deleted.';
            } else {
                try {
                    db_exec('DELETE FROM roles WHERE id = ?', [$roleId]);
                    admin_audit((int) $admin['id'], 'delete', 'roles', $roleId, $role['name']);
                    $notice = "Deleted the {$role['name']} role.";
                } catch (\Throwable $e) {
                    // fk_admin_role is ON DELETE RESTRICT -- this is the
                    // expected path when admins still hold the role, not
                    // an unexpected failure.
                    $errors[] = "Can't delete {$role['name']}: some admin users still hold it. Reassign them first.";
                }
            }
        }
    }
}

$roles       = db_all('SELECT * FROM roles ORDER BY is_system DESC, id');
$permissions = db_all('SELECT * FROM permissions ORDER BY module, slug');
$grantedRaw  = db_all('SELECT role_id, permission_id FROM role_permissions');

$granted = []; // [roleId][permId] = true
foreach ($grantedRaw as $row) {
    $granted[(int) $row['role_id']][(int) $row['permission_id']] = true;
}

$permsByModule = [];
foreach ($permissions as $p) {
    $permsByModule[$p['module']][] = $p;
}

$pageTitle = 'Roles & Permissions';
require __DIR__ . '/includes/layout-header.php';
?>

<header class="cd-admin-topbar">
    <a href="<?= esc(admin_url('dashboard.php')) ?>" class="cd-admin-topbar-logo">
        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
             alt="<?= esc(setting('site_name')) ?>" width="150" height="19">
    </a>
    <nav class="cd-admin-nav" aria-label="Admin">
        <a href="<?= esc(admin_url('dashboard.php')) ?>" class="cd-admin-nav-link">Dashboard</a>
        <a href="<?= esc(admin_url('users.php')) ?>" class="cd-admin-nav-link">Admin Users</a>
        <a href="<?= esc(admin_url('roles.php')) ?>" class="cd-admin-nav-link is-active">Roles &amp; Permissions</a>
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
    <h1>Roles &amp; Permissions</h1>
    <p class="cd-admin-lede">What each role can do. Super Admin always has every permission and can't be edited here.</p>

<?php if ($notice): ?>
    <div class="cd-admin-alert cd-admin-alert-ok"><p><?= esc($notice) ?></p></div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="cd-admin-alert cd-admin-alert-error" role="alert">
<?php foreach ($errors as $e): ?>
        <p><?= esc($e) ?></p>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <section class="cd-admin-panel">
        <h2>Permission matrix</h2>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_matrix">

            <div class="cd-admin-table-wrap">
                <table class="cd-admin-table cd-admin-matrix">
                    <thead>
                        <tr>
                            <th>Permission</th>
<?php foreach ($roles as $role): ?>
                            <th><?= esc($role['name']) ?></th>
<?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($permsByModule as $module => $perms): ?>
                        <tr class="cd-admin-matrix-module">
                            <td colspan="<?= 1 + count($roles) ?>"><?= esc(ucfirst($module)) ?></td>
                        </tr>
<?php foreach ($perms as $perm): ?>
                        <tr>
                            <td><?= esc($perm['name']) ?></td>
<?php foreach ($roles as $role): ?>
<?php $isSuper = $role['slug'] === 'super_admin'; ?>
                            <td class="cd-admin-matrix-cell">
                                <input type="checkbox"
                                       name="perm[<?= (int) $role['id'] ?>][<?= (int) $perm['id'] ?>]"
                                       value="1"
<?php if ($isSuper || isset($granted[(int) $role['id']][(int) $perm['id']])): ?> checked<?php endif; ?>
<?php if ($isSuper): ?> disabled<?php endif; ?>>
                            </td>
<?php endforeach; ?>
                        </tr>
<?php endforeach; ?>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save permissions</button>
            </div>
        </form>
    </section>

    <section class="cd-admin-panel">
        <h2>Add a role</h2>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="create_role">

            <div class="cd-admin-form-row">
                <div>
                    <label for="role_name">Name</label>
                    <input type="text" id="role_name" name="name" required placeholder="e.g. Blog Editor">
                </div>
                <div>
                    <label for="role_description">Description</label>
                    <input type="text" id="role_description" name="description" placeholder="Optional">
                </div>
            </div>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Create role</button>
            </div>
        </form>
    </section>

    <section class="cd-admin-panel">
        <h2>All roles</h2>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= esc($role['name']) ?></td>
                        <td><?= esc($role['description'] ?? '') ?></td>
                        <td>
<?php if ($role['is_system']): ?>
                            <span class="cd-admin-badge">System</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Custom</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if (!$role['is_system']): ?>
                            <form method="post" onsubmit="return confirm('Delete the <?= esc($role['name']) ?> role?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_role">
                                <input type="hidden" name="role_id" value="<?= (int) $role['id'] ?>">
                                <button type="submit" class="cd-admin-link-btn">Delete</button>
                            </form>
<?php endif; ?>
                        </td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
