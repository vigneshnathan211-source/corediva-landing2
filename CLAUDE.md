# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The Corediva Tech Solutions website: plain procedural PHP 8 + MySQL, no framework, no router, no Composer autoloading beyond PHPMailer. Content is DB-driven so it can be edited from `/admin/` without touching PHP. Built to deploy onto stock cPanel shared hosting with zero setup beyond importing the SQL and writing `config.local.php`.

Multi-country: Singapore is the primary market, with UAE, USA (+ Texas), UK, Australia, India and Malaysia to follow.

**Current scope: the Singapore landing page (`/sg/index.php`) only.** Service, product, blog, case-study and admin pages are designed in the schema but not yet built.

## Commands

```bash
php -S 127.0.0.1:8000 -t .        # dev server -> http://localhost:8000/sg/
php tools/db-run.php database/schema.sql   # (re)create tables -- DROPs first
php tools/db-run.php database/seed.sql     # (re)load seed data -- TRUNCATEs first
php tools/make-logos.php Docs/logos.png    # regenerate logo assets from master
composer install
php -l <file>                     # lint; there is no test suite
```

Local DB is on **port 3308** (not 3306). Credentials live in `includes/config.local.php`, which is gitignored — copy `includes/config.example.php` to create it.

Note `.htaccess` is inert under `php -S`, so clean URLs and the root redirect only work under Apache. `/sg/` resolves locally because `index.php` is the directory index.

## Architecture

**Request flow.** Every page is a standalone flat PHP file that: sets `$country_code`, requires `includes/db.php` + `includes/functions.php`, fetches its data, builds a `$seo` array, includes `header.php`, echoes markup, includes `footer.php`. This repetition across ~270 eventual page files is intentional per the build brief — do not introduce a router or template engine to hide it.

**`includes/` is the whole framework:**
- `config.php` — defaults; merges `config.local.php` over them via `array_replace_recursive`. Never put real credentials in `config.php`.
- `db.php` — `db()` returns a shared PDO handle with `ATTR_EMULATE_PREPARES => false`. Use the `db_all/db_one/db_value/db_exec` helpers; every query is a prepared statement with bound params, no exceptions.
- `functions.php` — content getters, URL builders, SEO/schema emitters, CSRF, `save_lead()`.
- `header.php` / `footer.php` / `lead-form.php` — expect `$country` (and `$seo` for header) already in scope.

**URL and filename convention.** Country is a folder *and* a filename suffix: `/sg/services/web-development-singapore.php`, served at `/sg/services/web-development-singapore/` via the extensionless rewrite in `.htaccess`. `countries.slug_suffix` holds the filename part (`singapore`, `uae`, `usa`…) and is distinct from `countries.code` (the folder: `sg`, `ae`, `us`…). Note `uk` maps to hreflang `en-gb` — they are separate columns for that reason.

**Per-country content model.** `services`/`products` hold country-independent base rows; `service_content`/`product_content` hold the per-country copy keyed by `(service_id, country_id, region_id)`. `region_id` uses `0` rather than `NULL` for country-level rows, because MySQL treats NULLs as distinct and the UNIQUE key would otherwise silently permit duplicates. `get_service_content()` falls back from a region row (Texas) to the country row.

The `invoice` product is deliberately generic at base level and renamed per country via `product_content.title` ("Invoice Singapore", "Invoice UAE"…). `get_products()` resolves this with `COALESCE(pc.title, p.title) AS display_title` — always render `display_title`, never `title`.

## Things that will bite you

- **`ensure_session()` must run before any output.** `header.php` calls it first thing. CSRF tokens are minted in `lead-form.php` long after `<head>` has flushed, so a session started there would never set its cookie and every POST would fail CSRF.
- **Exactly one `<h1>` per page.** The Synck theme uses `<h1 class="section-title">` for every section header and `<h1>` for stat figures. Both were retagged (`h2` for sections, `p.cd-stat-value` / `p.cd-footer-stat` for figures). Don't reintroduce theme markup verbatim.
- **`hreflang_map()` checks the filesystem** and omits countries whose page doesn't exist yet, so the set stays honest while markets ship one at a time. `render_seo_tags()` suppresses the block entirely below two alternates.
- **`render_schema_localbusiness()` only emits `LocalBusiness` where an office actually exists** (IN, UK, AE). Singapore, USA, Australia and Malaysia are served remotely, so they get `Organization` + `areaServed` instead. Claiming a physical presence that doesn't exist is a misrepresentation Google penalises.
- **Long-form fields render raw HTML** (`intro`, `body`, `core_description`) because they're admin-authored. Short fields must go through `esc()`. Admin write access is the trust boundary.
- **Texas gets no hreflang** — a state is not a locale. `header.php` strips `hreflang_pattern` when `$region` is set.

## Content status

Real data seeded from the live DB export: 16 services, 12 products, 8 FAQs, site settings, 4 offices, 4 stats, 5 partners, 4 social links, 4 hero slides.

**Not written yet (~45,000 words, client-supplied):** every `services.core_description`, all `service_content` rows, all `product_content` marketing copy, and the country landing page copy. The dump only ever had one-sentence `short_description` values.

**Genuinely empty:** blog (0 rows), case studies (0 rows). Testimonials are excluded on purpose — the only row in the source DB is flagged *"do not publish placeholder testimonials live."* Don't build a testimonials section until real client quotes exist.

**Do not submit the sitemap to Search Console until copy is imported** — the chosen architecture indexes everything by default (`is_published` defaults to `1`).

## Repo conventions

`Docs/`, `synck/` (a wget mirror of the old WordPress demo — reference only), the root `*.html` theme files, and `config.local.php` are all gitignored: the repo holds production files only. The GitHub remote is **public**, so nothing sensitive can be committed. `vendor/` *is* committed so cPanel deploys need no Composer.

CSS overrides live in `assets/css/corediva.css` (all classes `cd-` prefixed), loaded after the theme's stylesheets. Bootstrap 5.3.2 JS is vendored locally — the original theme loaded it from a `wpriverthemes.com` CDN.
