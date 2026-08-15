<?php
/**
 * Shared helpers: content getters, URL builders, SEO/schema emitters,
 * CSRF, and lead capture.
 *
 * Every query here goes through the db_* helpers, which use real prepared
 * statements. Nothing in this file interpolates a variable into SQL.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// =====================================================================
// Basics
// =====================================================================

/**
 * Escape for HTML output. Use on every short/plain-text field.
 *
 * Accepts any scalar, not just string: templates routinely interpolate
 * counts and IDs, and under strict_types a bare `?string` hint turns
 * esc(count($x)) into a fatal TypeError that takes the rest of the page
 * with it. Widening here is safer than relying on every call site to cast.
 */
function esc(string|int|float|bool|null $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Read a config value by dot path: cfg('app.base_url'). */
function cfg(string $path, $default = null)
{
    $parts = explode('.', $path);
    $node  = $GLOBALS['corediva_config'];
    foreach ($parts as $p) {
        if (!is_array($node) || !array_key_exists($p, $node)) {
            return $default;
        }
        $node = $node[$p];
    }
    return $node;
}

/**
 * Site base URL with no trailing slash.
 *
 * In debug mode, derived from the incoming request instead of
 * config.local.php's `base_url`. Local dev servers change port depending on
 * how they're launched -- `php -S 127.0.0.1:8000`, the VS Code PHP Server
 * extension, a different port picked because 8000 was busy -- and a
 * hardcoded value silently breaks every asset URL the moment the port
 * doesn't match. Production has no HTTP_HOST override risk: debug is always
 * false there, so `app.base_url` (the real domain) is what renders.
 *
 * X-Forwarded-Host/-Proto win over Host/HTTPS when present: a reverse
 * proxy sitting in front of the dev server (a Cloudflare Tunnel, ngrok)
 * commonly rewrites the Host header to match its own connection to the
 * origin -- cloudflared quick tunnels send Host: localhost:3000 to the
 * origin regardless of the public hostname the visitor actually used --
 * while still forwarding the original public host/scheme in the
 * X-Forwarded-* pair. Trusting client-supplied headers is normally a
 * spoofing risk, but this whole branch only runs when app.debug is true,
 * which is never the case in production.
 */
function base_url(): string
{
    if (cfg('app.debug', false)) {
        // A chain of proxies appends to these comma-separated, like
        // X-Forwarded-For -- the first entry is the original client-facing
        // hop, which is the one that matters here.
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
        $host = trim(explode(',', $host)[0]);
        if ($host !== '') {
            $scheme = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0])
                ?: ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
            return $scheme . '://' . $host;
        }
    }

    return rtrim((string) cfg('app.base_url', ''), '/');
}

/** Absolute URL for a site-relative path. */
function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

/**
 * Asset URL, resolved from the site root so it works at any folder depth.
 *
 * Appends a `?v=<mtime>` query string so the year-long browser cache set
 * in .htaccess (ExpiresByType, needed for PageSpeed) busts itself on every
 * edit instead of serving a stale file until someone manually clears their
 * cache -- the failure mode is silent (an old asset just keeps loading
 * with no error), so this has to be automatic rather than a step someone
 * remembers to do on deploy.
 */
function asset(string $path): string
{
    $relative = 'assets/' . ltrim($path, '/');
    $full     = dirname(__DIR__) . '/' . $relative;
    $version  = is_file($full) ? filemtime($full) : false;

    return url($relative) . ($version !== false ? '?v=' . $version : '');
}

/**
 * Asset URL for content read outside the current request, e.g. an
 * emailed <img>. asset()/url() follow the request's Host header in
 * debug mode so a page renders correctly from whatever local or tunnel
 * address served it -- but an email's image is fetched by the
 * recipient's mail server later, which has never heard of localhost.
 * Always resolves against the configured app.base_url, debug mode or not.
 */
function public_asset(string $path): string
{
    $relative = 'assets/' . ltrim($path, '/');
    $full     = dirname(__DIR__) . '/' . $relative;
    $version  = is_file($full) ? filemtime($full) : false;
    $base     = rtrim((string) cfg('app.base_url', ''), '/');

    return $base . '/' . $relative . ($version !== false ? '?v=' . $version : '');
}

/** Best-effort client IP as a packed binary string for storage. */
function client_ip_binary(): ?string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip === '') {
        return null;
    }
    $packed = @inet_pton($ip);
    return $packed === false ? null : $packed;
}

// =====================================================================
// Site settings / global chrome
// =====================================================================

/** All site_settings as a key => value map (loaded once per request). */
function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db_all('SELECT setting_key, setting_value FROM site_settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache;
}

function setting(string $key, ?string $default = null): ?string
{
    $all = settings();
    return $all[$key] ?? $default;
}

function get_stats(): array
{
    return db_all('SELECT * FROM stats WHERE is_active = 1 ORDER BY sort_order');
}

function get_partners(): array
{
    return db_all('SELECT * FROM partners WHERE is_active = 1 ORDER BY sort_order');
}

function get_social_links(): array
{
    return db_all('SELECT * FROM social_links WHERE is_active = 1 ORDER BY sort_order');
}

