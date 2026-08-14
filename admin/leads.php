<?php
/**
 * Leads: every submission from save_lead() (hero, RFQ, and any future lead
 * form) lands in one `leads` table with no viewer until now. Filter by
 * status/country/search, update status inline, export the filtered set
 * to CSV.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'leads.view');
$canEdit   = in_array('leads.edit', $admin['permissions'], true);
$canExport = in_array('leads.export', $admin['permissions'], true);

const LEAD_STATUSES = ['new', 'contacted', 'qualified', 'closed', 'spam'];

$countries = get_countries();

$status    = in_array($_GET['status'] ?? '', LEAD_STATUSES, true) ? $_GET['status'] : '';
$countryId = (int) ($_GET['country'] ?? 0);
$q         = trim((string) ($_GET['q'] ?? ''));

/** Build the shared WHERE clause + params for both the list and the export. */
function leads_admin_filter(string $status, int $countryId, string $q): array
{
    $where  = [];
    $params = [];

    if ($status !== '') {
        $where[]  = 'status = ?';
        $params[] = $status;
    }
    if ($countryId > 0) {
        $where[]  = 'country_id = ?';
        $params[] = $countryId;
    }
    if ($q !== '') {
        $where[]  = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
        $like     = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    return [$sql, $params];
}

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } elseif (($_POST['action'] ?? '') === 'update_status') {
        $id        = (int) ($_POST['id'] ?? 0);
        $newStatus = (string) ($_POST['new_status'] ?? '');

        if (!in_array($newStatus, LEAD_STATUSES, true)) {
            $errors[] = 'Choose a valid status.';
        } else {
            $target = db_one('SELECT id FROM leads WHERE id = ?', [$id]);
            if ($target === null) {
                $errors[] = 'That lead no longer exists.';
            } else {
                db_exec('UPDATE leads SET status = ? WHERE id = ?', [$newStatus, $id]);
                admin_audit((int) $admin['id'], 'update', 'leads', $id, 'status -> ' . $newStatus);
                $notice = 'Lead updated.';
            }
        }
    }
}

// -------------------------------------------------------------------
// CSV export: full filtered set, not just the current page. Runs before
// any HTML output so headers can still be set.
// -------------------------------------------------------------------
if ($canExport && isset($_GET['export'])) {
    [$whereSql, $whereParams] = leads_admin_filter($status, $countryId, $q);
    $rows = db_all(
        "SELECT l.*, c.name AS country_name FROM leads l
         LEFT JOIN countries c ON c.id = l.country_id
         {$whereSql} ORDER BY l.created_at DESC",
        $whereParams
    );

    admin_audit((int) $admin['id'], 'export', 'leads', null, count($rows) . ' lead(s)');

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="leads-' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Created', 'Name', 'Email', 'Phone', 'Country', 'Service interest', 'Status', 'Message', 'Source URL']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['created_at'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['country_name'] ?? $row['country_code'],
            $row['service_interest'],
            $row['status'],
            $row['message'],
            $row['source_url'],
        ]);
    }
    fclose($out);
    exit;
}

// -------------------------------------------------------------------
// Paginated list
// -------------------------------------------------------------------
$perPage = 25;
$page    = max(1, (int) ($_GET['page'] ?? 1));

[$whereSql, $whereParams] = leads_admin_filter($status, $countryId, $q);

$total      = (int) db_value("SELECT COUNT(*) FROM leads {$whereSql}", $whereParams);
$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$leads = db_all(
    "SELECT l.*, c.name AS country_name FROM leads l
     LEFT JOIN countries c ON c.id = l.country_id
     {$whereSql} ORDER BY l.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
    $whereParams
);

$qs = 'status=' . urlencode($status) . '&country=' . $countryId . '&q=' . urlencode($q);

$pageTitle = 'Leads';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'leads';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main cd-admin-main-wide">
    <h1>Leads</h1>
    <p class="cd-admin-lede"><?= (int) $total ?> lead<?= $total === 1 ? '' : 's' ?> matching the current filters.</p>

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
        <form method="get" class="cd-admin-form-row cd-admin-selector">
            <div>
                <label for="f-status">Status</label>
                <select id="f-status" name="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
<?php foreach (LEAD_STATUSES as $s): ?>
                    <option value="<?= esc($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= esc(ucfirst($s)) ?></option>
<?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="f-country">Country</label>
                <select id="f-country" name="country" onchange="this.form.submit()">
                    <option value="0">All countries</option>
<?php foreach ($countries as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $countryId === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
<?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="f-q">Search (name, email, phone)</label>
                <input type="text" id="f-q" name="q" value="<?= esc($q) ?>">
            </div>
            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn-ghost">Filter</button>
<?php if ($canExport): ?>
                <a href="<?= esc(admin_url('leads.php?' . $qs . '&export=1')) ?>" class="cd-admin-btn">Export CSV</a>
<?php endif; ?>
            </div>
        </form>
    </section>

    <section class="cd-admin-panel">
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr>
                        <th>Received</th><th>Name</th><th>Email</th><th>Phone</th>
                        <th>Country</th><th>Interested in</th><th>Message</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?= esc($lead['created_at']) ?></td>
                        <td><?= esc($lead['name']) ?></td>
                        <td><a href="mailto:<?= esc($lead['email']) ?>"><?= esc($lead['email']) ?></a></td>
                        <td><?= esc($lead['phone'] ?? '—') ?></td>
                        <td><?= esc($lead['country_name'] ?? $lead['country_code'] ?? '—') ?></td>
                        <td><?= esc($lead['service_interest'] ?? '—') ?></td>
                        <td class="cd-admin-wrap-cell"><?= esc(mb_strimwidth((string) ($lead['message'] ?? ''), 0, 80, '…')) ?></td>
                        <td>
<?php if ($canEdit): ?>
                            <form method="post" class="cd-admin-inline-form">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?= (int) $lead['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()">
<?php foreach (LEAD_STATUSES as $s): ?>
                                    <option value="<?= esc($s) ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= esc(ucfirst($s)) ?></option>
<?php endforeach; ?>
                                </select>
                            </form>
<?php else: ?>
                            <span class="cd-admin-badge"><?= esc(ucfirst($lead['status'])) ?></span>
<?php endif; ?>
                        </td>
                    </tr>
<?php endforeach; ?>
<?php if (!$leads): ?>
                    <tr><td colspan="8">No leads match these filters.</td></tr>
<?php endif; ?>
                </tbody>
            </table>
        </div>

<?php if ($totalPages > 1): ?>
        <nav class="cd-admin-pagination" aria-label="Pages">
<?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= esc(admin_url('leads.php?' . $qs . '&page=' . $p)) ?>" class="cd-admin-page-link<?= $p === $page ? ' is-active' : '' ?>"><?= $p ?></a>
<?php endfor; ?>
        </nav>
<?php endif; ?>
    </section>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
