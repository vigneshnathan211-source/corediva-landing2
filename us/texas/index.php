<?php
/**
 * Texas -- nested state landing page (region of United States).
 * URL: /us/texas/
 *
 * A lighter page than a full country landing: no hero swiper/lead-form
 * split, no hreflang (a state is not a locale -- header.php strips
 * hreflang_pattern whenever $region is set, before it's included below),
 * and only the 5 Texas-relevant services get their own region-scoped
 * blurb (service_content region_id=1). Every other service/product still
 * lives on the national /us/ pages this links out to.
 */

declare(strict_types=1);

$country_code = 'us';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$region = get_region($countryId, 'texas');
if (!$region) {
    http_response_code(404);
    exit('Region not configured.');
}
$regionId = (int) $region['id'];

// Handle the RFQ form before any output, so we can redirect/re-render cleanly.
$lead_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
    $lead_result = save_lead($_POST);
}

$texasServiceSlugs = ['web-development', 'erp-crm', 'ai-solutions', 'cybersecurity', 'cloud-solutions'];
$allServices = get_services();
$servicesBySlug = array_column($allServices, null, 'slug');

$texasServices = [];
foreach ($texasServiceSlugs as $slug) {
    $svc = $servicesBySlug[$slug] ?? null;
    if (!$svc) {
        continue;
    }
    $content = get_service_content((int) $svc['id'], $countryId, $regionId);
    $texasServices[] = ['service' => $svc, 'content' => $content];
}

$stats    = get_stats();
$partners = get_partners();

$pageSeo = get_page_seo('texas', $countryId);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'IT, ERP & AI Solutions for Texas Businesses | Corediva',
    'description'      => $pageSeo['meta_description'] ?? ('Web development, ERP/CRM, AI automation, cybersecurity and cloud '
                         . 'solutions for businesses in Austin, Dallas and Houston. Sales-tax-ready invoicing, '
                         . '4-hour response.'),
    'canonical'        => 'us/texas/',
    'hreflang_pattern' => '', // stripped again below by $region, kept empty here for clarity
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../../includes/header.php';
?>

    <!-- Hero: on the theme's service.html hero, matching the pattern
         services.php/products.php use for a sub-page that isn't the
         country home. -->
    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <h5 class="section-subtitle">Texas</h5>
                    <h1 class="section-title">Enterprise systems for Austin, Dallas and Houston businesses.</h1>
                    <p>The same engineering team behind our national US delivery, with support hours
                       aligned to Central Time and every quote itemizing Texas's combined state and
                       local sales tax.</p>
                    <div class="cd-hero-service-cta">
                        <a href="#rfq-form" class="theme-btn">Request a Quote <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
                    </div>
                </div>

                <div class="hero-service-about">
                    <img src="<?= esc(asset('imgs/hero-service-about.jpg')) ?>" alt="Corediva Tech Solutions delivery team at work">
                    <div class="hero-service-about-body">
                        <p>A senior engineer scopes the work and ships it, so support means talking
                           to someone who already understands the environment, not a rotating help
                           desk reading from a ticket.</p>
                        <ul>
                            <li><i class="las la-check"></i> <?= esc(setting('credentials')) ?></li>
                            <li><i class="las la-check"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                            <li><i class="las la-check"></i> Sales-tax-ready invoicing, itemized for Texas's combined rate</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="cd-stats">
        <div class="custom-container">
            <div class="cd-stats-grid">
<?php foreach ($stats as $stat): ?>
                <div class="cd-stat">
                    <p class="cd-stat-value"><?= esc($stat['value']) ?><span><?= esc($stat['suffix']) ?></span></p>
                    <p><?= esc($stat['label']) ?></p>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Texas-relevant services: 5 of the 16, each with its own
         region-scoped blurb, linking out to the full national service
         page for the rest of the detail. -->
    <section class="service-area" id="catalogue">
        <div class="custom-container">
            <div class="service-section-header section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h2 class="section-title">Where we focus <br>for Texas clients.</h2>
                </div>
                <p>Five of our sixteen services come up most often with Texas businesses. The full
                   sixteen-service catalogue is still available on our national US services page.</p>
            </div>

            <div class="cd-service-grid">
<?php foreach ($texasServices as $row): ?>
<?php $svc = $row['service']; $content = $row['content']; ?>
                <a href="<?= esc(service_url($svc['slug'], $country)) ?>" class="service-card simple-shadow">
                    <i class="<?= esc($svc['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                    <h4><?= esc($svc['title']) ?></h4>
                    <p><?= esc($content['local_proof'] ? strip_tags($content['local_proof']) : $svc['short_description']) ?></p>
                </a>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Partners: same cd-partner-band marquee as every other page. -->
    <section class="cd-partner-band" id="partners">
        <div class="cd-container">
            <p class="cd-partner-lead">Payments, cloud and banking partners we build on</p>

            <div class="cd-marquee">
                <ul class="cd-marquee-track">
<?php for ($pass = 0; $pass < 2; $pass++): ?>
<?php foreach ($partners as $partner): ?>
                    <li class="cd-partner simple-shadow"<?= $pass ? ' aria-hidden="true"' : '' ?>>
                        <a href="<?= esc($partner['url'] ?: '#') ?>" target="_blank" rel="noopener"
                           <?= $pass ? 'tabindex="-1" ' : '' ?>title="<?= esc($partner['name'] . ': ' . $partner['description']) ?>">
<?php if ($partner['logo']): ?>
                            <img class="cd-partner-mark" src="<?= esc(asset($partner['logo'])) ?>"
                                 alt="" aria-hidden="true" width="36" height="36">
<?php endif; ?>
                            <span class="cd-partner-name"><?= esc($partner['name']) ?></span>
                        </a>
                    </li>
<?php endforeach; ?>
<?php endfor; ?>
                </ul>
            </div>
        </div>
    </section>

    <!-- RFQ: even 50/50 split, same pattern as services.php/products.php. -->
    <section class="about-area cd-rfq-area">
        <div class="custom-container">
            <div class="cd-rfq-row">
                <div class="cd-rfq-col cd-rfq-form-col">
<?php $lead_variant = 'rfq'; $lead_service = null; include __DIR__ . '/../../includes/lead-form.php'; ?>
                </div>

                <div class="cd-rfq-col cd-rfq-media">
                    <img src="<?= esc(asset('imgs/about-service-6.jpg')) ?>" alt="A Corediva engineer working through a client's codebase">
                </div>
            </div>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home'  => 'us/',
    'Texas' => 'us/texas/',
]);
include __DIR__ . '/../../includes/footer.php';
?>
