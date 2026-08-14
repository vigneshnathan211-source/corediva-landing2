<?php
/**
 * Settings: the site chrome that isn't content -- topbar contact info +
 * social links, the main navbar menu, and the footer's identity/contact
 * settings + office list. Three tabs over the same page rather than three
 * files, since they share one permission (settings.view / settings.edit)
 * and none of them is big enough alone to earn its own screen.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

ensure_session();
$admin = require_admin_login();
require_permission($admin, 'settings.view');
$canEdit = in_array('settings.edit', $admin['permissions'], true);

/** Upsert a batch of site_settings rows in one call. */
function save_site_settings(array $kv): void
{
    foreach ($kv as $key => $value) {
        db_exec(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
    }
}

$tab = in_array($_GET['tab'] ?? '', ['topbar', 'navbar', 'footer'], true) ? $_GET['tab'] : 'topbar';

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        $errors[] = "You don't have permission to make changes here.";
    } elseif (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reload the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        // ---------------------------------------------------------------
        // Topbar: contact settings
        // ---------------------------------------------------------------
        if ($action === 'save_topbar_settings') {
            $phoneDisplay = trim((string) ($_POST['phone_display'] ?? ''));
            $phoneE164    = trim((string) ($_POST['phone_e164'] ?? ''));
            $email        = trim((string) ($_POST['email'] ?? ''));

            if ($phoneDisplay === '')                                             { $errors[] = 'Enter a phone number to display.'; }
            if ($phoneE164 === '' || !preg_match('/^\+[0-9]{7,15}$/', $phoneE164)) { $errors[] = 'Enter a valid E.164 phone number, e.g. +6580001234.'; }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))       { $errors[] = 'Enter a valid email address.'; }

            if (!$errors) {
                save_site_settings([
                    'phone_display' => $phoneDisplay,
                    'phone_e164'    => $phoneE164,
                    'email'         => $email,
                ]);
                admin_audit((int) $admin['id'], 'update', 'site_settings', null, 'topbar contact settings');
                $notice = 'Topbar settings saved.';
            }

        // ---------------------------------------------------------------
        // Topbar: social links
        // ---------------------------------------------------------------
        } elseif ($action === 'create_social' || $action === 'update_social') {
            $id       = (int) ($_POST['id'] ?? 0);
            $platform = trim((string) ($_POST['platform'] ?? ''));
            $url      = trim((string) ($_POST['url'] ?? ''));
            $icon     = trim((string) ($_POST['icon'] ?? '')) ?: 'link';
            $sort     = (int) ($_POST['sort_order'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($platform === '')                                     { $errors[] = 'Enter a platform name.'; }
            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) { $errors[] = 'Enter a valid URL.'; }

            if (!$errors && db_value('SELECT COUNT(*) FROM social_links WHERE platform = ? AND id <> ?', [$platform, $id]) > 0) {
                $errors[] = 'A social link for that platform already exists.';
            }

            if (!$errors && $action === 'create_social') {
                db_exec(
                    'INSERT INTO social_links (platform, url, icon, sort_order, is_active) VALUES (?,?,?,?,?)',
                    [$platform, $url, $icon, $sort, $isActive]
                );
                $newId = (int) db()->lastInsertId();
                admin_audit((int) $admin['id'], 'create', 'social_links', $newId, $platform);
                $notice = "Added {$platform}.";
            } elseif (!$errors) {
                $target = db_one('SELECT * FROM social_links WHERE id = ?', [$id]);
                if ($target === null) {
                    $errors[] = 'That social link no longer exists.';
                } else {
                    db_exec(
                        'UPDATE social_links SET platform=?, url=?, icon=?, sort_order=?, is_active=? WHERE id=?',
                        [$platform, $url, $icon, $sort, $isActive, $id]
                    );
                    admin_audit((int) $admin['id'], 'update', 'social_links', $id, $platform);
                    $notice = "Updated {$platform}.";
                }
            }

        } elseif ($action === 'delete_social') {
            $id     = (int) ($_POST['id'] ?? 0);
            $target = db_one('SELECT * FROM social_links WHERE id = ?', [$id]);
            if ($target === null) {
                $errors[] = 'That social link no longer exists.';
            } else {
                db_exec('DELETE FROM social_links WHERE id = ?', [$id]);
                admin_audit((int) $admin['id'], 'delete', 'social_links', $id, $target['platform']);
                $notice = "Deleted {$target['platform']}.";
            }

        // ---------------------------------------------------------------
        // Navbar: menu items
        // ---------------------------------------------------------------
        } elseif ($action === 'create_nav_item' || $action === 'update_nav_item') {
            $id          = (int) ($_POST['id'] ?? 0);
            $label       = trim((string) ($_POST['label'] ?? ''));
            $url         = trim((string) ($_POST['url'] ?? ''));
            $parentId    = (int) ($_POST['parent_id'] ?? 0) ?: null;
            $columnGroup = trim((string) ($_POST['column_group'] ?? ''));
            $megaType    = trim((string) ($_POST['mega_type'] ?? ''));
            $sort        = (int) ($_POST['sort_order'] ?? 0);
            $isActive    = isset($_POST['is_active']) ? 1 : 0;

            if ($label === '') {
                $errors[] = 'Enter a label.';
            }
            if ($megaType !== '' && $parentId !== null) {
                $errors[] = 'A mega-menu item must be top-level -- clear its parent first.';
            }
            if (!$errors && $parentId !== null) {
                if ($id !== 0 && $parentId === $id) {
                    $errors[] = "An item can't be its own parent.";
                } elseif (db_value('SELECT COUNT(*) FROM nav_items WHERE id = ? AND parent_id IS NULL', [$parentId]) == 0) {
                    $errors[] = 'Choose a valid top-level parent.';
                }
            }

            if (!$errors) {
                $isMega = $megaType !== '' ? 1 : 0;
                $params = [
                    $label,
                    $url !== '' ? $url : null,
                    $parentId,
                    $columnGroup !== '' ? $columnGroup : null,
                    $isMega,
                    $megaType !== '' ? $megaType : null,
                    $sort,
                    $isActive,
                ];

                if ($action === 'create_nav_item') {
                    db_exec(
                        'INSERT INTO nav_items (label, url, parent_id, column_group, is_mega, mega_type, sort_order, is_active)
                         VALUES (?,?,?,?,?,?,?,?)',
                        $params
                    );
                    $newId = (int) db()->lastInsertId();
                    admin_audit((int) $admin['id'], 'create', 'nav_items', $newId, $label);
                    $notice = "Added \"{$label}\" to the menu.";
                } else {
                    $target = db_one('SELECT * FROM nav_items WHERE id = ?', [$id]);
                    if ($target === null) {
                        $errors[] = 'That menu item no longer exists.';
                    } else {
                        $params[] = $id;
                        db_exec(
                            'UPDATE nav_items SET label=?, url=?, parent_id=?, column_group=?, is_mega=?, mega_type=?, sort_order=?, is_active=? WHERE id=?',
                            $params
                        );
                        admin_audit((int) $admin['id'], 'update', 'nav_items', $id, $label);
                        $notice = "Updated \"{$label}\".";
                    }
                }
            }

        } elseif ($action === 'delete_nav_item') {
            $id     = (int) ($_POST['id'] ?? 0);
            $target = db_one('SELECT * FROM nav_items WHERE id = ?', [$id]);
            if ($target === null) {
                $errors[] = 'That menu item no longer exists.';
            } else {
                $childCount = (int) db_value('SELECT COUNT(*) FROM nav_items WHERE parent_id = ?', [$id]);
                db_exec('DELETE FROM nav_items WHERE id = ?', [$id]); // fk_nav_parent cascades any children
                admin_audit((int) $admin['id'], 'delete', 'nav_items', $id, $target['label']);
                $notice = "Deleted \"{$target['label']}\"" . ($childCount > 0 ? " and {$childCount} sub-item(s)." : '.');
            }

        // ---------------------------------------------------------------
        // Footer: identity + contact settings
        // ---------------------------------------------------------------
        } elseif ($action === 'save_footer_settings') {
            $siteName    = trim((string) ($_POST['site_name'] ?? ''));
            $siteTagline = trim((string) ($_POST['site_tagline'] ?? ''));
            $responseHrs = trim((string) ($_POST['response_time_hours'] ?? ''));
            $credentials = trim((string) ($_POST['credentials'] ?? ''));
            $waNumber    = trim((string) ($_POST['whatsapp_number'] ?? ''));
            $waPrefill   = trim((string) ($_POST['whatsapp_prefill_text'] ?? ''));

            if ($siteName === '')                                    { $errors[] = 'Enter a site name.'; }
            if ($responseHrs !== '' && !ctype_digit($responseHrs))    { $errors[] = 'Response time must be a whole number of hours.'; }
            if ($waNumber !== '' && !preg_match('/^[0-9]{7,15}$/', $waNumber)) {
                $errors[] = 'WhatsApp number must be digits only, with country code and no leading +, e.g. 6580001234.';
            }

            if (!$errors) {
                save_site_settings([
                    'site_name'             => $siteName,
                    'site_tagline'          => $siteTagline,
                    'response_time_hours'   => $responseHrs,
                    'credentials'           => $credentials,
                    'whatsapp_number'       => $waNumber,
                    'whatsapp_prefill_text' => $waPrefill,
                ]);
                admin_audit((int) $admin['id'], 'update', 'site_settings', null, 'footer settings');
                $notice = 'Footer settings saved.';
            }

        // ---------------------------------------------------------------
        // Footer: offices
        // ---------------------------------------------------------------
        } elseif ($action === 'create_office' || $action === 'update_office') {
            $id          = (int) ($_POST['id'] ?? 0);
            $city        = trim((string) ($_POST['city'] ?? ''));
            $badge       = trim((string) ($_POST['badge'] ?? ''));
            $address     = trim((string) ($_POST['address'] ?? ''));
            $phone       = trim((string) ($_POST['phone'] ?? ''));
            $officeEmail = trim((string) ($_POST['email'] ?? ''));
            $countryId   = (int) ($_POST['country_id'] ?? 0) ?: null;
            $isHq        = isset($_POST['is_hq']) ? 1 : 0;
            $sort        = (int) ($_POST['sort_order'] ?? 0);
            $isActive    = isset($_POST['is_active']) ? 1 : 0;

            if ($city === '')                                                             { $errors[] = 'Enter a city.'; }
            if ($officeEmail !== '' && !filter_var($officeEmail, FILTER_VALIDATE_EMAIL))   { $errors[] = 'Enter a valid office email, or leave it blank.'; }

            if (!$errors) {
                $params = [$countryId, $city, $badge ?: null, $address ?: null, $phone ?: null, $officeEmail ?: null, $isHq, $isActive, $sort];

                if ($action === 'create_office') {
                    db_exec(
                        'INSERT INTO offices (country_id, city, badge, address, phone, email, is_hq, is_active, sort_order)
                         VALUES (?,?,?,?,?,?,?,?,?)',
                        $params
                    );
                    $newId = (int) db()->lastInsertId();
                    admin_audit((int) $admin['id'], 'create', 'offices', $newId, $city);
                    $notice = "Added the {$city} office.";
                } else {
                    $target = db_one('SELECT * FROM offices WHERE id = ?', [$id]);
                    if ($target === null) {
                        $errors[] = 'That office no longer exists.';
                    } else {
                        $params[] = $id;
                        db_exec(
                            'UPDATE offices SET country_id=?, city=?, badge=?, address=?, phone=?, email=?, is_hq=?, is_active=?, sort_order=? WHERE id=?',
                            $params
                        );
                        admin_audit((int) $admin['id'], 'update', 'offices', $id, $city);
                        $notice = "Updated the {$city} office.";
                    }
                }
            }

        } elseif ($action === 'delete_office') {
            $id     = (int) ($_POST['id'] ?? 0);
            $target = db_one('SELECT * FROM offices WHERE id = ?', [$id]);
            if ($target === null) {
                $errors[] = 'That office no longer exists.';
            } else {
                db_exec('DELETE FROM offices WHERE id = ?', [$id]);
                admin_audit((int) $admin['id'], 'delete', 'offices', $id, $target['city']);
                $notice = "Deleted the {$target['city']} office.";
            }
        }
    }
}