function get_offices(?int $country_id = null): array
{
    if ($country_id !== null) {
        return db_all(
            'SELECT * FROM offices WHERE is_active = 1 AND country_id = ? ORDER BY sort_order',
            [$country_id]
        );
    }
    return db_all('SELECT * FROM offices WHERE is_active = 1 ORDER BY sort_order');
}

function get_nav_items(): array
{
    return db_all('SELECT * FROM nav_items WHERE is_active = 1 AND parent_id IS NULL ORDER BY sort_order');
}

/** Top-level nav items with their children attached. */
function get_nav_tree(): array
{
    $items    = get_nav_items();
    $children = db_all('SELECT * FROM nav_items WHERE is_active = 1 AND parent_id IS NOT NULL ORDER BY sort_order');

    $byParent = [];
    foreach ($children as $child) {
        $byParent[(int) $child['parent_id']][] = $child;
    }

    foreach ($items as &$item) {
        $item['children'] = $byParent[(int) $item['id']] ?? [];
    }
    unset($item);

    return $items;
}

/**
 * Resolve a stored nav URL for the current country.
 *
 * Returns null when the target page does not exist yet, so the caller can
 * render plain text instead of a link. The intended menu structure stays
 * visible without shipping dead links, and entries light up on their own
 * as pages land on disk.
 */
function nav_target(?string $stored, array $country): ?string
{
    if ($stored === null || $stored === '') {
        return null;
    }

    // On-page anchors always work.
    if ($stored[0] === '#') {
        return $stored;
    }

    // Absolute links pass through untouched.
    if (preg_match('#^https?://#i', $stored)) {
        return $stored;
    }

    $path = str_replace('{suffix}', $country['slug_suffix'], trim($stored, '/'));

    return is_file(path_to_file($country['code'], $path))
        ? url($country['code'] . '/' . $path . '/')
        : null;
}

/** Services grouped by category, for the "What We Do" mega-menu panel. */
function get_services_by_category(): array
{
    $grouped = [];
    foreach (get_services() as $service) {
        $grouped[$service['category'] ?: 'Other'][] = $service;
    }
    return $grouped;
}

/** Delivery process steps for the "How we do" section. */
function get_process_steps(): array
{
    return db_all('SELECT * FROM process_steps WHERE is_active = 1 ORDER BY sort_order');
}

/**
 * Products bucketed by `group_label` for the tabbed products section.
 *
 * The ten `category` values are too granular to use as tabs, so a smaller
 * set of groups sits above them; `category` still labels each card.
 */
function get_products_by_group(int $country_id): array
{
    $grouped = [];
    foreach (get_products($country_id) as $product) {
        $grouped[$product['group_label'] ?: 'Other'][] = $product;
    }
    return $grouped;
}

/** WhatsApp click-to-chat link built from site settings. */
function whatsapp_url(): string
{
    $number = preg_replace('/\D+/', '', (string) setting('whatsapp_number', ''));
    $text   = (string) setting('whatsapp_prefill_text', '');
    return 'https://wa.me/' . $number . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/**
 * One-line data-handling reassurance shown under the lead form, naming the
 * regulation actually relevant to that country. NULL (no line shown) for
 * any country not yet in this list, rather than a generic guess -- see
 * Docs/BUILD-PLAN.md's compliance-hooks table for the source per market.
 */
const COUNTRY_COMPLIANCE_NOTES = [
    'sg' => "Handled in line with Singapore's PDPA.",
    'ae' => 'Built for FTA VAT and e-invoicing compliance.',
];

function compliance_note_for_country(string $country_code): ?string
{
    return COUNTRY_COMPLIANCE_NOTES[$country_code] ?? null;
}

// =====================================================================
// Countries & regions
// =====================================================================

function get_country(string $code): ?array
{
    return db_one('SELECT * FROM countries WHERE code = ? AND is_active = 1', [strtolower($code)]);
}

function get_countries(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db_all('SELECT * FROM countries WHERE is_active = 1 ORDER BY sort_order');
    }
    return $cache;
}

function get_region(int $country_id, string $region_code): ?array
{
    return db_one(
        'SELECT * FROM regions WHERE country_id = ? AND code = ? AND is_active = 1',
        [$country_id, strtolower($region_code)]
    );
}

// =====================================================================
// Services & products
// =====================================================================

function get_service(string $slug): ?array
{
    return db_one('SELECT * FROM services WHERE slug = ? AND is_active = 1', [$slug]);
}

function get_services(): array
{
    return db_all('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order');
}

/**
 * Per-country service copy. Falls back from a region-specific row
 * (e.g. Texas) to the country-level row when no region row exists.
 */
function get_service_content(int $service_id, int $country_id, int $region_id = 0): ?array
{
    if ($region_id > 0) {
        $row = db_one(
            'SELECT * FROM service_content WHERE service_id = ? AND country_id = ? AND region_id = ?',
            [$service_id, $country_id, $region_id]
        );
        if ($row) {
            return $row;
        }
    }
    return db_one(
        'SELECT * FROM service_content WHERE service_id = ? AND country_id = ? AND region_id = 0',
        [$service_id, $country_id]
    );
}

/**
 * Section types a service-detail page can be built from, admin-facing
 * label first. Validated here (not a DB ENUM) so a new block type is a
 * one-line addition, not a migration. 'faq' has no rows of its own -- it's
 * a placement marker that tells render_service_sections() where to pull
 * live rows from the `faqs` table (page_type='service'), so FAQ copy is
 * never duplicated between two tables.
 */
