<?php
declare(strict_types=1);

/**
 * Product detail: Corediva HRM (Australia)
 * URL: /au/products/hrm-australia/
 */

$country_code = 'au';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$product = get_product('hrm');
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
    'canonical'        => 'au/products/hrm-australia/',
    'hreflang_pattern' => 'products/hrm-{suffix}',
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/product-detail-body.php';
include __DIR__ . '/../../includes/footer.php';
