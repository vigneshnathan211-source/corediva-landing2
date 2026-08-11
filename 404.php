<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);

$country = get_country((string) cfg('app.default_country', 'sg'));

$seo = [
    'title'       => 'Page not found | ' . setting('site_name'),
    'description' => 'The page you were looking for could not be found.',
    'canonical'   => '404',
    'noindex'     => true,
];

include __DIR__ . '/includes/header.php';
?>

    <section class="cd-stats" style="padding:120px 0;text-align:center;">
        <div class="custom-container">
            <h5 class="section-subtitle">404</h5>
            <h1 class="section-title">We couldn&rsquo;t find that page.</h1>
            <p style="max-width:520px;margin:20px auto 32px;color:var(--dark2);">
                The link may be out of date, or the page may have moved.
            </p>
            <a href="<?= esc(country_url($country['code'])) ?>" class="theme-btn">Back to home</a>
        </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
