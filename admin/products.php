<?php
/**
 * Products: the country-independent base row (products), per-country page
 * basics (product_content), and the section stack (product_page_sections)
 * a product-detail page is built from. Mirrors admin/services.php's
 * three-tab pattern exactly, minus the region dimension -- no product
 * currently ships region-specific copy, so product_content/
 * product_page_sections have no region_id to pick.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'products.view');
$canEdit = in_array('products.edit', $admin['permissions'], true);

// Admin needs to see (and re-activate) inactive products too, so this
// queries the table directly rather than reusing the public get_products()
// getter, which filters is_active=1 for the live site.
$products  = db_all('SELECT * FROM products ORDER BY sort_order');
$countries = get_countries();

if (!$products || !$countries) {
    http_response_code(500);
    exit('Products or countries are not configured.');
}

$productId = (int) ($_GET['product'] ?? $products[0]['id']);
$countryId = (int) ($_GET['country'] ?? $countries[0]['id']);

$product = null;
foreach ($products as $p) {
    if ((int) $p['id'] === $productId) {
        $product = $p;
        break;
    }
}
$country = null;
foreach ($countries as $c) {
    if ((int) $c['id'] === $countryId) {
        $country = $c;
        break;
    }
}
if (!$product || !$country) {
    http_response_code(404);
    exit('Unknown product or country.');
}

$tab = in_array($_GET['tab'] ?? '', ['info', 'basics', 'sections'], true) ? $_GET['tab'] : 'basics';
$qs  = "product={$productId}&country={$countryId}";

$errors = [];
$notice = null;

/** Build the ordered items[] JSON for a section from repeated form rows, shape depends on $type. */
function products_admin_build_items(string $type, array $post): ?array
{
    $icons  = $post['item_icon']  ?? [];
    $titles = $post['item_title'] ?? [];
    $texts  = $post['item_text']  ?? [];
    $count  = max(count($icons), count($titles), count($texts));

    $items = [];
    for ($i = 0; $i < $count; $i++) {
        $icon  = trim((string) ($icons[$i]  ?? ''));
        $title = trim((string) ($titles[$i] ?? ''));
        $text  = trim((string) ($texts[$i]  ?? ''));

        if ($type === 'deliverables') {
            if ($text !== '') {
                $items[] = $text;
            }
            continue;
        }
        if ($title === '' && $text === '') {
            continue;
        }
        $row = ['title' => $title, 'text' => $text];
        if ($type === 'feature_grid' && $icon !== '') {
            $row = ['icon' => $icon] + $row;
        }
        $items[] = $row;
    }

    return $items ? $items : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        // -----------------------------------------------------------
        // Product info (products -- country-independent base row)
        // -----------------------------------------------------------
        if ($action === 'save_info') {
            $title      = trim((string) ($_POST['title'] ?? ''));
            $slug       = trim((string) ($_POST['slug'] ?? ''));
            $icon       = trim((string) ($_POST['icon'] ?? '')) ?: 'box';
            $category   = trim((string) ($_POST['category'] ?? '')) ?: null;
            $groupLabel = trim((string) ($_POST['group_label'] ?? '')) ?: null;
            $shortDesc  = trim((string) ($_POST['short_description'] ?? '')) ?: null;
            $coreDesc   = trim((string) ($_POST['core_description'] ?? '')) ?: null;
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $sortOrder  = (int) ($_POST['sort_order'] ?? 0);
            $isActive   = isset($_POST['is_active']) ? 1 : 0;

            if ($title === '') {
                $errors[] = 'Enter a product title.';
            }
            if ($slug === '' || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
                $errors[] = 'Slug must be lowercase letters, numbers and hyphens only.';
            }
            if (!$errors && db_value('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?', [$slug, $productId]) > 0) {
                $errors[] = 'That slug is already used by another product.';
            }

            if (!$errors) {
                db_exec(
                    'UPDATE products SET
                        title=?, slug=?, icon=?, category=?, group_label=?, short_description=?,
                        core_description=?, is_featured=?, sort_order=?, is_active=?
                     WHERE id = ?',
                    [$title, $slug, $icon, $category, $groupLabel, $shortDesc, $coreDesc, $isFeatured, $sortOrder, $isActive, $productId]
                );
                admin_audit((int) $admin['id'], 'update', 'products', $productId, $slug);
                $notice = 'Product info saved.';

                $products = db_all('SELECT * FROM products ORDER BY sort_order');
                foreach ($products as $p) {
                    if ((int) $p['id'] === $productId) {
                        $product = $p;
                        break;
                    }
                }
            }

        // -----------------------------------------------------------
        // Page basics (product_content)
        // -----------------------------------------------------------
        } elseif ($action === 'save_basics') {
            $title       = trim((string) ($_POST['title'] ?? '')) ?: null;
            $h1          = trim((string) ($_POST['h1'] ?? '')) ?: null;
            $intro       = trim((string) ($_POST['intro'] ?? '')) ?: null;
            $localProof  = trim((string) ($_POST['local_proof'] ?? '')) ?: null;
            $metaTitle   = trim((string) ($_POST['meta_title'] ?? '')) ?: null;
            $metaDesc    = trim((string) ($_POST['meta_description'] ?? '')) ?: null;
            $isPublished = isset($_POST['is_published']) ? 1 : 0;

            db_exec(
                'INSERT INTO product_content
                    (product_id, country_id, title, h1, intro, local_proof, meta_title, meta_description, is_published)
                 VALUES (?,?,?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                    title = VALUES(title), h1 = VALUES(h1), intro = VALUES(intro),
                    local_proof = VALUES(local_proof), meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description), is_published = VALUES(is_published)',
                [$productId, $countryId, $title, $h1, $intro, $localProof, $metaTitle, $metaDesc, $isPublished]
            );
            admin_audit((int) $admin['id'], 'update', 'product_content', $productId, $product['slug'] . '/' . $country['code']);
            $notice = 'Page basics saved.';

        // -----------------------------------------------------------
        // Sections (product_page_sections)
        // -----------------------------------------------------------
        } elseif ($action === 'create_section' || $action === 'update_section') {
            $id             = (int) ($_POST['id'] ?? 0);
            $sectionType    = (string) ($_POST['section_type'] ?? '');
            $heading        = trim((string) ($_POST['heading'] ?? '')) ?: null;
            $subheading     = trim((string) ($_POST['subheading'] ?? '')) ?: null;
            $body           = trim((string) ($_POST['body'] ?? '')) ?: null;
            $mediaUrl       = trim((string) ($_POST['media_url'] ?? '')) ?: null;
            $mediaAlt       = trim((string) ($_POST['media_alt'] ?? '')) ?: null;
            $imagePosition  = in_array($_POST['image_position'] ?? '', ['left', 'right'], true) ? $_POST['image_position'] : 'right';
            $ctaLabel       = trim((string) ($_POST['cta_label'] ?? '')) ?: null;
            $ctaUrl         = trim((string) ($_POST['cta_url'] ?? '')) ?: null;
            $isPublished    = isset($_POST['is_published']) ? 1 : 0;

            if (!array_key_exists($sectionType, PRODUCT_SECTION_TYPES)) {
                $errors[] = 'Choose a valid section type.';
            }

            $items = products_admin_build_items($sectionType, $_POST);
            $itemsJson = $items !== null ? json_encode($items, JSON_UNESCAPED_UNICODE) : null;

            if (!$errors && $action === 'create_section') {
                $sortOrder = (int) db_value(
                    'SELECT COALESCE(MAX(sort_order), 0) FROM product_page_sections WHERE product_id = ? AND country_id = ?',
                    [$productId, $countryId]
                ) + 10;

                db_exec(
                    'INSERT INTO product_page_sections
                        (product_id, country_id, section_type, heading, subheading, body, items, media_url, media_alt, image_position, cta_label, cta_url, sort_order, is_published)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    [$productId, $countryId, $sectionType, $heading, $subheading, $body, $itemsJson, $mediaUrl, $mediaAlt, $imagePosition, $ctaLabel, $ctaUrl, $sortOrder, $isPublished]
                );
                $newId = (int) db()->lastInsertId();
                admin_audit((int) $admin['id'], 'create', 'product_page_sections', $newId, $sectionType);
                $notice = 'Section added.';
            } elseif (!$errors) {
                $target = db_one('SELECT * FROM product_page_sections WHERE id = ? AND product_id = ? AND country_id = ?', [$id, $productId, $countryId]);
                if ($target === null) {
                    $errors[] = 'That section no longer exists.';
                } else {
                    db_exec(
                        'UPDATE product_page_sections SET
                            section_type=?, heading=?, subheading=?, body=?, items=?, media_url=?, media_alt=?,
                            image_position=?, cta_label=?, cta_url=?, is_published=?
                         WHERE id = ?',
                        [$sectionType, $heading, $subheading, $body, $itemsJson, $mediaUrl, $mediaAlt, $imagePosition, $ctaLabel, $ctaUrl, $isPublished, $id]
                    );
                    admin_audit((int) $admin['id'], 'update', 'product_page_sections', $id, $sectionType);
                    $notice = 'Section updated.';
                }
            }

        } elseif ($action === 'delete_section') {
            $id     = (int) ($_POST['id'] ?? 0);
            $target = db_one('SELECT * FROM product_page_sections WHERE id = ? AND product_id = ? AND country_id = ?', [$id, $productId, $countryId]);
            if ($target === null) {
                $errors[] = 'That section no longer exists.';
            } else {
                db_exec('DELETE FROM product_page_sections WHERE id = ?', [$id]);
                admin_audit((int) $admin['id'], 'delete', 'product_page_sections', $id, (string) $target['section_type']);
                $notice = 'Section deleted.';
            }

        // -----------------------------------------------------------
        // Drag-and-drop reorder: whole new sort_order sequence at once.
        // -----------------------------------------------------------
        } elseif ($action === 'reorder_sections') {
            $validIds = array_map('intval', array_column(
                db_all('SELECT id FROM product_page_sections WHERE product_id = ? AND country_id = ?', [$productId, $countryId]),
                'id'
            ));
            $order = array_values(array_filter(
                array_map('intval', $_POST['order'] ?? []),
                static fn ($id) => in_array($id, $validIds, true)
            ));

            if (!$order) {
                $errors[] = 'Nothing to reorder.';
            } else {
                db()->beginTransaction();
                try {
                    $sort = 10;
                    foreach ($order as $sectionId) {
                        db_exec('UPDATE product_page_sections SET sort_order = ? WHERE id = ?', [$sort, $sectionId]);
                        $sort += 10;
                    }
                    db()->commit();
                    admin_audit((int) $admin['id'], 'update', 'product_page_sections', null, 'reordered ' . count($order) . ' section(s)');
                    $notice = 'Section order updated.';
                } catch (Throwable $e) {
                    db()->rollBack();
                    $errors[] = 'Could not save the new order. Please try again.';
                }
            }
        }
    }
}

