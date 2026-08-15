<?php
/**
 * Case Studies: same plain list + create/edit/delete pattern as blog.php,
 * for the same reason -- case_studies.body is a single long-form field,
 * not a section stack, and rows are added over time rather than picked
 * from a fixed catalogue.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'case_studies.view');
$canEdit = in_array('case_studies.edit', $admin['permissions'], true);

$countries = get_countries();

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'save_case') {
            $id         = (int) ($_POST['id'] ?? 0);
            $slug       = trim((string) ($_POST['slug'] ?? ''));
            $countryId  = (int) ($_POST['country_id'] ?? 0) ?: null;
            $clientName = trim((string) ($_POST['client_name'] ?? '')) ?: null;
            $industry   = trim((string) ($_POST['industry'] ?? '')) ?: null;
            $summary    = trim((string) ($_POST['summary'] ?? '')) ?: null;
            $body       = trim((string) ($_POST['body'] ?? '')) ?: null;
            $results    = trim((string) ($_POST['results'] ?? '')) ?: null;
            $image      = trim((string) ($_POST['featured_image'] ?? '')) ?: null;
            $imageAlt   = trim((string) ($_POST['featured_image_alt'] ?? '')) ?: null;
            $metaTitle  = trim((string) ($_POST['meta_title'] ?? '')) ?: null;
            $metaDesc   = trim((string) ($_POST['meta_description'] ?? '')) ?: null;
            $sortOrder  = (int) ($_POST['sort_order'] ?? 0);
            $isPublished = isset($_POST['is_published']) ? 1 : 0;

            if ($slug === '' || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
                $errors[] = 'Slug must be lowercase letters, numbers and hyphens only.';
            }
            if (!$errors && db_value('SELECT COUNT(*) FROM case_studies WHERE slug = ? AND id <> ?', [$slug, $id]) > 0) {
                $errors[] = 'That slug is already used by another case study.';
            }

            if (!$errors) {
                if ($id > 0) {
                    db_exec(
                        'UPDATE case_studies SET
                            slug=?, country_id=?, client_name=?, industry=?, summary=?, body=?, results=?,
                            featured_image=?, featured_image_alt=?, meta_title=?, meta_description=?, sort_order=?, is_published=?
                         WHERE id = ?',
                        [$slug, $countryId, $clientName, $industry, $summary, $body, $results,
                         $image, $imageAlt, $metaTitle, $metaDesc, $sortOrder, $isPublished, $id]
                    );
                    admin_audit((int) $admin['id'], 'update', 'case_studies', $id, $slug);
                    $notice = 'Case study saved.';
                } else {
                    db_exec(
                        'INSERT INTO case_studies
                            (slug, country_id, client_name, industry, summary, body, results,
                             featured_image, featured_image_alt, meta_title, meta_description, sort_order, is_published)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                        [$slug, $countryId, $clientName, $industry, $summary, $body, $results,
                         $image, $imageAlt, $metaTitle, $metaDesc, $sortOrder, $isPublished]
                    );
                    $id = (int) db()->lastInsertId();
                    admin_audit((int) $admin['id'], 'create', 'case_studies', $id, $slug);
                    $notice = 'Case study created.';
                }
                header('Location: ' . admin_url('case-studies.php?edit=' . $id . '&saved=1'));
                exit;
            }
        } elseif ($action === 'delete_case') {
            $id     = (int) ($_POST['id'] ?? 0);
            $target = db_one('SELECT id, slug FROM case_studies WHERE id = ?', [$id]);
            if ($target === null) {
                $errors[] = 'That case study no longer exists.';
            } else {
                db_exec('DELETE FROM case_studies WHERE id = ?', [$id]);
                admin_audit((int) $admin['id'], 'delete', 'case_studies', $id, (string) $target['slug']);
                $notice = 'Case study deleted.';
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $notice = 'Case study saved.';
}

$editing = null;
$isNew   = isset($_GET['new']);
if (isset($_GET['edit'])) {
    $editing = db_one('SELECT * FROM case_studies WHERE id = ?', [(int) $_GET['edit']]);
    if ($editing === null) {
        $errors[] = 'That case study no longer exists.';
    }
}
$showForm = $isNew || $editing !== null;

$cases = db_all(
    'SELECT cs.*, c.name AS country_name FROM case_studies cs
     LEFT JOIN countries c ON c.id = cs.country_id
     ORDER BY cs.sort_order'
);

$pageTitle = 'Case Studies';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'case_studies';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main cd-admin-main-wide">
    <h1>Case Studies</h1>
    <p class="cd-admin-lede">Rows are unpublished by default. Nothing appears on the public site until "Published" is checked.</p>

<?php if (!$canEdit): ?>
    <div class="cd-admin-alert cd-admin-alert-info" role="status"><p>You have view-only access here. Changes are saved by someone with the case_studies.edit permission.</p></div>
<?php endif; ?>
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

<?php if ($showForm): ?>
<?php if ($canEdit): ?>
    <section class="cd-admin-panel">
        <h2><?= $editing ? 'Edit case study' : 'New case study' ?></h2>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_case">
<?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
<?php endif; ?>

            <div class="cd-admin-form-row">
                <div>
                    <label for="client_name">Client name</label>
                    <input type="text" id="client_name" name="client_name" value="<?= esc($editing['client_name'] ?? '') ?>">
                </div>
                <div>
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" required value="<?= esc($editing['slug'] ?? '') ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="country_id">Country</label>
                    <select id="country_id" name="country_id">
                        <option value="0" <?= empty($editing['country_id']) ? 'selected' : '' ?>>&mdash; All countries &mdash;</option>
<?php foreach ($countries as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($editing['country_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="industry">Industry</label>
                    <input type="text" id="industry" name="industry" value="<?= esc($editing['industry'] ?? '') ?>">
                </div>
                <div>
                    <label for="sort_order">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0) ?>">
                </div>
            </div>

            <div>
                <label for="summary">Summary (used on the hub card)</label>
                <textarea id="summary" name="summary" rows="2"><?= esc($editing['summary'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="body">Body</label>
                <textarea id="body" name="body" rows="8" data-rich-text><?= esc($editing['body'] ?? '') ?></textarea>
                <span class="cd-admin-hint">Renders directly on the public page.</span>
            </div>

            <div>
                <label for="results">Results</label>
                <textarea id="results" name="results" rows="4" data-rich-text><?= esc($editing['results'] ?? '') ?></textarea>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="featured_image">Featured image URL</label>
                    <input type="text" id="featured_image" name="featured_image" value="<?= esc($editing['featured_image'] ?? '') ?>">
                </div>
                <div>
                    <label for="featured_image_alt">Image alt text</label>
                    <input type="text" id="featured_image_alt" name="featured_image_alt" value="<?= esc($editing['featured_image_alt'] ?? '') ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="meta_title">Meta title</label>
                    <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= esc($editing['meta_title'] ?? '') ?>">
                </div>
                <div>
                    <label for="meta_description">Meta description</label>
                    <input type="text" id="meta_description" name="meta_description" maxlength="320" value="<?= esc($editing['meta_description'] ?? '') ?>">
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_published" value="1" <?= !empty($editing['is_published']) ? 'checked' : '' ?>>
                Published
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editing ? 'Save changes' : 'Create case study' ?></button>
                <a href="<?= esc(admin_url('case-studies.php')) ?>" class="cd-admin-btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
<?php endif; ?>
<?php endif; ?>

    <section class="cd-admin-panel">
        <div class="cd-admin-form-actions" style="margin-bottom: 1rem;">
<?php if ($canEdit): ?>
            <a href="<?= esc(admin_url('case-studies.php?new=1')) ?>" class="cd-admin-btn">New case study</a>
<?php endif; ?>
        </div>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr><th>Client</th><th>Country</th><th>Industry</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
<?php foreach ($cases as $case): ?>
                    <tr>
                        <td><?= esc($case['client_name'] ?? $case['slug']) ?></td>
                        <td><?= esc($case['country_name'] ?? 'All countries') ?></td>
                        <td><?= esc($case['industry'] ?? '—') ?></td>
                        <td>
<?php if ($case['is_published']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Published</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Draft</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('case-studies.php?edit=' . $case['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" class="cd-admin-inline-form" onsubmit="return confirm('Delete this case study?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_case">
                                <input type="hidden" name="id" value="<?= (int) $case['id'] ?>">
                                <button type="submit" class="cd-admin-link-btn">Delete</button>
                            </form>
<?php endif; ?>
                        </td>
                    </tr>
<?php endforeach; ?>
<?php if (!$cases): ?>
                    <tr><td colspan="5">No case studies yet.</td></tr>
<?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
