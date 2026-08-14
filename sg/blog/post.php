<?php
/**
 * Blog post detail (Singapore). One shared file serves every slug, unlike
 * services/products -- posts are added over time via admin, not from a
 * fixed catalogue, so there's no per-slug flat file to generate.
 * URL: /sg/blog/{slug}/  ->  /sg/blog/post.php?slug={slug}
 */

declare(strict_types=1);

$country_code = 'sg';

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

$country = get_country($country_code);
if (!$country) {
    http_response_code(404);
    exit('Country not configured.');
}
$countryId = (int) $country['id'];

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? get_post($slug, $countryId) : null;
if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}

// No hreflang for individual posts -- a post is not guaranteed to have an
// equivalent translation at the same slug in another country, so this
// self-canonicalizes only rather than claiming a false alternate-language
// relationship the way a service/product detail page can.
$seo = [
    'title'            => $post['meta_title'] ?? ($post['title'] . ' | Corediva Tech Solutions'),
    'description'      => $post['meta_description'] ?? $post['excerpt'],
    'canonical'        => 'sg/blog/' . $post['slug'] . '/',
    'hreflang_pattern' => '',
];

include __DIR__ . '/../../includes/header.php';
?>

    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
<?php if ($post['category']): ?>
                    <h5 class="section-subtitle"><?= esc($post['category']) ?></h5>
<?php endif; ?>
                    <h1 class="section-title"><?= esc($post['title']) ?></h1>
                    <p>
<?php if ($post['author']): ?>
                        By <?= esc($post['author']) ?><?php if ($post['published_at']): ?> &middot; <?php endif; ?>
<?php endif; ?>
<?php if ($post['published_at']): ?>
                        <?= esc(date('j F Y', strtotime((string) $post['published_at']))) ?>
<?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="about-area cd-svc-richtext">
        <div class="custom-container cd-svc-richtext-inner">
<?php if ($post['featured_image']): ?>
            <img src="<?= esc(asset($post['featured_image'])) ?>" alt="<?= esc($post['featured_image_alt'] ?? '') ?>" loading="lazy" style="width:100%;height:auto;border-radius:12px;margin-bottom:2rem;">
<?php endif; ?>
<?php if ($post['body']): ?>
            <div class="cd-svc-prose"><?= $post['body'] ?></div>
<?php endif; ?>
        </div>
    </section>

<?php
render_schema_breadcrumbs([
    'Home' => 'sg/',
    'Blog' => 'sg/blog/',
    $post['title'] => 'sg/blog/' . $post['slug'] . '/',
]);
include __DIR__ . '/../../includes/footer.php';
?>
