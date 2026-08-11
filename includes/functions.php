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

/** Site base URL with no trailing slash. */
function base_url(): string
{
    return rtrim((string) cfg('app.base_url', ''), '/');
}

/** Absolute URL for a site-relative path. */
function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

/** Asset URL, resolved from the site root so it works at any folder depth. */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
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

function get_hero_feature_rows(): array
{
    return db_all('SELECT * FROM hero_feature_rows WHERE is_active = 1 ORDER BY sort_order');
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
            mb_substr((string) ($post['source_url'] ?? ''), 0, 255) ?: null,
            mb_substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255) ?: null,
            $ip,
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]
    );

    return ['ok' => true, 'errors' => [], 'id' => (int) db()->lastInsertId()];
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
