<?php
/**
 * One-off content loader for the USA country rollout. Mirrors the shape of
 * AE's service_content/product_content rows (country_id=2) but genuinely
 * rewritten: US angle is the state-by-state sales-tax patchwork (no single
 * national VAT) and security-conscious delivery practices, not a
 * certification claim -- no SOC2 audit exists, so this never says
 * "SOC2-certified", only "security-conscious practices."
 *
 * Run once: php tools/load-us-content.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$countryId = 3; // us

$services = [
    'web-development' => [
        'h1' => "Web development built for how US buyers actually convert.",
        'intro' => "<p>Corporate websites, high-conversion e-commerce stores, custom web portals and performance-driven web applications, built by the same engineer who scopes the project and ships it. No agency handoffs, no template theme dressed up as custom work.</p>",
        'local_proof' => "<p>Built for a US buyer who checks pricing, reviews and mobile speed before ever filling out a form: fast page loads, mobile-first layouts, and checkout or quote flows priced in USD from day one, not retrofitted after launch.</p>",
        'compliance_note' => "<p>E-commerce and portal builds itemize state and local sales tax per line instead of folding it into one unclear total, so a build sold into any state doesn't need a separate finance project to make tax reporting legible.</p>",
        'cta_text' => "Get a fixed-scope web development quote",
        'meta_title' => "Web Development USA | Corediva Tech Solutions",
        'meta_description' => "Corporate websites, e-commerce and custom web apps built and hosted for US businesses. Fast, mobile-first, delivered by one accountable team.",
    ],
    'erp-crm' => [
        'h1' => "One system of record for finance, inventory, HR and sales in the US.",
        'intro' => "<p>End-to-end custom ERP development to manage finance, inventory, HR and payroll, plus CRM systems that keep your sales pipeline visible instead of scattered across inboxes and spreadsheets.</p>",
        'local_proof' => "<p>Built for US businesses selling across multiple states, where sales tax rates and filing rules differ county to county: one connected system instead of five disconnected tools and a spreadsheet nobody trusts at quarter end.</p>",
        'compliance_note' => "<p>Finance modules are structured so state and local sales tax lines are itemized and exportable per jurisdiction from the design stage, so quarterly filings pull straight from the system instead of being reconstructed by hand.</p>",
        'cta_text' => "Get a fixed-scope ERP/CRM quote",
        'meta_title' => "ERP & CRM Solutions USA | Corediva Tech Solutions",
        'meta_description' => "Custom ERP and CRM builds for US businesses: finance, inventory, HR, payroll and sales pipeline on one connected, tax-aware system.",
    ],
    'ai-solutions' => [
        'h1' => "AI chatbots that qualify leads across every US time zone.",
        'intro' => "<p>Intelligent AI chatbots, WhatsApp business bots, AI voice assistants and lead qualification systems that greet, qualify and route every enquiry the moment it lands, synced directly with your CRM.</p>",
        'local_proof' => "<p>A US business fields enquiries from Eastern to Pacific time, often outside any single office's working hours, so every bot we build answers instantly and hands a qualified lead to your team the moment someone's awake to take it.</p>",
        'compliance_note' => "<p>Conversations and captured contact data are handled with clear retention rules and security-conscious development practices from the outset, and any quote a bot triggers itemizes sales tax by state automatically.</p>",
        'cta_text' => "Get a fixed-scope AI automation quote",
        'meta_title' => "AI Chatbots & WhatsApp Automation USA | Corediva",
        'meta_description' => "AI chatbots and WhatsApp Business automation for US businesses. Qualify leads 24/7 across every time zone, sync straight into your CRM.",
    ],
    'microsoft-365' => [
        'h1' => "Business email and Microsoft 365, set up and secured properly.",
        'intro' => "<p>Professional business email setup, seamless migrations, DNS configuration and enterprise email security hardening, so your domain sends and receives mail the way a serious business should.</p>",
        'local_proof' => "<p>US businesses running distributed teams across states need mail that never drops and DNS/security records configured correctly the first time, not patched after a phishing incident hits a remote employee's inbox.</p>",
        'compliance_note' => "<p>Mailbox and record configuration follows the same security-conscious, tax-aware document handling we apply everywhere else, so invoices and quotes sent by email stay consistent with what your ERP or accounting system records.</p>",
        'cta_text' => "Get a fixed-scope Microsoft 365 quote",
        'meta_title' => "Microsoft 365 & Workspace Setup USA | Corediva",
        'meta_description' => "Business email setup, migration, DNS configuration and security hardening for US businesses on Microsoft 365.",
    ],
    'sap' => [
        'h1' => "SAP configured to match how your US business actually operates.",
        'intro' => "<p>End-to-end SAP implementation, migration and support, modules configured to match how your business actually operates, not the other way round.</p>",
        'local_proof' => "<p>US enterprises running SAP typically need finance modules that can itemize sales tax across every state and locality they sell into, a configuration decision we make explicit before go-live, not after an audit finds the gap.</p>",
        'compliance_note' => "<p>Finance and tax module configuration is built around state-by-state sales tax reporting from the start, so statutory filings are a system output, not a manual reconciliation exercise.</p>",
        'cta_text' => "Get a fixed-scope SAP quote",
        'meta_title' => "SAP Implementation & Support USA | Corediva",
        'meta_description' => "SAP implementation, migration and support for US businesses, with finance modules configured for state sales tax reporting.",
    ],
    'salesforce' => [
        'h1' => "Salesforce set up to unify sales, support and marketing.",
        'intro' => "<p>Salesforce setup, customization and CRM integration to unify sales, support and marketing in one system of record.</p>",
        'local_proof' => "<p>US sales teams working leads across web, phone and email get one pipeline view instead of three, with quote and currency formatting set correctly for USD transactions from day one.</p>",
        'compliance_note' => "<p>Quote and invoice objects are configured to itemize sales tax by state and locality directly from Salesforce, so sales and finance are working from the same numbers, not reconciling two systems.</p>",
        'cta_text' => "Get a fixed-scope Salesforce quote",
        'meta_title' => "Salesforce Setup & CRM Integration USA | Corediva",
        'meta_description' => "Salesforce setup, customization and CRM integration for US sales teams, configured for USD and state sales-tax-ready quoting.",
    ],
    'zoho' => [
        'h1' => "A fully connected Zoho stack, not four separate logins.",
        'intro' => "<p>Complete Zoho suite setup, CRM, Books, Desk and People, configured and connected for a fully working business stack.</p>",
        'local_proof' => "<p>We configure Zoho Books as the finance core with US chart-of-accounts conventions and state sales tax rates loaded in, then connect CRM, Desk and People around it rather than treating each app as a standalone tool.</p>",
        'compliance_note' => "<p>Books is set up with state and local sales tax rates matched to where you actually sell, so returns are filed from real system data instead of a manual spreadsheet built every quarter.</p>",
        'cta_text' => "Get a fixed-scope Zoho setup quote",
        'meta_title' => "Zoho Infrastructure Setup USA | Corediva",
        'meta_description' => "Zoho CRM, Books, Desk and People set up and connected for US businesses, with Books configured for state sales tax.",
    ],
    'digital-marketing' => [
        'h1' => "SEO and Google Ads built for measurable pipeline, not vanity metrics.",
        'intro' => "<p>Data-driven SEO, Google Ads management, social media marketing and automated global lead generation campaigns, measured against pipeline, not vanity metrics.</p>",
        'local_proof' => "<p>Campaigns targeting US buyers are built around how they actually search and compare: local-intent keywords by state or metro, USD pricing signals, and landing pages fast enough to survive a mobile connection on the go.</p>",
        'compliance_note' => "<p>Landing pages and quote forms built for a campaign itemize sales tax by state at the first enquiry, so marketing spend converts into properly documented sales instead of a pricing surprise at checkout.</p>",
        'cta_text' => "Get a fixed-scope digital marketing quote",
        'meta_title' => "Digital Marketing (SEO) USA | Corediva",
        'meta_description' => "Data-driven SEO, Google Ads and lead generation campaigns built for US businesses and measured against pipeline.",
    ],
    'graphic-design' => [
        'h1' => "Brand identity and design work built to be used, not just admired.",
        'intro' => "<p>Premium logo design, brand identity creation, corporate presentations and futuristic UI/UX dashboard designs, built to be used, not just admired in a pitch deck.</p>",
        'local_proof' => "<p>Brand and presentation work for US clients is built with the fast-turnaround, iteration-heavy review cycle American marketing teams expect, not a single big reveal after weeks of silence.</p>",
        'compliance_note' => "<p>Invoice and quote templates designed as part of a brand identity package itemize state sales tax by line, so the branding is business-ready, not just decorative.</p>",
        'cta_text' => "Get a fixed-scope design quote",
        'meta_title' => "Graphic Design & Brand Identity USA | Corediva",
        'meta_description' => "Logo design, brand identity, corporate presentations and UI/UX design for US businesses, built for fast iteration.",
    ],
    'video-editing' => [
        'h1' => "Video production built for social feeds and boardrooms.",
        'intro' => "<p>Professional video production, corporate promos, engaging social media reels and high-quality post-production, built for social feeds and boardrooms alike.</p>",
        'local_proof' => "<p>US audiences split their attention across a wider spread of platforms than most markets, so social cuts are edited per-platform first (vertical for Reels/TikTok, wider for YouTube), with a boardroom edit produced from the same shoot.</p>",
        'compliance_note' => "<p>Production quotes and invoices for video work itemize sales tax by state, consistent with every other engagement, so finance never has to chase a one-off document.</p>",
        'cta_text' => "Get a fixed-scope video production quote",
        'meta_title' => "Video Editing & Production USA | Corediva",
        'meta_description' => "Corporate video production, social media reels and post-production for US businesses.",
    ],
    'corporate-events' => [
        'h1' => "Corporate events run end to end, not just booked.",
        'intro' => "<p>End-to-end offline corporate program organization, technical seminars, product launches and IT workshops, run end to end, not just booked and handed off.</p>",
        'local_proof' => "<p>Event logistics for US clients account for the multi-time-zone invite list from the planning stage, so a launch date and start time actually work for attendees dialing in from both coasts.</p>",
        'compliance_note' => "<p>Sponsorship, vendor and attendee invoicing for an event itemizes sales tax by state as standard, so event finance reconciles the same way every other engagement does.</p>",
        'cta_text' => "Get a fixed-scope event quote",
        'meta_title' => "Corporate Events USA | Corediva",
        'meta_description' => "Corporate program organization, technical seminars, product launches and IT workshops for US businesses.",
    ],
    'managed-it-services' => [
        'h1' => "Managed IT support that answers before it becomes an outage.",
        'intro' => "<p>Complete IT infrastructure management, remote technical support, server administration and AMCs, built to answer before a slow warning becomes an outage.</p>",
        'local_proof' => "<p>Support coverage is scheduled to overlap US business hours across time zones, with the same delivery team on call so an AMC issue is handled by someone who already knows the environment, not a rotating help desk.</p>",
        'compliance_note' => "<p>Asset and service records kept under an AMC are maintained with the same documentation discipline as our sales-tax-ready invoicing, so annual renewals and audits are never a scramble.</p>",
        'cta_text' => "Get a fixed-scope managed IT quote",
        'meta_title' => "Managed IT Services USA | Corediva",
        'meta_description' => "IT infrastructure management, remote support, server administration and AMCs for US businesses.",
    ],
    'cybersecurity' => [
        'h1' => "Security hardening and rapid response when something goes wrong.",
        'intro' => "<p>Website security hardening, rapid malware removal, vulnerability assessments and comprehensive audits, for when something goes wrong and you need it fixed, not just diagnosed.</p>",
        'local_proof' => "<p>US businesses handling customer payment and identity data get hardening work scoped around what attackers actually target: exposed admin panels, unpatched plugins and weak payment-gateway configurations.</p>",
        'compliance_note' => "<p>Security audits document findings and remediation with security-conscious practices throughout, in a format suitable for a US insurer or auditor to review, not just an internal engineering ticket log. We describe our own practices honestly rather than claim a certification we don't hold.</p>",
        'cta_text' => "Get a fixed-scope security audit",
        'meta_title' => "Cybersecurity Services USA | Corediva",
        'meta_description' => "Security hardening, malware removal, vulnerability assessments and audits for US businesses.",
    ],
    'mobile-app-development' => [
        'h1' => "Native and cross-platform apps built to ship, not just demo.",
        'intro' => "<p>Native iOS and Android development, cross-platform Flutter applications and enterprise CRM mobile apps, built to ship to the App Store and Play Store, not just demo in a meeting.</p>",
        'local_proof' => "<p>Apps built for US users are wired for USD pricing, Apple Pay/Google Pay and Stripe-style payment rails from the first sprint, with state sales tax calculated at checkout rather than added as a late-stage patch.</p>",
        'compliance_note' => "<p>In-app billing and receipt flows itemize state and local sales tax at the point of purchase, so a purchase inside the app produces a document your accountant can actually use.</p>",
        'cta_text' => "Get a fixed-scope mobile app quote",
        'meta_title' => "Mobile App Development USA | Corediva",
        'meta_description' => "Native iOS, Android and Flutter app development for US businesses, with USD pricing and sales-tax-ready checkout built in.",
    ],
    'cloud-solutions' => [
        'h1' => "AWS and Azure infrastructure, migrated and managed properly.",
        'intro' => "<p>AWS and Microsoft Azure hosting, seamless cloud migrations, cloud backup setups and DevOps engineering, migrated and managed properly rather than left to drift.</p>",
        'local_proof' => "<p>Latency-sensitive US workloads are routed through the nearest available AWS/Azure region, with backup and disaster recovery configured so a regional outage doesn't take your business down with it.</p>",
        'compliance_note' => "<p>Billing and usage records for cloud infrastructure are kept in the same sales-tax-aware documentation format as every other engagement, so cloud spend reconciles cleanly at quarter end, and infrastructure is built with security-conscious practices throughout.</p>",
        'cta_text' => "Get a fixed-scope cloud migration quote",
        'meta_title' => "Cloud Solutions (AWS & Azure) USA | Corediva",
        'meta_description' => "AWS and Azure hosting, cloud migration, backup and DevOps engineering for US businesses.",
    ],
    'automation' => [
        'h1' => "Workflow automation that removes the manual re-typing, not adds a dashboard.",
        'intro' => "<p>End-to-end business process automation, CRM/HR workflow automation and custom API integration services, built to remove manual re-typing, not add another dashboard nobody checks.</p>",
        'local_proof' => "<p>Automations for US businesses typically connect a website form, a CRM and an accounting system like QuickBooks-style tools or an ERP, so a lead captured online becomes a quote and eventually an invoice without anyone retyping it three times.</p>",
        'compliance_note' => "<p>Any invoice or quote generated automatically by a workflow itemizes state and local sales tax at the point of creation, so automation doesn't outrun compliance.</p>",
        'cta_text' => "Get a fixed-scope automation quote",
        'meta_title' => "Automation Solutions USA | Corediva",
        'meta_description' => "Business process, CRM and HR workflow automation with custom API integrations for US businesses.",
    ],
];

$products = [
    'instant-appointments' => [
        'h1' => "Real-time appointment booking that runs across every US time zone.",
        'intro' => "<p>Instant Appointments gives your business a self-service booking calendar: clients see real available slots, book in a few taps, and get an automated reminder ahead of time.</p>",
        'local_proof' => "<p>Calendars handle multiple US time zones out of the box and reminders go out by SMS and email, so a booking made in Los Angeles at midnight still gets confirmed correctly for a New York-based team the next business morning.</p>",
        'meta_title' => "Appointment Booking Software USA | Corediva",
        'meta_description' => "Real-time, multi-time-zone appointment booking with automated reminders for US businesses.",
    ],
    'invoice' => [
        'title' => 'Invoice USA',
        'h1' => "Sales-Tax-Ready Invoicing Software for US Businesses",
        'intro' => "<p>Invoice is sales-tax-ready invoicing built for US businesses: quotes, recurring billing and payment tracking in one place, with tax fields itemized by state and locality instead of bundled into one unclear line.</p>",
        'local_proof' => "<p>Every invoice generated shows the applicable state and local sales tax as its own line item, with USD as the default currency, built for a business selling across state lines without a manual tax lookup for every order.</p>",
        'meta_title' => "Invoice USA | Sales-Tax-Ready Invoicing Software",
        'meta_description' => "Sales-tax-ready invoicing software for US businesses: quotes, recurring billing and payment tracking, itemized by state.",
    ],
    'scheduler' => [
        'h1' => "Team and resource scheduling without the double-bookings.",
        'intro' => "<p>Scheduler handles team and resource scheduling with conflict detection, shift planning and calendar sync across your organization.</p>",
        'local_proof' => "<p>Shift templates handle a standard Monday-Friday US working week out of the box, with time-zone-aware scheduling for teams spread across the country, so rota planning doesn't need a manual workaround every week.</p>",
        'meta_title' => "Team & Resource Scheduling Software USA | Corediva",
        'meta_description' => "Team and resource scheduling with time-zone-aware shift planning for US organizations.",
    ],
    'e-marketing' => [
        'h1' => "Email and SMS campaigns that run on a schedule, not a scramble.",
        'intro' => "<p>E-Marketing automates email and SMS campaigns with segmentation, drip sequences and performance analytics built in.</p>",
        'local_proof' => "<p>Campaigns can be segmented and timed per US time zone, with quote and pricing content in USD so a promotional email doesn't need manual currency edits before sending.</p>",
        'meta_title' => "Email & SMS Marketing Automation USA | Corediva",
        'meta_description' => "Email and SMS campaign automation with segmentation and analytics for US businesses.",
    ],
    'crm-leads' => [
        'h1' => "Lead capture and pipeline tracking that plugs into how you sell.",
        'intro' => "<p>CRM Leads handles lead capture, scoring and pipeline tracking, designed to plug straight into your existing sales workflow.</p>",
        'local_proof' => "<p>Leads captured from a website form, email or phone call all land in one pipeline, scored the same way, so a US sales team working several channels at once isn't reconciling three lead lists.</p>",
        'meta_title' => "CRM Lead Capture & Pipeline Software USA | Corediva",
        'meta_description' => "Lead capture, scoring and pipeline tracking for US sales teams working web, email and phone.",
    ],
    'connect-erp' => [
        'h1' => "A modular ERP core you can deploy in weeks, not months.",
        'intro' => "<p>Connect ERP is a modular ERP core covering inventory, finance, procurement and HR, ready to deploy in weeks, not months.</p>",
        'local_proof' => "<p>The finance module ships with a state-by-state sales tax table pre-loaded, so a US business selling across multiple states isn't starting a tax setup project from a blank chart of accounts.</p>",
        'meta_title' => "Modular ERP Software USA | Corediva",
        'meta_description' => "A modular ERP core for inventory, finance, procurement and HR, pre-configured for US state sales tax.",
    ],
    'hrm' => [
        'h1' => "Employee records, leave and reviews in one HR system.",
        'intro' => "<p>Corediva HRM keeps employee records, leave management, performance reviews and onboarding workflows in a single HR system.</p>",
        'local_proof' => "<p>Leave calendars and federal holiday lists are configured for US defaults out of the box, with state-specific holiday overrides available for multi-state teams, so HR isn't manually re-mapping a template built for a different country's calendar.</p>",
        'meta_title' => "HR Management Software USA | Corediva",
        'meta_description' => "Employee records, leave management and onboarding in one HR system, configured for US working conventions.",
    ],
    'whatsapp-automation' => [
        'h1' => "Automated lead intake that answers before a lead goes cold.",
        'intro' => "<p>Web Automate greets, qualifies and routes every enquiry the moment it lands, synced with your CRM, across WhatsApp, web chat or SMS depending on what your customers already use.</p>",
        'local_proof' => "<p>US buyers reach out through a mix of channels rather than one dominant app, so this is configured per client: WhatsApp for teams that already run it, SMS and web chat where that's the norm, all routing into the same CRM pipeline.</p>",
        'meta_title' => "Automated Lead Intake & WhatsApp Automation USA | Corediva",
        'meta_description' => "Automated lead qualification across WhatsApp, SMS and web chat for US businesses, synced to your CRM 24/7.",
    ],
    'web-integrations' => [
        'h1' => "Connect your website to the tools you already run on.",
        'intro' => "<p>Integrations on Web (IoW) connects your website to the third-party tools and APIs your business already runs on, payments, CRMs, logistics and more.</p>",
        'local_proof' => "<p>Common integrations for US businesses include Stripe-style payment gateways, Salesforce or Zoho CRM, and domestic logistics providers, all wired to keep data in sync without manual exports.</p>",
        'meta_title' => "Website Integrations & API Connections USA | Corediva",
        'meta_description' => "Connect your US business website to payment, CRM and logistics tools via API integration.",
    ],
    'attendance-payroll' => [
        'h1' => "Biometric attendance to payslip, without manual calculation.",
        'intro' => "<p>Attendance and Payroll Manager pairs biometric-ready attendance tracking with automated payroll calculation, tax deductions and payslip generation.</p>",
        'local_proof' => "<p>Payroll runs support federal and state tax withholding by default and USD payslips as standard, built for how US employers actually process biweekly or semi-monthly pay, not a template designed around a different country's payroll rules.</p>",
        'meta_title' => "Attendance & Payroll Software USA | Corediva",
        'meta_description' => "Biometric attendance tracking and automated payroll with federal and state tax withholding for US employers.",
    ],
    'payauth' => [
        'h1' => "Payment authorization built for multi-region businesses.",
        'intro' => "<p>Payauth is a secure payment authorization and gateway orchestration layer for businesses processing payments across multiple regions.</p>",
        'local_proof' => "<p>Orchestrates US and international payment gateways side by side, so a business invoicing customers in USD and other currencies isn't running two disconnected payment stacks.</p>",
        'meta_title' => "Payment Authorization & Gateway Orchestration USA | Corediva",
        'meta_description' => "Secure payment authorization and gateway orchestration for US businesses processing multi-currency payments.",
    ],
    'school-erp' => [
        'h1' => "School administration, from admissions to parent communication.",
        'intro' => "<p>School Management ERP (ScubaSchool) covers end-to-end school administration: admissions, fee management, timetables, attendance and parent communication.</p>",
        'local_proof' => "<p>Fee schedules and receipts are issued in USD with itemized sales tax where applicable, and the academic calendar follows the standard US school year, not a template built around a different region.</p>",
        'meta_title' => "School Management ERP Software USA | Corediva",
        'meta_description' => "End-to-end school administration software for US schools: admissions, fees, timetables and attendance.",
    ],
];

$serviceRows = db_all('SELECT id, slug FROM services');
$slugToServiceId = array_column($serviceRows, 'id', 'slug');

$inserted = 0;
foreach ($services as $slug => $c) {
    $serviceId = $slugToServiceId[$slug] ?? null;
    if (!$serviceId) {
        fwrite(STDERR, "Unknown service slug: $slug\n");
        continue;
    }
    db_exec(
        'INSERT INTO service_content
            (service_id, country_id, region_id, title, h1, intro, local_proof, compliance_note, cta_text, meta_title, meta_description, is_published)
         VALUES (?, ?, 0, NULL, ?, ?, ?, ?, ?, ?, ?, 1)',
        [$serviceId, $countryId, $c['h1'], $c['intro'], $c['local_proof'], $c['compliance_note'], $c['cta_text'], $c['meta_title'], $c['meta_description']]
    );
    $inserted++;
}
echo "service_content rows inserted: $inserted\n";

$productRows = db_all('SELECT id, slug FROM products');
$slugToProductId = array_column($productRows, 'id', 'slug');

$productInserted = 0;
$productUpdated = 0;
foreach ($products as $slug => $c) {
    $productId = $slugToProductId[$slug] ?? null;
    if (!$productId) {
        fwrite(STDERR, "Unknown product slug: $slug\n");
        continue;
    }
    $existing = db_one('SELECT id FROM product_content WHERE product_id = ? AND country_id = ?', [$productId, $countryId]);
    if ($existing) {
        db_exec(
            'UPDATE product_content SET title = ?, h1 = ?, intro = ?, local_proof = ?, meta_title = ?, meta_description = ?, is_published = 1 WHERE id = ?',
            [$c['title'] ?? null, $c['h1'], $c['intro'], $c['local_proof'], $c['meta_title'], $c['meta_description'], $existing['id']]
        );
        $productUpdated++;
    } else {
        db_exec(
            'INSERT INTO product_content (product_id, country_id, title, h1, intro, local_proof, meta_title, meta_description, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)',
            [$productId, $countryId, $c['title'] ?? null, $c['h1'], $c['intro'], $c['local_proof'], $c['meta_title'], $c['meta_description']]
        );
        $productInserted++;
    }
}
echo "product_content rows inserted: $productInserted, updated: $productUpdated\n";
