<?php
/**
 * Shared page head + site header.
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
?>
<!DOCTYPE html>
<html lang="<?= esc($country['hreflang']) ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?= esc(setting('theme_color', '#2563eb')) ?>">

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

<a class="visually-hidden-focusable" href="#main-content">Skip to content</a>

<main class="main-page homepage" id="top">

    <!-- Header Bar -->
    <div class="header-bar">
        <div class="custom-container">
            <div class="header-bar-body d-flex align-items-center justify-content-between">
                <div class="left">
                    <label for="country-switcher" class="visually-hidden">Choose your country</label>
                    <select class="country-select" id="country-switcher"
                            onchange="if(this.value){window.location.href=this.value;}">
<?php foreach (get_countries() as $c): ?>
                        <option value="<?= esc(country_url($c['code'])) ?>"
                            <?= $c['code'] === $country['code'] ? 'selected' : '' ?>>
                            <?= esc(strtoupper($c['code'])) ?> — <?= esc($c['name']) ?>
                        </option>
<?php endforeach; ?>
                    </select>
                </div>
                <div class="right">
                    <p>
                        <?= esc(setting('site_tagline')) ?>
                        <a href="<?= esc(whatsapp_url()) ?>" rel="noopener" target="_blank"
                           data-word="WhatsApp us" id="dataWord">WhatsApp us</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header-area">
        <div class="custom-container">
            <div class="custom-row align-items-center justify-content-between">
                <div class="header-left d-flex align-items-center">
                    <a href="<?= esc(country_url($country['code'])) ?>" class="logo">
                        <img src="<?= esc(asset('imgs/corediva-logo.png')) ?>"
                             alt="<?= esc(setting('site_name')) ?>" width="200" height="25" />
                    </a>

                    <div class="header-left-right">
                        <a href="#contact" class="theme-btn">Contact Us</a>
                        <span class="menu-bar"><i class="las la-bars"></i></span>
                    </div>

                    <nav class="navbar-wrapper" aria-label="Primary">
                        <span class="close-menu-bar"><i class="las la-times"></i></span>
                        <ul>
<?php foreach (get_nav_items() as $item): ?>
                            <li><a href="<?= esc($item['url'] ?? '#') ?>"><?= esc($item['label']) ?></a></li>
<?php endforeach; ?>
                        </ul>
                    </nav>
                </div>

                <div class="header-right">
                    <div class="header-contact-info d-flex align-items-center">
                        <div class="phone-number">
                            <a href="tel:<?= esc(setting('phone_e164')) ?>">
                                Call Us <i class="iconoir-arrow-up-right"></i>
                            </a>
                            <?= esc(setting('phone_display')) ?>
                        </div>
                        <a href="#contact" class="theme-btn">Get a Free Consultation</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="main-content">
