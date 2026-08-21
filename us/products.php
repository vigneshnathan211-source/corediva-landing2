<?php
/**
 * Products -- products overview page (United States).
 * URL: /us/products/
 */

declare(strict_types=1);

$country_code = 'us';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

// Handle the RFQ form before any output, so we can redirect/re-render cleanly.
$lead_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
    $lead_result = save_lead($_POST);
}

$productsByGroup = get_products_by_group($countryId);
$productCount     = count(get_products($countryId));
$groupCount       = count($productsByGroup);

$pageSeo = get_page_seo('products', $countryId);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'Products | ERP, CRM, HRM & Automation Tools | Corediva Tech Solutions',
    'description'      => $pageSeo['meta_description'] ?? ('Twelve products across scheduling, finance, CRM, ERP and automation, '
                         . 'ready to deploy as-is or configured to your workflow. See everything Corediva Tech '
                         . 'Solutions builds for US businesses.'),
    'canonical'        => 'us/products/',
    'hreflang_pattern' => 'products',
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: same hero-service-wrap pattern as services.php, checklist
         swapped for the same real commitments (credentials, response
         time, sales-tax-ready invoicing) since they apply to every page on
         this site, not just services. -->
    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <h5 class="section-subtitle">Our Products</h5>
                    <h1 class="section-title">Twelve products. Ready to deploy or customize.</h1>
                    <p>Scheduling, finance, CRM, ERP, HR and automation tools built and supported by the same
                       team that builds our custom engagements -- deploy a product as-is, or use one as the
                       starting point for something built to your process.</p>
                    <div class="cd-hero-service-cta">
                        <a href="#rfq-form" class="theme-btn">Request a Quote <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
                    </div>
                </div>

                <div class="hero-service-about">
                    <img src="<?= esc(asset('imgs/hero-service-about.jpg')) ?>" alt="Corediva Tech Solutions delivery team at work">
                    <div class="hero-service-about-body">
                        <p>Every product ships from the same engineering team that builds our custom ERP and
                           automation projects, so support means talking to someone who already understands
                           how it was built.</p>
                        <ul>
                            <li><i class="las la-check"></i> <?= esc(setting('credentials')) ?></li>
                            <li><i class="las la-check"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                            <li><i class="las la-check"></i> Sales-tax-ready invoicing, itemized by state</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product catalogue: grouped by group_label, same card pattern the
         homepage's tabbed products section and the services catalogue
         both use. -->
    <section class="service-area" id="catalogue">
        <div class="custom-container">
            <div class="service-section-header section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h2 class="section-title">Twelve products. <br>Six groups.</h2>
                </div>
                <p>Scheduling, finance, CRM & marketing, ERP & HR, automation & integration:
                   <?= esc($productCount) ?> products across <?= esc($groupCount) ?> groups, built and
                   supported by one team.</p>
            </div>

<?php foreach ($productsByGroup as $group => $items): ?>
            <div class="cd-service-group">
                <h3 class="cd-service-group-title"><?= esc($group) ?></h3>
                <div class="cd-service-grid">
<?php foreach ($items as $prod): ?>
                    <a href="<?= esc(product_url($prod['slug'], $country)) ?>" class="service-card simple-shadow<?= $prod['is_featured'] ? ' cd-featured' : '' ?>">
<?php if ($prod['is_featured']): ?>
                        <span class="service-badge">Popular</span>
<?php endif; ?>
                        <i class="<?= esc($prod['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                        <h4><?= esc($prod['display_title']) ?></h4>
                        <p><?= esc($prod['short_description']) ?></p>
                    </a>
<?php endforeach; ?>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </section>

    <!-- Consulting Excellence: same real copy as the homepage/services hub. -->
    <section class="about-area cd-consulting-area" id="approach">
        <div class="custom-container">
            <div class="custom-row justify-content-between align-items-center">
                <div class="left-content">
                    <h2 class="section-title">A clear path to the system you need.</h2>
                    <p>Every engagement starts the same way: a straight assessment of
                       what's running today, where it's costing you time, and what
                       actually needs to change.</p>
                    <ul class="cd-about-points">
                        <li><i class="las la-check-circle" aria-hidden="true"></i>
                            24/7 automated lead qualification, not a contact form black hole</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i>
                            One point of contact from scoping to launch</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i>
                            Sales-tax-ready invoicing, itemized by state, built in by default</li>
                    </ul>
                </div>

                <div class="right-content">
                    <div class="about-timeline">
                        <div class="about-timeline-item">
                            <img src="<?= esc(asset('imgs/bg-shape-2.svg')) ?>" alt="" aria-hidden="true" class="line-shape">
                            <span class="number">01</span>
                            <h3>Discovery and Analysis</h3>
                            <p>We map the problem, the systems already in place, and the
                               architecture decisions that follow, before a line of code
                               is written.</p>
                        </div>
                        <div class="about-timeline-item">
                            <img src="<?= esc(asset('imgs/bg-shape-2.svg')) ?>" alt="" aria-hidden="true" class="line-shape">
                            <span class="number">02</span>
                            <h3>Tailored Solutions</h3>
                            <p>Built in short sprints against the agreed scope, and connected
                               to the CRM, accounting or payment systems you already run.</p>
                        </div>
                        <div class="about-timeline-item">
                            <img src="<?= esc(asset('imgs/bg-shape-2.svg')) ?>" alt="" aria-hidden="true" class="line-shape">
                            <span class="number">03</span>
                            <h3>Deployment and Support</h3>
                            <p>Released with monitoring and rollback in place, then maintained
                               and patched once it's carrying real work.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RFQ: same 50/50 form/image split as services.php. -->
    <section class="about-area cd-rfq-area">
        <div class="custom-container">
            <div class="cd-rfq-row">
                <div class="cd-rfq-col cd-rfq-form-col">
<?php $lead_variant = 'rfq'; $lead_service = null; $lead_options = get_products($countryId); include __DIR__ . '/../includes/lead-form.php'; ?>
                </div>

                <div class="cd-rfq-col cd-rfq-media">
                    <img src="<?= esc(asset('imgs/about-service-6.jpg')) ?>" alt="A Corediva engineer working through a client's codebase">
                </div>
            </div>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home'     => 'us/',
    'Products' => 'us/products/',
]);
include __DIR__ . '/../includes/footer.php';
?>
