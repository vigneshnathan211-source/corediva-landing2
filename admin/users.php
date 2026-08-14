<?php
/**
 * Admin Users: create, edit, deactivate, assign roles, reset passwords.
 * Part of the Roles & Access module -- gated behind admins.manage.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'admins.manage');

$roles  = db_all('SELECT * FROM roles ORDER BY id');
$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'create') {
            $name     = trim((string) ($_POST['name'] ?? ''));
            $email    = trim((string) ($_POST['email'] ?? ''));
            $roleId   = (int) ($_POST['role_id'] ?? 0);
            $password = (string) ($_POST['password'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '')                                   { $errors[] = 'Enter a name.'; }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Enter a valid email.'; }
            if (!in_array($roleId, array_column($roles, 'id'), true))        { $errors[] = 'Choose a role.'; }
            if (($pwErr = admin_validate_password($password)) !== null)      { $errors[] = $pwErr; }

            if (!$errors && db_value('SELECT COUNT(*) FROM admin_users WHERE email = ?', [$email]) > 0) {
                $errors[] = 'That email already has an admin account.';
            }

            if (!$errors) {
                db_exec(
                    'INSERT INTO admin_users (email, password_hash, name, role_id, is_active) VALUES (?,?,?,?,?)',
                    [$email, password_hash($password, PASSWORD_DEFAULT), $name, $roleId, $isActive]
                );
                $newId = (int) db()->lastInsertId();
                admin_audit((int) $admin['id'], 'create', 'admin_users', $newId, $email);
                $notice = "Created {$name}.";
            }
        } elseif ($action === 'update') {
            $id       = (int) ($_POST['id'] ?? 0);
            $name     = trim((string) ($_POST['name'] ?? ''));
            $roleId   = (int) ($_POST['role_id'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $password = (string) ($_POST['password'] ?? '');

            $target = db_one('SELECT * FROM admin_users WHERE id = ?', [$id]);

            if ($target === null) {
                $errors[] = 'That admin no longer exists.';
            } elseif ($id === (int) $admin['id'] && $isActive === 0) {
                // Self-lockout guard: nobody can deactivate their own
                // account, so a mistake here can't lock every admin out.
                $errors[] = "You can't deactivate your own account.";
            } else {
                if ($name === '')                                            { $errors[] = 'Enter a name.'; }
                if (!in_array($roleId, array_column($roles, 'id'), true))     { $errors[] = 'Choose a role.'; }
                if ($password !== '' && ($pwErr = admin_validate_password($password)) !== null) {
                    $errors[] = $pwErr;
                }

                if (!$errors) {
                    if ($password !== '') {
                        db_exec(
                            'UPDATE admin_users SET name = ?, role_id = ?, is_active = ?, password_hash = ? WHERE id = ?',
                            [$name, $roleId, $isActive, password_hash($password, PASSWORD_DEFAULT), $id]
                        );
                        admin_audit((int) $admin['id'], 'update', 'admin_users', $id, 'profile + password reset');
                    } else {
                        db_exec(
                            'UPDATE admin_users SET name = ?, role_id = ?, is_active = ? WHERE id = ?',
                            [$name, $roleId, $isActive, $id]
                        );
                        admin_audit((int) $admin['id'], 'update', 'admin_users', $id, 'profile');
                    }
                    $notice = "Updated {$name}.";
                }
            }
        }
    }
}

$users = db_all(
    'SELECT u.id, u.email, u.name, u.is_active, u.last_login_at, r.name AS role_name
       FROM admin_users u
       JOIN roles r ON r.id = u.role_id
      ORDER BY u.name'
);

$editing = null;
if (isset($_GET['edit'])) {
    $editing = db_one('SELECT * FROM admin_users WHERE id = ?', [(int) $_GET['edit']]);
}

$pageTitle = 'Admin Users';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'users';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main cd-admin-main-wide">
    <h1>Admin Users</h1>
    <p class="cd-admin-lede">Who can sign in, and what role they hold.</p>

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
        <h2><?= $editing ? 'Edit ' . esc($editing['name']) : 'Add an admin user' ?></h2>

        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
<?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
<?php endif; ?>

            <div class="cd-admin-form-row">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required
                           value="<?= esc($editing['name'] ?? '') ?>">
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?= esc($editing['email'] ?? '') ?>"
<?php if ($editing): ?>
                           readonly title="Email can't be changed here."
<?php endif; ?>
                    >
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="role_id">Role</label>
                    <select id="role_id" name="role_id" required>
<?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id'] ?>"
<?php if (($editing['role_id'] ?? null) == $role['id']): ?> selected<?php endif; ?>>
                            <?= esc($role['name']) ?>
                        </option>
<?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="password"><?= $editing ? 'New password (leave blank to keep current)' : 'Password' ?></label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
<?php if (!$editing): ?> required<?php endif; ?>>
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_active" value="1"
<?php if (!$editing || $editing['is_active']): ?> checked<?php endif; ?>>
                Active (can sign in)
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editing ? 'Save changes' : 'Create admin user' ?></button>
<?php if ($editing): ?>
                <a href="<?= esc(admin_url('users.php')) ?>" class="cd-admin-btn-ghost">Cancel</a>
<?php endif; ?>
            </div>
        </form>
    </section>

    <section class="cd-admin-panel">
        <h2>All admin users</h2>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last login</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= esc($u['name']) ?><?= (int) $u['id'] === (int) $admin['id'] ? ' <span class="cd-admin-you">(you)</span>' : '' ?></td>
                        <td><?= esc($u['email']) ?></td>
                        <td><span class="cd-admin-badge"><?= esc($u['role_name']) ?></span></td>
                        <td>
<?php if ($u['is_active']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Active</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Inactive</span>
<?php endif; ?>
                        </td>
                        <td><?= $u['last_login_at'] ? esc($u['last_login_at']) : '—' ?></td>
                        <td><a href="<?= esc(admin_url('users.php?edit=' . $u['id'])) ?>">Edit</a></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