// =========================================================================
// Fresh data for whichever tab renders (POST handling above always runs
// first, so a save on this request is already reflected below -- including
// through setting(), whose request-wide cache hasn't been primed yet at
// this point in the file).
// =========================================================================

$topbarSettings = [
    'phone_display' => (string) setting('phone_display', ''),
    'phone_e164'    => (string) setting('phone_e164', ''),
    'email'         => (string) setting('email', ''),
];
$socialLinks   = db_all('SELECT * FROM social_links ORDER BY sort_order');
$editingSocial = isset($_GET['edit_social']) ? db_one('SELECT * FROM social_links WHERE id = ?', [(int) $_GET['edit_social']]) : null;

// Admin listing needs every item, active or not -- get_nav_tree() filters
// to is_active = 1, which is right for the public menu but would hide the
// very rows an admin needs to re-enable.
$allNavItems  = db_all('SELECT * FROM nav_items ORDER BY sort_order');
$navParents   = array_values(array_filter($allNavItems, static fn (array $i) => $i['parent_id'] === null));
$navChildren  = [];
foreach ($allNavItems as $i) {
    if ($i['parent_id'] !== null) {
        $navChildren[(int) $i['parent_id']][] = $i;
    }
}
$editingNavItem = isset($_GET['edit_nav']) ? db_one('SELECT * FROM nav_items WHERE id = ?', [(int) $_GET['edit_nav']]) : null;

