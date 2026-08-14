<?php
/**
 * Stamps out service/product detail page files for one country, mirroring
 * the hand-written sg/services/*.php and sg/products/*.php pattern exactly.
 *
 * Run once per new country as its rollout begins:
 *   php tools/generate-pages.php ae
 *
 * This runs at build time only and its output is committed as real, flat
 * files -- per Docs/BUILD-PLAN.md's own reasoning, writing ~30 near-identical
 * page files by hand per country isn't sensible, but the files on disk must
 * still be the plain, readable PHP the rest of the site uses, not routed
 * through this script at runtime. Existing files are left untouched (content
 * pages may have been hand-edited), so this is safe to re-run.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$code = $argv[1] ?? null;
if (!$code) {
    fwrite(STDERR, "Usage: php tools/generate-pages.php <country_code>\n");
    exit(1);
}

$country = get_country($code);
if (!$country) {
    fwrite(STDERR, "Unknown or inactive country code: {$code}\n");
    exit(1);
}

$cc     = $country['code'];
$suffix = $country['slug_suffix'];
$root   = dirname(__DIR__);

$serviceStub = <<<'PHP'
<?php
declare(strict_types=1);

/**
 * Service detail: __TITLE__ (__CNAME__)
 * URL: /__CC__/services/__SLUG__-__SUF__/
 */

$country_code = '__CC__';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$detailService = get_service('__SLUG__');
if (!$detailService) {
    http_response_code(404);
    exit('Service not found.');
}
$serviceId = (int) $detailService['id'];

$content  = get_service_content($serviceId, $countryId);
$sections = get_service_sections($serviceId, $countryId);

$lead_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
    $lead_result = save_lead($_POST);
}

$seo = [
    'title'            => $content['meta_title'] ?? ($detailService['title'] . ' | Corediva Tech Solutions'),
    'description'      => $content['meta_description'] ?? $detailService['short_description'],
    'canonical'        => '__CC__/services/__SLUG__-__SUF__/',
    'hreflang_pattern' => 'services/__SLUG__-{suffix}',
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/service-detail-body.php';
include __DIR__ . '/../../includes/footer.php';

PHP;

$productStub = <<<'PHP'
<?php
declare(strict_types=1);

/**
 * Product detail: __TITLE__ (__CNAME__)
 * URL: /__CC__/products/__SLUG__-__SUF__/
 */

$country_code = '__CC__';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$product = get_product('__SLUG__');
if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}
$productId = (int) $product['id'];

$content  = get_product_content($productId, $countryId);
$sections = get_product_sections($productId, $countryId);

$lead_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
    $lead_result = save_lead($_POST);
}

$seo = [
    'title'            => $content['meta_title'] ?? ($product['title'] . ' | Corediva Tech Solutions'),
    'description'      => $content['meta_description'] ?? $product['short_description'],
    'canonical'        => '__CC__/products/__SLUG__-__SUF__/',
    'hreflang_pattern' => 'products/__SLUG__-{suffix}',
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/product-detail-body.php';
include __DIR__ . '/../../includes/footer.php';

PHP;

/**
 * @param array<int,array{slug:string,title:string}> $items
 */
function stamp_pages(array $items, string $stub, string $dir, string $cc, string $suffix, string $cname): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    foreach ($items as $item) {
        $file = $dir . '/' . $item['slug'] . '-' . $suffix . '.php';
        if (is_file($file)) {
            echo "skip (exists): {$file}\n";
            continue;
        }
        $out = strtr($stub, [
            '__CC__'    => $cc,
            '__SUF__'   => $suffix,
            '__SLUG__'  => $item['slug'],
            '__TITLE__' => $item['title'],
            '__CNAME__' => $cname,
        ]);
        file_put_contents($file, $out);
        echo "wrote: {$file}\n";
    }
}

$services = db_all('SELECT slug, title FROM services WHERE is_active = 1 ORDER BY sort_order');
$products = db_all('SELECT slug, title FROM products WHERE is_active = 1 ORDER BY sort_order');

stamp_pages($services, $serviceStub, "{$root}/{$cc}/services", $cc, $suffix, $country['name']);
stamp_pages($products, $productStub, "{$root}/{$cc}/products", $cc, $suffix, $country['name']);

echo "Done: {$cc} ({$country['name']}) -- " . count($services) . " services, " . count($products) . " products.\n";
