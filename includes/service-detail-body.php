<?php
/**
 * Body for a single service-detail page. Included by each flat
 * sg/services/{slug}-singapore.php file, between header.php and
 * footer.php, with these already in scope:
 *
 *   $detailService      row from `services`
 *   $content      row from `service_content`, or null if not written yet
 *   $sections     rows from `service_page_sections` (render_service_sections()
 *                 already knows how to draw each type)
 *   $country      row from `countries`
 *   $lead_result  return value of save_lead(), or null
 *
 * Same shared-include pattern as header.php/footer.php: one physical file
 * per country per service (so nav_target()/hreflang_map() still gate on
 * is_file() correctly), the repeated section-loop markup lives here once.
 */

declare(strict_types=1);

$heroH1     = $content['h1'] ?? $detailService['title'];
$heroIntro  = $content['intro'] ?? ('<p>' . esc($detailService['short_description'] ?? '') . '</p>');
$servicesUrl = url($country['code'] . '/services/');
$heroBanner = service_card_image($detailService);
?>

    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
<?php if ($heroBanner): ?>
                <div class="cd-hero-video-content cd-hero-photo-banner">
                    <div class="cd-hero-video-bg" aria-hidden="true">
                        <img src="<?= esc($heroBanner) ?>" alt="">
                        <div class="cd-hero-video-overlay"></div>
                    </div>
<?php endif; ?>
                    <div class="hero-section-content text-center">
                        <ol class="cd-svc-breadcrumb">
                            <li><a href="<?= esc(country_url($country['code'])) ?>">Home</a></li>
                            <li><a href="<?= esc($servicesUrl) ?>">What We Do</a></li>
                            <li><?= esc($detailService['title']) ?></li>
                        </ol>
                        <?php if (!empty($detailService['category'])): ?>
                        <h5 class="section-subtitle"><?= esc($detailService['category']) ?></h5>
                        <?php endif; ?>
                        <h1 class="section-title"><?= esc($heroH1) ?></h1>
                        <div class="cd-svc-prose cd-svc-hero-intro"><?= $heroIntro ?></div>
                        <div class="cd-hero-service-cta">
                            <a href="#rfq-form" class="theme-btn">Request a Quote <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
                        </div>
                    </div>
<?php if ($heroBanner): ?>
                </div>
<?php endif; ?>
            </div>
        </div>
    </section>

<?php if (empty($sections)): ?>
    <section class="about-area cd-svc-richtext">
        <div class="custom-container cd-svc-richtext-inner">
            <p class="cd-svc-prose">We're still building out this page's detail sections. In the meantime, tell us
               what you need below and we'll reply within <?= esc(setting('response_time_hours', '4')) ?> hours.</p>
        </div>
    </section>
<?php else: ?>
    <?php render_service_sections($sections, $detailService, $country); ?>
<?php endif; ?>

    <section class="about-area cd-rfq-area">
        <div class="custom-container">
            <div class="cd-rfq-row">
                <div class="cd-rfq-col cd-rfq-form-col">
<?php $lead_variant = 'rfq'; $lead_service = $detailService; include __DIR__ . '/lead-form.php'; ?>
                </div>

                <div class="cd-rfq-col cd-rfq-media">
                    <img src="<?= esc(asset('imgs/service-details.jpg')) ?>" alt="Corediva engineer working on a client engagement">
                </div>
            </div>
        </div>
    </section>

<?php
$faqs = get_faqs('service', (int) $detailService['id'], (int) $country['id']);
render_schema_service($detailService, $content, $country);
render_schema_faq($faqs);
render_schema_breadcrumbs([
    'Home'             => $country['code'] . '/',
    'What We Do'       => $country['code'] . '/services/',
    $detailService['title']  => $country['code'] . '/services/' . $detailService['slug'] . '-' . $country['slug_suffix'] . '/',
]);