const SERVICE_SECTION_TYPES = [
    'rich_text'     => 'Rich text',
    'feature_grid'  => 'Feature grid',
    'image_feature' => 'Image + text',
    'process_steps' => 'Process steps',
    'deliverables'  => 'Deliverables checklist',
    'cta_banner'    => 'CTA banner',
    'faq'           => 'FAQ (pulls from this service\'s FAQs)',
];

/**
 * Section rows for a service-detail page, in display order. Same
 * region-falls-back-to-country semantics as get_service_content(): if the
 * region has any published sections, use those; otherwise use the
 * country-level (region_id = 0) rows.
 */
function get_service_sections(int $service_id, int $country_id, int $region_id = 0): array
{
    if ($region_id > 0) {
        $rows = db_all(
            'SELECT * FROM service_page_sections
             WHERE service_id = ? AND country_id = ? AND region_id = ? AND is_published = 1
             ORDER BY sort_order',
            [$service_id, $country_id, $region_id]
        );
        if ($rows) {
            return $rows;
        }
    }
    return db_all(
        'SELECT * FROM service_page_sections
         WHERE service_id = ? AND country_id = ? AND region_id = 0 AND is_published = 1
         ORDER BY sort_order',
        [$service_id, $country_id]
    );
}

/**
 * Renders a service-detail page's section stack. Long-form fields
 * (`body`) are admin-authored HTML and render raw, same trust boundary as
 * service_content.intro (see CLAUDE.md "Long-form fields render raw
 * HTML"). Short fields (`heading`, `subheading`, item text, CTA labels)
 * always go through esc().
 */
function render_service_sections(array $sections, array $service, array $country): void
{
    foreach ($sections as $section) {
        $items = [];
        if (!empty($section['items'])) {
            $decoded = json_decode((string) $section['items'], true);
            $items   = is_array($decoded) ? $decoded : [];
        }

        switch ($section['section_type']) {
            case 'rich_text': ?>
                <section class="about-area cd-svc-richtext">
                    <div class="custom-container cd-svc-richtext-inner">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <div class="cd-svc-prose"><?= $section['body'] ?? '' ?></div>
                    </div>
                </section>
            <?php break;

            case 'feature_grid': ?>
                <section class="service-area cd-svc-features">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($section['subheading'])): ?>
                        <p class="cd-svc-subheading"><?= esc($section['subheading']) ?></p>
                        <?php endif; ?>
                        <div class="cd-service-grid cd-expertise-grid">
                            <?php foreach ($items as $item): ?>
                            <article class="service-card simple-shadow cd-expertise-card">
                                <?php if (!empty($item['icon'])): ?>
                                <i class="<?= esc($item['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                                <?php endif; ?>
                                <div class="cd-expertise-content">
                                    <h4><?= esc($item['title'] ?? '') ?></h4>
                                    <p><?= esc($item['text'] ?? '') ?></p>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'image_feature':
                $reverse = ($section['image_position'] ?? 'right') === 'left'; ?>
                <section class="about-area cd-svc-image-feature<?= $reverse ? ' cd-svc-image-feature-reverse' : '' ?>">
                    <div class="custom-container cd-svc-image-feature-row">
                        <div class="cd-svc-image-feature-media">
                            <?php if (!empty($section['media_url'])): ?>
                            <img src="<?= esc($section['media_url']) ?>" alt="<?= esc($section['media_alt'] ?? '') ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="cd-svc-image-feature-body">
                            <?php if (!empty($section['heading'])): ?>
                            <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                            <?php endif; ?>
                            <div class="cd-svc-prose"><?= $section['body'] ?? '' ?></div>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'process_steps': ?>
                <section class="about-area cd-svc-process">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <div class="cd-svc-process-items">
                            <?php foreach ($items as $i => $item): ?>
                            <div class="cd-svc-process-item">
                                <span class="cd-svc-process-number"><?= esc(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                <h3><?= esc($item['title'] ?? '') ?></h3>
                                <p><?= esc($item['text'] ?? '') ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'deliverables': ?>
                <section class="about-area cd-svc-deliverables">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <ul class="cd-svc-deliverables-list">
                            <?php foreach ($items as $item): ?>
                            <li><i class="las la-check-circle" aria-hidden="true"></i> <?= esc(is_array($item) ? ($item['text'] ?? '') : $item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php break;

            case 'cta_banner': ?>
                <section class="about-area cd-svc-cta-banner">
                    <div class="custom-container cd-svc-cta-banner-inner">
                        <div>
                            <?php if (!empty($section['heading'])): ?>
                            <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($section['body'])): ?>
                            <div class="cd-svc-prose"><?= $section['body'] ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($section['cta_label']) && !empty($section['cta_url'])): ?>
                        <a href="<?= esc($section['cta_url']) ?>" class="theme-btn">
                            <?= esc($section['cta_label']) ?> <i class="iconoir-arrow-up-right" aria-hidden="true"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </section>
            <?php break;

            case 'faq':
                $faqs = get_faqs('service', (int) $service['id'], (int) $country['id']);
                if (empty($faqs)) {
                    break;
                } ?>
                <section class="faq-area cd-faq cd-svc-faq">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <div class="accordion cd-accordion" id="cd-svc-faq-accordion">
                            <?php foreach ($faqs as $i => $faq): ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="cd-svc-faq-heading-<?= (int) $faq['id'] ?>">
                                    <button class="accordion-button<?= $i === 0 ? '' : ' collapsed' ?>" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#cd-svc-faq-<?= (int) $faq['id'] ?>"
                                            aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                            aria-controls="cd-svc-faq-<?= (int) $faq['id'] ?>">
                                        <?= esc($faq['question']) ?>
                                    </button>
                                </h3>
                                <div id="cd-svc-faq-<?= (int) $faq['id'] ?>" class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>"
                                     aria-labelledby="cd-svc-faq-heading-<?= (int) $faq['id'] ?>" data-bs-parent="#cd-svc-faq-accordion">
                                    <div class="accordion-body"><?= esc($faq['answer']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;
        }
    }
}

