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

$stats    = get_stats();
$partners = get_partners();
$offices  = get_offices();

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

    <!-- Partners: same cd-partner-band marquee as the homepage, reused
         verbatim rather than re-styled -- it's the same 5 real partners
         (payments, banking, cloud), just placed here so this page states
         them too. Also gives the nav's "Partners" -> #partners anchor
         something to land on on this page, which it didn't before. -->
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
         DGFT (IEC), MSME, DUNS, CERSAI and CKYC. cd-cert-grid sizes each
         badge by height rather than width, since the source marks are
         wildly different aspect ratios (IEC is a wide banner, the others
         are closer to square) and matching height is what actually reads
         as "one consistent row" the way the theme's own logo strips do. -->
    <section class="about-area" id="certifications">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">Global Trust &amp; Verification</h5>
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

    <!-- Where We Work: real office/representative locations, replacing
         the CTA that used to sit here -- it duplicated "Our Company"'s
         own "Talk to our team" button (same WhatsApp link, same intent).
         No eyebrow: this page already has one above every section, so a
         fifth would be excessive. A stagger fade-in rather than the
         Expertise section's skeleton -- there's no data to wait on here,
         a skeleton would be theatre, not a loading state. -->
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
    'Home'       => 'sg/',
    'Who We Are' => 'sg/about/',
]);
include __DIR__ . '/../includes/footer.php';
?>
