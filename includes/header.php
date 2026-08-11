<?php
/**
 * Shared page head, top bar and site header.
 *
 * Expects these to already be in scope when included:
 *   $country  array   row from `countries`
 *   $seo      array   options for render_seo_tags() -- title, description,
 *                     canonical, hreflang_pattern, noindex
 *
 * Optional:
 *   $region   array   row from `regions` (Texas). Suppresses hreflang.
 */

declare(strict_types=1);

if (!isset($country) || !is_array($country)) {
    http_response_code(500);
    exit('header.php included without $country in scope.');
}

// Must happen before a single byte of output: session_start() sets a cookie,
// and lead-form.php mints its CSRF token long after the <head> has flushed.
ensure_session();

$seo = $seo ?? [];

// A region page is a geo-targeted page inside a country, not a locale of its
// own -- so it must not advertise hreflang alternates.
if (isset($region) && $region) {
    unset($seo['hreflang_pattern']);
}

$navTree     = get_nav_tree();
$hqOffice    = get_offices()[0] ?? null;
$socials     = get_social_links();
$contactHref = nav_target('contact', $country) ?? '#contact';
$loginHref   = is_file(doc_root() . '/admin/login.php') ? url('admin/login.php') : null;
?>
<!DOCTYPE html>
<html lang="<?= esc($country['hreflang']) ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?= esc(setting('theme_color', '#0D1155')) ?>">