/** Same vocabulary as SERVICE_SECTION_TYPES -- one shared section-type
 *  system, just stored against a different table per detail-page kind. */
const PRODUCT_SECTION_TYPES = [
    'rich_text'     => 'Rich text',
    'feature_grid'  => 'Feature grid',
    'image_feature' => 'Image + text',
    'process_steps' => 'Process steps',
    'deliverables'  => 'Deliverables checklist',
    'cta_banner'    => 'CTA banner',
    'faq'           => 'FAQ (pulls from this product\'s FAQs)',
];

/** Section rows for a product-detail page, in display order. No region
 *  fallback here -- product_content has no region_id to fall back from. */
function get_product_sections(int $product_id, int $country_id): array
{
    return db_all(
        'SELECT * FROM product_page_sections
         WHERE product_id = ? AND country_id = ? AND is_published = 1
         ORDER BY sort_order',
        [$product_id, $country_id]
    );
}

/**
 * Renders a product-detail page's section stack. Identical block types
 * and markup to render_service_sections() (same cd-svc-* classes are
 * reused deliberately -- the section vocabulary isn't service-specific,
 * and duplicating the CSS for a product-flavoured class name would just
 * be the same rules twice), except the FAQ case pulls from this
 * product's own FAQs instead of a service's.
 */
