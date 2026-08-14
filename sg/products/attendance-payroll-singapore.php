<?php
declare(strict_types=1);

/**
 * Product detail: Attendance and Payroll Manager (Singapore)
 * URL: /sg/products/attendance-payroll-singapore/
 */

$country_code = 'sg';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$product = get_product('attendance-payroll');
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
    'canonical'        => 'sg/products/attendance-payroll-singapore/',
    'hreflang_pattern' => 'products/attendance-payroll-{suffix}',
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/product-detail-body.php';
include __DIR__ . '/../../includes/footer.php';
