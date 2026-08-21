<?php
/**
 * Partners -- alliances, tech stack and platform integrations (United Kingdom).
 * URL: /uk/partners/
 *
 * Same hero-service-wrap / service-area / consulting-area skeleton as
 * services.php and products.php. Global Alliances pulls real partner rows
 * (category='alliance') from the same `partners` table the homepage's
 * payments/cloud marquee uses (category='homepage') -- see get_partners().
 * Engineering Stack and Platform Integrations are static: they describe
 * the company's own tooling and social/API footprint, not per-country or
 * admin-editable content, so there's no table backing them, same as the
 * Consulting Excellence section below. Product Ecosystem is a teaser (the
 * featured products already on file, not a duplicate list) linking through
 * to the full /products/ catalogue rather than re-describing it here.
 */

declare(strict_types=1);

$country_code = 'uk';

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

$alliancePartners = get_partners('alliance');
$featuredProducts  = array_values(array_filter(get_products($countryId), static fn (array $p) => (bool) $p['is_featured']));

$pageSeo = get_page_seo('partners', $countryId);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'Partners & Technologies | Corediva Tech Solutions',
    'description'      => $pageSeo['meta_description'] ?? ('The alliances, engineering stack and platform integrations behind '
                         . "Corediva Tech Solutions' delivery for UK businesses."),
    'canonical'        => 'uk/partners/',
    'hreflang_pattern' => 'partners',
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: same confined-to-CTA-block video treatment as services.php
         -- hero-service-about stays a plain sibling, outside the video. -->
    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="cd-hero-video-content">
                    <div class="cd-hero-video-bg" aria-hidden="true">
                        <video autoplay muted loop playsinline>
                            <source src="<?= esc(asset(setting('hero_video_partners', 'video/3.mp4'))) ?>" type="video/mp4">
                        </video>
                        <div class="cd-hero-video-overlay"></div>
                    </div>
                    <div class="hero-section-content text-center">
                        <h5 class="section-subtitle">Our Ecosystem</h5>
                        <h1 class="section-title">Real partners. Real technology stack.</h1>
                        <p>Every alliance, tool and platform integration on this page is one we actually use,
                           not an aspirational list. See who we build alongside and what we build with.</p>
                        <div class="cd-hero-service-cta">
                            <a href="#rfq-form" class="theme-btn">Request a Quote <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="hero-service-about">
                    <img src="<?= esc(asset('imgs/hero-service-about.jpg')) ?>" alt="Corediva Tech Solutions delivery team at work">
                    <div class="hero-service-about-body">
                        <p>From engineering alliances to the cloud and AI platforms behind every build, this
                           is the ecosystem your project actually runs on.</p>
                        <ul>
                            <li><i class="las la-check"></i> <?= esc(setting('credentials')) ?></li>
                            <li><i class="las la-check"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                            <li><i class="las la-check"></i> HMRC Making Tax Digital-ready invoicing</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Global Alliances: real partner organizations (category='alliance'
         in `partners`), logo card if we have artwork, wordmark card if not
         -- same graceful-degradation the homepage marquee already uses. -->
    <section class="service-area" id="alliances">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">Global Alliances</h5>
                <h2 class="section-title">Our trusted partners</h2>
                <p>We work alongside enterprises, institutions and development agencies who extend what we
                   can deliver in infrastructure, compliance and reach.</p>
            </div>

            <div class="cd-alliance-grid">
<?php foreach ($alliancePartners as $partner): ?>
                <div class="cd-alliance-card simple-shadow">
<?php if ($partner['logo']): ?>
                    <img src="<?= esc(asset($partner['logo'])) ?>" alt="<?= esc($partner['logo_alt'] ?? $partner['name']) ?>" loading="lazy">
<?php else: ?>
                    <span class="cd-alliance-wordmark"><?= esc($partner['name']) ?></span>
<?php endif; ?>
                    <h4><?= esc($partner['name']) ?></h4>
                    <p><?= esc($partner['description']) ?></p>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Engineering Stack: the company's own tooling, grouped exactly as
         it is internally -- static, not per-country, no DB table. -->
    <section class="cd-tech-area" id="stack">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle cd-tech-eyebrow">Engineering Stack</h5>
                <h2 class="section-title cd-tech-title">Our technologies</h2>
                <p class="cd-tech-lede">Vetted, current tooling behind every build, not a buzzword list.</p>
            </div>

            <div class="cd-tech-grid">
                <div class="cd-tech-col">
                    <h3><i class="las la-code" aria-hidden="true"></i> Full-Stack Dev</h3>
                    <div class="cd-tech-pills">
