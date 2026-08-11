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

if (!isset($GLOBALS['corediva_config'])) {
    $GLOBALS['corediva_config'] = require __DIR__ . '/config.php';
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

    <link rel="stylesheet" href="<?= esc(asset('css/admin.css')) ?>">
</head>
<body class="cd-admin">
