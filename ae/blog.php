<?php
/**
 * Blog -- post listing (United Arab Emirates).
 * URL: /ae/blog/
 */

declare(strict_types=1);

$country_code = 'ae';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$posts = get_posts($countryId, 24);

$pageSeo = get_page_seo('blog', $countryId);
$seo = [
    'title'            => $pageSeo['meta_title'] ?? 'Blog | Corediva Tech Solutions UAE',
    'description'      => $pageSeo['meta_description'] ?? 'Insights on ERP, AI automation, cloud infrastructure and cybersecurity from the Corediva Tech Solutions engineering team.',
    'canonical'        => 'ae/blog/',
    'hreflang_pattern' => 'blog',
    'noindex'          => (bool) ($pageSeo['is_noindex'] ?? false),
];

include __DIR__ . '/../includes/header.php';
?>

    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <h5 class="section-subtitle">Insights</h5>
                    <h1 class="section-title">Blog</h1>
                    <p>Notes on ERP, AI automation, cloud infrastructure and cybersecurity from the
                       team that ships them.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="service-area" id="posts">
        <div class="custom-container">
<?php if ($posts): ?>
            <div class="cd-service-grid">
<?php foreach ($posts as $post): ?>
                <a href="<?= esc(blog_url($post['slug'], $country)) ?>" class="service-card simple-shadow">
<?php if ($post['category']): ?>
                    <p class="cd-product-category"><?= esc($post['category']) ?></p>
<?php endif; ?>
                    <h4><?= esc($post['title']) ?></h4>
<?php if ($post['excerpt']): ?>
                    <p><?= esc($post['excerpt']) ?></p>
<?php endif; ?>
<?php if ($post['published_at']): ?>
                    <p class="cd-product-category"><?= esc(date('j M Y', strtotime((string) $post['published_at']))) ?></p>
<?php endif; ?>
                </a>
<?php endforeach; ?>
            </div>
<?php else: ?>
            <div class="alert alert-info" role="status">
                <p class="mb-0">Nothing published yet -- check back soon.</p>
            </div>
<?php endif; ?>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home' => 'ae/',
    'Blog' => 'ae/blog/',
]);
include __DIR__ . '/../includes/footer.php';
?>