function render_product_sections(array $sections, array $product, array $country): void
{
    foreach ($sections as $section) {
        $items = [];
        if (!empty($section['items'])) {
            $decoded = json_decode((string) $section['items'], true);
            $items   = is_array($decoded) ? $decoded : [];
        }

        switch ($section['section_type']) {
            case 'rich_text': ?>
                <section class="about-area cd-svc-richtext">
                    <div class="custom-container cd-svc-richtext-inner">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <div class="cd-svc-prose"><?= $section['body'] ?? '' ?></div>
                    </div>
                </section>
            <?php break;

            case 'feature_grid': ?>
                <section class="service-area cd-svc-features">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <?php if (!empty($section['subheading'])): ?>
                        <p class="cd-svc-subheading"><?= esc($section['subheading']) ?></p>
                        <?php endif; ?>
                        <div class="cd-service-grid cd-expertise-grid">
                            <?php foreach ($items as $item): ?>
                            <article class="service-card simple-shadow cd-expertise-card">
                                <?php if (!empty($item['icon'])): ?>
                                <i class="<?= esc($item['icon']) ?> cd-service-icon" aria-hidden="true"></i>
                                <?php endif; ?>
                                <div class="cd-expertise-content">
                                    <h4><?= esc($item['title'] ?? '') ?></h4>
                                    <p><?= esc($item['text'] ?? '') ?></p>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'image_feature':
                $reverse = ($section['image_position'] ?? 'right') === 'left'; ?>
                <section class="about-area cd-svc-image-feature<?= $reverse ? ' cd-svc-image-feature-reverse' : '' ?>">
                    <div class="custom-container cd-svc-image-feature-row">
                        <div class="cd-svc-image-feature-media">
                            <?php if (!empty($section['media_url'])): ?>
                            <img src="<?= esc($section['media_url']) ?>" alt="<?= esc($section['media_alt'] ?? '') ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="cd-svc-image-feature-body">
                            <?php if (!empty($section['heading'])): ?>
                            <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                            <?php endif; ?>
                            <div class="cd-svc-prose"><?= $section['body'] ?? '' ?></div>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'process_steps': ?>
                <section class="about-area cd-svc-process">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <div class="cd-svc-process-items">
                            <?php foreach ($items as $i => $item): ?>
                            <div class="cd-svc-process-item">
                                <span class="cd-svc-process-number"><?= esc(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                <h3><?= esc($item['title'] ?? '') ?></h3>
                                <p><?= esc($item['text'] ?? '') ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;

            case 'deliverables': ?>
                <section class="about-area cd-svc-deliverables">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <ul class="cd-svc-deliverables-list">
                            <?php foreach ($items as $item): ?>
                            <li><i class="las la-check-circle" aria-hidden="true"></i> <?= esc(is_array($item) ? ($item['text'] ?? '') : $item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php break;

            case 'cta_banner': ?>
                <section class="about-area cd-svc-cta-banner">
                    <div class="custom-container cd-svc-cta-banner-inner">
                        <div>
                            <?php if (!empty($section['heading'])): ?>
                            <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($section['body'])): ?>
                            <div class="cd-svc-prose"><?= $section['body'] ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($section['cta_label']) && !empty($section['cta_url'])): ?>
                        <a href="<?= esc($section['cta_url']) ?>" class="theme-btn">
                            <?= esc($section['cta_label']) ?> <i class="iconoir-arrow-up-right" aria-hidden="true"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </section>
            <?php break;

            case 'faq':
                $faqs = get_faqs('product', (int) $product['id'], (int) $country['id']);
                if (empty($faqs)) {
                    break;
                } ?>
                <section class="faq-area cd-faq cd-svc-faq">
                    <div class="custom-container">
                        <?php if (!empty($section['heading'])): ?>
                        <h2 class="cd-svc-heading"><?= esc($section['heading']) ?></h2>
                        <?php endif; ?>
                        <div class="accordion cd-accordion" id="cd-prod-faq-accordion">
                            <?php foreach ($faqs as $i => $faq): ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="cd-prod-faq-heading-<?= (int) $faq['id'] ?>">
                                    <button class="accordion-button<?= $i === 0 ? '' : ' collapsed' ?>" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#cd-prod-faq-<?= (int) $faq['id'] ?>"
                                            aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                            aria-controls="cd-prod-faq-<?= (int) $faq['id'] ?>">
                                        <?= esc($faq['question']) ?>
                                    </button>
                                </h3>
                                <div id="cd-prod-faq-<?= (int) $faq['id'] ?>" class="accordion-collapse collapse<?= $i === 0 ? ' show' : '' ?>"
                                     aria-labelledby="cd-prod-faq-heading-<?= (int) $faq['id'] ?>" data-bs-parent="#cd-prod-faq-accordion">
                                    <div class="accordion-body"><?= esc($faq['answer']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php break;
        }
    }
}

function get_product(string $slug): ?array
{
    return db_one('SELECT * FROM products WHERE slug = ? AND is_active = 1', [$slug]);
}

/**
 * Products for a country, with the per-country title override already
 * applied (so `invoice` renders as "Invoice Singapore" on /sg/).
 */
function get_products(int $country_id): array
{
    return db_all(
        'SELECT p.*,
                COALESCE(pc.title, p.title) AS display_title,
                pc.h1, pc.intro, pc.is_published
         FROM products p
         LEFT JOIN product_content pc
                ON pc.product_id = p.id AND pc.country_id = ?
         WHERE p.is_active = 1
         ORDER BY p.sort_order',
        [$country_id]
    );
}

function get_product_content(int $product_id, int $country_id): ?array
{
    return db_one(
        'SELECT * FROM product_content WHERE product_id = ? AND country_id = ?',
        [$product_id, $country_id]
    );
}

// =====================================================================
// FAQs, hero, blog, case studies
// =====================================================================

/**
 * FAQs for a page. Rows with country_id NULL show everywhere; rows with a
 * country_id show only on that country.
 */
function get_faqs(string $page_type, ?int $reference_id = null, ?int $country_id = null): array
{
    $sql = 'SELECT * FROM faqs WHERE page_type = ? AND is_active = 1';
    $params = [$page_type];

    if ($reference_id !== null) {
        $sql .= ' AND reference_id = ?';
        $params[] = $reference_id;
    } else {
        $sql .= ' AND reference_id IS NULL';
    }

    if ($country_id !== null) {
        $sql .= ' AND (country_id IS NULL OR country_id = ?)';
        $params[] = $country_id;
    } else {
        $sql .= ' AND country_id IS NULL';
    }

    return db_all($sql . ' ORDER BY sort_order', $params);
}

/** Hero slides for a country: country-specific rows plus global ones. */
function get_hero_slides(int $country_id, string $page_slug = 'home'): array
{
    return db_all(
        'SELECT hs.*, hf.title AS feature_title, hf.subtitle AS feature_subtitle, hf.icon AS feature_icon
         FROM hero_slides hs
         LEFT JOIN hero_feature_rows hf ON hf.id = hs.feature_row_id
         WHERE hs.page_slug = ? AND hs.is_active = 1
           AND (hs.country_id IS NULL OR hs.country_id = ?)
         ORDER BY hs.sort_order',
        [$page_slug, $country_id]
    );
}

/** Hero trust-badge list for a country: country-specific rows plus global ones. */
function get_hero_feature_rows(int $country_id): array
{
    return db_all(
        'SELECT * FROM hero_feature_rows
         WHERE is_active = 1 AND (country_id IS NULL OR country_id = ?)
         ORDER BY sort_order',
        [$country_id]
    );
}

function get_posts(?int $country_id = null, int $limit = 6, int $offset = 0): array
{
    $sql = 'SELECT * FROM blog_posts WHERE is_published = 1';
    $params = [];
    if ($country_id !== null) {
        $sql .= ' AND (country_id IS NULL OR country_id = ?)';
        $params[] = $country_id;
    }
    $sql .= ' ORDER BY published_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    return db_all($sql, $params);
}

function get_case_studies(?int $country_id = null, int $limit = 6): array
{
    $sql = 'SELECT * FROM case_studies WHERE is_published = 1';
    $params = [];
    if ($country_id !== null) {
        $sql .= ' AND (country_id IS NULL OR country_id = ?)';
        $params[] = $country_id;
    }
    $sql .= ' ORDER BY sort_order LIMIT ' . (int) $limit;
    return db_all($sql, $params);
}

/** A single published post, visible on $country_id (global posts show on every country). */
function get_post(string $slug, ?int $country_id = null): ?array
{
    $sql = 'SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1';
    $params = [$slug];
    if ($country_id !== null) {
        $sql .= ' AND (country_id IS NULL OR country_id = ?)';
        $params[] = $country_id;
    }
    return db_one($sql, $params);
}

/** A single published case study, visible on $country_id (global rows show on every country). */
function get_case_study(string $slug, ?int $country_id = null): ?array
{
    $sql = 'SELECT * FROM case_studies WHERE slug = ? AND is_published = 1';
    $params = [$slug];
    if ($country_id !== null) {
        $sql .= ' AND (country_id IS NULL OR country_id = ?)';
        $params[] = $country_id;
    }
    return db_one($sql, $params);
}

/**
 * Admin-editable title/description/noindex for a page that has no content
 * table of its own to hang meta fields off (home, about, services hub).
 * Callers keep their existing hardcoded copy as the fallback default --
 * same pattern as service_content's meta_title/meta_description.
 */
function get_page_seo(string $page_key, int $country_id): ?array
{
    return db_one('SELECT * FROM page_seo WHERE page_key = ? AND country_id = ?', [$page_key, $country_id]);
}

// =====================================================================
// URL builders
// =====================================================================

/** /sg/ */
function country_url(string $country_code): string
{
    return url($country_code . '/');
}

/** /sg/services/web-development-singapore/ */
function service_url(string $service_slug, array $country): string
{
    return url($country['code'] . '/services/' . $service_slug . '-' . $country['slug_suffix'] . '/');
}

/** /sg/products/invoice-singapore/ */
function product_url(string $product_slug, array $country): string
{
    return url($country['code'] . '/products/' . $product_slug . '-' . $country['slug_suffix'] . '/');
}

/** /sg/blog/some-post-slug/ -- unlike services/products, one shared post.php serves every slug. */
function blog_url(string $post_slug, array $country): string
{
    return url($country['code'] . '/blog/' . $post_slug . '/');
}

/** /sg/case-studies/some-client-slug/ */
function case_study_url(string $case_slug, array $country): string
{
    return url($country['code'] . '/case-studies/' . $case_slug . '/');
}

/** Absolute filesystem path of the web root. */
function doc_root(): string
{
    return dirname(__DIR__);
}

/**
 * Map a country-relative URL path to the flat PHP file that serves it.
 *
 *   ''                              -> /sg/index.php
 *   'services/ai-solutions-{...}/'  -> /sg/services/ai-solutions-singapore.php
 */
function path_to_file(string $country_code, string $path): string
{
    $path = trim($path, '/');
    $rel  = $path === '' ? $country_code . '/index.php' : $country_code . '/' . $path . '.php';
    return doc_root() . '/' . $rel;
}

/**
 * Build the hreflang alternate map from a country-relative pattern.
 *
 * The pattern may contain {suffix}, which expands to each country's
 * slug_suffix -- needed because page filenames carry the country name:
 *
 *   hreflang_map('')                                -> /sg/,  /ae/,  ...
 *   hreflang_map('services/ai-solutions-{suffix}/') -> /sg/services/ai-solutions-singapore/
 *
 * Countries whose page does not exist on disk are omitted. Advertising an
 * hreflang alternate that 404s is an error Google reports and, worse, it
 * invalidates the reciprocity of the whole cluster -- so the set has to
 * stay honest as markets are built out one at a time.
 *
 * @return array<string,string> hreflang => absolute URL
 */
function hreflang_map(string $pattern = ''): array
{
    $map = [];
    foreach (get_countries() as $c) {
        $path = str_replace('{suffix}', $c['slug_suffix'], $pattern);
        if (!is_file(path_to_file($c['code'], $path))) {
            continue;
        }
        $map[$c['hreflang']] = url($c['code'] . '/' . ltrim($path, '/'));
    }
    return $map;
}

// =====================================================================
// SEO tags
// =====================================================================

/**
 * Emit <title>, meta description, canonical, hreflang set, OG and Twitter
 * tags. Called from header.php.
 *
 * @param array $opts title, description, canonical (site-relative),
 *                    image, type, hreflang_pattern, noindex, x_default
 */
function render_seo_tags(array $opts): void
{
    $title       = $opts['title'] ?? setting('site_name', 'Corediva Tech Solutions');
    $description = $opts['description'] ?? setting('site_tagline', '');
    $canonical   = url($opts['canonical'] ?? '');
    $image       = $opts['image'] ?? asset(ltrim((string) setting('logo_url', 'imgs/corediva-logo.png'), '/'));
    $type        = $opts['type'] ?? 'website';
    $siteName    = setting('site_name', 'Corediva Tech Solutions');

    echo '    <title>' . esc($title) . "</title>\n";
    echo '    <meta name="description" content="' . esc($description) . "\">\n";
    echo '    <link rel="canonical" href="' . esc($canonical) . "\">\n";

    if (!empty($opts['noindex'])) {
        echo "    <meta name=\"robots\" content=\"noindex, follow\">\n";
    } else {
        echo "    <meta name=\"robots\" content=\"index, follow, max-image-preview:large\">\n";
    }

    // hreflang. Regions (Texas) deliberately opt out: a state is not a locale.
    // A single self-referencing alternate tells Google nothing, so the block
    // is only emitted once at least two markets actually exist on disk.
    if (isset($opts['hreflang_pattern'])) {
        $alternates = hreflang_map($opts['hreflang_pattern']);

        if (count($alternates) > 1) {
            foreach ($alternates as $lang => $href) {
                echo '    <link rel="alternate" hreflang="' . esc($lang) . '" href="' . esc($href) . "\">\n";
            }

            $defaultCode = (string) cfg('app.default_country', 'sg');
            $defaultRow  = get_country($defaultCode);
            $xDefault    = $opts['x_default'] ?? ($defaultCode . '/' . ltrim(
                str_replace('{suffix}', $defaultRow['slug_suffix'] ?? '', $opts['hreflang_pattern']),
                '/'
            ));

            if (isset($alternates[$defaultRow['hreflang'] ?? ''])) {
                echo '    <link rel="alternate" hreflang="x-default" href="' . esc(url($xDefault)) . "\">\n";
            }
        }
    }

    echo '    <meta property="og:type" content="' . esc($type) . "\">\n";
    echo '    <meta property="og:title" content="' . esc($title) . "\">\n";
    echo '    <meta property="og:description" content="' . esc($description) . "\">\n";
    echo '    <meta property="og:url" content="' . esc($canonical) . "\">\n";
    echo '    <meta property="og:image" content="' . esc($image) . "\">\n";
    echo '    <meta property="og:site_name" content="' . esc($siteName) . "\">\n";
    echo "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo '    <meta name="twitter:title" content="' . esc($title) . "\">\n";
    echo '    <meta name="twitter:description" content="' . esc($description) . "\">\n";
    echo '    <meta name="twitter:image" content="' . esc($image) . "\">\n";
}

// =====================================================================
// schema.org (JSON-LD)
// =====================================================================

/** Print a JSON-LD block. */
function emit_jsonld(array $data): void
{
    echo '<script type="application/ld+json">'
        . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        . "</script>\n";
}

function render_schema_organization(): void
{
    $sameAs = array_column(get_social_links(), 'url');

    emit_jsonld([
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        '@id'         => url('#organization'),
        'name'        => setting('site_name'),
        'alternateName' => setting('site_alt_name'),
        'url'         => url(),
        'logo'        => asset(ltrim((string) setting('logo_url', 'imgs/corediva-logo.png'), '/')),
        'description' => setting('site_tagline'),
        'foundingDate' => setting('founding_year'),
        'sameAs'      => array_values($sameAs),
        'contactPoint' => [[
            '@type'             => 'ContactPoint',
            'contactType'       => 'sales',
            'telephone'         => setting('phone_e164'),
            'email'             => setting('email'),
            'availableLanguage' => ['en'],
        ]],
    ]);
}

/**
 * Emit location markup for a country.
 *
 * Only emits LocalBusiness when Corediva actually has an office in that
 * country -- claiming a physical presence you don't have is a
 * misrepresentation Google can penalise. For served-remotely markets this
 * emits an Organization with areaServed instead, which is accurate.
 */
function render_schema_localbusiness(array $country): void
{
    $offices = get_offices((int) $country['id']);

    if (empty($offices)) {
        emit_jsonld([
            '@context'   => 'https://schema.org',
            '@type'      => 'Organization',
            '@id'        => url($country['code'] . '/#organization'),
            'name'       => setting('site_name') . ' ' . $country['name'],
            'url'        => country_url($country['code']),
            'parentOrganization' => ['@id' => url('#organization')],
            'areaServed' => [
                '@type' => 'Country',
                'name'  => $country['name'],
            ],
            'telephone'  => $country['phone'] ?: setting('phone_e164'),
            'email'      => $country['email'] ?: setting('email'),
        ]);
        return;
    }

    $office = $offices[0];
    $data = [
        '@context'  => 'https://schema.org',
        '@type'     => 'ProfessionalService',
        '@id'       => url($country['code'] . '/#localbusiness'),
        'name'      => setting('site_name'),
        'url'       => country_url($country['code']),
        'telephone' => $country['phone'] ?: setting('phone_e164'),
        'email'     => $country['email'] ?: setting('email'),
        'address'   => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $office['address'],
            'addressLocality' => $office['city'],
            'addressCountry'  => strtoupper($country['code']),
        ],
        'areaServed' => ['@type' => 'Country', 'name' => $country['name']],
    ];

    if ($office['lat'] !== null && $office['lng'] !== null) {
        $data['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $office['lat'],
            'longitude' => (float) $office['lng'],
        ];
    }

    emit_jsonld($data);
}