<?php render_seo_tags($seo); ?>

    <link rel="icon" href="<?= esc(setting('favicon_url', '/favicon.ico')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..800;1,9..40,100..600&family=Syne:wght@400;500;600;700;800&family=Yantramanav:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="<?= esc(asset('css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= esc(asset('css/swiper-bundle.min.css')) ?>">
    <link rel="stylesheet" href="<?= esc(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= esc(asset('css/responsive.css')) ?>">
    <link rel="stylesheet" href="<?= esc(asset('css/corediva.css')) ?>">

<?php
render_schema_organization();
render_schema_localbusiness($country);
?>
</head>

<body>

<a class="cd-skip-link" href="#main-content">Skip to content</a>

<main class="main-page" id="top">

    <!-- ============ Top bar ============ -->
    <div class="cd-topbar">
        <div class="cd-container">
            <div class="cd-topbar-inner">

                <ul class="cd-topbar-contact">
                    <li>
                        <i class="las la-phone" aria-hidden="true"></i>
                        <a href="tel:<?= esc(setting('phone_e164')) ?>"><?= esc(setting('phone_display')) ?></a>
                    </li>
                    <li>
                        <i class="las la-envelope" aria-hidden="true"></i>
                        <a href="mailto:<?= esc(setting('email')) ?>"><?= esc(setting('email')) ?></a>
                    </li>
<?php if ($hqOffice): ?>
                    <li class="cd-topbar-address">
                        <i class="las la-map-marker-alt" aria-hidden="true"></i>
                        <span><?= esc($hqOffice['address']) ?></span>
                    </li>
<?php endif; ?>
                </ul>

                <div class="cd-topbar-right">
                    <span class="cd-follow-label">Follow:</span>
                    <ul class="cd-topbar-social">
<?php foreach ($socials as $social): ?>
                        <li>
                            <a href="<?= esc($social['url']) ?>" target="_blank" rel="noopener"
                               aria-label="<?= esc($social['platform']) ?>">
                                <i class="<?= esc($social['icon']) ?>" aria-hidden="true"></i>
                            </a>
                        </li>
<?php endforeach; ?>
                    </ul>

                    <label for="country-switcher" class="cd-visually-hidden">Choose your country</label>
                    <select class="cd-country-select" id="country-switcher"
                            onchange="if(this.value){window.location.href=this.value;}">
<?php foreach (get_countries() as $c): ?>
                        <option value="<?= esc(country_url($c['code'])) ?>"
                            <?= $c['code'] === $country['code'] ? 'selected' : '' ?>>
                            <?= esc(strtoupper($c['code'])) ?> — <?= esc($c['name']) ?>
                        </option>
<?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <!-- ============ Header / navigation ============ -->
    <header class="cd-header">
        <div class="cd-container">
            <div class="cd-header-inner">

                <a href="<?= esc(country_url($country['code'])) ?>" class="cd-logo">
                    <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
                         alt="<?= esc(setting('site_name')) ?>" width="200" height="25">
                </a>

                <button class="cd-menu-toggle" type="button"
                        aria-expanded="false" aria-controls="cd-primary-nav" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>

                <nav class="cd-nav" id="cd-primary-nav" aria-label="Primary">
                    <ul class="cd-nav-list">
<?php foreach ($navTree as $item): ?>
<?php
    $isMegaServices = $item['mega_type'] === 'services';
    $hasChildren    = !empty($item['children']);
    $href           = nav_target($item['url'], $country);
?>
<?php if ($isMegaServices): ?>
                        <li class="cd-nav-item cd-has-panel cd-has-mega">
                            <button type="button" class="cd-nav-link cd-panel-toggle" aria-expanded="false">
                                <?= esc($item['label']) ?>
                                <i class="las la-angle-down" aria-hidden="true"></i>
                            </button>
                            <div class="cd-mega">
                                <div class="cd-container">
                                    <div class="cd-mega-grid">
<?php foreach (get_services_by_category() as $category => $services): ?>
                                        <div class="cd-mega-col">
                                            <h3 class="cd-mega-heading"><?= esc($category) ?></h3>
                                            <ul>
<?php foreach ($services as $service): ?>
<?php $svcHref = nav_target('services/' . $service['slug'] . '-{suffix}', $country); ?>
                                                <li>
<?php if ($svcHref): ?>
                                                    <a href="<?= esc($svcHref) ?>"><?= esc($service['title']) ?></a>
<?php else: ?>
                                                    <span class="cd-nav-pending"><?= esc($service['title']) ?></span>
<?php endif; ?>
                                                </li>
<?php endforeach; ?>
                                            </ul>
                                        </div>
<?php endforeach; ?>
                                    </div>
                                    <div class="cd-mega-footer">
                                        <span>Not sure which service fits? Tell us the problem and we'll scope it.</span>
                                        <a href="#contact" class="cd-mega-cta">
                                            Get a free consultation <i class="iconoir-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>

<?php elseif ($hasChildren): ?>
                        <li class="cd-nav-item cd-has-panel cd-has-dropdown">
                            <button type="button" class="cd-nav-link cd-panel-toggle" aria-expanded="false">
                                <?= esc($item['label']) ?>
                                <i class="las la-angle-down" aria-hidden="true"></i>
                            </button>
                            <ul class="cd-dropdown">
<?php foreach ($item['children'] as $child): ?>
<?php $childHref = nav_target($child['url'], $country); ?>
                                <li>
<?php if ($child['column_group']): ?>
                                    <span class="cd-dropdown-kicker"><?= esc($child['column_group']) ?></span>
<?php endif; ?>
<?php if ($childHref): ?>
                                    <a href="<?= esc($childHref) ?>"><?= esc($child['label']) ?></a>
<?php else: ?>
                                    <span class="cd-nav-pending"><?= esc($child['label']) ?></span>
<?php endif; ?>
                                </li>
<?php endforeach; ?>
                            </ul>
                        </li>

<?php else: ?>
                        <li class="cd-nav-item">
<?php if ($href): ?>
                            <a class="cd-nav-link" href="<?= esc($href) ?>"><?= esc($item['label']) ?></a>
<?php else: ?>
                            <span class="cd-nav-link cd-nav-pending"><?= esc($item['label']) ?></span>
<?php endif; ?>
                        </li>
<?php endif; ?>
<?php endforeach; ?>
                    </ul>

                    <div class="cd-nav-actions">
<?php if ($loginHref): ?>
                        <a href="<?= esc($loginHref) ?>" class="cd-login-link">Login</a>
<?php endif; ?>
                        <a href="<?= esc($contactHref) ?>" class="cd-btn cd-btn-primary">Contact Us</a>
                    </div>
                </nav>

            </div>
        </div>
    </header>

    <div id="main-content">