$footerSettings = [
    'site_name'             => (string) setting('site_name', ''),
    'site_tagline'          => (string) setting('site_tagline', ''),
    'response_time_hours'   => (string) setting('response_time_hours', '4'),
    'credentials'           => (string) setting('credentials', ''),
    'whatsapp_number'       => (string) setting('whatsapp_number', ''),
    'whatsapp_prefill_text' => (string) setting('whatsapp_prefill_text', ''),
];
$offices = db_all(
    'SELECT o.*, c.name AS country_name
       FROM offices o
       LEFT JOIN countries c ON c.id = o.country_id
      ORDER BY o.sort_order'
);
$editingOffice = isset($_GET['edit_office']) ? db_one('SELECT * FROM offices WHERE id = ?', [(int) $_GET['edit_office']]) : null;
$allCountries  = get_countries();

$pageTitle = 'Settings';
require __DIR__ . '/includes/layout-header.php';
$activeNav = 'settings';
?>
<div class="cd-admin-shell">
<?php require __DIR__ . '/includes/admin-nav.php'; ?>
    <div class="cd-admin-content">
        <main class="cd-admin-main">
    <h1>Settings</h1>
    <p class="cd-admin-lede">Topbar contact info, the main menu, and the footer -- the site chrome that isn't page content.</p>

