<?php
/**
 * One-off content loader for the Australia country rollout. Mirrors the
 * shape of AE's (country_id=2), USA's (country_id=3) and UK's (country_id=4)
 * service_content/product_content rows, genuinely rewritten: AU angle is
 * ABN/BAS (Australian Business Number / Business Activity Statement, GST
 * reporting) and delivery hours spanning AEST through AWST -- no specific
 * Australian Privacy Principles certification or registration is claimed,
 * since none is documented anywhere in this project's real facts.
 *
 * Run once: php tools/load-au-content.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$countryId = 5; // au

$services = [
    'web-development' => [
        'h1' => "Websites and web apps built to sell in AUD from day one.",
        'intro' => "<p>Corporate websites, high-conversion e-commerce stores, custom web portals and performance-driven web applications, scoped and shipped by the same engineer, not handed between a sales rep, a designer and a developer who never talk to each other.</p>",
        'local_proof' => "<p>Every build ships with WhatsApp click-to-chat wired in, mobile-first layouts, and checkout, quote or booking flows priced in AUD from launch, not converted after the fact.</p>",
        'compliance_note' => "<p>Where a build handles transactions, it's structured so records export in a format that lines up with ABN and BAS reporting, instead of leaving that reconciliation to a separate finance project later.</p>",
        'cta_text' => 'Get a fixed-scope web development quote',
        'meta_title' => 'Web Development Australia | Corediva Tech Solutions',
        'meta_description' => 'Corporate websites, e-commerce and custom web apps built and hosted for Australian businesses. Fast, mobile-first, delivered by one accountable team.',
    ],
    'erp-crm' => [
        'h1' => "ERP and CRM systems built around your BAS cycle, not against it.",
        'intro' => "<p>Custom ERP and CRM platforms configured to match how your finance, sales and operations teams already work, rather than a rigid template that forces a workaround for every edge case.</p>",
        'local_proof' => "<p>Systems delivered for Australian businesses default to AUD and GST handling, with delivery hours spanning AEST through AWST so a change request doesn't sit unanswered for a full day.</p>",
        'compliance_note' => "<p>Finance modules keep digital records structured for ABN and BAS reporting, so your quarterly or monthly activity statement pulls from the system instead of a spreadsheet reconciliation.</p>",
        'cta_text' => 'Get a fixed-scope ERP/CRM quote',
        'meta_title' => 'ERP & CRM Solutions Australia | Corediva Tech Solutions',
        'meta_description' => 'Custom ERP and CRM systems for Australian businesses, built around finance, sales and operations as they actually run, with ABN/BAS-ready records.',
    ],
    'ai-solutions' => [
        'h1' => "AI chatbots that qualify a lead the moment it lands, whatever the hour.",
        'intro' => "<p>WhatsApp and web AI bots that greet, qualify and route every enquiry the moment it arrives, synced directly to your CRM so nothing sits unanswered overnight or over a long weekend.</p>",
        'local_proof' => "<p>Support hours span AEST through AWST, and every bot hands off to a real person the moment a query needs judgement, rather than looping an Australian customer through scripted replies.</p>",
        'compliance_note' => "<p>Chat and lead data is handled with clear retention rules and consent capture built in, and any quote a bot generates is issued in an ABN/BAS-ready format automatically.</p>",
        'cta_text' => 'Get a fixed-scope AI automation quote',
        'meta_title' => 'AI Chatbots & Automation Australia | Corediva Tech Solutions',
        'meta_description' => 'WhatsApp and web AI chatbots for Australian businesses, synced to your CRM, with AEST/AWST-aligned support and ABN/BAS-ready quoting.',
    ],
    'microsoft-365' => [
        'h1' => "Microsoft 365 configured properly once, not patched every quarter.",
        'intro' => "<p>Full Microsoft 365 and Workspace setup: mailboxes, SharePoint, Teams and admin policies configured to match how your business is actually structured, not left on default tenant settings.</p>",
        'local_proof' => "<p>Migrations are scheduled around Australian business hours so mailbox cutover happens with minimal disruption, and support tickets go to the same team that did the original setup.</p>",
        'compliance_note' => "<p>Retention and access policies are configured with Australian data-handling expectations in mind, keeping mailbox and document records straightforward to produce if the ATO or an auditor asks.</p>",
        'cta_text' => 'Get a fixed-scope Microsoft 365 quote',
        'meta_title' => 'Microsoft 365 Setup Australia | Corediva Tech Solutions',
        'meta_description' => 'Microsoft 365 and Workspace setup for Australian businesses: mailboxes, SharePoint, Teams and admin policies configured properly the first time.',
    ],
    'sap' => [
        'h1' => "SAP rollouts that don't stall the moment finance gets involved.",
        'intro' => "<p>End-to-end SAP implementation, migration and support, with modules configured to match how your business actually operates rather than forcing your process to bend to SAP's defaults.</p>",
        'local_proof' => "<p>Australian rollouts are scoped with AUD, GST handling and ABN/BAS reporting built into the finance module configuration from the first workshop, not added as a change request later.</p>",
        'compliance_note' => "<p>Finance and reporting configuration is built to keep GST records in a format that lines up with BAS lodgement, cutting the manual reconciliation work at quarter-end.</p>",
        'cta_text' => 'Get a fixed-scope SAP quote',
        'meta_title' => 'SAP Implementation & Support Australia | Corediva Tech Solutions',
        'meta_description' => 'SAP implementation, migration and support for Australian businesses, with ABN/BAS-ready finance configuration built in from the start.',
    ],
    'salesforce' => [
        'h1' => "Salesforce that sales actually opens, not a CRM nobody updates.",
        'intro' => "<p>Salesforce setup, customisation and CRM integration to unify sales, support and marketing in one system of record, configured around your existing pipeline instead of a generic template.</p>",
        'local_proof' => "<p>Currency, date formats and reporting are set up for the Australian market from day one, and support requests land during Australian business hours across our AEST-to-AWST coverage.</p>",
        'compliance_note' => "<p>Lead and contact data fields are structured with Australian data-handling practices in mind, and any quote or invoice object Salesforce generates follows ABN/BAS-ready formatting.</p>",
        'cta_text' => 'Get a fixed-scope Salesforce quote',
        'meta_title' => 'Salesforce Setup & Customisation Australia | Corediva Tech Solutions',
        'meta_description' => 'Salesforce setup, customisation and integration for Australian businesses, unifying sales, support and marketing in one system.',
    ],
    'zoho' => [
        'h1' => "One connected Zoho stack instead of five apps sharing a login.",
        'intro' => "<p>Complete Zoho suite setup, CRM, Books, Desk and People, configured and connected so you get one working business stack rather than five disconnected apps that happen to share a login.</p>",
        'local_proof' => "<p>Zoho Books is configured for AUD invoicing and Australian GST rates from setup, and support runs across AEST-to-AWST hours so a configuration question doesn't wait a full day for an answer.</p>",
        'compliance_note' => "<p>Zoho Books is set up to produce GST records structured for BAS lodgement, so quarterly or monthly filing pulls straight from the system.</p>",
        'cta_text' => 'Get a fixed-scope Zoho setup quote',
        'meta_title' => 'Zoho Suite Setup Australia | Corediva Tech Solutions',
        'meta_description' => 'Zoho CRM, Books, Desk and People, set up and connected for Australian businesses, with ABN/BAS-ready invoicing built in.',
    ],
    'digital-marketing' => [
        'h1' => "SEO judged on enquiries generated, not a traffic chart.",
        'intro' => "<p>Search engine optimisation built around the keywords Australian buyers actually search, technical SEO fixes, content strategy and ongoing reporting tied to enquiries generated, not just impressions.</p>",
        'local_proof' => "<p>Campaigns are built around Australian search intent and .com.au signals where relevant, with reporting delivered on a schedule aligned to AEST-to-AWST business hours.</p>",
        'compliance_note' => "<p>Analytics and cookie-consent implementation is built with the Australian Privacy Principles in mind, so tracking doesn't run ahead of visitor consent.</p>",
        'cta_text' => 'Get a fixed-scope SEO quote',
        'meta_title' => 'SEO & Digital Marketing Australia | Corediva Tech Solutions',
        'meta_description' => 'SEO built around Australian search intent, with technical fixes, content strategy and reporting tied to real enquiries, not vanity traffic.',
    ],
    'graphic-design' => [
        'h1' => "Brand and campaign design that ships on your deadline, not around it.",
        'intro' => "<p>Brand identity, marketing collateral and campaign design work, produced by the same team that builds your website and product, so visual language stays consistent across every touchpoint.</p>",
        'local_proof' => "<p>Design briefs are scoped and reviewed during Australian business hours, with revisions turned around against agreed deadlines rather than an open-ended queue.</p>",
        'compliance_note' => "<p>Licensing for any third-party stock assets used is documented and handed over with the final files, so there's no ambiguity about what your business can reuse.</p>",
        'cta_text' => 'Get a fixed-scope design quote',
        'meta_title' => 'Graphic Design Australia | Corediva Tech Solutions',
        'meta_description' => 'Brand identity, marketing collateral and campaign design for Australian businesses, delivered by the same team building your website and product.',
    ],
    'video-editing' => [
        'h1' => "Video editing that turns raw footage into something worth posting.",
        'intro' => "<p>Promotional, product and social video editing, from raw footage to a finished cut ready for the platform it's built for, without a multi-week agency turnaround.</p>",
        'local_proof' => "<p>Deliverables are scoped against your publishing calendar and delivered across Australian business hours, so a launch date doesn't slip because a video is still in review.</p>",
        'compliance_note' => "<p>Music and stock footage licensing used in a project is documented and cleared for your intended use before final delivery.</p>",
        'cta_text' => 'Get a fixed-scope video editing quote',
        'meta_title' => 'Video Editing Australia | Corediva Tech Solutions',
        'meta_description' => 'Promotional, product and social video editing for Australian businesses, delivered against your publishing calendar, not an open-ended queue.',
    ],
    'corporate-events' => [
        'h1' => "Event tech support that covers the run sheet, not just the guest list.",
        'intro' => "<p>Technical and production support for corporate events, launches and conferences: registration systems, AV coordination and on-the-day support handled by the same team that builds your other digital systems.</p>",
        'local_proof' => "<p>Event support is scoped around Australian venues and time zones, with a point of contact reachable throughout the event day, not just during a pre-agreed support window.</p>",
        'compliance_note' => "<p>Attendee registration data collected for an event is handled with clear retention and consent rules in line with the Australian Privacy Principles, and deleted on an agreed schedule after the event.</p>",
        'cta_text' => 'Get a fixed-scope event support quote',
        'meta_title' => 'Corporate Event Support Australia | Corediva Tech Solutions',
        'meta_description' => 'Technical and production support for Australian corporate events and launches: registration systems, AV coordination and on-the-day support.',
    ],
    'managed-it-services' => [
        'h1' => "Managed IT that answers the phone instead of routing to a ticket queue.",
        'intro' => "<p>Ongoing IT support, monitoring and maintenance for the systems we build and the ones already running in your business, so a problem gets a person, not an auto-reply.</p>",
        'local_proof' => "<p>Support hours span AEST through AWST, with response times measured from when a ticket lands, not when someone eventually opens it.</p>",
        'compliance_note' => "<p>Access to client systems is logged and limited to the engineers actually working the ticket, in line with Australian data-handling expectations for third-party IT support.</p>",
        'cta_text' => 'Get a fixed-scope managed IT quote',
        'meta_title' => 'Managed IT Services Australia | Corediva Tech Solutions',
        'meta_description' => 'Ongoing IT support, monitoring and maintenance for Australian businesses, with AEST/AWST-aligned response times and one accountable team.',
    ],
    'cybersecurity' => [
        'h1' => "Security work scoped to your actual attack surface, not a generic checklist.",
        'intro' => "<p>Security assessments, hardening and ongoing monitoring built around the systems you actually run, whether that's a customer-facing web app, an internal ERP or a cloud environment.</p>",
        'local_proof' => "<p>Findings are reported in plain language with a prioritised remediation plan, not a lengthy scan output that leaves your team to work out what matters first.</p>",
        'compliance_note' => "<p>We don't claim a certification the company doesn't hold: assessments follow established good practice (OWASP-aligned testing, least-privilege access reviews) rather than being marketed as a formal compliance audit.</p>",
        'cta_text' => 'Get a fixed-scope security assessment quote',
        'meta_title' => 'Cybersecurity Services Australia | Corediva Tech Solutions',
        'meta_description' => 'Security assessments, hardening and monitoring for Australian businesses, scoped to your actual systems with a prioritised, plain-language remediation plan.',
    ],
    'mobile-app-development' => [
        'h1' => "Mobile apps built to clear app store review, not just demo well.",
        'intro' => "<p>Native iOS and Android development, plus cross-platform Flutter apps, built to actually pass app store review and ship, not stall at the last submission step.</p>",
        'local_proof' => "<p>Payment and subscription flows are built to work in AUD from the first build, and launch-day support is scheduled across AEST-to-AWST hours so an issue gets picked up during Australian time.</p>",
        'compliance_note' => "<p>Data collected through the app is scoped and documented for the app store's privacy disclosure requirements and handled in line with the Australian Privacy Principles.</p>",
        'cta_text' => 'Get a fixed-scope mobile app quote',
        'meta_title' => 'Mobile App Development Australia | Corediva Tech Solutions',
        'meta_description' => 'Native iOS, Android and cross-platform Flutter app development for Australian businesses, built to pass app store review and ship.',
    ],
    'cloud-solutions' => [
        'h1' => "Cloud infrastructure sized for your real traffic, not a guess.",
        'intro' => "<p>Cloud architecture, migration and ongoing infrastructure management, sized and configured around the traffic and workloads your business actually runs, not a default template tier.</p>",
        'local_proof' => "<p>Where latency to Australian users matters, workloads are deployed in Australian or APAC regions, and infrastructure alerts are monitored across AEST-to-AWST hours by the same team that built the environment.</p>",
        'compliance_note' => "<p>Data residency is discussed and agreed explicitly during scoping rather than assumed, so you know exactly where Australian customer data physically sits.</p>",
        'cta_text' => 'Get a fixed-scope cloud infrastructure quote',
        'meta_title' => 'Cloud Solutions Australia | Corediva Tech Solutions',
        'meta_description' => 'Cloud architecture, migration and infrastructure management for Australian businesses, sized to real traffic with Australian/APAC-region deployment where it matters.',
    ],
    'automation' => [
        'h1' => "Workflow automation that removes the manual step, not just relabels it.",
        'intro' => "<p>Business process and workflow automation connecting the tools you already run, so a lead, invoice or support ticket moves through your systems without someone re-typing it three times.</p>",
        'local_proof' => "<p>Automations are built and tested against your actual Australian working hours and public holiday calendar, so a workflow doesn't silently misfire on a long weekend.</p>",
        'compliance_note' => "<p>Any automation that touches invoicing or financial records is built to produce output in an ABN/BAS-ready format automatically.</p>",
        'cta_text' => 'Get a fixed-scope automation quote',
        'meta_title' => 'Business Process Automation Australia | Corediva Tech Solutions',
        'meta_description' => 'Workflow and business process automation for Australian businesses, connecting the tools you already run with ABN/BAS-ready output.',
    ],
];

$products = [
    'instant-appointments' => [
        'title' => null,
        'h1' => "Real-time appointment booking that runs itself, across every time zone.",
        'intro' => "<p>Instant Appointments gives your business a self-service booking calendar: clients see real available slots, book in a few taps, and get an automated reminder ahead of time.</p>",
        'local_proof' => "<p>Calendars default to Australian time zones and reminders go out over WhatsApp, so a booking made late on a Friday still gets confirmed correctly for Monday.</p>",
        'meta_title' => 'Appointment Booking Software Australia | Corediva',
        'meta_description' => 'Real-time appointment booking with WhatsApp reminders for Australian businesses.',
    ],
    'invoice' => [
        'title' => 'Invoice Australia',
        'h1' => "Invoicing that's ABN and BAS-ready out of the box.",
        'intro' => "<p>Invoice generates client-ready invoices and quotes in minutes, tracks payment status, and keeps digital records structured for ABN and BAS reporting.</p>",
        'local_proof' => "<p>Invoices default to AUD with GST itemised by line, and every record exports in a format built to line up with your BAS lodgement, no manual reformatting before it's due.</p>",
        'meta_title' => 'Invoicing Software Australia | Corediva',
        'meta_description' => 'ABN/BAS-ready invoicing software for Australian businesses, with AUD support and itemised GST by default.',
    ],
    'scheduler' => [
        'title' => null,
        'h1' => "Team and resource scheduling that doesn't live in a group chat.",
        'intro' => "<p>Scheduler coordinates staff, equipment or room bookings from one calendar, so double-bookings and \"is this slot free?\" messages stop happening.</p>",
        'local_proof' => "<p>Set up around Australian working hours and public holidays out of the box, so a team's schedule doesn't need manual correction every long weekend.</p>",
        'meta_title' => 'Team & Resource Scheduling Software Australia | Corediva',
        'meta_description' => 'Staff and resource scheduling software for Australian businesses, built around real working hours and public holidays.',
    ],
    'e-marketing' => [
        'title' => null,
        'h1' => "Email and WhatsApp campaigns sent from one dashboard, not three.",
        'intro' => "<p>E-Marketing runs email and WhatsApp campaigns from a single dashboard, with segmentation, scheduling and open/click reporting built in.</p>",
        'local_proof' => "<p>Sending schedules default to Australian time zones so campaigns land in inboxes during business hours, not the middle of the night.</p>",
        'meta_title' => 'Email & WhatsApp Marketing Software Australia | Corediva',
        'meta_description' => 'Email and WhatsApp campaign software for Australian businesses, with segmentation, scheduling and reporting in one dashboard.',
    ],
    'crm-leads' => [
        'title' => null,
        'h1' => "A CRM built to catch leads, not just store contact cards.",
        'intro' => "<p>CRM Leads captures enquiries from your website, WhatsApp and ad campaigns into one pipeline, so nothing gets lost between channels.</p>",
        'local_proof' => "<p>Pipeline stages and follow-up reminders are set up around Australian working hours, so a lead captured overnight is flagged first thing the next business day.</p>",
        'meta_title' => 'Lead Management CRM Australia | Corediva',
        'meta_description' => 'Lead capture and pipeline CRM for Australian businesses, unifying website, WhatsApp and ad-campaign enquiries in one place.',
    ],
    'connect-erp' => [
        'title' => null,
        'h1' => "ERP that connects to the tools you already run, not instead of them.",
        'intro' => "<p>Connect ERP links inventory, finance and operations data across the systems your business already uses, instead of forcing a single monolithic replacement.</p>",
        'local_proof' => "<p>Finance modules are configured for AUD and ABN/BAS-ready reporting from setup, so your existing accounting workflow doesn't need to be rebuilt.</p>",
        'meta_title' => 'Connected ERP Software Australia | Corediva',
        'meta_description' => 'ERP software for Australian businesses that connects inventory, finance and operations across the tools you already run.',
    ],
    'hrm' => [
        'title' => null,
        'h1' => "HR software that handles Australian pay cycles without a spreadsheet backup.",
        'intro' => "<p>Corediva HRM manages employee records, leave, and payroll workflow in one system, replacing the spreadsheet-plus-email process most growing teams start with.</p>",
        'local_proof' => "<p>Leave calendars and pay cycles are configured around Australian public holidays and standard pay periods from setup.</p>",
        'meta_title' => 'HR Management Software Australia | Corediva',
        'meta_description' => 'HR and payroll workflow software for Australian businesses, configured around Australian public holidays and pay periods.',
    ],
    'whatsapp-automation' => [
        'title' => null,
        'h1' => "WhatsApp automation that answers the first message instantly.",
        'intro' => "<p>Web Automate handles WhatsApp Business automation: auto-replies, lead qualification and routing to the right team member, all synced to your CRM.</p>",
        'local_proof' => "<p>Support hand-off is scheduled around Australian working hours, so an automated reply during off-hours still sets the right expectation for when a person will follow up.</p>",
        'meta_title' => 'WhatsApp Business Automation Australia | Corediva',
        'meta_description' => 'WhatsApp Business automation for Australian businesses: auto-replies, lead qualification and CRM-synced routing.',
    ],
    'web-integrations' => [
        'title' => null,
        'h1' => "Web integrations that stop your team copy-pasting between tabs.",
        'intro' => "<p>Integrations on Web (IoW) connects your website's forms, checkout and booking flows directly into your CRM, accounting or scheduling tools.</p>",
        'local_proof' => "<p>Integration mapping is built and tested against your actual Australian business workflow, not a generic template that needs reworking after go-live.</p>",
        'meta_title' => 'Website Integrations Australia | Corediva',
        'meta_description' => 'Website form, checkout and booking integrations for Australian businesses, connected directly into your CRM and accounting tools.',
    ],
    'attendance-payroll' => [
        'title' => null,
        'h1' => "Attendance tracking that feeds payroll automatically, not manually.",
        'intro' => "<p>Attendance and Payroll Manager tracks staff clock-ins and leave, then feeds that data straight into payroll processing, removing a manual reconciliation step.</p>",
        'local_proof' => "<p>Configured around Australian working patterns and statutory leave entitlements, so payroll calculations start from an accurate base.</p>",
        'meta_title' => 'Attendance & Payroll Software Australia | Corediva',
        'meta_description' => 'Attendance tracking and payroll software for Australian businesses, configured around Australian working patterns and leave entitlements.',
    ],
    'payauth' => [
        'title' => null,
        'h1' => "Payment collection that reconciles itself against your invoices.",
        'intro' => "<p>Payauth handles payment collection and reconciliation, matching incoming payments against invoices automatically instead of a manual bank-statement cross-check.</p>",
        'local_proof' => "<p>Built for AUD transactions with records formatted to stay lined up with ABN/BAS reporting.</p>",
        'meta_title' => 'Payment Collection & Reconciliation Software Australia | Corediva',
        'meta_description' => 'Payment collection and invoice reconciliation software for Australian businesses, with ABN/BAS-ready records.',
    ],
    'school-erp' => [
        'title' => null,
        'h1' => "School management software that covers admissions to fees in one system.",
        'intro' => "<p>School Management ERP (ScubaSchool) handles admissions, attendance, timetabling and fee collection in one platform, built for schools running that process across spreadsheets and paper today.</p>",
        'local_proof' => "<p>Fee collection is configured for AUD, and term/holiday calendars follow the Australian academic year out of the box.</p>",
        'meta_title' => 'School Management Software Australia | Corediva',
        'meta_description' => 'School management software for Australian institutions: admissions, attendance, timetabling and fee collection in one platform.',
    ],
];

// -----------------------------------------------------------------------
// service_content
// -----------------------------------------------------------------------
$serviceRows = db_all('SELECT id, slug FROM services');
$slugToServiceId = [];
foreach ($serviceRows as $r) {
    $slugToServiceId[$r['slug']] = (int) $r['id'];
}

$scInserted = 0;
foreach ($services as $slug => $c) {
    if (!isset($slugToServiceId[$slug])) {
        echo "WARN: unknown service slug $slug\n";
        continue;
    }
    $serviceId = $slugToServiceId[$slug];

    $existing = db_one('SELECT id FROM service_content WHERE service_id = ? AND country_id = ? AND region_id = 0', [$serviceId, $countryId]);
    if ($existing) {
        db_exec(
            'UPDATE service_content SET h1=?, intro=?, local_proof=?, compliance_note=?, cta_text=?, meta_title=?, meta_description=?, is_published=1, updated_at=NOW() WHERE id=?',
            [$c['h1'], $c['intro'], $c['local_proof'], $c['compliance_note'], $c['cta_text'], $c['meta_title'], $c['meta_description'], $existing['id']]
        );
    } else {
        db_exec(
            'INSERT INTO service_content (service_id, country_id, region_id, title, h1, intro, local_proof, compliance_note, cta_text, meta_title, meta_description, is_published, created_at, updated_at)
             VALUES (?, ?, 0, "", ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
            [$serviceId, $countryId, $c['h1'], $c['intro'], $c['local_proof'], $c['compliance_note'], $c['cta_text'], $c['meta_title'], $c['meta_description']]
        );
        $scInserted++;
    }
}
echo "service_content: inserted $scInserted (total should be 16)\n";

// -----------------------------------------------------------------------
// product_content
// -----------------------------------------------------------------------
$productRows = db_all('SELECT id, slug FROM products');
$slugToProductId = [];
foreach ($productRows as $r) {
    $slugToProductId[$r['slug']] = (int) $r['id'];
}

$pcInserted = 0;
foreach ($products as $slug => $c) {
    if (!isset($slugToProductId[$slug])) {
        echo "WARN: unknown product slug $slug\n";
        continue;
    }
    $productId = $slugToProductId[$slug];
    $title = $c['title'] ?? '';

    $existing = db_one('SELECT id FROM product_content WHERE product_id = ? AND country_id = ?', [$productId, $countryId]);
    if ($existing) {
        db_exec(
            'UPDATE product_content SET title=?, h1=?, intro=?, local_proof=?, meta_title=?, meta_description=?, is_published=1, updated_at=NOW() WHERE id=?',
            [$title, $c['h1'], $c['intro'], $c['local_proof'], $c['meta_title'], $c['meta_description'], $existing['id']]
        );
    } else {
        db_exec(
            'INSERT INTO product_content (product_id, country_id, title, h1, intro, local_proof, meta_title, meta_description, is_published, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
            [$productId, $countryId, $title, $c['h1'], $c['intro'], $c['local_proof'], $c['meta_title'], $c['meta_description']]
        );
        $pcInserted++;
    }
}
echo "product_content: inserted $pcInserted (total should be 12)\n";

// -----------------------------------------------------------------------
// Clone service_page_sections / product_page_sections from SG (country_id=1)
// -----------------------------------------------------------------------
$existingSPS = (int) db_value('SELECT COUNT(*) FROM service_page_sections WHERE country_id = ?', [$countryId]);
if ($existingSPS === 0) {
    $sgSections = db_all('SELECT * FROM service_page_sections WHERE country_id = 1');
    foreach ($sgSections as $s) {
        db_exec(
            'INSERT INTO service_page_sections (service_id, country_id, region_id, section_type, heading, subheading, body, items, media_url, media_alt, image_position, cta_label, cta_url, sort_order, is_published, created_at, updated_at)
             VALUES (?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
            [
                $s['service_id'], $countryId, $s['section_type'], $s['heading'], $s['subheading'], $s['body'],
                $s['items'], $s['media_url'], $s['media_alt'], $s['image_position'], $s['cta_label'], $s['cta_url'], $s['sort_order'],
            ]
        );
    }
    echo "service_page_sections: cloned " . count($sgSections) . " from SG\n";
} else {
    echo "service_page_sections: already present ($existingSPS), skipped clone\n";
}

$existingPPS = (int) db_value('SELECT COUNT(*) FROM product_page_sections WHERE country_id = ?', [$countryId]);
if ($existingPPS === 0) {
    $sgPSections = db_all('SELECT * FROM product_page_sections WHERE country_id = 1');
    foreach ($sgPSections as $s) {
        db_exec(
            'INSERT INTO product_page_sections (product_id, country_id, section_type, heading, subheading, body, items, media_url, media_alt, image_position, cta_label, cta_url, sort_order, is_published, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())',
            [
                $s['product_id'], $countryId, $s['section_type'], $s['heading'], $s['subheading'], $s['body'],
                $s['items'], $s['media_url'], $s['media_alt'], $s['image_position'], $s['cta_label'], $s['cta_url'], $s['sort_order'],
            ]
        );
    }
    echo "product_page_sections: cloned " . count($sgPSections) . " from SG\n";
} else {
    echo "product_page_sections: already present ($existingPPS), skipped clone\n";
}

// -----------------------------------------------------------------------
// Clone service-level FAQs from SG (page_type='service'), with the
// PDPA-specific one (ai-solutions) rewritten for Australia.
// -----------------------------------------------------------------------
$existingServiceFaqs = (int) db_value("SELECT COUNT(*) FROM faqs WHERE page_type='service' AND country_id = ?", [$countryId]);
if ($existingServiceFaqs === 0) {
    $sgServiceFaqs = db_all("SELECT * FROM faqs WHERE page_type='service' AND country_id = 1");
    $aiSolutionsServiceId = $slugToServiceId['ai-solutions'] ?? null;
    foreach ($sgServiceFaqs as $f) {
        $question = $f['question'];
        $answer   = $f['answer'];
        if ($aiSolutionsServiceId !== null && (int) $f['reference_id'] === $aiSolutionsServiceId
            && stripos($question, 'PDPA') !== false) {
            $question = 'Is your chatbot and lead data handled in line with Australian privacy expectations?';
            $answer   = "Yes. Every chatbot and CRM integration we deliver for Australian businesses is built with clear data retention rules and consent capture in line with the Australian Privacy Principles, and any quote it generates is issued in an ABN/BAS-ready format automatically.";
        }
        db_exec(
            'INSERT INTO faqs (page_type, reference_id, country_id, question, answer, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, 1)',
            [$f['page_type'], $f['reference_id'], $countryId, $question, $answer, $f['sort_order']]
        );
    }
    echo "service faqs: cloned " . count($sgServiceFaqs) . " from SG (1 rewritten for AU)\n";
} else {
    echo "service faqs: already present ($existingServiceFaqs), skipped clone\n";
}

// -----------------------------------------------------------------------
// 3 new AU-specific home FAQs
// -----------------------------------------------------------------------
$existingHomeFaqsAu = (int) db_value("SELECT COUNT(*) FROM faqs WHERE page_type='home' AND country_id = ?", [$countryId]);
if ($existingHomeFaqsAu === 0) {
    $homeFaqs = [
        [
            'Is your invoicing software ABN/BAS-ready for Australian businesses?',
            'Yes. Our Invoice product and every ERP/CRM build we deliver for Australian businesses keep digital records structured for ABN and BAS reporting, with GST itemised by line.',
            60,
        ],
        [
            'Do you support WhatsApp Business API for Australian numbers?',
            'Yes, WhatsApp is usually the primary channel we deploy for Australian businesses, connected directly to your CRM so no enquiry goes unanswered after hours.',
            70,
        ],
        [
            'What time zone do you support for Australian clients?',
            'Our delivery team works from IST and aligns meeting and support hours to span AEST through AWST for Australian engagements.',
            80,
        ],
    ];
    foreach ($homeFaqs as [$q, $a, $sort]) {
        db_exec(
            'INSERT INTO faqs (page_type, reference_id, country_id, question, answer, sort_order, is_active)
             VALUES ("home", NULL, ?, ?, ?, ?, 1)',
            [$countryId, $q, $a, $sort]
        );
    }
    echo "home faqs: added 3 for AU\n";
} else {
    echo "home faqs: already present ($existingHomeFaqsAu), skipped\n";
}

// -----------------------------------------------------------------------
// hero_feature_rows + hero_slides
// -----------------------------------------------------------------------
$existingFeature = db_one('SELECT id FROM hero_feature_rows WHERE country_id = ?', [$countryId]);
if (!$existingFeature) {
    db_exec(
        'INSERT INTO hero_feature_rows (country_id, title, subtitle, icon, sort_order, is_active)
         VALUES (?, "ABN/BAS-Ready Invoicing", "GST records built into every system", "las la-file-invoice", 55, 1)',
        [$countryId]
    );
    $featureRowId = (int) db_value('SELECT id FROM hero_feature_rows WHERE country_id = ? ORDER BY id DESC LIMIT 1', [$countryId]);
    echo "hero_feature_rows: added 1 for AU (id=$featureRowId)\n";
} else {
    $featureRowId = (int) $existingFeature['id'];
    echo "hero_feature_rows: already present (id=$featureRowId), skipped\n";
}

$existingSlide = db_one('SELECT id FROM hero_slides WHERE country_id = ?', [$countryId]);
if (!$existingSlide) {
    db_exec(
        'INSERT INTO hero_slides (country_id, page_slug, badge, heading, heading_highlight, subheading, image, image_alt, cta1_text, cta1_url, cta2_text, cta2_url, feature_row_id, sort_order, is_active)
         VALUES (?, "home", "Trusted IT & AI Partner for Australian Businesses", "ABN/BAS-Ready Systems, Built for How Australia Does Business.", "ABN/BAS-Ready Systems", "We build WhatsApp-first AI chatbots, ERP/CRM systems and ABN/BAS-ready invoicing for Australian businesses that want enterprise-grade systems without enterprise-grade overhead, delivered by a team working AEST through AWST.", "", "", "Book a Free Consultation", "https://wa.me/918903855325", "See AI Chatbot Solutions", "#services", ?, 8, 1)',
        [$countryId, $featureRowId]
    );
    echo "hero_slides: added 1 for AU\n";
} else {
    echo "hero_slides: already present, skipped\n";
}

echo "\nDone.\n";
