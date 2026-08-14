<?php
/**
 * SEO: admin-editable title/meta description/noindex for the pages that
 * have no content table of their own to hang meta fields off (home, about,
 * services hub). service_content already carries its own meta_title/
 * meta_description per service/country -- this screen is the same idea
 * for the pages built before that pattern existed.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'seo.view');
$canEdit = in_array('seo.edit', $admin['permissions'], true);

const PAGE_SEO_KEYS = [
    'home'         => 'Home',
    'about'        => 'Who We Are',
    'services'     => 'What We Do (hub)',
    'products'     => 'Products (hub)',
    'blog'         => 'Blog (hub)',
    'case-studies' => 'Case Studies (hub)',
];

$countries = get_countries();
if (!$countries) {
    http_response_code(500);
    exit('Countries are not configured.');
}

$pageKey   = array_key_exists($_GET['page'] ?? '', PAGE_SEO_KEYS) ? $_GET['page'] : array_key_first(PAGE_SEO_KEYS);
$countryId = (int) ($_GET['country'] ?? $countries[0]['id']);

$country = null;
foreach ($countries as $c) {
    if ((int) $c['id'] === $countryId) {
        $country = $c;
        break;
    }
}
if (!$country) {
    http_response_code(404);
    exit('Unknown country.');
}

$qs = "page={$pageKey}&country={$countryId}";

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $metaTitle = trim((string) ($_POST['meta_title'] ?? '')) ?: null;
        $metaDesc  = trim((string) ($_POST['meta_description'] ?? '')) ?: null;
        $isNoindex = isset($_POST['is_noindex']) ? 1 : 0;

        db_exec(
            'INSERT INTO page_seo (page_key, country_id, meta_title, meta_description, is_noindex)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
                meta_title = VALUES(meta_title), meta_description = VALUES(meta_description),
                is_noindex = VALUES(is_noindex)',
            [$pageKey, $countryId, $metaTitle, $metaDesc, $isNoindex]
        );
        admin_audit((int) $admin['id'], 'update', 'page_seo', null, $pageKey . '/' . $country['code']);
        $notice = 'SEO settings saved.';
    }
}

$row = db_one('SELECT * FROM page_seo WHERE page_key = ? AND country_id = ?', [$pageKey, $countryId]);

$pageTitle = 'SEO';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'seo';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main">
    <h1>SEO</h1>
    <p class="cd-admin-lede">Title, meta description and indexing for pages that don't have their own content table.
       Leave a field blank to fall back to the page's built-in default copy.</p>

<?php if (!$canEdit): ?>
    <div class="cd-admin-alert cd-admin-alert-info" role="status"><p>You have view-only access here. Changes are saved by someone with the seo.edit permission.</p></div>
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

    <section class="cd-admin-panel cd-admin-panel-narrow">
        <form method="get" class="cd-admin-form-row cd-admin-selector">
            <div>
                <label for="sel-page">Page</label>
                <select id="sel-page" name="page" onchange="this.form.submit()">
<?php foreach (PAGE_SEO_KEYS as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= $key === $pageKey ? 'selected' : '' ?>><?= esc($label) ?></option>
<?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="sel-country">Country</label>
                <select id="sel-country" name="country" onchange="this.form.submit()">
<?php foreach ($countries as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $countryId ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
<?php endforeach; ?>
                </select>
            </div>
        </form>
    </section>

    <section class="cd-admin-panel cd-admin-panel-narrow">
        <h2><?= esc(PAGE_SEO_KEYS[$pageKey]) ?> &mdash; <?= esc($country['name']) ?></h2>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">

            <div>
                <label for="meta_title">Meta title</label>
                <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= esc($row['meta_title'] ?? '') ?>">
            </div>

            <div>
                <label for="meta_description">Meta description</label>
                <input type="text" id="meta_description" name="meta_description" maxlength="320" value="<?= esc($row['meta_description'] ?? '') ?>">
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_noindex" value="1" <?= !empty($row['is_noindex']) ? 'checked' : '' ?>>
                Hide from search engines (noindex)
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save SEO settings</button>
            </div>
        </form>
<?php else: ?>
        <p><strong>Meta title:</strong> <?= esc($row['meta_title'] ?? '(using page default)') ?></p>
        <p><strong>Meta description:</strong> <?= esc($row['meta_description'] ?? '(using page default)') ?></p>
        <p><strong>Noindex:</strong> <?= !empty($row['is_noindex']) ? 'Yes' : 'No' ?></p>
<?php endif; ?>
    </section>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