<?php if (!$canEdit): ?>
    <div class="cd-admin-alert cd-admin-alert-info" role="status"><p>You have view-only access here. Changes are saved by someone with the settings.edit permission.</p></div>
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

    <div class="cd-admin-tabs">
        <a href="<?= esc(admin_url('settings.php?tab=topbar')) ?>" class="cd-admin-tab<?= $tab === 'topbar' ? ' is-active' : '' ?>">Topbar</a>
        <a href="<?= esc(admin_url('settings.php?tab=navbar')) ?>" class="cd-admin-tab<?= $tab === 'navbar' ? ' is-active' : '' ?>">Navbar Menu</a>
        <a href="<?= esc(admin_url('settings.php?tab=footer')) ?>" class="cd-admin-tab<?= $tab === 'footer' ? ' is-active' : '' ?>">Footer</a>
    </div>

<?php if ($tab === 'topbar'): ?>

    <section class="cd-admin-panel">
        <h2>Contact info</h2>
        <p class="cd-admin-lede">The phone and email shown in the topbar, and reused for the mega-menu call-out and the footer's contact list.</p>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_topbar_settings">

            <div class="cd-admin-form-row">
                <div>
                    <label for="phone_display">Phone (display)</label>
                    <input type="text" id="phone_display" name="phone_display" required
                           value="<?= esc($topbarSettings['phone_display']) ?>" placeholder="+91 89038 55325">
                </div>
                <div>
                    <label for="phone_e164">Phone (tel: link, E.164)</label>
                    <input type="tel" id="phone_e164" name="phone_e164" required
                           value="<?= esc($topbarSettings['phone_e164']) ?>" placeholder="+918903855325">
                </div>
            </div>
            <div>
                <label for="topbar_email">Email</label>
                <input type="email" id="topbar_email" name="email" required
                       value="<?= esc($topbarSettings['email']) ?>">
            </div>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save contact info</button>
            </div>
        </form>
<?php endif; ?>
    </section>

    <section class="cd-admin-panel">
        <h2><?= $editingSocial ? 'Edit ' . esc($editingSocial['platform']) : 'Add a social link' ?></h2>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editingSocial ? 'update_social' : 'create_social' ?>">
<?php if ($editingSocial): ?>
            <input type="hidden" name="id" value="<?= (int) $editingSocial['id'] ?>">