function render_schema_service(array $service, ?array $content, array $country): void
{
    emit_jsonld([
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $content['h1'] ?? $service['title'],
        'serviceType' => $service['title'],
        'description' => $content['intro'] ?? $service['short_description'],
        'url'         => service_url($service['slug'], $country),
        'provider'    => ['@id' => url('#organization')],
        'areaServed'  => ['@type' => 'Country', 'name' => $country['name']],
    ]);
}

function render_schema_product(array $product, ?array $content, array $country): void
{
    emit_jsonld([
        '@context'    => 'https://schema.org',
        '@type'       => 'SoftwareApplication',
        'name'        => $content['title'] ?? $product['title'],
        'applicationCategory' => 'BusinessApplication',
        'description' => $content['intro'] ?? $product['short_description'],
        'url'         => product_url($product['slug'], $country),
        'publisher'   => ['@id' => url('#organization')],
    ]);
}

function render_schema_faq(array $faqs): void
{
    if (empty($faqs)) {
        return;
    }

    $items = [];
    foreach ($faqs as $faq) {
        $items[] = [
            '@type'          => 'Question',
            'name'           => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => strip_tags($faq['answer']),
            ],
        ];
    }

    emit_jsonld([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $items,
    ]);
}

/**
 * @param array $parts ordered [label => site-relative path]
 */
