<?php
/**
 * Singapore landing page (primary market).
 * URL: /sg/  -- the root / 302-redirects here.
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

// Handle the lead form before any output, so we can redirect/re-render cleanly.
$lead_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lead') {
    $lead_result = save_lead($_POST);
}

$slides   = get_hero_slides($countryId);
$features = get_hero_feature_rows();
$stats    = get_stats();
$services = get_services();
$products = get_products($countryId);
$partners = get_partners();
$faqs     = get_faqs('home', null, $countryId);

// Group services by category for a readable IA rather than a flat 16-card wall.
$servicesByCategory = [];
foreach ($services as $svc) {
    $servicesByCategory[$svc['category'] ?: 'Other'][] = $svc;
}

$seo = [
    'title'            => 'IT, ERP & AI Solutions Company in Singapore | Corediva',
    'description'      => 'Enterprise ERP, AI chatbots, cloud & cybersecurity for Singapore businesses. 4-hour response. Get a free technical assessment today.',
    'canonical'        => 'sg/',
    'hreflang_pattern' => '',
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: rotating message on the left, lead form pinned on the right -->
    <section class="cd-hero">
        <div class="cd-container">
            <div class="cd-hero-grid">

                <div class="cd-hero-left">
                    <div class="swiper cd-hero-swiper">
                        <div class="swiper-wrapper">
<?php foreach ($slides as $i => $slide): ?>
<?php
    // Exactly one H1 per page. The lead slide (Singapore-specific here, because
    // country rows sort ahead of global ones) carries it; the rest are H2.
    $headingTag = $i === 0 ? 'h1' : 'h2';
?>
                            <div class="swiper-slide">
                                <div class="cd-hero-slide">
<?php if ($slide['badge']): ?>
                                    <span class="cd-hero-badge"><?= esc($slide['badge']) ?></span>
<?php endif; ?>
                                    <<?= $headingTag ?> class="cd-hero-title">
                                        <?= highlight_heading($slide['heading'], $slide['heading_highlight']) ?>
                                    </<?= $headingTag ?>>
<?php if ($slide['subheading']): ?>
                                    <p class="cd-hero-sub"><?= esc($slide['subheading']) ?></p>
<?php endif; ?>
                                    <div class="cd-hero-ctas">
<?php if ($slide['cta1_text']): ?>
                                        <a href="<?= esc($slide['cta1_url'] ?: '#contact') ?>" class="theme-btn">
                                            <?= esc($slide['cta1_text']) ?> <i class="iconoir-arrow-up-right" aria-hidden="true"></i>
                                        </a>
<?php endif; ?>
<?php if ($slide['cta2_text']): ?>
                                        <a href="<?= esc($slide['cta2_url'] ?: '#services') ?>" class="theme-btn2">
                                            <?= esc($slide['cta2_text']) ?>
                                        </a>
<?php endif; ?>
                                    </div>
                                </div>
                            </div>
<?php endforeach; ?>
                        </div>
                        <div class="cd-hero-pagination"></div>
                    </div>

                    <ul class="cd-hero-trust">
<?php foreach ($features as $feature): ?>
                        <li>
                            <i class="<?= esc($feature['icon']) ?>" aria-hidden="true"></i>
                            <span><?= esc($feature['title']) ?></span>
                        </li>
<?php endforeach; ?>
                    </ul>
                </div>

                <div class="cd-hero-right">
<?php
$lead_variant = 'hero';
include __DIR__ . '/../includes/lead-form.php';
?>
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

    <!-- Services -->
    <section class="service-area" id="services">
        <div class="custom-container">
            <div class="service-section-header section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h5 class="section-subtitle">WHAT WE DO</h5>
                    <h2 class="section-title">Enterprise technology,<br>delivered end to end.</h2>
                </div>
                <p>Sixteen services across web, ERP, AI, cloud and marketing. Architected,
                   built and supported by one team, so nothing falls between vendors.</p>
            </div>

<?php foreach ($servicesByCategory as $category => $items): ?>
            <div class="cd-service-group">
                <h3 class="cd-service-group-title"><?= esc($category) ?></h3>
                <div class="cd-service-grid">
<?php foreach ($items as $svc): ?>
                    <article class="service-card simple-shadow">
                        <i class="<?= esc($svc['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                        <h4><?= esc($svc['title']) ?></h4>
                        <p><?= esc($svc['short_description']) ?></p>
                    </article>
<?php endforeach; ?>
                </div>
            </div>
<?php endforeach; ?>
        </div>
    </section>

    <!-- About -->
    <section class="about-area" id="about">
        <div class="custom-container">
            <div class="cd-about-grid">
                <div class="cd-about-copy">
                    <h5 class="section-subtitle">WHO WE ARE</h5>
                    <h2 class="section-title">Headquartered in India.<br>Engineered for Singapore.</h2>
                    <p>Corediva Tech Solutions has been building enterprise systems since
                       <?= esc(setting('founding_year')) ?>: custom ERP and CRM platforms, AI and
                       WhatsApp automation, cloud infrastructure and cybersecurity. We work with
                       Singapore SMEs that need enterprise-grade engineering without
                       enterprise-grade overhead.</p>
                    <p>Most enquiries in Singapore arrive on WhatsApp, and a slow reply loses the
                       deal. Everything we build (chatbots, CRM integrations, lead routing) is
                       designed around that reality, and around PDPA-compliant data handling.</p>
                    <ul class="cd-about-points">
                        <li><i class="las la-check-circle" aria-hidden="true"></i> <?= esc(setting('credentials')) ?></li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> Delivery hours aligned to SGT (UTC+8)</li>
                        <li><i class="las la-check-circle" aria-hidden="true"></i> PDPA-aligned data handling on every build</li>
                    </ul>
                    <a href="#contact" class="theme-btn">Talk to our team</a>
                </div>
                <div class="cd-about-media">
                    <img src="<?= esc(asset('imgs/about-service-2.png')) ?>"
                         alt="<?= esc(setting('about_image_alt')) ?>" loading="lazy" width="560" height="420">
                </div>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="project-area" id="products">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">PRODUCTS</h5>
                <h2 class="section-title">Ready-to-deploy platforms</h2>
                <p>Twelve products already running in production. Deploy as-is, or use one as the
                   foundation for something built to your process.</p>
            </div>

            <div class="cd-product-grid">
<?php foreach ($products as $product): ?>
                <article class="service-card simple-shadow<?= $product['is_featured'] ? ' cd-featured' : '' ?>">
<?php if ($product['is_featured']): ?>
                    <span class="service-badge">Popular</span>
<?php endif; ?>
                    <i class="<?= esc($product['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                    <h3><?= esc($product['display_title']) ?></h3>
                    <p class="cd-product-category"><?= esc($product['category']) ?></p>
                    <p><?= esc($product['short_description']) ?></p>
                </article>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Partners -->
    <section class="client-area cd-partners" id="partners">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">PARTNERS</h5>
                <h2 class="section-title">Who we build with</h2>
            </div>
            <?php /* Mark + name lockup rather than a bare logo wall. The
                     available marks are glyph-only monograms (PayPal's "P",
                     Payoneer's swoosh), which identify nobody on their own,
                     so the name carries the recognition and the mark carries
                     the brand. Partners with no artwork yet show the name
                     alone and the row still reads as one set.
                     Deliberately no category label under each: that turns a
                     logo row into a caption grid and tells the reader
                     nothing they can't infer. */ ?>
            <ul class="cd-partner-grid">
