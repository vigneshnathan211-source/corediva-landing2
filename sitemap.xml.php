<?php
/**
 * Dynamic XML sitemap. Served at /sitemap.xml via the rewrite rule in
 * .htaccess (inert under `php -S`, so hit this file directly in dev).
 *
 * Walks every country and includes a URL only when the flat file that
 * serves it actually exists on disk -- same is_file() gating hreflang_map()
 * uses, so the sitemap never advertises a page that 404s. Per-service URLs
 * additionally respect service_content.is_published, and the three hub
 * pages (home/about/services) respect page_seo.is_noindex.
 *
 * CLAUDE.md is explicit: do not submit this to Search Console until real
 * copy is imported -- is_published defaults to 1, so nothing here blocks
 * premature indexing on its own.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

/** @var array<int,array{loc:string,lastmod?:?string}> $urls */
$urls = [];

foreach (get_countries() as $country) {
    $code      = $country['code'];
    $countryId = (int) $country['id'];

    $hubs = [
        ''             => '',
        'about'        => 'about',
        'services'     => 'services',
        'products'     => 'products',
        'partners'     => 'partners',
        'blog'         => 'blog',
        'case-studies' => 'case-studies',
    ];

    foreach ($hubs as $pageKey => $path) {
        if (!is_file(path_to_file($code, $path))) {
            continue;
        }
        $pageSeo = get_page_seo($pageKey === '' ? 'home' : $pageKey, $countryId);
        if (!empty($pageSeo['is_noindex'])) {
            continue;
        }
        $urls[] = ['loc' => url($code . '/' . ($path !== '' ? $path . '/' : ''))];
    }

    foreach (get_services() as $service) {
        $path = 'services/' . $service['slug'] . '-' . $country['slug_suffix'];
        if (!is_file(path_to_file($code, $path))) {
            continue;
        }

        $content = get_service_content((int) $service['id'], $countryId);
        if ($content !== null && !$content['is_published']) {
            continue;
        }

        $urls[] = [
            'loc'     => url($code . '/' . $path . '/'),
            'lastmod' => $content['updated_at'] ?? null,
        ];
    }

    foreach (db_all('SELECT id, slug FROM products WHERE is_active = 1') as $product) {
        $path = 'products/' . $product['slug'] . '-' . $country['slug_suffix'];
        if (!is_file(path_to_file($code, $path))) {
            continue;
        }

        $content = get_product_content((int) $product['id'], $countryId);
        if ($content !== null && !$content['is_published']) {
            continue;
        }

        $urls[] = [
            'loc'     => url($code . '/' . $path . '/'),
            'lastmod' => $content['updated_at'] ?? null,
        ];
    }

    // Region landing pages (Texas today, any future state/region follows
    // the same nested-file convention: {code}/{region-code}/index.php).
    foreach (db_all('SELECT code FROM regions WHERE country_id = ? AND is_active = 1', [$countryId]) as $region) {
        $regionPath = $region['code'] . '/index';
        if (!is_file(path_to_file($code, $regionPath))) {
            continue;
        }
        $pageSeo = get_page_seo($region['code'], $countryId);
        if (!empty($pageSeo['is_noindex'])) {
            continue;
        }
        $urls[] = ['loc' => url($code . '/' . $region['code'] . '/')];
    }

    // Blog posts and case studies use one shared handler file per country
    // (blog/post.php, case-studies/view.php) rather than a file per slug,
    // so the is_file() check happens once outside the row loop.
    if (is_file(path_to_file($code, 'blog/post'))) {
        foreach (get_posts($countryId, 1000) as $post) {
            $urls[] = [
                'loc'     => url($code . '/blog/' . $post['slug'] . '/'),
                'lastmod' => $post['updated_at'] ?? null,
            ];
        }
    }

    if (is_file(path_to_file($code, 'case-studies/view'))) {
        foreach (get_case_studies($countryId, 1000) as $case) {
            $urls[] = [
                'loc'     => url($code . '/case-studies/' . $case['slug'] . '/'),
                'lastmod' => $case['updated_at'] ?? null,
            ];
        }
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . esc($u['loc']) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . esc(date('Y-m-d', strtotime((string) $u['lastmod']))) . "</lastmod>\n";
    }
    echo "  </url>\n";
}
echo "</urlset>\n";
