<?php
/**
 * XML sitemap.
 *
 * Only emits URLs that actually exist on disk right now -- currently the
 * country landing pages. As service/product/blog pages get generated they
 * are added here, so the sitemap never advertises a page that 404s or is
 * still an empty shell.
 *
 * Served at /sitemap.xml via the rewrite in .htaccess.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [];

foreach (get_countries() as $c) {
    // Only list a country once its landing page exists on disk.
    if (!is_file(__DIR__ . '/' . $c['code'] . '/index.php')) {
        continue;
    }

    $alternates = [];
    foreach (get_countries() as $alt) {
        if (!is_file(__DIR__ . '/' . $alt['code'] . '/index.php')) {
            continue;
        }
        $alternates[$alt['hreflang']] = country_url($alt['code']);
    }

    $urls[] = [
        'loc'        => country_url($c['code']),
        'priority'   => $c['is_primary'] ? '1.0' : '0.8',
        'changefreq' => 'weekly',
        'alternates' => $alternates,
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= esc($u['loc']) ?></loc>
        <changefreq><?= esc($u['changefreq']) ?></changefreq>
        <priority><?= esc($u['priority']) ?></priority>
<?php foreach ($u['alternates'] as $lang => $href): ?>
        <xhtml:link rel="alternate" hreflang="<?= esc($lang) ?>" href="<?= esc($href) ?>"/>
<?php endforeach; ?>
<?php if (!empty($u['alternates'])): ?>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= esc(country_url((string) cfg('app.default_country', 'sg'))) ?>"/>
<?php endif; ?>
    </url>
<?php endforeach; ?>
</urlset>
