<?php
/**
 * Who We Are -- company/about page (Singapore).
 * URL: /sg/about/
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

$stats = get_stats();

$seo = [
    'title'            => 'Who We Are | Corediva Tech Solutions Singapore',
    'description'      => 'A decade-old, MSME & DUNS certified engineering team headquartered in '
                         . 'India, building ERP, CRM, AI and cloud systems for Singapore SMEs.',
    'canonical'        => 'sg/about/',
    'hreflang_pattern' => 'about',
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: on the theme's about.html "hero-about-wrap" layout, with the
         four stat boxes fed from real data instead of the theme's demo
         figures. Figures are marked up as p.cd-stat-value (not <h1>), same
         fix as the homepage's stats strip -- data, not document structure. -->
    <section class="hero-service-wrap hero-section-wrap hero-about-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-portfolio-body">
                    <div class="hero-section-content text-center">
                        <h5 class="section-subtitle">Who We Are</h5>
                        <h1 class="section-title">Enterprise engineering, built for how Singapore buys.</h1>
                        <p>Corediva Tech Solutions is the team behind the ERP, CRM, AI automation
                           and cloud projects Singapore SMEs run on -- MSME and DUNS certified, and
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

    <!-- Our company: banner panel + checklist, on the theme's about.html
         "company-service-area" panel (hero-service-about), wrapped in
         .about-area rather than .company-service-area so the section
         spacing matches the rest of the site instead of the theme's
         180px rhythm (tuned for the extra sections this page doesn't
         carry). cd-about-points keeps its own grid layout rather than the
         theme's single-row flex <ul>, hand-tuned for three short words and
         too narrow for our longer, real checklist copy. -->
    <section class="about-area" id="company">
        <div class="custom-container">
            <div class="hero-service-about simple-shadow">
                <div class="section-header d-flex align-items-center justify-content-between w-full">
                    <div class="left">
                        <h5 class="section-subtitle">OUR COMPANY</h5>
                        <h2 class="section-title">Headquartered in India.<br>Engineered for Singapore.</h2>
                        <p>An engineering team built for how Singapore buys, backed by a decade
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
                       representatives in the UK and UAE, and clients across Singapore, the US,
                       Australia and beyond. For Singapore SMEs, that means a team that has
                       already solved the scaling problems you're about to hit, not a freelancer
                       figuring it out on your dime.</p>
                    <ul class="cd-about-points cd-about-points-grid">
                        <li><i class="las la-check-circle" aria-hidden="true"></i> <?= esc(setting('credentials')) ?></li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Delivery hours aligned to SGT (UTC+8)</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> PDPA-aligned data handling on every build</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Expertise: three practice areas, on the same service-card
         pattern the homepage uses for its full 16-service catalogue --
         .cd-expertise-grid just pins it to 3 columns instead of 4 so a
         3-item row doesn't leave a gap. Each card carries a skeleton
         placeholder that shimmers until cd-reveal.js swaps it for the real
         content once the grid scrolls into view (see corediva.js). -->
    <section class="about-area" id="expertise">
        <div class="custom-container">
            <div class="section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h5 class="section-subtitle">OUR EXPERTISE</h5>
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
                        <p>WhatsApp bots, lead qualification and workflow automation that cut
                           the manual work out of the funnel.</p>
                    </span>
                </article>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-area">
        <div class="custom-container">
            <div class="cta-body text-center">
                <h2>Need to talk to someone first?</h2>
                <p>Tell us what you're building and we'll route it to the right engineer.</p>
                <a href="<?= esc(whatsapp_url()) ?>" target="_blank" rel="noopener" class="theme-btn">Chat on WhatsApp</a>
            </div>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home'       => 'sg/',
    'Who We Are' => 'sg/about/',
]);
include __DIR__ . '/../includes/footer.php';
?>
