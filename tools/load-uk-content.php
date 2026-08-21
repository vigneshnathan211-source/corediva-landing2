<?php
/**
 * One-off content loader for the UK country rollout. Mirrors the shape of
 * AE's (country_id=2) and USA's (country_id=3) service_content/product_content
 * rows, genuinely rewritten: UK angle is HMRC's Making Tax Digital reporting
 * requirement and GMT/BST delivery hours -- no ICO registration or specific
 * UK GDPR certification is claimed, since none is documented anywhere in
 * this project's real facts.
 *
 * Run once: php tools/load-uk-content.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$countryId = 4; // uk

$services = [
    'web-development' => [
        'h1' => "Web development that reports correctly the moment it goes live.",
        'intro' => "<p>Corporate websites, high-conversion e-commerce stores, custom web portals and performance-driven web applications, built by the same engineer who scopes the project and ships it. No agency handoffs, no template theme dressed up as custom work.</p>",
        'local_proof' => "<p>Built for how UK businesses actually receive enquiries: WhatsApp click-to-chat wired into every build, mobile-first layouts, and checkout, quote or booking flows that work in GBP from day one, not retrofitted after launch.</p>",
        'compliance_note' => "<p>E-commerce and portal builds are structured with HMRC's Making Tax Digital reporting requirements in mind from the start, so transaction records export cleanly instead of needing a separate finance project afterwards.</p>",
        'cta_text' => 'Get a fixed-scope web development quote',
        'meta_title' => 'Web Development UK | Corediva Tech Solutions',
        'meta_description' => 'Corporate websites, e-commerce and custom web apps built and hosted for UK businesses. Fast, mobile-first, delivered by one accountable team.',
    ],
    'erp-crm' => [
        'h1' => "ERP and CRM systems that keep your books MTD-ready by design.",
        'intro' => "<p>Custom ERP and CRM platforms built around how your finance, sales and operations teams actually work, not a rigid template that forces a workaround for every edge case.</p>",
        'local_proof' => "<p>Every system we deliver for UK businesses is configured for GBP by default, with a delivery team working GMT/BST hours so a change request raised in the morning doesn't sit unanswered until the next working day.</p>",
        'compliance_note' => "<p>Finance modules are built to keep digital records in the format HMRC's Making Tax Digital rules expect, so quarterly submissions pull straight from the system instead of a spreadsheet reconciliation.</p>",
        'cta_text' => 'Get a fixed-scope ERP/CRM quote',
        'meta_title' => 'ERP & CRM Solutions UK | Corediva Tech Solutions',
        'meta_description' => 'Custom ERP and CRM systems for UK businesses, built around finance, sales and operations as they actually run, with Making Tax Digital-ready records.',
    ],
    'ai-solutions' => [
        'h1' => "AI chatbots and automation that qualify leads while your team sleeps.",
        'intro' => "<p>WhatsApp and web AI bots that greet, qualify and route every enquiry the moment it lands, synced directly with your CRM so no lead sits unanswered overnight.</p>",
        'local_proof' => "<p>Delivery and support hours are aligned to GMT/BST, and every bot is wired to hand off to a real person the moment a query needs one, rather than looping a UK customer through scripted replies.</p>",
        'compliance_note' => "<p>Chat and lead data is handled with clear retention rules and consent capture built in, and every quote a bot generates is issued in a Making Tax Digital-compliant format automatically.</p>",
        'cta_text' => 'Get a fixed-scope AI automation quote',
        'meta_title' => 'AI Chatbots & Automation UK | Corediva Tech Solutions',
        'meta_description' => 'WhatsApp and web AI chatbots for UK businesses, synced to your CRM, with GMT/BST-aligned support and Making Tax Digital-ready quoting.',
    ],
    'microsoft-365' => [
        'h1' => "Microsoft 365 set up once, properly, instead of patched over time.",
        'intro' => "<p>Full Microsoft 365 and Workspace setup: mailboxes, SharePoint, Teams and admin policies configured to match how your business is actually structured, not the default tenant settings left as-is.</p>",
        'local_proof' => "<p>Migrations are scheduled around UK business hours so mailbox cutover happens with minimal disruption, and support tickets are picked up by the same team that did the original setup.</p>",
        'compliance_note' => "<p>Retention and access policies are configured with UK data-handling expectations in mind, keeping mailbox and document records in a state that's straightforward to produce if HMRC or an auditor asks.</p>",
        'cta_text' => 'Get a fixed-scope Microsoft 365 quote',
        'meta_title' => 'Microsoft 365 Setup UK | Corediva Tech Solutions',
        'meta_description' => 'Microsoft 365 and Workspace setup for UK businesses: mailboxes, SharePoint, Teams and admin policies configured properly the first time.',
    ],
    'sap' => [
        'h1' => "SAP implementations that don't stall six months into rollout.",
        'intro' => "<p>End-to-end SAP implementation, migration and support, with modules configured to match how your business actually operates rather than forcing your process to bend to SAP's defaults.</p>",
        'local_proof' => "<p>UK rollouts are scoped with GBP, VAT-registration handling and Making Tax Digital reporting built into the finance module configuration from the first workshop, not added as a change request later.</p>",
        'compliance_note' => "<p>Finance and reporting configuration is built to keep digital VAT records in the format HMRC's Making Tax Digital rules require, reducing the manual reconciliation work at quarter-end.</p>",
        'cta_text' => 'Get a fixed-scope SAP quote',
        'meta_title' => 'SAP Implementation & Support UK | Corediva Tech Solutions',
        'meta_description' => 'SAP implementation, migration and support for UK businesses, with Making Tax Digital-ready finance configuration built in from the start.',
    ],
    'salesforce' => [
        'h1' => "Salesforce setup that your sales team actually uses.",
        'intro' => "<p>Salesforce setup, customisation and CRM integration to unify sales, support and marketing in one system of record, configured around your existing pipeline instead of a generic template.</p>",
        'local_proof' => "<p>Currency, date formats and reporting are set up for the UK market from day one, and our delivery team's GMT/BST hours mean support requests get picked up during your working day.</p>",
        'compliance_note' => "<p>Lead and contact data fields are structured with UK data-handling practices in mind, and quote or invoice objects generated through Salesforce follow Making Tax Digital-compliant formatting.</p>",
        'cta_text' => 'Get a fixed-scope Salesforce quote',
        'meta_title' => 'Salesforce Setup & Customisation UK | Corediva Tech Solutions',
        'meta_description' => 'Salesforce setup, customisation and integration for UK businesses, unifying sales, support and marketing in one system.',
    ],
    'zoho' => [
        'h1' => "A full Zoho suite, wired together instead of installed piecemeal.",
        'intro' => "<p>Complete Zoho suite setup, CRM, Books, Desk and People, configured and connected so you get one working business stack rather than five disconnected apps that happen to share a login.</p>",
        'local_proof' => "<p>Zoho Books is configured for GBP invoicing and UK VAT rates from setup, and support runs on GMT/BST hours so a configuration question doesn't wait until the next day to get answered.</p>",
        'compliance_note' => "<p>Zoho Books is set up to produce digital VAT records in the format HMRC's Making Tax Digital rules expect, so quarterly filing pulls straight from the system.</p>",
        'cta_text' => 'Get a fixed-scope Zoho setup quote',
        'meta_title' => 'Zoho Suite Setup UK | Corediva Tech Solutions',
        'meta_description' => 'Zoho CRM, Books, Desk and People, set up and connected for UK businesses, with Making Tax Digital-ready invoicing built in.',
    ],
    'digital-marketing' => [
        'h1' => "SEO that's measured on rankings and enquiries, not vanity traffic.",
        'intro' => "<p>Search engine optimisation built around the keywords UK buyers actually search, technical SEO fixes, content strategy and ongoing reporting tied to enquiries generated, not just impressions.</p>",
        'local_proof' => "<p>Campaigns are built around UK search intent and .co.uk/UK-hosted signals where relevant, with reporting delivered on a schedule aligned to GMT/BST business hours.</p>",
        'compliance_note' => "<p>Analytics and cookie-consent implementation is built with UK GDPR expectations in mind, so tracking doesn't run ahead of visitor consent.</p>",
        'cta_text' => 'Get a fixed-scope SEO quote',
        'meta_title' => 'SEO & Digital Marketing UK | Corediva Tech Solutions',
        'meta_description' => 'SEO built around UK search intent, with technical fixes, content strategy and reporting tied to real enquiries, not vanity traffic.',
    ],
    'graphic-design' => [
        'h1' => "Brand and marketing design that ships on your deadline, not around it.",
        'intro' => "<p>Brand identity, marketing collateral and campaign design work, produced by the same team that builds your website and product, so visual language stays consistent across every touchpoint.</p>",
        'local_proof' => "<p>Design briefs are scoped and reviewed during UK business hours, with revisions turned around against agreed deadlines rather than an open-ended queue.</p>",
        'compliance_note' => "<p>Licensing for any third-party stock assets used is documented and handed over with the final files, so there's no ambiguity about what your business can reuse.</p>",
        'cta_text' => 'Get a fixed-scope design quote',
        'meta_title' => 'Graphic Design UK | Corediva Tech Solutions',
        'meta_description' => 'Brand identity, marketing collateral and campaign design for UK businesses, delivered by the same team building your website and product.',
    ],
    'video-editing' => [
        'h1' => "Video editing that turns raw footage into something you'll actually post.",
        'intro' => "<p>Promotional, product and social video editing, from raw footage to a finished cut ready for the platform it's built for, without a multi-week agency turnaround.</p>",
        'local_proof' => "<p>Deliverables are scoped against your publishing calendar and delivered on UK business hours, so a launch date doesn't slip because a video is still in review.</p>",
        'compliance_note' => "<p>Music and stock footage licensing used in a project is documented and cleared for your intended use before final delivery.</p>",
        'cta_text' => 'Get a fixed-scope video editing quote',
        'meta_title' => 'Video Editing UK | Corediva Tech Solutions',
        'meta_description' => 'Promotional, product and social video editing for UK businesses, delivered against your publishing calendar, not an open-ended queue.',
    ],
    'corporate-events' => [
        'h1' => "Corporate event support that handles the tech, not just the guest list.",
        'intro' => "<p>Technical and production support for corporate events, launches and conferences: registration systems, AV coordination and on-the-day support handled by the same team that builds your other digital systems.</p>",
        'local_proof' => "<p>Event support is scoped around UK venues and time zones, with a point of contact reachable throughout the event day, not just during a pre-agreed support window.</p>",
        'compliance_note' => "<p>Attendee registration data collected for an event is handled with clear retention and consent rules in line with UK GDPR expectations, and deleted on an agreed schedule after the event.</p>",
        'cta_text' => 'Get a fixed-scope event support quote',
        'meta_title' => 'Corporate Event Support UK | Corediva Tech Solutions',
        'meta_description' => 'Technical and production support for UK corporate events and launches: registration systems, AV coordination and on-the-day support.',
    ],
    'managed-it-services' => [
        'h1' => "Managed IT that answers the phone instead of a ticket queue.",
        'intro' => "<p>Ongoing IT support, monitoring and maintenance for the systems we build and the ones already running in your business, so a problem gets a person, not a auto-reply.</p>",
        'local_proof' => "<p>Support hours are aligned to GMT/BST, with response times measured from when a ticket lands, not when someone eventually opens it.</p>",
        'compliance_note' => "<p>Access to client systems is logged and limited to the engineers actually working the ticket, in line with UK data-handling expectations for third-party IT support.</p>",
        'cta_text' => 'Get a fixed-scope managed IT quote',
        'meta_title' => 'Managed IT Services UK | Corediva Tech Solutions',
        'meta_description' => 'Ongoing IT support, monitoring and maintenance for UK businesses, with GMT/BST-aligned response times and one accountable team.',
    ],
    'cybersecurity' => [
        'h1' => "Cybersecurity work scoped to your actual attack surface, not a checklist.",
        'intro' => "<p>Security assessments, hardening and ongoing monitoring built around the systems you actually run, whether that's a customer-facing web app, an internal ERP or a cloud environment.</p>",
        'local_proof' => "<p>Findings are reported in plain language with a prioritised remediation plan, not a 40-page scan output that leaves your team to work out what matters first.</p>",
        'compliance_note' => "<p>We don't claim a certification the company doesn't hold: assessments follow established good practice (OWASP-aligned testing, least-privilege access reviews) rather than being marketed as a formal compliance audit.</p>",
        'cta_text' => 'Get a fixed-scope security assessment quote',
        'meta_title' => 'Cybersecurity Services UK | Corediva Tech Solutions',
        'meta_description' => 'Security assessments, hardening and monitoring for UK businesses, scoped to your actual systems with a prioritised, plain-language remediation plan.',
    ],
    'mobile-app-development' => [
        'h1' => "Mobile apps built for the App Store review process, not just a demo build.",
        'intro' => "<p>Native iOS and Android development, plus cross-platform Flutter apps, built to actually pass app store review and ship, not stall at the last submission step.</p>",
        'local_proof' => "<p>Payment and subscription flows are built to work in GBP from the first build, and push notification/support scheduling is aligned to GMT/BST so a launch-day issue gets picked up during UK hours.</p>",
        'compliance_note' => "<p>Data collected through the app is scoped and documented for the app store's privacy disclosure requirements and handled in line with UK GDPR expectations.</p>",
        'cta_text' => 'Get a fixed-scope mobile app quote',
        'meta_title' => 'Mobile App Development UK | Corediva Tech Solutions',
        'meta_description' => 'Native iOS, Android and cross-platform Flutter app development for UK businesses, built to pass app store review and ship.',
    ],
    'cloud-solutions' => [
        'h1' => "Cloud infrastructure sized for your actual traffic, not a guess.",
        'intro' => "<p>Cloud architecture, migration and ongoing infrastructure management, sized and configured around the traffic and workloads your business actually runs, not a default template tier.</p>",
        'local_proof' => "<p>Where latency to UK users matters, workloads are deployed in UK/EU regions, and infrastructure alerts are monitored during GMT/BST hours by the same team that built the environment.</p>",
        'compliance_note' => "<p>Data residency is discussed and agreed explicitly during scoping rather than assumed, so you know exactly where UK customer data physically sits.</p>",
        'cta_text' => 'Get a fixed-scope cloud infrastructure quote',
        'meta_title' => 'Cloud Solutions UK | Corediva Tech Solutions',
        'meta_description' => 'Cloud architecture, migration and infrastructure management for UK businesses, sized to real traffic with UK/EU-region deployment where it matters.',
    ],
    'automation' => [
        'h1' => "Workflow automation that removes the manual step, not just relabels it.",
        'intro' => "<p>Business process and workflow automation connecting the tools you already run, so a lead, invoice or support ticket moves through your systems without someone re-typing it three times.</p>",
        'local_proof' => "<p>Automations are built and tested against your actual UK working hours and holiday calendar, so a workflow doesn't silently misfire on a bank holiday.</p>",
        'compliance_note' => "<p>Any automation that touches invoicing or financial records is built to produce output in a Making Tax Digital-compliant format automatically.</p>",
        'cta_text' => 'Get a fixed-scope automation quote',
        'meta_title' => 'Business Process Automation UK | Corediva Tech Solutions',
        'meta_description' => 'Workflow and business process automation for UK businesses, connecting the tools you already run with Making Tax Digital-ready output.',
    ],
];

$products = [
    'instant-appointments' => [
        'title' => null,
        'h1' => "Real-time appointment booking that runs itself, on UK time.",
        'intro' => "<p>Instant Appointments gives your business a self-service booking calendar: clients see real available slots, book in a few taps, and get an automated reminder ahead of time.</p>",
        'local_proof' => "<p>Calendars run on GMT/BST by default and reminders go out over WhatsApp, so a booking made late in the evening still gets confirmed correctly the next business day.</p>",
        'meta_title' => 'Appointment Booking Software UK | Corediva',
        'meta_description' => 'Real-time appointment booking with WhatsApp reminders for UK businesses.',
    ],
    'invoice' => [
        'title' => 'Invoice UK',
        'h1' => "Invoicing that's Making Tax Digital-ready out of the box.",
        'intro' => "<p>Invoice generates client-ready invoices and quotes in minutes, tracks payment status, and keeps digital records formatted for HMRC's Making Tax Digital reporting requirements.</p>",
        'local_proof' => "<p>Invoices default to GBP with VAT itemised by line, and every record is exportable in the digital format Making Tax Digital submissions expect, no manual reformatting before quarter-end.</p>",
        'meta_title' => 'Invoicing Software UK | Corediva',
        'meta_description' => 'Making Tax Digital-ready invoicing software for UK businesses, with GBP support and itemised VAT by default.',
    ],
    'scheduler' => [
        'title' => null,
        'h1' => "Team and resource scheduling that doesn't live in a group chat.",
        'intro' => "<p>Scheduler coordinates staff, equipment or room bookings from one calendar, so double-bookings and \"is this slot free?\" messages stop happening.</p>",
        'local_proof' => "<p>Set up around UK working hours and bank holidays out of the box, so a team's schedule doesn't need manual correction every public holiday.</p>",
        'meta_title' => 'Team & Resource Scheduling Software UK | Corediva',
        'meta_description' => 'Staff and resource scheduling software for UK businesses, built around real working hours and bank holidays.',
    ],
    'e-marketing' => [
        'title' => null,
        'h1' => "Email and WhatsApp campaigns sent from one dashboard, not three.",
        'intro' => "<p>E-Marketing runs email and WhatsApp campaigns from a single dashboard, with segmentation, scheduling and open/click reporting built in.</p>",
        'local_proof' => "<p>Sending schedules default to UK time zones so campaigns land in inboxes during business hours, not the middle of the night.</p>",
        'meta_title' => 'Email & WhatsApp Marketing Software UK | Corediva',
        'meta_description' => 'Email and WhatsApp campaign software for UK businesses, with segmentation, scheduling and reporting in one dashboard.',
    ],
    'crm-leads' => [
        'title' => null,
        'h1' => "A CRM built to catch leads, not just store contact cards.",
        'intro' => "<p>CRM Leads captures enquiries from your website, WhatsApp and ad campaigns into one pipeline, so nothing gets lost between channels.</p>",
        'local_proof' => "<p>Pipeline stages and follow-up reminders are set up around UK working hours, so a lead captured overnight is flagged first thing the next business day.</p>",
        'meta_title' => 'Lead Management CRM UK | Corediva',
        'meta_description' => 'Lead capture and pipeline CRM for UK businesses, unifying website, WhatsApp and ad-campaign enquiries in one place.',
    ],
    'connect-erp' => [
        'title' => null,
        'h1' => "ERP that connects to the tools you already run, not instead of them.",
        'intro' => "<p>Connect ERP links inventory, finance and operations data across the systems your business already uses, instead of forcing a single monolithic replacement.</p>",
        'local_proof' => "<p>Finance modules are configured for GBP and Making Tax Digital-ready reporting from setup, so your existing accounting workflow doesn't need to be rebuilt.</p>",
        'meta_title' => 'Connected ERP Software UK | Corediva',
        'meta_description' => 'ERP software for UK businesses that connects inventory, finance and operations across the tools you already run.',
    ],
    'hrm' => [
        'title' => null,
        'h1' => "HR software that handles UK payroll cycles without a spreadsheet backup.",
        'intro' => "<p>Corediva HRM manages employee records, leave, and payroll workflow in one system, replacing the spreadsheet-plus-email process most growing teams start with.</p>",
        'local_proof' => "<p>Leave calendars and payroll cycles are configured around UK statutory holidays and pay periods from setup.</p>",
        'meta_title' => 'HR Management Software UK | Corediva',
        'meta_description' => 'HR and payroll workflow software for UK businesses, configured around UK statutory holidays and pay periods.',
    ],
    'whatsapp-automation' => [
        'title' => null,
        'h1' => "WhatsApp automation that answers the first message instantly.",
        'intro' => "<p>Web Automate handles WhatsApp Business automation: auto-replies, lead qualification and routing to the right team member, all synced to your CRM.</p>",
        'local_proof' => "<p>Support hand-off is scheduled around UK working hours, so an automated reply during off-hours still sets the right expectation for when a person will follow up.</p>",
        'meta_title' => 'WhatsApp Business Automation UK | Corediva',
        'meta_description' => 'WhatsApp Business automation for UK businesses: auto-replies, lead qualification and CRM-synced routing.',
    ],
    'web-integrations' => [
        'title' => null,
        'h1' => "Web integrations that stop your team copy-pasting between tabs.",
        'intro' => "<p>Integrations on Web (IoW) connects your website's forms, checkout and booking flows directly into your CRM, accounting or scheduling tools.</p>",
        'local_proof' => "<p>Integration mapping is built and tested against your actual UK business workflow, not a generic template that needs reworking after go-live.</p>",
        'meta_title' => 'Website Integrations UK | Corediva',
        'meta_description' => 'Website form, checkout and booking integrations for UK businesses, connected directly into your CRM and accounting tools.',
    ],
    'attendance-payroll' => [
        'title' => null,
        'h1' => "Attendance tracking that feeds payroll automatically, not manually.",
        'intro' => "<p>Attendance and Payroll Manager tracks staff clock-ins and leave, then feeds that data straight into payroll processing, removing a manual reconciliation step.</p>",
        'local_proof' => "<p>Configured around UK working patterns and statutory leave entitlements, so payroll calculations start from an accurate base.</p>",
        'meta_title' => 'Attendance & Payroll Software UK | Corediva',
        'meta_description' => 'Attendance tracking and payroll software for UK businesses, configured around UK working patterns and leave entitlements.',
    ],
    'payauth' => [
        'title' => null,
        'h1' => "Payment collection that reconciles itself against your invoices.",
        'intro' => "<p>Payauth handles payment collection and reconciliation, matching incoming payments against invoices automatically instead of a manual bank-statement cross-check.</p>",
        'local_proof' => "<p>Built for GBP transactions with records formatted to stay compliant with Making Tax Digital reporting requirements.</p>",
        'meta_title' => 'Payment Collection & Reconciliation Software UK | Corediva',
        'meta_description' => 'Payment collection and invoice reconciliation software for UK businesses, with Making Tax Digital-ready records.',
    ],
    'school-erp' => [
        'title' => null,
        'h1' => "School management software that covers admissions to fees in one system.",
        'intro' => "<p>School Management ERP (ScubaSchool) handles admissions, attendance, timetabling and fee collection in one platform, built for schools running that process across spreadsheets and paper today.</p>",
        'local_proof' => "<p>Fee collection is configured for GBP, and term/holiday calendars follow the UK academic year out of the box.</p>",
        'meta_title' => 'School Management Software UK | Corediva',
        'meta_description' => 'School management software for UK institutions: admissions, attendance, timetabling and fee collection in one platform.',
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
// PDPA-specific one (ai-solutions, service_id=3) rewritten for UK.
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
            $question = 'Is your chatbot and lead data handled in line with UK data protection expectations?';
            $answer   = "Yes. Every chatbot and CRM integration we deliver in the UK is built with clear data retention rules and consent capture in line with UK GDPR principles, and any quote it generates is issued in Making Tax Digital-compliant format automatically.";
        }
        db_exec(
            'INSERT INTO faqs (page_type, reference_id, country_id, question, answer, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, 1)',
            [$f['page_type'], $f['reference_id'], $countryId, $question, $answer, $f['sort_order']]
        );
    }
    echo "service faqs: cloned " . count($sgServiceFaqs) . " from SG (1 rewritten for UK)\n";
} else {
    echo "service faqs: already present ($existingServiceFaqs), skipped clone\n";
}

// -----------------------------------------------------------------------
// 3 new UK-specific home FAQs
// -----------------------------------------------------------------------
$existingHomeFaqsUk = (int) db_value("SELECT COUNT(*) FROM faqs WHERE page_type='home' AND country_id = ?", [$countryId]);
if ($existingHomeFaqsUk === 0) {
    $homeFaqs = [
        [
            'Is your invoicing software Making Tax Digital-compliant for UK businesses?',
            "Yes. Our Invoice product and every ERP/CRM build we deliver in the UK keep digital records formatted for HMRC's Making Tax Digital reporting requirements.",
            60,
        ],
        [
            'Do you support WhatsApp Business API for UK numbers?',
            'Yes, WhatsApp is usually the primary channel we deploy for UK businesses, connected directly to your CRM so no enquiry goes unanswered after hours.',
            70,
        ],
        [
            'What time zone do you support for UK clients?',
            'Our delivery team works across IST and aligns meeting and support hours to GMT/BST for UK engagements.',
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
    echo "home faqs: added 3 for UK\n";
} else {
    echo "home faqs: already present ($existingHomeFaqsUk), skipped\n";
}

// -----------------------------------------------------------------------
// hero_feature_rows + hero_slides
// -----------------------------------------------------------------------
$existingFeature = db_one('SELECT id FROM hero_feature_rows WHERE country_id = ?', [$countryId]);
if (!$existingFeature) {
    db_exec(
        'INSERT INTO hero_feature_rows (country_id, title, subtitle, icon, sort_order, is_active)
         VALUES (?, "Making Tax Digital-Ready Invoicing", "Compliant records built into every system", "las la-file-invoice", 55, 1)',
        [$countryId]
    );
    $featureRowId = (int) db_value('SELECT id FROM hero_feature_rows WHERE country_id = ? ORDER BY id DESC LIMIT 1', [$countryId]);
    echo "hero_feature_rows: added 1 for UK (id=$featureRowId)\n";
} else {
    $featureRowId = (int) $existingFeature['id'];
    echo "hero_feature_rows: already present (id=$featureRowId), skipped\n";
}

$existingSlide = db_one('SELECT id FROM hero_slides WHERE country_id = ?', [$countryId]);
if (!$existingSlide) {
    db_exec(
        'INSERT INTO hero_slides (country_id, page_slug, badge, heading, heading_highlight, subheading, image, image_alt, cta1_text, cta1_url, cta2_text, cta2_url, feature_row_id, sort_order, is_active)
         VALUES (?, "home", "Trusted IT & AI Partner for UK Businesses", "MTD-Ready Systems, Built for How the UK Does Business.", "MTD-Ready Systems", "Our UK representative coordinates delivery locally, while our engineering team builds WhatsApp-first AI chatbots, ERP/CRM systems and Making Tax Digital-ready invoicing for UK businesses that want enterprise-grade systems without enterprise-grade overhead.", "", "", "Book a Free Consultation", "https://wa.me/918903855325", "See AI Chatbot Solutions", "#services", ?, 7, 1)',
        [$countryId, $featureRowId]
    );
    echo "hero_slides: added 1 for UK\n";
} else {
    echo "hero_slides: already present, skipped\n";
}

echo "\nDone.\n";