$basics = db_one('SELECT * FROM product_content WHERE product_id = ? AND country_id = ?', [$productId, $countryId]);

$editingSection = null;
if ($tab === 'sections' && isset($_GET['edit'])) {
    $editingSection = db_one('SELECT * FROM product_page_sections WHERE id = ? AND product_id = ? AND country_id = ?', [(int) $_GET['edit'], $productId, $countryId]);
}

$sectionsList = db_all('SELECT * FROM product_page_sections WHERE product_id = ? AND country_id = ? ORDER BY sort_order', [$productId, $countryId]);

$editingItems = [];
if ($editingSection && !empty($editingSection['items'])) {
    $decoded = json_decode((string) $editingSection['items'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $row) {
            $editingItems[] = is_array($row) ? $row : ['text' => $row];
        }
    }
}
while (count($editingItems) < 6) {
    $editingItems[] = ['icon' => '', 'title' => '', 'text' => ''];
}

$pageTitle = 'Products';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'products';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main">
    <h1>Products</h1>
    <p class="cd-admin-lede">Pick a product and a country to edit that page's H1/intro/meta, then build its body as an ordered stack of sections.</p>

<?php if (!$canEdit): ?>
    <div class="cd-admin-alert cd-admin-alert-info" role="status"><p>You have view-only access here. Changes are saved by someone with the products.edit permission.</p></div>
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

    <section class="cd-admin-panel">
        <form method="get" class="cd-admin-form-row cd-admin-selector">
            <div>
                <label for="sel-product">Product</label>
                <select id="sel-product" name="product" onchange="this.form.submit()">
