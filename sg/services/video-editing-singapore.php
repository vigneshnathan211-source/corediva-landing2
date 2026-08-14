<?php
declare(strict_types=1);

/**
 * Service detail: Video Editing (Singapore)
 * URL: /sg/services/video-editing-singapore/
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

$detailService = get_service('video-editing');
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
    'canonical'        => 'sg/services/video-editing-singapore/',
    'hreflang_pattern' => 'services/video-editing-{suffix}',
];

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/service-detail-body.php';
include __DIR__ . '/../../includes/footer.php';
