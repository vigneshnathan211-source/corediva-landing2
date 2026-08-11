<?php
/**
 * Admin logout. POST-only (see the form in dashboard.php) so a logout
 * can't be triggered by just linking to this URL.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: ' . admin_url('dashboard.php'));
    exit;
}

$admin = admin_current_user();
if ($admin !== null) {
    admin_audit((int) $admin['id'], 'logout');
}

admin_destroy_session();

header('Location: ' . admin_url('login.php'));
exit;