<?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (int) $p['id'] === $productId ? 'selected' : '' ?>><?= esc($p['title']) ?></option>
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
            <input type="hidden" name="tab" value="<?= esc($tab) ?>">
        </form>
    </section>

    <div class="cd-admin-tabs">
        <a href="<?= esc(admin_url('products.php?' . $qs . '&tab=info')) ?>" class="cd-admin-tab<?= $tab === 'info' ? ' is-active' : '' ?>">Product info</a>
        <a href="<?= esc(admin_url('products.php?' . $qs . '&tab=basics')) ?>" class="cd-admin-tab<?= $tab === 'basics' ? ' is-active' : '' ?>">Page basics</a>
        <a href="<?= esc(admin_url('products.php?' . $qs . '&tab=sections')) ?>" class="cd-admin-tab<?= $tab === 'sections' ? ' is-active' : '' ?>">Sections (<?= count($sectionsList) ?>)</a>
    </div>

<?php if ($tab === 'info'): ?>

    <section class="cd-admin-panel">
        <h2>Product info &mdash; <?= esc($product['title']) ?></h2>
        <p class="cd-admin-lede">The country-independent base row: title, slug, icon, category/group and short
           description. This is what every country's page falls back to.</p>
        <p class="cd-admin-hint">Changing the slug does not rename the existing <code>sg/products/*.php</code>
           file on disk &mdash; its links will 404 until the file is renamed to match.</p>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_info">

            <div class="cd-admin-form-row">
                <div>
                    <label for="info_title">Title</label>
                    <input type="text" id="info_title" name="title" required value="<?= esc($product['title']) ?>">
                </div>
                <div>
                    <label for="info_slug">Slug</label>
                    <input type="text" id="info_slug" name="slug" required value="<?= esc($product['slug']) ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="info_icon">Icon class</label>
                    <input type="text" id="info_icon" name="icon" value="<?= esc($product['icon']) ?>" placeholder="las la-box">
                </div>
                <div>
                    <label for="info_category">Category (card label)</label>
                    <input type="text" id="info_category" name="category" value="<?= esc($product['category'] ?? '') ?>">
                </div>
                <div>
                    <label for="info_group">Group (tab grouping)</label>
                    <input type="text" id="info_group" name="group_label" value="<?= esc($product['group_label'] ?? '') ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="info_sort">Sort order</label>
                    <input type="number" id="info_sort" name="sort_order" value="<?= (int) $product['sort_order'] ?>">
                </div>
            </div>

            <div>
                <label for="info_short">Short description (one line, used on cards/grids)</label>
                <input type="text" id="info_short" name="short_description" maxlength="500" value="<?= esc($product['short_description'] ?? '') ?>">
            </div>

            <div>
                <label for="info_core_desc">Core description (HTML, country-independent long copy)</label>
                <textarea id="info_core_desc" name="core_description" rows="5"><?= esc($product['core_description'] ?? '') ?></textarea>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                Featured
            </label>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                Active
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save product info</button>
            </div>
        </form>
<?php endif; ?>
    </section>

<?php elseif ($tab === 'basics'): ?>

    <section class="cd-admin-panel">
        <h2>Page basics &mdash; <?= esc($product['title']) ?> / <?= esc($country['name']) ?></h2>
        <p class="cd-admin-lede">The H1, intro and meta tags for this page. Leave a field blank to fall back to the product's default copy.</p>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_basics">

            <div class="cd-admin-form-row">
                <div>
                    <label for="title">Nav/card title override</label>
                    <input type="text" id="title" name="title" value="<?= esc($basics['title'] ?? '') ?>" placeholder="<?= esc($product['title']) ?>">
                </div>
                <div>
                    <label for="h1">H1</label>
                    <input type="text" id="h1" name="h1" value="<?= esc($basics['h1'] ?? '') ?>" placeholder="<?= esc($product['title']) ?>">
                </div>
            </div>

            <div>
                <label for="intro">Intro (HTML, renders raw on the page)</label>
                <textarea id="intro" name="intro" rows="4"><?= esc($basics['intro'] ?? '') ?></textarea>
                <span class="cd-admin-hint">This is admin-authored HTML and is not escaped on the public page -- only people with products.edit can write it.</span>
            </div>

            <div>
                <label for="local_proof">Local proof (HTML)</label>
                <textarea id="local_proof" name="local_proof" rows="3"><?= esc($basics['local_proof'] ?? '') ?></textarea>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="meta_title">Meta title</label>
                    <input type="text" id="meta_title" name="meta_title" maxlength="255" value="<?= esc($basics['meta_title'] ?? '') ?>">
                </div>
                <div>
                    <label for="meta_description">Meta description</label>
                    <input type="text" id="meta_description" name="meta_description" maxlength="320" value="<?= esc($basics['meta_description'] ?? '') ?>">
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_published" value="1" <?= (!$basics || $basics['is_published']) ? 'checked' : '' ?>>
                Published
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save page basics</button>
            </div>
        </form>
<?php endif; ?>
    </section>

<?php else: /* sections */ ?>

