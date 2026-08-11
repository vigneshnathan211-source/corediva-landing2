<?php
/**
 * What We Do -- services overview page (Singapore).
 * URL: /sg/services/
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

$servicesByCategory = get_services_by_category();
$serviceCount        = count(get_services());
$categoryCount       = count($servicesByCategory);

$seo = [
    'title'            => 'What We Do | IT, ERP & AI Services | Corediva Tech Solutions',
    'description'      => "Sixteen services across web, ERP, AI and cloud, delivered by one "
                         . "accountable team. See everything Corediva Tech Solutions builds for "
                         . "Singapore SMEs.",
    'canonical'        => 'sg/services/',
    'hreflang_pattern' => 'services',
];

include __DIR__ . '/../includes/header.php';
?>

    <!-- Hero: on the theme's service.html hero, which nests a
         hero-service-about panel inside the hero itself rather than
         after it. Checklist swapped for real commitments (credentials,
         response time, PDPA) instead of the theme's generic "Flexibility
         and Adaptability" placeholders. -->
    <section class="hero-service-wrap hero-section-wrap">
        <div class="hero-section-content-wrap">
            <div class="custom-container">
                <div class="hero-section-content text-center">
                    <h5 class="section-subtitle">Our Services</h5>
                    <h1 class="section-title">Sixteen services. One accountable team.</h1>
                    <p>From web platforms to AI automation, ERP to cybersecurity: every service
                       on this page is delivered by the same team that scoped it, not handed off
                       to a subcontractor partway through.</p>
                </div>

                <div class="hero-service-about">
                    <img src="<?= esc(asset('imgs/hero-service-about.jpg')) ?>" alt="Corediva Tech Solutions delivery team at work">
                    <div class="hero-service-about-body">
                        <p>Every engagement runs through the same delivery model: a senior
                           engineer scopes the work, that same engineer ships it, and support
                           doesn't require re-explaining the project to someone new. No handoffs
                           between sales, discovery and delivery.</p>
                        <ul>
                            <li><i class="las la-check"></i> <?= esc(setting('credentials')) ?></li>
                            <li><i class="las la-check"></i> Replies within <?= esc(setting('response_time_hours')) ?> hours on business days</li>
                            <li><i class="las la-check"></i> PDPA-aligned data handling</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Area: the full 16-service catalogue grouped by category,
         same cd-service-grid pattern the homepage uses for its own "What
         We Do" section. No eyebrow -- Hero already used this page's first
         eyebrow, and Consulting Excellence below needs the second slot
         left clear per the same budget. -->
    <section class="service-area" id="catalogue">
        <div class="custom-container">
            <div class="service-section-header section-header d-flex align-items-end justify-content-between">
                <div class="left">
                    <h2 class="section-title">Sixteen services. <br>Five disciplines.</h2>
                </div>
                <p>Web and app development, ERP and CRM, AI and automation, IT and cloud,
                   marketing: <?= esc($serviceCount) ?> services across <?= esc($categoryCount) ?>
                   disciplines, architected and supported by one team.</p>
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

    <!-- Consulting Excellence: same section, same real copy, as the
         homepage's #approach -- reused rather than rewritten, since it's
         already-approved company content, not filler. No eyebrow here
         either: it sits within two sections of Hero's eyebrow, over this
         page's budget if it kept its own. The theme's own "Case Studio"
         and second "About" (differentiator) sections from service.html
         are dropped rather than faked -- we have no real case studies or
         client photography to fill them with, and the differentiator
         copy is already covered by the hero panel's checklist above. -->
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
                            PDPA-compliant data handling, built in by default</li>
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

<?php
render_schema_breadcrumbs([
    'Home'        => 'sg/',
    'What We Do'  => 'sg/services/',
]);
include __DIR__ . '/../includes/footer.php';
?>
