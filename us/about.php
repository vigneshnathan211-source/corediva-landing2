<?php
/**
 * Who We Are -- company/about page (United States).
 * URL: /us/about/
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

$stats    = get_stats();
$partners = get_partners();
$offices  = get_offices();

$pageSeo = get_page_seo('about', (int) $country['id']);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'Who We Are | Corediva Tech Solutions USA',
    'description'      => $pageSeo['meta_description'] ?? ('A decade-old, MSME & DUNS certified engineering team headquartered in '
                         . 'India, building sales-tax-ready ERP, CRM, AI and cloud systems for US businesses.'),
    'canonical'        => 'us/about/',
    'hreflang_pattern' => 'about',
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: on the theme's about.html "hero-about-wrap" layout, with the
         four stat boxes fed from real data instead of the theme's demo
         figures. -->
    <section class="hero-service-wrap hero-section-wrap hero-about-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-portfolio-body">
                    <div class="hero-section-content text-center">
                        <h5 class="section-subtitle">Who We Are</h5>
                        <h1 class="section-title">Enterprise engineering, built for how US businesses buy.</h1>
                        <p>Corediva Tech Solutions is the team behind the ERP, CRM, AI automation
                           and cloud projects US businesses run on, MSME and DUNS certified, and
                           built around one rule: the person who scopes your project is the person
                           who ships it.</p>
                    </div>

                    <div class="hero-company-boxes">
<?php foreach ($stats as $stat): ?>
                        <div class="hero-company-box simple-shadow">
                            <p class="cd-stat-value"><?= esc($stat['value']) ?><span><?= esc($stat['suffix']) ?></span></p>
                            <h3><?= esc($stat['label']) ?></h3>
                        </div>
<?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners: same cd-partner-band marquee as the homepage, reused
         verbatim rather than re-styled. -->
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

    <!-- Certifications: real badges migrated from corediva365.com/about --
         DGFT (IEC), MSME, DUNS, CERSAI and CKYC. These are Indian
         registrations, real regardless of which country page they're
         shown on, so they're reused verbatim here. -->
    <section class="about-area" id="certifications">
        <div class="custom-container">
            <div class="section-header text-center">
                <h2 class="section-title">Fully Compliant &amp; Certified Operations</h2>
            </div>

            <div class="cd-cert-grid">
                <div class="cd-cert-badge">
                    <img src="<?= esc(asset('imgs/certifications/iec.png')) ?>"
                         alt="DGFT Indian Government Approved" width="240" height="88" loading="lazy">
                    <p>DGFT Indian Government Approved</p>
                </div>
                <div class="cd-cert-badge">
                    <img src="<?= esc(asset('imgs/certifications/msme.png')) ?>"
                         alt="MSME Certified" width="240" height="132" loading="lazy">
                    <p>MSME Certified</p>
                </div>
                <div class="cd-cert-badge">
                    <img src="<?= esc(asset('imgs/certifications/duns.png')) ?>"
                         alt="DUNS Verified" width="240" height="197" loading="lazy">
                    <p>DUNS Verified</p>
                </div>
                <div class="cd-cert-badge">
                    <img src="<?= esc(asset('imgs/certifications/cersai.png')) ?>"
                         alt="CERSAI Approved" width="240" height="225" loading="lazy">
                    <p>CERSAI Approved</p>
                </div>
                <div class="cd-cert-badge">
                    <img src="<?= esc(asset('imgs/certifications/ckyc.png')) ?>"
                         alt="CKYC Verified" width="240" height="232" loading="lazy">
                    <p>CKYC Verified</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our company: banner panel + checklist. -->
    <section class="about-area" id="company">
        <div class="custom-container">
            <div class="hero-service-about simple-shadow">
                <div class="section-header d-flex align-items-center justify-content-between w-full">
                    <div class="left">
                        <h5 class="section-subtitle">OUR COMPANY</h5>
                        <h2 class="section-title">Headquartered in India.<br>Engineered for the US.</h2>
                        <p>An engineering team built for how US businesses buy, backed by a decade
                           of enterprise delivery.</p>
                    </div>
                    <a href="<?= esc(whatsapp_url()) ?>" target="_blank" rel="noopener" class="theme-btn">Talk to our team</a>
                </div>

                <img src="<?= esc(asset('imgs/hero-company-about.jpg')) ?>"
                     alt="<?= esc(setting('about_image_alt')) ?>" loading="lazy" width="1360" height="480">

                <div class="hero-service-about-body">
                    <p>Corediva Tech Solutions has spent
                       <?= (int) date('Y') - (int) setting('founding_year') ?>+ years building the
                       systems mid-sized companies actually run on: ERP, CRM, AI automation and
                       cloud infrastructure. We're headquartered in Tiruchirappalli, India, with
                       representatives in the UAE and UK, and clients across Singapore, the US,
                       Australia and beyond. For US businesses, that means a team that already
                       understands the state-by-state sales tax patchwork and builds around it,
                       not a freelancer figuring it out on your dime.</p>
                    <ul class="cd-about-points cd-about-points-grid">
                        <li><i class="las la-check-circle" aria-hidden="true"></i> <?= esc(setting('credentials')) ?></li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Delivery hours overlapping US time zones, coast to coast</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Sales-tax-ready invoicing, itemized by state, built in</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Expertise: three practice areas, same pattern the homepage uses
         for its full 16-service catalogue. -->
    <section class="about-area" id="expertise">
        <div class="custom-container">
            <div class="section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h2 class="section-title">Three disciplines.<br>One accountable team.</h2>
                </div>
                <p>We don't split your project across three vendors. The same team that
                   architects your ERP also wires up the AI layer and ships the interface.</p>
            </div>

            <div class="cd-service-grid cd-expertise-grid" data-cd-reveal>
                <article class="service-card simple-shadow cd-expertise-card">
                    <span class="cd-skeleton" aria-hidden="true">
                        <span class="cd-skeleton-icon"></span>
                        <span class="cd-skeleton-line cd-skeleton-line-title"></span>
                        <span class="cd-skeleton-line"></span>
                        <span class="cd-skeleton-line cd-skeleton-line-short"></span>
                    </span>
                    <span class="cd-expertise-content">
                        <i class="las la-code cd-service-icon" aria-hidden="true"></i>
                        <h4>Custom Software Development</h4>
                        <p>Web platforms, e-commerce and internal tools built to your process,
                           not bent to fit a template.</p>
                    </span>
                </article>
                <article class="service-card simple-shadow cd-expertise-card">
                    <span class="cd-skeleton" aria-hidden="true">
                        <span class="cd-skeleton-icon"></span>
                        <span class="cd-skeleton-line cd-skeleton-line-title"></span>
                        <span class="cd-skeleton-line"></span>
                        <span class="cd-skeleton-line cd-skeleton-line-short"></span>
                    </span>
                    <span class="cd-expertise-content">
                        <i class="las la-layer-group cd-service-icon" aria-hidden="true"></i>
                        <h4>ERP & CRM Implementations</h4>
                        <p>SAP, Salesforce, Zoho and custom-built systems, configured to match
                           how finance, sales and ops already work.</p>
                    </span>
                </article>
                <article class="service-card simple-shadow cd-expertise-card">
                    <span class="cd-skeleton" aria-hidden="true">
                        <span class="cd-skeleton-icon"></span>
                        <span class="cd-skeleton-line cd-skeleton-line-title"></span>
                        <span class="cd-skeleton-line"></span>
                        <span class="cd-skeleton-line cd-skeleton-line-short"></span>
                    </span>
                    <span class="cd-expertise-content">
                        <i class="las la-robot cd-service-icon" aria-hidden="true"></i>
                        <h4>AI & Business Automation</h4>
                        <p>Lead qualification bots and workflow automation that cut the
                           manual work out of the funnel.</p>
                    </span>
                </article>
            </div>
        </div>
    </section>

    <!-- Where We Work: real office/representative locations. -->
    <section class="about-area" id="presence">
        <div class="custom-container">
            <div class="section-header text-center">
                <h2 class="section-title">A global presence, grounded in India.</h2>
            </div>

            <ul class="cd-presence-row" data-cd-stagger>
<?php foreach ($offices as $i => $office): ?>
                <li class="cd-presence-stop" style="--cd-stagger-index: <?= $i ?>">
                    <i class="las la-map-marker-alt" aria-hidden="true"></i>
                    <p class="cd-presence-city"><?= esc($office['city']) ?></p>
                    <p class="cd-presence-role"><?= esc($office['badge']) ?></p>
                </li>
<?php endforeach; ?>
            </ul>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home'       => 'us/',
    'Who We Are' => 'us/about/',
]);
include __DIR__ . '/../includes/footer.php';
?>