<?php if ($canEdit): ?>
    <section class="cd-admin-panel">
        <h2><?= $editingSection ? 'Edit section' : 'Add section' ?></h2>
        <form method="post" class="cd-admin-form" id="section-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editingSection ? 'update_section' : 'create_section' ?>">
<?php if ($editingSection): ?>
            <input type="hidden" name="id" value="<?= (int) $editingSection['id'] ?>">
<?php endif; ?>

            <div>
                <label for="section_type">Section type</label>
                <select id="section_type" name="section_type">
<?php foreach (PRODUCT_SECTION_TYPES as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= ($editingSection['section_type'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
<?php endforeach; ?>
                </select>
<?php if (!$editingSection): ?>
                <span class="cd-admin-hint">New sections join the end of the stack. Drag rows in the list below to reorder.</span>
<?php endif; ?>
            </div>

            <div>
                <label for="heading">Heading</label>
                <input type="text" id="heading" name="heading" value="<?= esc($editingSection['heading'] ?? '') ?>">
            </div>

            <div data-section-fields="feature_grid">
                <label for="subheading">Subheading</label>
                <input type="text" id="subheading" name="subheading" value="<?= esc($editingSection['subheading'] ?? '') ?>">
            </div>

            <div data-section-fields="rich_text,image_feature,cta_banner">
                <label for="body">Body (HTML, renders raw)</label>
                <textarea id="body" name="body" rows="5"><?= esc($editingSection['body'] ?? '') ?></textarea>
                <span class="cd-admin-hint">Admin-authored HTML, not escaped on the public page.</span>
            </div>

            <div class="cd-admin-form-row" data-section-fields="image_feature">
                <div>
                    <label for="media_url">Image URL</label>
                    <input type="text" id="media_url" name="media_url" value="<?= esc($editingSection['media_url'] ?? '') ?>" placeholder="/assets/imgs/service-details.jpg">
                </div>
                <div>
                    <label for="media_alt">Image alt text</label>
                    <input type="text" id="media_alt" name="media_alt" value="<?= esc($editingSection['media_alt'] ?? '') ?>">
                </div>
                <div>
                    <label for="image_position">Image side</label>
                    <select id="image_position" name="image_position">
                        <option value="right" <?= ($editingSection['image_position'] ?? 'right') === 'right' ? 'selected' : '' ?>>Right</option>
                        <option value="left" <?= ($editingSection['image_position'] ?? '') === 'left' ? 'selected' : '' ?>>Left</option>
                    </select>
                </div>
            </div>

            <div class="cd-admin-form-row" data-section-fields="cta_banner">
                <div>
                    <label for="cta_label">CTA label</label>
                    <input type="text" id="cta_label" name="cta_label" value="<?= esc($editingSection['cta_label'] ?? '') ?>">
                </div>
                <div>
                    <label for="cta_url">CTA URL</label>
                    <input type="text" id="cta_url" name="cta_url" value="<?= esc($editingSection['cta_url'] ?? '') ?>" placeholder="#rfq-form">
                </div>
            </div>

            <div data-section-fields="feature_grid,process_steps,deliverables">
                <label>Items</label>
                <span class="cd-admin-hint">Feature grid: icon class (e.g. <code>las la-check</code>), title, text. Process steps: title + text. Deliverables: text only, one per row.</span>
<?php foreach ($editingItems as $item): ?>
                <div class="cd-admin-form-row cd-admin-item-row">
                    <div data-item-field="icon">
                        <input type="text" name="item_icon[]" value="<?= esc($item['icon'] ?? '') ?>" placeholder="Icon class">
                    </div>
                    <div data-item-field="title">
                        <input type="text" name="item_title[]" value="<?= esc($item['title'] ?? '') ?>" placeholder="Title">
                    </div>
                    <div data-item-field="text">
                        <input type="text" name="item_text[]" value="<?= esc($item['text'] ?? '') ?>" placeholder="Text">
                    </div>
                </div>
<?php endforeach; ?>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_published" value="1" <?= (!$editingSection || $editingSection['is_published']) ? 'checked' : '' ?>>
                Published
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editingSection ? 'Save changes' : 'Add section' ?></button>
<?php if ($editingSection): ?>
                <a href="<?= esc(admin_url('products.php?' . $qs . '&tab=sections')) ?>" class="cd-admin-btn-ghost">Cancel</a>
<?php endif; ?>
            </div>
        </form>
    </section>
<?php endif; ?>

    <section class="cd-admin-panel">
        <h2>Section stack</h2>
<?php if ($canEdit && count($sectionsList) > 1): ?>
        <p class="cd-admin-lede">Drag a row by its handle to reorder. The new order saves as soon as you drop it.</p>
<?php endif; ?>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr><th></th><th>Type</th><th>Heading</th><th>Status</th><th></th></tr>
                </thead>
                <tbody id="section-drag-body">
<?php foreach ($sectionsList as $sec): ?>
                    <tr<?php if ($canEdit): ?> draggable="true" data-id="<?= (int) $sec['id'] ?>" class="cd-admin-drag-row"<?php endif; ?>>
                        <td class="cd-admin-drag-handle-cell">
<?php if ($canEdit): ?>
                            <span class="cd-admin-drag-handle" title="Drag to reorder" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="4" cy="3" r="1.3" fill="currentColor"/>
                                    <circle cx="10" cy="3" r="1.3" fill="currentColor"/>
                                    <circle cx="4" cy="7" r="1.3" fill="currentColor"/>
                                    <circle cx="10" cy="7" r="1.3" fill="currentColor"/>
                                    <circle cx="4" cy="11" r="1.3" fill="currentColor"/>
                                    <circle cx="10" cy="11" r="1.3" fill="currentColor"/>
                                </svg>
                            </span>
<?php endif; ?>
                        </td>
                        <td><?= esc(PRODUCT_SECTION_TYPES[$sec['section_type']] ?? $sec['section_type']) ?></td>
                        <td><?= esc($sec['heading'] ?? '—') ?></td>
                        <td>
<?php if ($sec['is_published']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Published</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Hidden</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('products.php?' . $qs . '&tab=sections&edit=' . $sec['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this section?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_section">
                                <input type="hidden" name="id" value="<?= (int) $sec['id'] ?>">
                                <button type="submit" class="cd-admin-link-btn">Delete</button>
                            </form>
<?php endif; ?>
                        </td>
                    </tr>
<?php endforeach; ?>
<?php if (!$sectionsList): ?>
                    <tr><td colspan="5">No sections yet for this product/country.</td></tr>
<?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php if ($canEdit): ?>
    <form method="post" id="reorder-form" style="display:none">
        <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
        <input type="hidden" name="action" value="reorder_sections">
    </form>
<?php endif; ?>

<?php endif; ?>
        </main>
    </div>
</div>

<script>
(function () {
    var select = document.getElementById('section_type');
    if (!select) { return; }
    function sync() {
        var type = select.value;
        document.querySelectorAll('[data-section-fields]').forEach(function (el) {
            var types = el.getAttribute('data-section-fields').split(',');
            el.style.display = types.indexOf(type) === -1 ? 'none' : '';
        });
        var showIcon = type === 'feature_grid';
        var showTitle = type === 'feature_grid' || type === 'process_steps';
        document.querySelectorAll('[data-item-field="icon"]').forEach(function (el) { el.style.display = showIcon ? '' : 'none'; });
        document.querySelectorAll('[data-item-field="title"]').forEach(function (el) { el.style.display = showTitle ? '' : 'none'; });
    }
    select.addEventListener('change', sync);
    sync();
})();

(function () {
    var body = document.getElementById('section-drag-body');
    var form = document.getElementById('reorder-form');
    if (!body || !form) { return; }

    var dragRow = null;

    body.addEventListener('dragstart', function (e) {
        dragRow = e.target.closest('.cd-admin-drag-row');
        if (!dragRow) { return; }
        dragRow.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragRow.getAttribute('data-id'));
    });

    body.addEventListener('dragover', function (e) {
        if (!dragRow) { return; }
        e.preventDefault();
        var overRow = e.target.closest('.cd-admin-drag-row');
        if (!overRow || overRow === dragRow) { return; }
        var rect = overRow.getBoundingClientRect();
        var putBefore = (e.clientY - rect.top) < rect.height / 2;
        body.insertBefore(dragRow, putBefore ? overRow : overRow.nextSibling);
    });

    body.addEventListener('dragend', function () {
        if (!dragRow) { return; }
        dragRow.classList.remove('is-dragging');
        dragRow = null;

        form.querySelectorAll('input[name="order[]"]').forEach(function (i) { i.remove(); });
        body.querySelectorAll('.cd-admin-drag-row').forEach(function (row) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order[]';
            input.value = row.getAttribute('data-id');
            form.appendChild(input);
        });
        form.submit();
    });
})();
</script>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