<?php endif; ?>

            <div class="cd-admin-form-row">
                <div>
                    <label for="platform">Platform</label>
                    <input type="text" id="platform" name="platform" required
                           value="<?= esc($editingSocial['platform'] ?? '') ?>" placeholder="LinkedIn">
                </div>
                <div>
                    <label for="social_url">URL</label>
                    <input type="url" id="social_url" name="url" required
                           value="<?= esc($editingSocial['url'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>
            <div class="cd-admin-form-row">
                <div>
                    <label for="icon">Icon class</label>
                    <input type="text" id="icon" name="icon"
                           value="<?= esc($editingSocial['icon'] ?? '') ?>" placeholder="lab la-linkedin-in">
                    <span class="cd-admin-hint">A Line Awesome icon class -- see the icons already used on the live site.</span>
                </div>
                <div>
                    <label for="social_sort">Sort order</label>
                    <input type="number" id="social_sort" name="sort_order"
                           value="<?= (int) ($editingSocial['sort_order'] ?? 0) ?>">
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_active" value="1"
<?php if (!$editingSocial || $editingSocial['is_active']): ?> checked<?php endif; ?>>
                Active (shown on the site)
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editingSocial ? 'Save changes' : 'Add social link' ?></button>
<?php if ($editingSocial): ?>
                <a href="<?= esc(admin_url('settings.php?tab=topbar')) ?>" class="cd-admin-btn-ghost">Cancel</a>
<?php endif; ?>
            </div>
        </form>
<?php endif; ?>
    </section>

    <section class="cd-admin-panel">
        <h2>All social links</h2>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr><th>Platform</th><th>URL</th><th>Icon</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
<?php foreach ($socialLinks as $s): ?>
                    <tr>
                        <td><?= esc($s['platform']) ?></td>
                        <td><?= esc($s['url']) ?></td>
                        <td><?= esc($s['icon']) ?></td>
                        <td>
