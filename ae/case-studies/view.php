<?php
/**
 * Case study detail (United Arab Emirates). One shared file serves every
 * slug -- see sg/case-studies/view.php for the full reasoning.
 * URL: /ae/case-studies/{slug}/  ->  /ae/case-studies/view.php?slug={slug}
 */

declare(strict_types=1);

$country_code = 'ae';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$slug = trim((string) ($_GET['slug'] ?? ''));
$case = $slug !== '' ? get_case_study($slug, $countryId) : null;
if (!$case) {
    http_response_code(404);
    exit('Case study not found.');
}

$seo = [
    'title'            => $case['meta_title'] ?? (($case['client_name'] ?: 'Case Study') . ' | Corediva Tech Solutions'),
    'description'      => $case['meta_description'] ?? $case['summary'],
    'canonical'        => 'ae/case-studies/' . $case['slug'] . '/',
    'hreflang_pattern' => '',
];

include __DIR__ . '/../../includes/header.php';
?>

    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
<?php if ($case['industry']): ?>
                    <h5 class="section-subtitle"><?= esc($case['industry']) ?></h5>
<?php endif; ?>
                    <h1 class="section-title"><?= esc($case['client_name'] ?: 'Client project') ?></h1>
<?php if ($case['summary']): ?>
                    <p><?= esc($case['summary']) ?></p>
<?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="about-area cd-svc-richtext">
        <div class="custom-container cd-svc-richtext-inner">
<?php if ($case['featured_image']): ?>
            <img src="<?= esc(asset($case['featured_image'])) ?>" alt="<?= esc($case['featured_image_alt'] ?? '') ?>" loading="lazy" style="width:100%;height:auto;border-radius:12px;margin-bottom:2rem;">
<?php endif; ?>
<?php if ($case['body']): ?>
            <div class="cd-svc-prose"><?= $case['body'] ?></div>
<?php endif; ?>
        </div>
    </section>

<?php if ($case['results']): ?>
    <section class="about-area cd-svc-richtext">
        <div class="custom-container cd-svc-richtext-inner">
            <h2 class="cd-svc-heading">Results</h2>
            <div class="cd-svc-prose"><?= $case['results'] ?></div>
        </div>
    </section>
<?php endif; ?>

<?php
render_schema_breadcrumbs([
    'Home'                                 => 'ae/',
    'Case Studies'                         => 'ae/case-studies/',
    ($case['client_name'] ?: 'Case Study') => 'ae/case-studies/' . $case['slug'] . '/',
]);
include __DIR__ . '/../../includes/footer.php';
?>
