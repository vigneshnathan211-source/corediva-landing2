<?php
/**
 * Case Studies -- listing (Singapore).
 * URL: /sg/case-studies/
 */

declare(strict_types=1);

$country_code = 'sg';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$cases = get_case_studies($countryId, 24);

$pageSeo = get_page_seo('case-studies', $countryId);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'Case Studies | Corediva Tech Solutions Singapore',
    'description'      => $pageSeo['meta_description'] ?? 'Real project outcomes from the Corediva Tech Solutions engineering team: ERP, CRM, AI automation and cloud infrastructure.',
    'canonical'        => 'sg/case-studies/',
    'hreflang_pattern' => 'case-studies',
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../includes/header.php';
?>

    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <h5 class="section-subtitle">Insights</h5>
                    <h1 class="section-title">Case Studies</h1>
                    <p>Real project outcomes from the team that scoped, built and shipped them.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="service-area" id="cases">
        <div class="custom-container">
<?php if ($cases): ?>
            <div class="cd-service-grid">
<?php foreach ($cases as $case): ?>
                <a href="<?= esc(case_study_url($case['slug'], $country)) ?>" class="service-card simple-shadow">
<?php if ($case['industry']): ?>
                    <p class="cd-product-category"><?= esc($case['industry']) ?></p>
<?php endif; ?>
                    <h4><?= esc($case['client_name'] ?: 'Client project') ?></h4>
<?php if ($case['summary']): ?>
                    <p><?= esc($case['summary']) ?></p>
<?php endif; ?>
                </a>
<?php endforeach; ?>
            </div>
<?php else: ?>
            <div class="alert alert-info" role="status">
                <p class="mb-0">Nothing published yet -- check back soon.</p>
            </div>
<?php endif; ?>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home'          => 'sg/',
    'Case Studies'  => 'sg/case-studies/',
]);
include __DIR__ . '/../includes/footer.php';
?>