<?php if ($s['is_active']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Active</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Inactive</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('settings.php?tab=topbar&edit_social=' . $s['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete the <?= esc($s['platform']) ?> link?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_social">
                                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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

<?php elseif ($tab === 'navbar'): ?>

    <section class="cd-admin-panel">
        <h2><?= $editingNavItem ? 'Edit "' . esc($editingNavItem['label']) . '"' : 'Add a menu item' ?></h2>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editingNavItem ? 'update_nav_item' : 'create_nav_item' ?>">
<?php if ($editingNavItem): ?>
            <input type="hidden" name="id" value="<?= (int) $editingNavItem['id'] ?>">
<?php endif; ?>

            <div class="cd-admin-form-row">
                <div>
                    <label for="label">Label</label>
                    <input type="text" id="label" name="label" required
                           value="<?= esc($editingNavItem['label'] ?? '') ?>" placeholder="Who We Are">
                </div>
                <div>
                    <label for="nav_url">URL</label>
                    <input type="text" id="nav_url" name="url"
                           value="<?= esc($editingNavItem['url'] ?? '') ?>" placeholder="about">
                    <span class="cd-admin-hint">Country-relative page slug (e.g. <code>about</code>), an anchor (<code>#products</code>), or a full <code>https://</code> link. Leave blank for a heading with no link.</span>
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="parent_id">Parent</label>
                    <select id="parent_id" name="parent_id">
                        <option value="0">&mdash; Top level &mdash;</option>
<?php foreach ($navParents as $p): ?>
<?php if (!$editingNavItem || (int) $p['id'] !== (int) $editingNavItem['id']): ?>
                        <option value="<?= (int) $p['id'] ?>"
<?php if (($editingNavItem['parent_id'] ?? null) == $p['id']): ?> selected<?php endif; ?>>
                            <?= esc($p['label']) ?>
                        </option>
<?php endif; ?>
<?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="column_group">Column group</label>
                    <input type="text" id="column_group" name="column_group"
                           value="<?= esc($editingNavItem['column_group'] ?? '') ?>" placeholder="Optional -- sub-item heading in a dropdown">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="mega_type">Mega menu</label>
                    <select id="mega_type" name="mega_type">
                        <option value="" <?= empty($editingNavItem['mega_type']) ? 'selected' : '' ?>>&mdash; None &mdash;</option>
                        <option value="services" <?= ($editingNavItem['mega_type'] ?? '') === 'services' ? 'selected' : '' ?>>Services (built from the services catalogue)</option>
                    </select>
                    <span class="cd-admin-hint">Top-level only. This is currently the only mega-menu panel the site knows how to render.</span>
                </div>
                <div>
                    <label for="nav_sort">Sort order</label>
                    <input type="number" id="nav_sort" name="sort_order"
                           value="<?= (int) ($editingNavItem['sort_order'] ?? 0) ?>">
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_active" value="1"
<?php if (!$editingNavItem || $editingNavItem['is_active']): ?> checked<?php endif; ?>>
                Active (shown in the menu)
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editingNavItem ? 'Save changes' : 'Add menu item' ?></button>
<?php if ($editingNavItem): ?>
                <a href="<?= esc(admin_url('settings.php?tab=navbar')) ?>" class="cd-admin-btn-ghost">Cancel</a>
<?php endif; ?>
            </div>
        </form>
<?php endif; ?>
    </section>

    <section class="cd-admin-panel">
        <h2>Menu structure</h2>
        <p class="cd-admin-lede">A greyed-out link means the target page doesn't exist yet -- it becomes clickable on the live site the moment that page ships, no edit needed here.</p>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr><th>Label</th><th>URL</th><th>Mega</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
<?php foreach ($navParents as $p): ?>
                    <tr>
                        <td><?= esc($p['label']) ?></td>
                        <td><?= esc($p['url'] ?? '—') ?></td>
                        <td><?= $p['mega_type'] ? esc($p['mega_type']) : '—' ?></td>
                        <td>
<?php if ($p['is_active']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Active</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Inactive</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('settings.php?tab=navbar&edit_nav=' . $p['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete &quot;<?= esc($p['label']) ?>&quot;<?= isset($navChildren[$p['id']]) ? ' and its ' . count($navChildren[$p['id']]) . ' sub-item(s)' : '' ?>?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_nav_item">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="cd-admin-link-btn">Delete</button>
                            </form>
<?php endif; ?>
                        </td>
                    </tr>
<?php foreach ($navChildren[$p['id']] ?? [] as $c): ?>
                    <tr class="cd-admin-child-row">
                        <td>&#8627; <?= esc($c['label']) ?><?= $c['column_group'] ? ' <span class="cd-admin-badge">' . esc($c['column_group']) . '</span>' : '' ?></td>
                        <td><?= esc($c['url'] ?? '—') ?></td>
                        <td>—</td>
                        <td>
<?php if ($c['is_active']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Active</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Inactive</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('settings.php?tab=navbar&edit_nav=' . $c['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete &quot;<?= esc($c['label']) ?>&quot;?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_nav_item">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                <button type="submit" class="cd-admin-link-btn">Delete</button>
                            </form>
<?php endif; ?>
                        </td>
                    </tr>
<?php endforeach; ?>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: /* footer */ ?>

    <section class="cd-admin-panel">
        <h2>Identity &amp; contact</h2>
        <p class="cd-admin-lede">Powers the footer's brand block, its "Talk to an engineer" call-out, and the WhatsApp float button site-wide.</p>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="save_footer_settings">

            <div class="cd-admin-form-row">
                <div>
                    <label for="site_name">Site name</label>
                    <input type="text" id="site_name" name="site_name" required
                           value="<?= esc($footerSettings['site_name']) ?>">
                </div>
                <div>
                    <label for="credentials">Credentials</label>
                    <input type="text" id="credentials" name="credentials"
                           value="<?= esc($footerSettings['credentials']) ?>" placeholder="MSME &amp; DUNS Certified">
                </div>
            </div>

            <div>
                <label for="site_tagline">Tagline</label>
                <input type="text" id="site_tagline" name="site_tagline"
                       value="<?= esc($footerSettings['site_tagline']) ?>">
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="response_time_hours">Response time (hours)</label>
                    <input type="number" id="response_time_hours" name="response_time_hours" min="0"
                           value="<?= esc($footerSettings['response_time_hours']) ?>">
                </div>
                <div>
                    <label for="whatsapp_number">WhatsApp number</label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number"
                           value="<?= esc($footerSettings['whatsapp_number']) ?>" placeholder="918903855325">
                    <span class="cd-admin-hint">Digits only, country code first, no leading +.</span>
                </div>
            </div>

            <div>
                <label for="whatsapp_prefill_text">WhatsApp prefill message</label>
                <textarea id="whatsapp_prefill_text" name="whatsapp_prefill_text" rows="2"><?= esc($footerSettings['whatsapp_prefill_text']) ?></textarea>
            </div>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn">Save footer settings</button>
            </div>
        </form>
<?php endif; ?>
    </section>

    <section class="cd-admin-panel">
        <h2><?= $editingOffice ? 'Edit the ' . esc($editingOffice['city']) . ' office' : 'Add an office' ?></h2>
<?php if ($canEdit): ?>
        <form method="post" class="cd-admin-form">
            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editingOffice ? 'update_office' : 'create_office' ?>">
<?php if ($editingOffice): ?>
            <input type="hidden" name="id" value="<?= (int) $editingOffice['id'] ?>">
<?php endif; ?>

            <div class="cd-admin-form-row">
                <div>
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" required
                           value="<?= esc($editingOffice['city'] ?? '') ?>">
                </div>
                <div>
                    <label for="badge">Badge</label>
                    <input type="text" id="badge" name="badge"
                           value="<?= esc($editingOffice['badge'] ?? '') ?>" placeholder="Primary Headquarters">
                </div>
            </div>

            <div>
                <label for="address">Address</label>
                <input type="text" id="address" name="address"
                       value="<?= esc($editingOffice['address'] ?? '') ?>">
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="office_phone">Phone</label>
                    <input type="text" id="office_phone" name="phone"
                           value="<?= esc($editingOffice['phone'] ?? '') ?>">
                </div>
                <div>
                    <label for="office_email">Email</label>
                    <input type="email" id="office_email" name="email"
                           value="<?= esc($editingOffice['email'] ?? '') ?>">
                </div>
            </div>

            <div class="cd-admin-form-row">
                <div>
                    <label for="country_id">Country</label>
                    <select id="country_id" name="country_id">
                        <option value="0">&mdash; None &mdash;</option>
<?php foreach ($allCountries as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"
<?php if (($editingOffice['country_id'] ?? null) == $c['id']): ?> selected<?php endif; ?>>
                            <?= esc($c['name']) ?>
                        </option>
<?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="office_sort">Sort order</label>
                    <input type="number" id="office_sort" name="sort_order"
                           value="<?= (int) ($editingOffice['sort_order'] ?? 0) ?>">
                </div>
            </div>

            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_hq" value="1"
<?php if (!empty($editingOffice['is_hq'])): ?> checked<?php endif; ?>>
                Headquarters
            </label>
            <label class="cd-admin-checkbox">
                <input type="checkbox" name="is_active" value="1"
<?php if (!$editingOffice || $editingOffice['is_active']): ?> checked<?php endif; ?>>
                Active (shown in the footer)
            </label>

            <div class="cd-admin-form-actions">
                <button type="submit" class="cd-admin-btn"><?= $editingOffice ? 'Save changes' : 'Add office' ?></button>
<?php if ($editingOffice): ?>
                <a href="<?= esc(admin_url('settings.php?tab=footer')) ?>" class="cd-admin-btn-ghost">Cancel</a>
<?php endif; ?>
            </div>
        </form>
<?php endif; ?>
    </section>

    <section class="cd-admin-panel">
        <h2>All offices</h2>
        <div class="cd-admin-table-wrap">
            <table class="cd-admin-table">
                <thead>
                    <tr><th>City</th><th>Badge</th><th>Country</th><th>HQ</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
<?php foreach ($offices as $o): ?>
                    <tr>
                        <td><?= esc($o['city']) ?></td>
                        <td><?= esc($o['badge'] ?? '—') ?></td>
                        <td><?= esc($o['country_name'] ?? '—') ?></td>
                        <td><?= $o['is_hq'] ? 'Yes' : '—' ?></td>
                        <td>
<?php if ($o['is_active']): ?>
                            <span class="cd-admin-badge cd-admin-badge-ok">Active</span>
<?php else: ?>
                            <span class="cd-admin-badge cd-admin-badge-off">Inactive</span>
<?php endif; ?>
                        </td>
                        <td>
<?php if ($canEdit): ?>
                            <a href="<?= esc(admin_url('settings.php?tab=footer&edit_office=' . $o['id'])) ?>">Edit</a>
                            &nbsp;
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete the <?= esc($o['city']) ?> office?');">
                                <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_office">
                                <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
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

<?php endif; ?>
        </main>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
