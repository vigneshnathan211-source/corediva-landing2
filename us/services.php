<?php
/**
 * What We Do -- services overview page (United States).
 * URL: /us/services/
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

// Handle the RFQ form before any output, so we can redirect/re-render cleanly.
$lead_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
    $lead_result = save_lead($_POST);
}

$servicesByCategory = get_services_by_category();
$serviceCount        = count(get_services());
$categoryCount       = count($servicesByCategory);

$pageSeo = get_page_seo('services', (int) $country['id']);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'What We Do | IT, ERP & AI Services | Corediva Tech Solutions',
    'description'      => $pageSeo['meta_description'] ?? ("Sixteen services across web, ERP, AI and cloud, delivered by one "
                         . "accountable team. See everything Corediva Tech Solutions builds for "
                         . "US businesses."),
    'canonical'        => 'us/services/',
    'hreflang_pattern' => 'services',
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: on the theme's service.html hero. Checklist swapped for real
         commitments (credentials, response time, sales-tax-ready invoicing). -->
    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <h5 class="section-subtitle">Our Services</h5>
                    <h1 class="section-title">Sixteen services. One accountable team.</h1>
                    <p>From web platforms to AI automation, ERP to cybersecurity: every service
                       on this page is delivered by the same team that scoped it, not handed off
                       to a subcontractor partway through.</p>
                    <div class="cd-hero-service-cta">
                        <a href="#rfq-form" class="theme-btn">Request a Quote <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
                    </div>
                </div>

                <div class="hero-service-about">
                    <img src="<?= esc(asset('imgs/hero-service-about.jpg')) ?>" alt="Corediva Tech Solutions delivery team at work">
                    <div class="hero-service-about-body">
                        <p>Every engagement runs through the same delivery model: a senior
                           engineer scopes the work, that same engineer ships it, and support
                           doesn't require re-explaining the project to someone new. No handoffs
                           between sales, discovery and delivery.</p>
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

    <!-- Service Area: the full 16-service catalogue grouped by category. -->
    <section class="service-area" id="catalogue">
        <div class="custom-container">
            <div class="service-section-header section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h2 class="section-title">Sixteen services. <br>Five disciplines.</h2>
                </div>
                <p>Web and app development, ERP and CRM, AI and automation, IT and cloud,
                   marketing: <?= esc($serviceCount) ?> services across <?= esc($categoryCount) ?>
                   disciplines, architected and supported by one team.</p>
            </div>

<?php foreach ($servicesByCategory as $category => $items): ?>
            <div class="cd-service-group">
                <h3 class="cd-service-group-title"><?= esc($category) ?></h3>
                <div class="cd-service-grid">
<?php foreach ($items as $svc): ?>
                    <a href="<?= esc(service_url($svc['slug'], $country)) ?>" class="service-card simple-shadow">
                        <i class="<?= esc($svc['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                        <h4><?= esc($svc['title']) ?></h4>
                        <p><?= esc($svc['short_description']) ?></p>
                    </a>
<?php endforeach; ?>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </section>

    <!-- Consulting Excellence: same section, same real copy, as the
         homepage's #approach -- reused rather than rewritten. -->
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

    <!-- RFQ: even 50/50 split. -->
    <section class="about-area cd-rfq-area">
        <div class="custom-container">
            <div class="cd-rfq-row">
                <div class="cd-rfq-col cd-rfq-form-col">
<?php $lead_variant = 'rfq'; $lead_service = null; include __DIR__ . '/../includes/lead-form.php'; ?>
                </div>

                <div class="cd-rfq-col cd-rfq-media">
                    <img src="<?= esc(asset('imgs/about-service-6.jpg')) ?>" alt="A Corediva engineer working through a client's codebase">
                </div>
            </div>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home'        => 'us/',
    'What We Do'  => 'us/services/',
]);
include __DIR__ . '/../includes/footer.php';
?>
