<?php
/**
 * <head> + opening chrome for /admin/ pages.
 *
 * Expects (optional): $pageTitle string.
 *
 * Deliberately not the public site's header.php: the admin panel isn't
 * per-country, doesn't need SEO/schema output, and shouldn't load the
 * marketing theme's full asset bundle (Swiper, GSAP, the mega-menu CSS)
 * for what is a handful of internal screens.
 */

declare(strict_types=1);

// Every admin page requires includes/db.php before this file, which
// already sets this -- this is only a defensive fallback, and it must
// point at the site-wide config (db/app/mail/security), not this
// directory's own admin/includes/config.php, which is a differently
// shaped array (password/otp/session policy only).
if (!isset($GLOBALS['corediva_config'])) {
    $GLOBALS['corediva_config'] = require __DIR__ . '/../../includes/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc(($pageTitle ?? 'Admin') . ' — ' . setting('site_name', 'Corediva')) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= esc(asset('css/quill.snow.css')) ?>">
    <link rel="stylesheet" href="<?= esc(asset('css/admin.css')) ?>">
</head>
<body class="cd-admin">