<?php foreach (['Python', 'PHP', 'Node.js', 'JavaScript', 'React', '.NET', 'Vue.js', 'Laravel', 'PostgreSQL'] as $tech): ?>
                        <span class="cd-tech-pill"><?= esc($tech) ?></span>
<?php endforeach; ?>
                    </div>
                </div>
                <div class="cd-tech-col">
                    <h3><i class="las la-pen-nib" aria-hidden="true"></i> UI/UX &amp; Design</h3>
                    <div class="cd-tech-pills">
<?php foreach (['Figma', 'Adobe Photoshop', 'Adobe Illustrator', 'Adobe Premiere', 'Adobe After Effects', 'Canva Pro'] as $tech): ?>
                        <span class="cd-tech-pill"><?= esc($tech) ?></span>
<?php endforeach; ?>
                    </div>
                </div>
                <div class="cd-tech-col">
                    <h3><i class="las la-cloud" aria-hidden="true"></i> Hosting &amp; Cloud</h3>
                    <div class="cd-tech-pills">
<?php foreach (['AWS', 'Azure', 'Google Cloud', 'DigitalOcean', 'Synology', 'VMM', 'Namecheap', 'Hostinger'] as $tech): ?>
                        <span class="cd-tech-pill"><?= esc($tech) ?></span>
<?php endforeach; ?>
                    </div>
                </div>
                <div class="cd-tech-col">
                    <h3><i class="las la-brain" aria-hidden="true"></i> Core AI Modules</h3>
                    <div class="cd-tech-pills">
<?php foreach (['ChatGPT', 'Gemini', 'Claude', 'Groq API', 'DeepSeek', 'Llama 3'] as $tech): ?>
                        <span class="cd-tech-pill"><?= esc($tech) ?></span>
<?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php if ($featuredProducts): ?>
    <!-- Product Ecosystem: a teaser of the featured rows already in
         `products`, not a re-description -- the full catalogue lives on
         /products/, this just proves it exists and links through. -->
    <section class="service-area" id="product-ecosystem">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">Proprietary Assets</h5>
                <h2 class="section-title">Our product ecosystem</h2>
                <p>Business software we've built and maintain ourselves, ready to deploy as-is or as a
                   starting point for something custom.</p>
            </div>

            <div class="cd-service-grid">
<?php foreach ($featuredProducts as $prod): ?>
                <a href="<?= esc(product_url($prod['slug'], $country)) ?>" class="service-card simple-shadow">
                    <i class="<?= esc($prod['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                    <h4><?= esc($prod['display_title']) ?></h4>
                    <p><?= esc($prod['short_description']) ?></p>
                </a>
<?php endforeach; ?>
            </div>

            <div class="cd-section-cta">
                <a href="<?= esc(url($country['code'] . '/products/')) ?>" class="theme-btn theme-btn-outline">See all products <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </section>
<?php endif; ?>

    <!-- Platform Integrations: icon-font brand marks, same as the
         corporate site -- these are glyphs, not logo artwork, so no
         asset download was needed. -->
    <section class="service-area cd-integrations-area" id="integrations">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">Ecosystem Connectivity</h5>
                <h2 class="section-title">Supported platform integrations</h2>
                <p>Our builds connect cleanly to the channels your customers and your team already use.</p>
            </div>

            <div class="cd-integration-grid">
<?php foreach ([
    'Google' => 'lab la-google', 'Microsoft' => 'lab la-microsoft', 'Meta' => 'lab la-facebook-f',
    'YouTube' => 'lab la-youtube', 'WhatsApp' => 'lab la-whatsapp', 'Instagram' => 'lab la-instagram',
    'X Platform' => 'lab la-twitter', 'Medium' => 'lab la-medium-m', 'Salesforce' => 'lab la-salesforce',
    'LinkedIn' => 'lab la-linkedin-in', 'HubSpot' => 'lab la-hubspot', 'Slack' => 'lab la-slack-hash',
] as $label => $iconClass): ?>
                <div class="cd-integration-card simple-shadow">
                    <i class="<?= esc($iconClass) ?>" aria-hidden="true"></i>
                    <span><?= esc($label) ?></span>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Consulting Excellence: same real copy as the services/products hub
         pages -- reused rather than rewritten, per the same reasoning
         services.php already documents. -->
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
                            Invoicing formatted for HMRC's Making Tax Digital requirements, built in by default</li>
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

    <!-- RFQ: same 50/50 form/image split as services.php/products.php. -->
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
    'Home'     => 'uk/',
    'Partners' => 'uk/partners/',
]);
include __DIR__ . '/../includes/footer.php';
?>