function render_schema_breadcrumbs(array $parts): void
{
    $items = [];
    $i = 1;
    foreach ($parts as $label => $path) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i++,
            'name'     => $label,
            'item'     => url($path),
        ];
    }

    emit_jsonld([
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ]);
}

// =====================================================================
// CSRF
// =====================================================================

function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
    }
}

function csrf_token(): string
{
    ensure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(?string $token): bool
{
    ensure_session();
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

// =====================================================================
// Leads
// =====================================================================

/**
 * Validate and store a lead.
 *
 * @return array{ok:bool, errors:array<string>, id:?int}
 */
function save_lead(array $post): array
{
    $errors = [];

    if (!csrf_verify($post['csrf_token'] ?? null)) {
        return ['ok' => false, 'errors' => ['Your session expired. Please reload the page and try again.'], 'id' => null];
    }

    // Honeypot: real users never fill a hidden field.
    if (!empty($post['website'])) {
        // Pretend success so bots don't learn they were caught.
        return ['ok' => true, 'errors' => [], 'id' => null];
    }

    $name    = trim((string) ($post['name'] ?? ''));
    $email   = trim((string) ($post['email'] ?? ''));
    $phone   = trim((string) ($post['phone'] ?? ''));
    $service = trim((string) ($post['service_interest'] ?? ''));
    $message = trim((string) ($post['message'] ?? ''));
    $ccode   = trim((string) ($post['country_code'] ?? ''));

    if ($name === '')                                  { $errors[] = 'Please enter your name.'; }
    if ($email === '')                                 { $errors[] = 'Please enter your email address.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'That email address does not look valid.'; }
    if (mb_strlen($name) > 150)                        { $errors[] = 'Name is too long.'; }
    if (mb_strlen($message) > 5000)                    { $errors[] = 'Message is too long.'; }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors, 'id' => null];
    }

    // Rate limit per IP.
    $ip  = client_ip_binary();
    $max = (int) cfg('security.lead_max_per_hour', 5);
    if ($ip !== null) {
        $recent = (int) db_value(
            'SELECT COUNT(*) FROM leads WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)',
            [$ip]
        );
        if ($recent >= $max) {
            return ['ok' => false, 'errors' => ['Too many submissions. Please try again later.'], 'id' => null];
        }
    }

    $country = $ccode !== '' ? get_country($ccode) : null;

    $sourceUrl = mb_substr((string) ($post['source_url'] ?? ''), 0, 255) ?: null;

    db_exec(
        'INSERT INTO leads
           (name, email, phone, country_code, country_id, service_interest,
            message, source_url, referrer, ip, user_agent)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)',
        [
            $name,
            $email,
            $phone !== '' ? $phone : null,
            $ccode !== '' ? $ccode : null,
            $country['id'] ?? null,
            $service !== '' ? $service : null,
            $message !== '' ? $message : null,
            $sourceUrl,
            mb_substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255) ?: null,
            $ip,
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]
    );

    $leadId = (int) db()->lastInsertId();

    // Best-effort: the lead is already captured in the DB above regardless
    // of whether either email goes out, so a mail failure here must not
    // turn a successful submission into an error response for the visitor.
    require_once __DIR__ . '/mailer.php';
    send_lead_notifications(
        [
            'name'             => $name,
            'email'            => $email,
            'phone'            => $phone !== '' ? $phone : null,
            'country_code'     => $ccode !== '' ? $ccode : null,
            'service_interest' => $service !== '' ? $service : null,
            'message'          => $message !== '' ? $message : null,
            'source_url'       => $sourceUrl,
        ],
        $country
    );

    return ['ok' => true, 'errors' => [], 'id' => $leadId];
}

// =====================================================================
// View helpers
// =====================================================================

/**
 * Wrap the highlighted fragment of a hero heading in a <span>.
 * Both arguments are escaped before the span is inserted.
 */
function highlight_heading(string $heading, ?string $fragment): string
{
    $safe = esc($heading);
    if (!$fragment) {
        return $safe;
    }
    $safeFragment = esc($fragment);
    $pos = mb_strpos($safe, $safeFragment);
    if ($pos === false) {
        return $safe;
    }
    return mb_substr($safe, 0, $pos)
        . '<span>' . $safeFragment . '</span>'
        . mb_substr($safe, $pos + mb_strlen($safeFragment));
}
