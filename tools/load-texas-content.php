<?php
/**
 * One-off content loader for the Texas region addendum. Region-scoped
 * (region_id = 1, the real `regions` row for Texas) service_content rows
 * for the 5 Texas-relevant services only. get_service_content() already
 * falls back to the country-level (region_id=0) row for any other
 * service, so this never breaks the other 11 US service pages.
 *
 * Run once: php tools/load-texas-content.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$countryId = 3; // us
$regionId  = 1; // texas

$content = [
    'web-development' => [
        'h1' => "Web development for Austin, Dallas and Houston businesses.",
        'intro' => "<p>Corporate websites, high-conversion e-commerce stores, custom web portals and performance-driven web applications, built by the same engineer who scopes the project and ships it.</p>",
        'local_proof' => "<p>Built for Texas's fast-growing tech and services corridor: mobile-first layouts, Central Time-aligned delivery calls, and checkout flows priced in USD with Texas sales tax itemized from day one.</p>",
        'compliance_note' => "<p>Texas combines a state sales tax rate with local city and county add-ons that vary by address, so every build itemizes the combined rate per line rather than a single guessed total.</p>",
        'cta_text' => "Get a fixed-scope web development quote",
        'meta_title' => "Web Development Texas | Corediva Tech Solutions",
        'meta_description' => "Corporate websites, e-commerce and custom web apps built for Texas businesses in Austin, Dallas and Houston.",
    ],
    'erp-crm' => [
        'h1' => "One system of record for Texas businesses running finance, inventory and sales together.",
        'intro' => "<p>End-to-end custom ERP development to manage finance, inventory, HR and payroll, plus CRM systems that keep your sales pipeline visible instead of scattered across inboxes and spreadsheets.</p>",
        'local_proof' => "<p>Built for Texas businesses selling across multiple cities and counties, each with its own combined sales tax rate: one connected system instead of a spreadsheet nobody trusts at quarter end.</p>",
        'compliance_note' => "<p>Finance modules itemize Texas's state-plus-local combined sales tax by jurisdiction from the design stage, so quarterly filings pull straight from the system.</p>",
        'cta_text' => "Get a fixed-scope ERP/CRM quote",
        'meta_title' => "ERP & CRM Solutions Texas | Corediva Tech Solutions",
        'meta_description' => "Custom ERP and CRM builds for Texas businesses: finance, inventory, HR, payroll and sales pipeline on one connected system.",
    ],
    'ai-solutions' => [
        'h1' => "AI chatbots that qualify leads on Central Time, around the clock.",
        'intro' => "<p>Intelligent AI chatbots, WhatsApp business bots, AI voice assistants and lead qualification systems that greet, qualify and route every enquiry the moment it lands, synced directly with your CRM.</p>",
        'local_proof' => "<p>A Texas business fielding enquiries from customers across the state and beyond gets a bot that answers instantly on Central Time and hands a qualified lead to your team the moment someone's available.</p>",
        'compliance_note' => "<p>Conversations and captured contact data are handled with clear retention rules and security-conscious development practices, and any quote a bot triggers itemizes Texas's combined sales tax automatically.</p>",
        'cta_text' => "Get a fixed-scope AI automation quote",
        'meta_title' => "AI Chatbots & Automation Texas | Corediva",
        'meta_description' => "AI chatbots and lead qualification automation for Texas businesses. Qualify leads 24/7, sync straight into your CRM.",
    ],
    'cybersecurity' => [
        'h1' => "Security hardening and rapid response when something goes wrong.",
        'intro' => "<p>Website security hardening, rapid malware removal, vulnerability assessments and comprehensive audits, for when something goes wrong and you need it fixed, not just diagnosed.</p>",
        'local_proof' => "<p>Texas businesses handling customer payment and identity data get hardening work scoped around what attackers actually target: exposed admin panels, unpatched plugins and weak payment-gateway configurations.</p>",
        'compliance_note' => "<p>Security audits document findings and remediation with security-conscious practices throughout, in a format suitable for a Texas insurer or auditor to review.</p>",
        'cta_text' => "Get a fixed-scope security audit",
        'meta_title' => "Cybersecurity Services Texas | Corediva",
        'meta_description' => "Security hardening, malware removal, vulnerability assessments and audits for Texas businesses.",
    ],
    'cloud-solutions' => [
        'h1' => "AWS and Azure infrastructure, migrated and managed properly.",
        'intro' => "<p>AWS and Microsoft Azure hosting, seamless cloud migrations, cloud backup setups and DevOps engineering, migrated and managed properly rather than left to drift.</p>",
        'local_proof' => "<p>Latency-sensitive Texas workloads are routed through the nearest available AWS/Azure region, with backup and disaster recovery configured so a regional outage doesn't take your business down with it.</p>",
        'compliance_note' => "<p>Billing and usage records for cloud infrastructure itemize Texas's combined sales tax the same way every other engagement does, so cloud spend reconciles cleanly at quarter end.</p>",
        'cta_text' => "Get a fixed-scope cloud migration quote",
        'meta_title' => "Cloud Solutions (AWS & Azure) Texas | Corediva",
        'meta_description' => "AWS and Azure hosting, cloud migration, backup and DevOps engineering for Texas businesses.",
    ],
];

$serviceRows = db_all('SELECT id, slug FROM services');
$slugToServiceId = array_column($serviceRows, 'id', 'slug');

$inserted = 0;
foreach ($content as $slug => $c) {
    $serviceId = $slugToServiceId[$slug] ?? null;
    if (!$serviceId) {
        fwrite(STDERR, "Unknown service slug: $slug\n");
        continue;
    }
    db_exec(
        'INSERT INTO service_content
            (service_id, country_id, region_id, title, h1, intro, local_proof, compliance_note, cta_text, meta_title, meta_description, is_published)
         VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, 1)',
        [$serviceId, $countryId, $regionId, $c['h1'], $c['intro'], $c['local_proof'], $c['compliance_note'], $c['cta_text'], $c['meta_title'], $c['meta_description']]
    );
    $inserted++;
}
echo "Texas region service_content rows inserted: $inserted\n";
