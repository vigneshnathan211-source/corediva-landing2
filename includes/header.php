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
$socials     = get_social_links();
// countries.code is a URL folder ("uk"), not an ISO 3166-1 alpha-2 code --
// the flag-icons library needs "gb" for the Union Jack. Every other country
// code in this project already matches its ISO code.
$flagIso = static fn (string $code): string => $code === 'uk' ? 'gb' : $code;
// No standalone contact page exists yet for any country, and the SG
// landing page no longer has a #contact section to fall back to (it was
// replaced by the Consulting Excellence section). WhatsApp is the site's
// own stated fastest channel, so it's the fallback until a real contact
// page ships and nav_target() resolves to it instead.
$contactHref   = nav_target('contact', $country) ?? whatsapp_url();
$contactExtern = (bool) preg_match('#^https?://#i', $contactHref);
$loginHref   = is_file(doc_root() . '/admin/login.php') ? url('admin/login.php') : null;
?>
<!DOCTYPE html>
<html lang="<?= esc($country['hreflang']) ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?= esc(setting('theme_color', '#1351D8')) ?>">

<?php render_seo_tags($seo); ?>

    <link rel="icon" href="<?= esc(setting('favicon_url', '/favicon.ico')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..800;1,9..40,100..600&family=Yantramanav:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/iconoir-icons/iconoir@main/css/iconoir.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">
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

                    <div class="cd-country-switch">
                        <button type="button" class="cd-country-toggle" id="country-switcher"
                                aria-haspopup="true" aria-expanded="false" aria-controls="cd-country-menu">
                            <span class="fi fi-<?= esc($flagIso($country['code'])) ?> cd-country-flag" aria-hidden="true"></span>
                            <span class="cd-country-code"><?= esc(strtoupper($country['code'])) ?></span>
                            <i class="las la-angle-down" aria-hidden="true"></i>
                        </button>
                        <ul class="cd-country-menu" id="cd-country-menu" role="menu" aria-label="Choose your country">
<?php foreach (get_countries() as $c): ?>
                            <li role="none">
                                <a role="menuitem" href="<?= esc(country_url($c['code'])) ?>"
                                   class="cd-country-option<?= $c['code'] === $country['code'] ? ' is-active' : '' ?>">
                                    <span class="fi fi-<?= esc($flagIso($c['code'])) ?> cd-country-flag" aria-hidden="true"></span>
                                    <span class="cd-country-code"><?= esc(strtoupper($c['code'])) ?></span>
                                    <span class="cd-country-name"><?= esc($c['name']) ?></span>
                                </a>
                            </li>
<?php endforeach; ?>
                        </ul>
                    </div>
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
<?php
    /* Panel laid out like the theme's mega menu: a wide left column of link
       groups sitting above a social + hiring footer, and a tinted right rail
       whose background bleeds to the viewport edge.

       The theme's own left column pairs four icon "service cards" with two
       link lists. We keep all five category columns instead -- those five
       categories ARE the site's service taxonomy, and collapsing them to two
       lists to make room for decorative cards would hide half the catalogue. */
?>
                            <div class="cd-mega">
                                <div class="cd-mega-inner">
                                    <div class="cd-container cd-mega-cols">
                                        <div class="cd-mega-main">
                                            <div class="cd-mega-link-wrap">
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
                                            </div>

                                            <div class="cd-mega-foot">
                                                <ul class="cd-mega-social">
<?php foreach ($socials as $social): ?>
                                                    <li>
                                                        <a href="<?= esc($social['url']) ?>" target="_blank" rel="noopener"
                                                           aria-label="<?= esc($social['platform']) ?>">
                                                            <i class="<?= esc($social['icon']) ?>" aria-hidden="true"></i>
                                                        </a>
                                                    </li>
<?php endforeach; ?>
                                                </ul>
                                                <p>Not sure which service fits?
                                                    <a href="<?= esc($contactHref) ?>"<?= $contactExtern ? ' target="_blank" rel="noopener"' : '' ?>>Tell us the problem</a>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cd-mega-aside">
<?php
    /* The theme fills this rail with a product screenshot captioned "Our
       product hits". We have no product imagery, so the rail carries the
       thing a visitor stuck in a service menu actually needs: a way to
       talk to someone. */
?>
                                            <div class="cd-mega-promo">
                                                <span class="cd-mega-promo-eyebrow">Talk it through</span>
                                                <h3>Not sure where to start?</h3>
                                                <p>Tell us what is slowing the business down and we will scope
                                                   the work, no obligation.</p>
                                                <a class="cd-mega-promo-phone" href="tel:<?= esc(setting('phone_e164')) ?>">
                                                    <i class="las la-phone" aria-hidden="true"></i>
                                                    <?= esc(setting('phone_display')) ?>
                                                </a>
                                                <a href="<?= esc($contactHref) ?>" class="cd-mega-promo-link"<?= $contactExtern ? ' target="_blank" rel="noopener"' : '' ?>>
                                                    Book a free consultation
                                                </a>
                                            </div>
                                        </div>
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
                        <a href="<?= esc($contactHref) ?>" class="theme-btn"<?= $contactExtern ? ' target="_blank" rel="noopener"' : '' ?>>Contact Us</a>
                    </div>
                </nav>

            </div>
        </div>
    </header>

    <div id="main-content">
