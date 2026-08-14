<?php
/**
 * Body for a single product-detail page. Included by each flat
 * sg/products/{slug}-singapore.php file, between header.php and
 * footer.php, with these already in scope:
 *
 *   $product      row from `products`
 *   $content      row from `product_content`, or null if not written yet
 *   $sections     rows from `product_page_sections` (render_product_sections()
 *                 already knows how to draw each type)
 *   $country      row from `countries`
 *   $lead_result  return value of save_lead(), or null
 *
 * Same shared-include pattern as includes/service-detail-body.php. No
 * $product/$service naming collision to work around here -- header.php's
 * mega-menu loop only exists for services (mega_type='services'), so
 * unlike service-detail-body.php this doesn't need a renamed variable.
 */

declare(strict_types=1);

$heroH1      = $content['h1'] ?? $product['title'];
$heroIntro   = $content['intro'] ?? ('<p>' . esc($product['short_description'] ?? '') . '</p>');
$productsUrl = url($country['code'] . '/products/');
?>

    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <ol class="cd-svc-breadcrumb">
                        <li><a href="<?= esc(country_url($country['code'])) ?>">Home</a></li>
                        <li><a href="<?= esc($productsUrl) ?>">Products</a></li>
                        <li><?= esc($product['title']) ?></li>
                    </ol>
                    <?php if (!empty($product['category'])): ?>
                    <h5 class="section-subtitle"><?= esc($product['category']) ?></h5>
                    <?php endif; ?>
                    <h1 class="section-title"><?= esc($heroH1) ?></h1>
                    <div class="cd-svc-prose cd-svc-hero-intro"><?= $heroIntro ?></div>
                    <div class="cd-hero-service-cta">
                        <a href="#rfq-form" class="theme-btn">Request a Quote <i class="iconoir-arrow-up-right" aria-hidden="true"></i></a>
                    </div>
                </div>
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
    <?php render_product_sections($sections, $product, $country); ?>
<?php endif; ?>

    <section class="about-area cd-rfq-area">
        <div class="custom-container">
            <div class="cd-rfq-row">
                <div class="cd-rfq-col cd-rfq-form-col">
<?php $lead_variant = 'rfq'; $lead_service = $product; $lead_label = 'Product'; include __DIR__ . '/lead-form.php'; ?>
                </div>

                <div class="cd-rfq-col cd-rfq-media">
                    <img src="<?= esc(asset('imgs/service-details.jpg')) ?>" alt="Corediva engineer working on a client engagement">
                </div>
            </div>
        </div>
    </section>

<?php
$faqs = get_faqs('product', (int) $product['id'], (int) $country['id']);
render_schema_product($product, $content, $country);
render_schema_faq($faqs);
render_schema_breadcrumbs([
    'Home'               => $country['code'] . '/',
    'Products'           => $country['code'] . '/products/',
    $product['title']    => $country['code'] . '/products/' . $product['slug'] . '-' . $country['slug_suffix'] . '/',
]);