<?php foreach ($partners as $partner): ?>
                <li class="cd-partner">
                    <a href="<?= esc($partner['url'] ?: '#') ?>" target="_blank" rel="noopener"
                       title="<?= esc($partner['name'] . ': ' . $partner['description']) ?>">
<?php if ($partner['logo']): ?>
                        <img class="cd-partner-mark" src="<?= esc(asset($partner['logo'])) ?>"
                             alt="" aria-hidden="true" width="30" height="30">
<?php endif; ?>
                        <span class="cd-partner-name"><?= esc($partner['name']) ?></span>
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-area cd-faq" id="faq">
        <div class="custom-container">
            <div class="section-header text-center">
                <h5 class="section-subtitle">FAQ</h5>
                <h2 class="section-title">Questions we get from Singapore</h2>
            </div>

            <div class="accordion cd-accordion" id="faqAccordion">
<?php foreach ($faqs as $i => $faq): ?>
                <div class="accordion-item">
                    <h3 class="accordion-header" id="faq-heading-<?= $i ?>">
                        <button class="accordion-button<?= $i === 0 ? '' : ' collapsed' ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq-body-<?= $i ?>"
                                aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                aria-controls="faq-body-<?= $i ?>">
                            <?= esc($faq['question']) ?>
                        </button>
                    </h3>
                    <div id="faq-body-<?= $i ?>" class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>"
                         aria-labelledby="faq-heading-<?= $i ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body"><?= esc($faq['answer']) ?></div>
                    </div>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section class="contact-area" id="contact">
        <div class="custom-container">
            <div class="cd-contact-grid">
                <div class="cd-contact-copy">
                    <h5 class="section-subtitle">GET IN TOUCH</h5>
                    <h2 class="section-title">Tell us what you're building.</h2>
                    <p>Send us the problem, not a spec. We'll come back within
                       <?= esc(setting('response_time_hours')) ?> hours with a technical read on it.
                       No obligation, no sales sequence.</p>

                    <ul class="cd-contact-list">
                        <li>
                            <i class="las la-phone" aria-hidden="true"></i>
                            <a href="tel:<?= esc(setting('phone_e164')) ?>"><?= esc(setting('phone_display')) ?></a>
                        </li>
                        <li>
                            <i class="las la-envelope" aria-hidden="true"></i>
                            <a href="mailto:<?= esc(setting('email')) ?>"><?= esc(setting('email')) ?></a>
                        </li>
                        <li>
                            <i class="lab la-whatsapp" aria-hidden="true"></i>
                            <a href="<?= esc(whatsapp_url()) ?>" target="_blank" rel="noopener">WhatsApp us</a>
                        </li>
                    </ul>
                </div>

                <div class="cd-contact-form">
<?php
$lead_variant = 'full';
include __DIR__ . '/../includes/lead-form.php';
?>
                </div>
            </div>
        </div>
    </section>

<?php
render_schema_faq($faqs);
include __DIR__ . '/../includes/footer.php';
?>
