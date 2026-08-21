-- =====================================================================
-- Corediva Tech Solutions - seed data
-- Run AFTER schema.sql.
--
-- Everything here is real business data migrated from the live
-- u490523298_corediva export. Long-form marketing copy
-- (services.core_description, service_content.*, product_content.intro)
-- is deliberately left NULL -- that is client-supplied.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `page_seo`;
TRUNCATE TABLE `role_permissions`;
TRUNCATE TABLE `admin_audit_log`;
TRUNCATE TABLE `admin_sessions`;
TRUNCATE TABLE `admin_otp_codes`;
TRUNCATE TABLE `admin_users`;
TRUNCATE TABLE `permissions`;
TRUNCATE TABLE `roles`;
TRUNCATE TABLE `faqs`;
TRUNCATE TABLE `product_page_sections`;
TRUNCATE TABLE `product_content`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `service_page_sections`;
TRUNCATE TABLE `service_content`;
TRUNCATE TABLE `services`;
TRUNCATE TABLE `hero_slides`;
TRUNCATE TABLE `hero_feature_rows`;
TRUNCATE TABLE `process_steps`;
TRUNCATE TABLE `nav_items`;
TRUNCATE TABLE `social_links`;
TRUNCATE TABLE `partners`;
TRUNCATE TABLE `stats`;
TRUNCATE TABLE `offices`;
TRUNCATE TABLE `site_settings`;
TRUNCATE TABLE `regions`;
TRUNCATE TABLE `countries`;

-- ---------------------------------------------------------------------
-- Countries. `code` is the URL folder, `slug_suffix` the filename suffix
-- (web-development-singapore.php). Note uk -> en-gb.
-- ---------------------------------------------------------------------
INSERT INTO `countries`
(`id`,`code`,`slug_suffix`,`name`,`hreflang`,`locale`,`currency`,`currency_symbol`,`dial_code`,`phone`,`whatsapp`,`email`,`address`,`timezone`,`is_primary`,`is_active`,`sort_order`) VALUES
(1,'sg','singapore','Singapore',     'en-sg','en_SG','SGD','S$','+65', '+91 89038 55325','918903855325','support@corediva365.com',NULL,'Asia/Singapore',1,1,10),
(2,'ae','uae',      'United Arab Emirates','en-ae','en_AE','AED','AED','+971','+91 89038 55325','918903855325','support@corediva365.com','United Arab Emirates','Asia/Dubai',0,1,20),
(3,'us','usa',      'United States', 'en-us','en_US','USD','$', '+1',  '+91 89038 55325','918903855325','support@corediva365.com',NULL,'America/Chicago',0,1,30),
(4,'uk','uk',       'United Kingdom','en-gb','en_GB','GBP','£', '+44', '+91 89038 55325','918903855325','support@corediva365.com','United Kingdom','Europe/London',0,1,40),
(5,'au','australia','Australia',     'en-au','en_AU','AUD','A$','+61', '+91 89038 55325','918903855325','support@corediva365.com',NULL,'Australia/Sydney',0,1,50),
(6,'in','india',    'India',         'en-in','en_IN','INR','₹', '+91', '+91 89038 55325','918903855325','support@corediva365.com','No:09, Sai Street, Thendral Nagar, Trichy - 620021','Asia/Kolkata',0,1,60),
(7,'my','malaysia', 'Malaysia',      'en-my','en_MY','MYR','RM','+60', '+91 89038 55325','918903855325','support@corediva365.com',NULL,'Asia/Kuala_Lumpur',0,1,70);

INSERT INTO `regions` (`id`,`country_id`,`code`,`slug_suffix`,`name`,`cities`,`notes`,`is_active`,`sort_order`) VALUES
(1,3,'texas','texas','Texas','Austin, Dallas, Houston','State-level geo page nested under /us/. Gets no hreflang of its own -- a state is not a locale.',1,10);

-- ---------------------------------------------------------------------
-- Site settings
-- ---------------------------------------------------------------------
INSERT INTO `site_settings` (`setting_key`,`setting_value`,`setting_group`) VALUES
('site_name','Corediva Tech Solutions','general'),
('site_alt_name','Corediva365','general'),
('site_tagline','Enterprise IT, AI & Cloud Solutions Globally','general'),
('founding_year','2016','general'),
('years_experience','10+','general'),
('projects_delivered','550+','general'),
('customers_served','240+','general'),
('countries_with_office','3+','general'),
('response_time_hours','4','general'),
('email','support@corediva365.com','contact'),
('phone_display','+91 89038 55325','contact'),
('phone_e164','+918903855325','contact'),
('whatsapp_number','918903855325','contact'),
('whatsapp_prefill_text','Hi Corediva, I''d like to know more about your services','contact'),
('canonical_base','https://www.corediva365.com','seo'),
('logo_url','imgs/corediva-logo.png','brand'),
('favicon_url','/favicon.ico','brand'),
('theme_color','#1351D8','brand'),
('about_image_url','assets/imgs/about-service-2.png','brand'),
('about_image_alt','Corediva Tech Solutions engineering team at work','brand'),
('linkedin_url','https://www.linkedin.com/company/corediva-tech-solutions','social'),
('twitter_url','https://twitter.com/corediva365','social'),
('facebook_url','https://www.facebook.com/corediva365','social'),
('instagram_url','https://instagram.com/corediva365','social'),
('credentials','MSME & DUNS Certified','trust');

-- ---------------------------------------------------------------------
-- Offices / stats / partners / social
-- ---------------------------------------------------------------------
INSERT INTO `offices` (`id`,`country_id`,`city`,`badge`,`address`,`is_hq`,`is_active`,`sort_order`) VALUES
(1,6,'Tiruchirapalli','Primary Headquarters','No:09, Sai Street, Thendral Nagar, Trichy - 620021',1,1,10),
(2,6,'Coimbatore','Company Representative','Coimbatore, Tamil Nadu, India',0,1,20),
(3,4,'United Kingdom','Company Representative','United Kingdom',0,1,30),
(4,2,'United Arab Emirates','Company Representative','United Arab Emirates',0,1,40);

INSERT INTO `stats` (`stat_key`,`value`,`suffix`,`label`,`sort_order`,`is_active`) VALUES
('years_experience','10','+','Years of enterprise experience',10,1),
('projects_delivered','550','+','Projects delivered globally',20,1),
('customers_served','240','+','International customers served',30,1),
('countries','3','+','Countries with local representation',40,1);

-- Partners. `logo` is a path under assets/; rows without one fall back to a
-- styled wordmark, so a missing asset degrades cleanly instead of leaving a
-- gap. NxtGen and Bank of Baroda are not in the Simple Icons set, so they
-- use the wordmark until Corediva supplies official artwork.
-- `description` is retained for the admin UI and title attributes only: it is
-- deliberately NOT printed under each logo on the page.
-- `category` splits two unrelated logo walls sharing one table: 'homepage'
-- is the small payments/cloud/banking marquee under the homepage hero;
-- 'alliance' is the Partners page's larger business-alliance grid (real
-- logos pulled from corediva365.com, the company's live site -- Bizsafe's
-- source logo is a broken image there too, so it ships wordmark-only here).
INSERT INTO `partners` (`name`,`logo`,`logo_alt`,`url`,`description`,`category`,`sort_order`,`is_active`) VALUES
('NxtGen',                 NULL,                            NULL,                'https://www.nxtgen.com',      'Cloud infrastructure partner','homepage',10,1),
('Razorpay International','imgs/partners/razorpay.svg',    'Razorpay logo',      'https://razorpay.com',        'Payments partner','homepage',20,1),
('PayPal',                'imgs/partners/paypal.svg',      'PayPal logo',        'https://www.paypal.com',      'Payments partner','homepage',30,1),
('Payoneer',              'imgs/partners/payoneer.svg',    'Payoneer logo',      'https://www.payoneer.com',    'Payments partner','homepage',40,1),
('Bank of Baroda',         NULL,                            NULL,                'https://www.bankofbaroda.in', 'Banking partner','homepage',50,1),
('Jawaad Trading Academy','imgs/partners/jawaad-trading.png','Jawaad Trading Academy logo',NULL,'Financial Education Alliance','alliance',10,1),
('Environmental India Foundation','imgs/partners/eif-usa.png','Environmental India Foundation logo',NULL,'United States Development Alliance','alliance',20,1),
('Vision Construction','imgs/partners/vision-construction.png','Vision Construction logo',NULL,'Trichy Operations Infrastructure','alliance',30,1),
('New Life Trust','imgs/partners/new-life-trust.png','New Life Trust logo',NULL,'Social Impact Ecosystem Partner','alliance',40,1),
('Directs Technologies','imgs/partners/directs-tech.png','Directs Technologies logo',NULL,'Systems Outsourcing Alliance','alliance',50,1),
('Bizsafe Company Singapore',NULL,NULL,NULL,'Singapore Compliance Alliance','alliance',60,1),
('Miniox Tech Park','imgs/partners/miniox.png','Miniox Tech Park logo',NULL,'Incubation & Infrastructure','alliance',70,1),
('Webops Groups','imgs/partners/webops.png','Webops Groups logo',NULL,'Network Routing Alliance','alliance',80,1);

INSERT INTO `social_links` (`platform`,`url`,`icon`,`sort_order`,`is_active`) VALUES
('LinkedIn','https://www.linkedin.com/company/corediva-tech-solutions','lab la-linkedin-in',10,1),
('X','https://twitter.com/corediva365','lab la-twitter',20,1),
('Facebook','https://www.facebook.com/corediva365','lab la-facebook-f',30,1),
('Instagram','https://instagram.com/corediva365','lab la-instagram',40,1);

-- ---------------------------------------------------------------------
-- Delivery process ("How we do").
--
-- DRAFT COPY. Corediva's actual delivery process is not documented in the
-- source export, so these six steps are a plausible enterprise-IT sequence
-- written to make the section reviewable, not a description of how the team
-- genuinely works. Replace before launch.
-- ---------------------------------------------------------------------
INSERT INTO `process_steps` (`title`,`subtitle`,`summary`,`icon`,`sort_order`,`is_active`) VALUES
('Discovery','Scope & requirements','We map the problem, the systems already in place, and what success has to look like.','imgs/hwd-icon-1.svg',10,1),
('Architecture','Solution design','Data model, integrations and infrastructure decided up front, so nothing is retrofitted later.','imgs/hwd-icon-2.svg',20,1),
('Build','Development sprints','Short sprints with working software at the end of each, reviewed against the original scope.','imgs/hwd-icon-3.svg',30,1),
('Integrate','APIs & migration','Existing CRM, accounting and payment systems connected, with historic data migrated and reconciled.','imgs/hwd-icon-4.svg',40,1),
('Deploy','Cloud & go-live','Release onto AWS or Azure with monitoring, backups and rollback in place before switchover.','imgs/hwd-icon-5.svg',50,1),
('Support','AMC & monitoring','Ongoing maintenance, security patching and support once the system is carrying real work.','imgs/hwd-icon-6.svg',60,1);

-- ---------------------------------------------------------------------
-- Navigation. Mirrors the live corediva365.com menu.
--
-- "Services" (id=3) uses mega_type='services', so its panel is built from
-- the services table grouped by `category` -- the five category values are
-- exactly the live site's five mega-menu columns, so the IA matches while
-- every entry maps to a real service page rather than a hand-kept list.
--
-- Items whose target page does not exist yet render as plain text rather
-- than links (see nav_target()), so the intended structure is visible
-- without shipping a single dead link. They become links automatically
-- the moment the page lands on disk.
-- ---------------------------------------------------------------------
INSERT INTO `nav_items` (`id`,`parent_id`,`label`,`url`,`column_group`,`is_mega`,`mega_type`,`sort_order`,`is_active`) VALUES
(1,NULL,'Home','#top',NULL,0,NULL,10,1),
(2,NULL,'Who We Are','about',NULL,0,NULL,20,1),
(3,NULL,'Services','services',NULL,1,'services',30,1),
(4,NULL,'Insights',NULL,NULL,0,NULL,40,1),
(5,NULL,'Products','products',NULL,1,'products',50,1),
(6,NULL,'Partners','partners',NULL,0,NULL,60,1),

-- Insights dropdown -- case studies and blog only for now. Neither table
-- has rows yet, so both render as plain text (see nav_target()) until
-- case-studies.php / blog.php land, then light up on their own.
(15,4,'Case Studies','case-studies',NULL,0,NULL,10,1),
(16,4,'Blog','blog',NULL,0,NULL,20,1);

-- ---------------------------------------------------------------------
-- Hero
-- ---------------------------------------------------------------------
-- country_id NULL = shown on every country's trust list, same convention
-- as hero_slides. Rows 1-3 are universal capabilities; row 4 (PDPA) is
-- Singapore-specific and must not appear as a trust claim on other
-- countries' pages.
INSERT INTO `hero_feature_rows` (`id`,`country_id`,`title`,`subtitle`,`icon`,`sort_order`,`is_active`) VALUES
(1,NULL,'WhatsApp & AI Bots','Automated lead qualification 24/7','las la-robot',10,1),
(2,NULL,'Custom ERP & CRM','Streamlining finance, inventory & payroll','las la-database',20,1),
(3,NULL,'Data-Driven Global SEO','High-conversion Google campaigns','las la-chart-line',30,1),
(4,1,'PDPA-Ready Data Handling','Your customer data stays compliant, always','las la-shield-alt',40,1);

-- country_id NULL = shown on every country. Slide 4 is Singapore-only.
INSERT INTO `hero_slides`
(`id`,`country_id`,`page_slug`,`badge`,`heading`,`heading_highlight`,`subheading`,`cta1_text`,`cta1_url`,`cta2_text`,`cta2_url`,`feature_row_id`,`sort_order`,`is_active`) VALUES
(1,NULL,'home','Architecting Scalable Enterprise Solutions Globally',
 'Next-Generation IT, AI & Cloud Architecture for Global Leaders.','IT, AI & Cloud Architecture',
 'Headquartered in India, engineered for the world. Wherever your business is based, find the exact service you need in seconds — from custom ERPs to AI automation and resilient cloud infrastructure — for companies across India, the United Kingdom, and the United Arab Emirates, and beyond.',
 'Schedule Global Discovery Call','https://wa.me/918903855325','Find Your Service','#services',3,10,1),

(2,NULL,'home','AI Chatbots & WhatsApp Automation',
 'Let AI Qualify Your Leads While You Focus on Closing Deals.','AI Qualify Your Leads',
 'We build WhatsApp and web AI bots that greet, qualify, and route every enquiry the moment it lands — synced directly with your CRM, so no business owner has to chase a lead manually again.',
 'See AI Chatbot Solutions','#services','Talk to Our Team','https://wa.me/918903855325',1,20,1),

(3,NULL,'home','Custom ERP, Cloud & Cybersecurity',
 'One Secure System of Record for Your Entire Business.','Secure System of Record',
 'Custom ERP and CRM builds, AWS & Azure cloud hosting, and cybersecurity hardening — engineered so finance, inventory, HR, and payroll finally run off the same trusted source of truth.',
 'Explore ERP & Cloud','#services','Cybersecurity Services','#services',2,30,1),

(4,1,'home','Trusted IT & AI Partner for Singapore SMEs',
 'Faster Replies, More Qualified Leads — Built for How Singapore Buys.','More Qualified Leads',
 'Most enquiries in Singapore happen on WhatsApp, and slow replies lose the deal. We build PDPA-compliant WhatsApp & web AI chatbots, ERP/CRM systems, and cloud infrastructure for Singapore SMEs who want faster response times without adding headcount.',
 'Book a Free Consultation','https://wa.me/918903855325','See AI Chatbot Solutions','#services',4,5,1);

-- ---------------------------------------------------------------------
-- Services (16). core_description / core_deliverables = client-supplied.
-- ---------------------------------------------------------------------
INSERT INTO `services` (`id`,`slug`,`title`,`short_description`,`icon`,`category`,`sort_order`,`is_active`) VALUES
(1,'web-development','Web Development','Corporate websites, high-conversion e-commerce stores, custom web portals, and performance-driven web applications.','las la-laptop-code','Web & App Dev',10,1),
(2,'erp-crm','ERP & CRM Solutions','End-to-end custom ERP development to manage finance, inventory, HR, payroll, and powerful CRM systems.','las la-layer-group','ERP & CRM',20,1),
(3,'ai-solutions','AI Solutions','Intelligent AI chatbots, WhatsApp business bots, AI voice assistants, and lead qualification systems.','las la-robot','AI & Automation',30,1),
(4,'microsoft-365','Microsoft 365 & Workspace','Professional business email setup, seamless migrations, DNS configuration, and enterprise email security hardening.','las la-envelope-open-text','IT & Cloud',40,1),
(5,'sap','SAP Service','End-to-end SAP implementation, migration, and support — modules configured to match how your business actually operates.','las la-cubes','ERP & CRM',45,1),
(6,'salesforce','Salesforce','Salesforce setup, customization, and CRM integration to unify sales, support, and marketing in one system of record.','las la-cloud','ERP & CRM',46,1),
(7,'zoho','Zoho Infrastructure Setup','Complete Zoho suite setup — CRM, Books, Desk, and People — configured and connected for a fully working business stack.','las la-project-diagram','ERP & CRM',47,1),
(8,'digital-marketing','Digital Marketing (SEO)','Data-driven SEO, Google Ads management, social media marketing, and automated global lead generation campaigns.','las la-chart-line','Marketing',50,1),
(9,'graphic-design','Graphic Design','Premium logo design, brand identity creation, corporate presentations, and futuristic UI/UX dashboard designs.','las la-palette','Marketing',60,1),
(10,'video-editing','Video Editing','Professional video production, corporate promos, engaging social media reels, and high-quality post-production.','las la-video','Marketing',70,1),
(11,'corporate-events','Corporate Events','End-to-end offline corporate program organization, technical seminars, product launches, and IT workshops.','las la-calendar-check','Marketing',80,1),
(12,'managed-it-services','Managed IT Services','Complete IT infrastructure management, remote technical support, server administration, and AMCs.','las la-server','IT & Cloud',90,1),
(13,'cybersecurity','Cybersecurity Services','Website security hardening, rapid malware removal, vulnerability assessments, and comprehensive audits.','las la-shield-alt','IT & Cloud',100,1),
(14,'mobile-app-development','Mobile App Development','Native iOS and Android development, cross-platform Flutter applications, and enterprise CRM mobile apps.','las la-mobile','Web & App Dev',110,1),
(15,'cloud-solutions','Cloud Solutions','AWS and Microsoft Azure hosting, seamless cloud migrations, cloud backup setups, and DevOps engineering.','las la-cloud-upload-alt','IT & Cloud',120,1),
(16,'automation','Automation Solutions','End-to-end business process automation, CRM/HR workflow automation, and custom API integration services.','las la-cogs','AI & Automation',130,1);

-- ---------------------------------------------------------------------
-- Products (12). `invoice` is deliberately generic at base level -- each
-- country renames it via product_content.title (Invoice Singapore, etc).
-- ---------------------------------------------------------------------
INSERT INTO `products` (`id`,`slug`,`title`,`short_description`,`icon`,`category`,`group_label`,`is_featured`,`sort_order`,`is_active`) VALUES
(1,'instant-appointments','Instant Appointments','Real-time appointment booking with automated reminders, so clients self-schedule without a single phone call.','las la-calendar-plus','Scheduling','Scheduling',1,10,1),
(2,'invoice','Invoice','Tax-compliant invoicing built for local businesses — quotes, recurring billing, and payment tracking in one place.','las la-file-invoice-dollar','Finance','Finance & Payments',1,20,1),
(3,'scheduler','Scheduler','Team and resource scheduling with conflict detection, shift planning, and calendar sync across your organization.','las la-clock','Scheduling','Scheduling',0,30,1),
(4,'e-marketing','E-Marketing','Email and SMS campaign automation with segmentation, drip sequences, and performance analytics built in.','las la-bullhorn','Marketing','CRM & Marketing',0,40,1),
(5,'crm-leads','CRM Leads','Lead capture, scoring, and pipeline tracking designed to plug straight into your existing sales workflow.','las la-funnel-dollar','CRM','CRM & Marketing',1,50,1),
(6,'connect-erp','Connect ERP','A modular ERP core covering inventory, finance, procurement, and HR — ready to deploy in weeks, not months.','las la-network-wired','ERP','ERP & HR',1,60,1),
(7,'hrm','Corediva HRM','Employee records, leave management, performance reviews, and onboarding workflows in a single HR system.','las la-users','HRM','ERP & HR',0,70,1),
(8,'whatsapp-automation','Web Automate (WhatsApp)','WhatsApp business automation that greets, qualifies, and routes every enquiry the moment it lands.','lab la-whatsapp','AI Automation','Automation & Integration',1,80,1),
(9,'web-integrations','Integrations on Web (IoW)','Connect your website to the third-party tools and APIs your business already runs on — payments, CRMs, logistics, and more.','las la-plug','Integration','Automation & Integration',0,90,1),
(10,'attendance-payroll','Attendance and Payroll Manager','Biometric-ready attendance tracking with automated payroll calculation, tax deductions, and payslip generation.','las la-fingerprint','HRM','ERP & HR',0,100,1),
(11,'payauth','Payauth','Secure payment authorization and gateway orchestration layer for businesses processing payments across multiple regions.','las la-credit-card','Fintech','Finance & Payments',0,110,1),
(12,'school-erp','School Management ERP — ScubaSchool','End-to-end school administration: admissions, fee management, timetables, attendance, and parent communication.','las la-school','Education ERP','ERP & HR',1,120,1);

-- Per-country naming for the Invoice product. Marketing copy left blank.
INSERT INTO `product_content` (`product_id`,`country_id`,`title`,`h1`,`is_published`) VALUES
(2,1,'Invoice Singapore','GST-Compliant Invoicing Software for Singapore Businesses',1),
(2,2,'Invoice UAE',      'VAT-Compliant Invoicing Software for UAE Businesses',1),
(2,3,'Invoice USA',      'Invoicing & Billing Software for US Businesses',1),
(2,4,'Invoice UK',       'MTD-Ready VAT Invoicing Software for UK Businesses',1),
(2,5,'Invoice Australia','BAS & GST Invoicing Software for Australian Businesses',1),
(2,6,'Invoice India',    'GST-Compliant Invoicing Software for Indian Businesses',1),
(2,7,'Invoice Malaysia', 'SST & e-Invoice Ready Billing Software for Malaysian Businesses',1);

-- ---------------------------------------------------------------------
-- FAQs. country_id NULL = shown everywhere; 1 = Singapore only.
-- ---------------------------------------------------------------------
INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('home',NULL,NULL,'Do you provide services outside of India?','Yes. We are headquartered in Tiruchirapalli, India, with company representatives in Coimbatore, the United Kingdom, and the United Arab Emirates, and we also serve enterprise clients across Singapore, the USA, Canada, and Australia.',10,1),
('home',NULL,NULL,'What technologies do you use for Custom ERP & Web Apps?','We utilize modern, scalable tech stacks including React, Node.js, Python, Flutter for mobile, and robust cloud databases on AWS and Microsoft Azure, tailored to specific project requirements.',20,1),
('home',NULL,NULL,'Can you integrate AI bots into our existing CRM?','Absolutely. We specialize in API integrations, allowing our custom AI Chatbots and WhatsApp Business automation to seamlessly sync leads and data directly into your existing CRM workflows.',30,1),
('home',NULL,NULL,'Do you offer Annual Maintenance Contracts (AMC)?','Yes, we offer comprehensive Managed IT Services, server administration, and AMCs to ensure your digital infrastructure runs securely with 99.9% uptime.',40,1),
('home',NULL,NULL,'How quickly can you respond to a new inquiry?','We respond to global inquiries submitted through our contact form within 4 hours during business days.',50,1),
('home',NULL,1,'Is your chatbot and data handling PDPA-compliant?','Yes. We build data handling practices aligned with Singapore''s Personal Data Protection Act into every chatbot and CRM integration we deliver, including clear data retention and consent capture.',60,1),
('home',NULL,1,'Do you support WhatsApp Business API for Singapore numbers?','Yes, WhatsApp is usually the primary channel we deploy for Singapore SMEs, connected directly to your CRM so no enquiry goes unanswered after hours.',70,1),
('home',NULL,1,'What time zone do you support for Singapore clients?','Our delivery team works across IST and can align meeting and support hours to SGT (UTC+8) for Singapore engagements.',80,1);

-- ---------------------------------------------------------------------
-- RBAC
-- ---------------------------------------------------------------------
INSERT INTO `roles` (`id`,`slug`,`name`,`description`,`is_system`) VALUES
(1,'super_admin','Super Admin','Full access including admin user management.',1),
(2,'editor','Content Editor','Edit services, products, blog and case studies. Read-only on leads.',1),
(3,'lead_manager','Lead Manager','View, update and export leads. No content access.',1),
(4,'viewer','Viewer','Read-only across all modules.',1);

INSERT INTO `permissions` (`id`,`slug`,`name`,`module`) VALUES
(1,'services.view','View services','services'),
(2,'services.edit','Create/edit services','services'),
(3,'products.view','View products','products'),
(4,'products.edit','Create/edit products','products'),
(5,'blog.view','View blog posts','blog'),
(6,'blog.edit','Create/edit blog posts','blog'),
(7,'case_studies.view','View case studies','case_studies'),
(8,'case_studies.edit','Create/edit case studies','case_studies'),
(9,'leads.view','View leads','leads'),
(10,'leads.edit','Update lead status','leads'),
(11,'leads.export','Export leads to CSV','leads'),
(12,'settings.view','View site settings','settings'),
(13,'settings.edit','Edit site settings','settings'),
(14,'admins.manage','Manage admin users and roles','admin'),
(15,'seo.view','View page SEO settings','seo'),
(16,'seo.edit','Edit page SEO settings','seo'),
(17,'ai_chat.view','View AI chat settings','ai_chat'),
(18,'ai_chat.edit','Edit AI chat settings','ai_chat');

-- super_admin -> everything
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 1, `id` FROM `permissions`;

-- editor -> content modules + read-only leads
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 2, `id` FROM `permissions`
WHERE `slug` IN ('services.view','services.edit','products.view','products.edit',
                 'blog.view','blog.edit','case_studies.view','case_studies.edit',
                 'leads.view','settings.view','seo.view','seo.edit','ai_chat.view','ai_chat.edit');

-- lead_manager -> leads only
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 3, `id` FROM `permissions`
WHERE `slug` IN ('leads.view','leads.edit','leads.export');

-- viewer -> every *.view permission
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `slug` LIKE '%.view';

-- First super admin. CHANGE THIS EMAIL before deploying -- whoever owns
-- this inbox can sign in once a password is set (this row seeds no
-- password_hash on purpose -- a real hash has no business sitting in
-- version control, even for a dev seed). Set one via the Admin Users
-- screen, or a one-off `UPDATE admin_users SET password_hash = ...`.
INSERT INTO `admin_users` (`email`,`name`,`role_id`,`is_active`) VALUES
('vigneshnathan211@gmail.com','Vignesh Nathan',1,1);

-- Service-detail content: service_content + service_page_sections + service FAQs (Singapore).

-- Generated by scratchpad/load-service-content.php from Docs/corediva-content.md source data.



INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(1,1,0,'Web development built to convert, not just to launch.','<p>Corporate websites, high-conversion e-commerce stores, custom web portals and performance-driven web applications, built by the same engineer who scopes the project and ships it. No agency handoffs, no template theme dressed up as custom work.</p>','Get a fixed-scope web development quote','Web Development Singapore | Corediva Tech Solutions','Corporate websites, e-commerce and custom web apps built and hosted for Singapore SMEs. Fast, Core Web Vitals-ready, delivered by one accountable team.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(1,1,0,'feature_grid','What we build','Three ways to get a working web presence, matched to what your business actually needs.',NULL,'[{\"icon\":\"las la-laptop-code\",\"title\":\"Corporate websites\",\"text\":\"Fast, mobile-first sites built for Core Web Vitals, not just a design mockup.\"},{\"icon\":\"las la-shopping-cart\",\"title\":\"E-commerce stores\",\"text\":\"High-conversion storefronts with payment, inventory and checkout wired up from day one.\"},{\"icon\":\"las la-cubes\",\"title\":\"Custom web portals\",\"text\":\"Client and staff portals built to your workflow, not bent around someone else\'s.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(1,1,0,'image_feature','One engineer, start to finish',NULL,'<p>The engineer who scopes your project is the one who builds it, and the one who answers when something needs changing after launch. Nothing gets re-explained to a new contractor halfway through.</p>',NULL,'/assets/imgs/service-details.jpg','Web Development at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(1,1,0,'process_steps','How a build runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map pages, integrations and content sources before quoting a fixed price.\"},{\"title\":\"Build & integration\",\"text\":\"Built in short sprints, connected to the CRM, payment or booking systems you already run.\"},{\"title\":\"Launch & support\",\"text\":\"Released with monitoring in place, then maintained as your traffic and content grow.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(1,1,0,'deliverables','What is included',NULL,NULL,'[\"Responsive, Core Web Vitals-optimised front end\",\"Content that you can edit without calling a developer\",\"SSL, backups and uptime monitoring configured at launch\",\"A fixed-scope quote before any work begins\",\"One accountable engineer for the life of the project\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(1,1,0,'cta_banner','Ready to talk about Web Development?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(1,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',1,1,'How long does a typical website project take?','Timelines depend on scope, but every engagement starts with a fixed-scope proposal so you know cost and delivery date before work begins, not after.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',1,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(2,1,0,'One system of record for finance, inventory, HR and sales.','<p>End-to-end custom ERP development to manage finance, inventory, HR and payroll, plus CRM systems that keep your sales pipeline visible instead of scattered across inboxes and spreadsheets.</p>','Get a fixed-scope ERP/CRM quote','ERP & CRM Solutions Singapore | Corediva Tech Solutions','Custom ERP and CRM builds for Singapore SMEs: finance, inventory, HR, payroll and sales pipeline on one connected system, not five disconnected spreadsheets.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(2,1,0,'feature_grid','What we build','Modular systems, so you\'re not paying for modules you don\'t need.',NULL,'[{\"icon\":\"las la-layer-group\",\"title\":\"Custom ERP\",\"text\":\"Finance, inventory, HR and payroll on one connected core, built to how your business actually runs.\"},{\"icon\":\"las la-users\",\"title\":\"CRM systems\",\"text\":\"Lead capture, pipeline tracking and reporting that plugs into the sales workflow you already use.\"},{\"icon\":\"las la-plug\",\"title\":\"System integration\",\"text\":\"Connected to the accounting, payment or e-commerce platforms already running in your business.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(2,1,0,'image_feature','Built around your process, not a template',NULL,'<p>Off-the-shelf ERP forces your team to adapt to the software. We start by mapping how finance, inventory and HR actually work in your business, then build the system around that, not the other way round.</p>',NULL,'/assets/imgs/about-service-6.jpg','ERP & CRM Solutions at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(2,1,0,'process_steps','How an ERP/CRM build runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map current systems, spreadsheets and manual processes before proposing a structure.\"},{\"title\":\"Build & integration\",\"text\":\"Modules built and connected to your existing accounting, HR or payment tools.\"},{\"title\":\"Launch & support\",\"text\":\"Staff onboarding, data migration and ongoing support once the system carries real work.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(2,1,0,'deliverables','What is included',NULL,NULL,'[\"Finance, inventory, HR and payroll modules built to your workflow\",\"CRM pipeline and lead tracking connected to your sales process\",\"Data migration from existing spreadsheets or legacy systems\",\"API integration with accounting, payment or e-commerce tools\",\"One accountable engineer for the life of the system\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(2,1,0,'cta_banner','Ready to talk about ERP & CRM Solutions?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(2,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',2,1,'Can this integrate with our existing accounting or sales tools?','Yes. We specialise in API integrations, so a custom ERP or CRM build syncs with the accounting, payment or e-commerce systems you already run rather than replacing them outright.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',2,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(3,1,0,'AI chatbots that qualify leads while you sleep.','<p>Intelligent AI chatbots, WhatsApp business bots, AI voice assistants and lead qualification systems that greet, qualify and route every enquiry the moment it lands, synced directly with your CRM.</p>','Get a fixed-scope AI automation quote','AI Chatbots & WhatsApp Automation Singapore | Corediva','PDPA-aligned AI chatbots and WhatsApp Business automation for Singapore SMEs. Qualify leads 24/7, sync straight into your CRM, no missed enquiries after hours.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(3,1,0,'feature_grid','What we build','WhatsApp is the dominant channel Singapore buyers use to enquire. We build for it first.',NULL,'[{\"icon\":\"las la-comments\",\"title\":\"WhatsApp Business bots\",\"text\":\"Greets, qualifies and routes enquiries on the channel Singapore customers already use.\"},{\"icon\":\"las la-robot\",\"title\":\"AI chatbots & voice assistants\",\"text\":\"Trained on your actual services and FAQs, not a generic scripted flow.\"},{\"icon\":\"las la-filter\",\"title\":\"Lead qualification\",\"text\":\"Every enquiry scored and routed to the right person before a human ever replies.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(3,1,0,'image_feature','Synced into your CRM, not a silo',NULL,'<p>A chatbot that captures leads nobody sees isn\'t automation, it\'s a black hole with better UX. Every conversation we build syncs straight into your CRM so a qualified lead reaches a real person, not a spreadsheet nobody checks.</p>',NULL,'/assets/imgs/service-details-2.jpg','AI Solutions at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(3,1,0,'process_steps','How an AI build runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map your enquiry flow, common questions and where leads currently drop off.\"},{\"title\":\"Build & integration\",\"text\":\"Bot trained on your services, connected to WhatsApp Business API and your CRM.\"},{\"title\":\"Launch & support\",\"text\":\"Live monitoring and conversation tuning once real enquiries start coming through.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(3,1,0,'deliverables','What is included',NULL,NULL,'[\"WhatsApp Business API or web chatbot, built on your real services and FAQs\",\"Automatic lead scoring and routing into your CRM\",\"PDPA-aligned data handling with clear retention and consent capture\",\"24\\/7 coverage for enquiries that land outside business hours\",\"One accountable engineer for the life of the system\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(3,1,0,'cta_banner','Ready to talk about AI Solutions?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(3,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',3,1,'Is your chatbot and data handling PDPA-compliant?','Yes. We build data handling aligned with Singapore\'s Personal Data Protection Act into every chatbot and CRM integration we deliver, including clear data retention and consent capture.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',3,1,'Can you integrate the bot into our existing CRM?','Yes, we specialise in API integrations, so leads captured by the chatbot sync directly into the CRM you already use.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(4,1,0,'Business email and Microsoft 365, set up and secured properly.','<p>Professional business email setup, seamless migrations, DNS configuration and enterprise email security hardening, so your team stops fighting spoofed emails and misconfigured DNS records.</p>','Get a fixed-scope Microsoft 365 quote','Microsoft 365 Setup & Email Security Singapore | Corediva','Business email setup, migration and DNS configuration for Singapore SMEs, plus enterprise-grade email security hardening. Done once, done properly.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(4,1,0,'feature_grid','What we set up','The parts most businesses get half-configured, done properly the first time.',NULL,'[{\"icon\":\"las la-envelope\",\"title\":\"Business email\",\"text\":\"Custom-domain mailboxes set up and migrated without losing a single message.\"},{\"icon\":\"las la-server\",\"title\":\"DNS configuration\",\"text\":\"SPF, DKIM and DMARC configured correctly, not just copied from a tutorial.\"},{\"icon\":\"las la-lock\",\"title\":\"Email security hardening\",\"text\":\"Anti-phishing and spoofing protection layered on top of the Microsoft 365 defaults.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(4,1,0,'image_feature','Migration without the downtime',NULL,'<p>Email migrations fail quietly: a missed DNS record, a mailbox left mid-sync, calendar invites that vanish. We plan the cutover in advance and verify every mailbox after migration, not after your team notices something is missing.</p>',NULL,'/assets/imgs/about-service-7.jpg','Microsoft 365 & Workspace at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(4,1,0,'process_steps','How a setup runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We audit current mailboxes, DNS records and any existing security gaps.\"},{\"title\":\"Build & integration\",\"text\":\"Domains, mailboxes and security policies configured, migration scheduled around your team.\"},{\"title\":\"Launch & support\",\"text\":\"Verified mailbox by mailbox, with ongoing support for changes as your team grows.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(4,1,0,'deliverables','What is included',NULL,NULL,'[\"Custom-domain mailboxes provisioned for your whole team\",\"SPF, DKIM and DMARC configured to stop spoofed email\",\"Migration from your current provider with verified mailbox counts\",\"Anti-phishing security policy layered on top of Microsoft 365 defaults\",\"One accountable engineer for the life of the setup\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(4,1,0,'cta_banner','Ready to talk about Microsoft 365 & Workspace?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(4,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',4,1,'Do you offer ongoing support after the migration?','Yes, we offer Managed IT Services and AMCs alongside Microsoft 365 setup so mailboxes, security policies and DNS records stay maintained after go-live.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',4,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(5,1,0,'SAP configured to match how your business actually operates.','<p>End-to-end SAP implementation, migration and support, with modules configured to match how your business actually operates rather than forcing your process to fit a default template.</p>','Get a fixed-scope SAP quote','SAP Implementation & Support Singapore | Corediva','End-to-end SAP implementation, migration and support for Singapore businesses. Modules configured around your real workflow, not a generic template.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(5,1,0,'feature_grid','What we deliver','SAP is powerful and easy to misconfigure. We do the second part properly.',NULL,'[{\"icon\":\"las la-database\",\"title\":\"Implementation\",\"text\":\"Modules scoped and configured against your actual finance and operations workflow.\"},{\"icon\":\"las la-exchange-alt\",\"title\":\"Migration\",\"text\":\"Data moved from legacy systems with validation, not just a bulk export and hope.\"},{\"icon\":\"las la-life-ring\",\"title\":\"Ongoing support\",\"text\":\"A single point of contact for configuration changes as your business grows.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(5,1,0,'image_feature','Configured around your process',NULL,'<p>A default SAP configuration is built for a generic business that doesn\'t exist. We map your finance, inventory and reporting workflow first, then configure modules against that, so the system fits without months of workarounds.</p>',NULL,'/assets/imgs/service-details-3.jpg','SAP Service at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(5,1,0,'process_steps','How an implementation runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map your current finance, inventory and reporting processes before configuration.\"},{\"title\":\"Build & integration\",\"text\":\"Modules implemented and tested against real transaction data, not sample data.\"},{\"title\":\"Launch & support\",\"text\":\"Staff training and go-live support, then ongoing configuration as needs change.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(5,1,0,'deliverables','What is included',NULL,NULL,'[\"Modules configured against your real finance and operations workflow\",\"Validated data migration from your current system\",\"Staff training ahead of go-live\",\"A single point of contact for post-launch changes\",\"One accountable engineer for the life of the engagement\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(5,1,0,'cta_banner','Ready to talk about SAP Service?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(5,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',5,1,'Can SAP integrate with our existing accounting or CRM tools?','Yes. We specialise in API integrations, so an SAP implementation connects to the accounting, payment or CRM systems you already run.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',5,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(6,1,0,'Salesforce set up to unify sales, support and marketing.','<p>Salesforce setup, customisation and CRM integration to unify sales, support and marketing in one system of record, instead of three teams working off three different tools.</p>','Get a fixed-scope Salesforce quote','Salesforce Setup & CRM Integration Singapore | Corediva','Salesforce setup, customisation and CRM integration for Singapore businesses, unifying sales, support and marketing in one system of record.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(6,1,0,'feature_grid','What we deliver','Salesforce out of the box is a blank canvas. We configure it around your sales process.',NULL,'[{\"icon\":\"las la-sitemap\",\"title\":\"Setup & customisation\",\"text\":\"Pipelines, fields and dashboards configured to match how your sales team actually sells.\"},{\"icon\":\"las la-random\",\"title\":\"CRM integration\",\"text\":\"Connected to your marketing, support and accounting tools so data stops living in silos.\"},{\"icon\":\"las la-chart-line\",\"title\":\"Reporting & dashboards\",\"text\":\"Pipeline visibility built for what your sales leadership actually needs to see.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(6,1,0,'image_feature','Built for how your team sells',NULL,'<p>A stock Salesforce setup tracks generic deal stages that rarely match how your team actually closes business. We configure pipelines, fields and automation around your real sales process, not a default template.</p>',NULL,'/assets/imgs/service-1.png','Salesforce at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(6,1,0,'process_steps','How a Salesforce build runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map your current sales, support and marketing workflow before configuring anything.\"},{\"title\":\"Build & integration\",\"text\":\"Pipelines and dashboards built, connected to your marketing and support tools.\"},{\"title\":\"Launch & support\",\"text\":\"Team onboarding and ongoing configuration as your sales process evolves.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(6,1,0,'deliverables','What is included',NULL,NULL,'[\"Pipelines and fields configured to your real sales process\",\"Integration with your marketing, support and accounting tools\",\"Dashboards built for what your sales leadership needs to see\",\"Team onboarding ahead of go-live\",\"One accountable engineer for the life of the engagement\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(6,1,0,'cta_banner','Ready to talk about Salesforce?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(6,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',6,1,'Can Salesforce integrate with our existing marketing or support tools?','Yes. We specialise in API integrations, so your Salesforce setup connects to the marketing, support and accounting systems you already run.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',6,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(7,1,0,'A fully connected Zoho stack, not four separate logins.','<p>Complete Zoho suite setup, CRM, Books, Desk and People, configured and connected for a fully working business stack instead of four disconnected apps your team logs into separately.</p>','Get a fixed-scope Zoho setup quote','Zoho Setup: CRM, Books, Desk & People | Corediva Singapore','Complete Zoho suite setup for Singapore SMEs: CRM, Books, Desk and People configured and connected into one working business stack.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(7,1,0,'feature_grid','What we set up','Zoho\'s apps are strong individually. The value is in wiring them together.',NULL,'[{\"icon\":\"las la-address-book\",\"title\":\"Zoho CRM\",\"text\":\"Lead and pipeline tracking configured to your actual sales process.\"},{\"icon\":\"las la-file-invoice-dollar\",\"title\":\"Zoho Books\",\"text\":\"Invoicing and accounting connected directly to your CRM and payment flow.\"},{\"icon\":\"las la-headset\",\"title\":\"Zoho Desk & People\",\"text\":\"Support tickets and HR records set up and cross-linked to the rest of the stack.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(7,1,0,'image_feature','Connected, not just installed',NULL,'<p>Most Zoho setups stop at \"each app works.\" We go further and wire CRM, Books, Desk and People together so a closed deal in CRM shows up as an invoice in Books without anyone re-typing it.</p>',NULL,'/assets/imgs/service-2.png','Zoho Infrastructure Setup at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(7,1,0,'process_steps','How a Zoho setup runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map which Zoho apps you need and how they should talk to each other.\"},{\"title\":\"Build & integration\",\"text\":\"Apps configured and cross-connected, data migrated from your current tools.\"},{\"title\":\"Launch & support\",\"text\":\"Team onboarding and ongoing support as your Zoho stack grows.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(7,1,0,'deliverables','What is included',NULL,NULL,'[\"CRM, Books, Desk and People configured to your workflow\",\"Apps connected so data flows between them automatically\",\"Data migration from your current tools\",\"Team onboarding ahead of go-live\",\"One accountable engineer for the life of the engagement\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(7,1,0,'cta_banner','Ready to talk about Zoho Infrastructure Setup?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(7,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',7,1,'Can you connect Zoho to our existing website or payment tools?','Yes. We specialise in API integrations, so your Zoho stack connects to the website, payment or accounting systems you already run.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',7,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(8,1,0,'SEO and Google Ads built for measurable pipeline, not vanity traffic.','<p>Data-driven SEO, Google Ads management, social media marketing and automated global lead generation campaigns, built around leads you can trace back to spend, not traffic charts that don\'t convert.</p>','Get a fixed-scope marketing quote','SEO Agency & Digital Marketing Singapore | Corediva','Data-driven SEO, Google Ads management and social media marketing for Singapore SMEs, built around lead generation you can trace back to spend.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(8,1,0,'feature_grid','What we run','Three channels, one shared reporting view.',NULL,'[{\"icon\":\"las la-search\",\"title\":\"SEO\",\"text\":\"Technical and content SEO built for Google\'s current ranking signals, not last year\'s.\"},{\"icon\":\"las la-bullhorn\",\"title\":\"Google Ads\",\"text\":\"Campaigns managed against cost-per-lead, not just clicks or impressions.\"},{\"icon\":\"las la-share-alt\",\"title\":\"Social media marketing\",\"text\":\"Organic and paid social run as a lead channel, not just a content calendar.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(8,1,0,'image_feature','Reported against pipeline, not just traffic',NULL,'<p>Rankings and impressions are inputs, not outcomes. Every campaign we run is reported against enquiries and pipeline, so you can see which channel is actually paying for itself.</p>',NULL,'/assets/imgs/about-service-2.png','Digital Marketing (SEO) at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(8,1,0,'process_steps','How a campaign runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We audit current rankings, ad accounts and where existing traffic is actually converting.\"},{\"title\":\"Build & integration\",\"text\":\"Campaigns built and connected to your CRM so leads are trackable end to end.\"},{\"title\":\"Launch & support\",\"text\":\"Ongoing optimisation against cost-per-lead, not just month-over-month traffic.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(8,1,0,'deliverables','What is included',NULL,NULL,'[\"Technical and content SEO audit and roadmap\",\"Google Ads campaigns managed against cost-per-lead\",\"Social media marketing run as a lead channel\",\"Reporting tied to enquiries and pipeline, not just traffic\",\"One accountable engineer for the life of the engagement\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(8,1,0,'cta_banner','Ready to talk about Digital Marketing (SEO)?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(8,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',8,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',8,1,'Can you connect campaign reporting to our CRM?','Yes. We specialise in API integrations, so campaign leads and reporting connect directly to the CRM you already use.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(9,1,0,'Brand identity and design work built to be used, not just admired.','<p>Premium logo design, brand identity creation, corporate presentations and futuristic UI/UX dashboard designs, delivered as ready-to-use files your team can actually put to work.</p>','Get a fixed-scope design quote','Graphic Design & Brand Identity Singapore | Corediva','Logo design, brand identity, corporate presentations and UI/UX dashboard design for Singapore SMEs, delivered as ready-to-use files, not a mood board.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(9,1,0,'feature_grid','What we design','Design work built for how it will actually be used.',NULL,'[{\"icon\":\"las la-palette\",\"title\":\"Brand identity\",\"text\":\"Logo, colour and typography systems built with usable files, not just a PDF mockup.\"},{\"icon\":\"las la-file-powerpoint\",\"title\":\"Corporate presentations\",\"text\":\"Pitch decks and reports designed to be edited by your team afterwards.\"},{\"icon\":\"las la-desktop\",\"title\":\"UI\\/UX dashboard design\",\"text\":\"Interface design handed off in a format your developers can build directly from.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(9,1,0,'image_feature','Delivered as usable files',NULL,'<p>A brand identity that only exists as a locked PDF isn\'t usable. We hand off source files, style guides and export-ready assets so your team can keep using the work long after the project ends.</p>',NULL,'/assets/imgs/about-service-3.png','Graphic Design at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(9,1,0,'process_steps','How a design project runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map your brand, audience and where existing materials are falling short.\"},{\"title\":\"Build & integration\",\"text\":\"Concepts developed and refined against real feedback, not a single take-it-or-leave-it draft.\"},{\"title\":\"Launch & support\",\"text\":\"Final files, style guide and export formats handed off, with revisions available after.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(9,1,0,'deliverables','What is included',NULL,NULL,'[\"Editable source files, not just flattened exports\",\"A style guide your team can apply to future materials\",\"Export-ready formats for web, print and presentations\",\"A fixed-scope quote before any work begins\",\"One accountable designer for the life of the project\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(9,1,0,'cta_banner','Ready to talk about Graphic Design?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(9,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',9,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',9,1,'Do we get the source files, not just exports?','Yes. Every design engagement hands off editable source files and a style guide, so your team can keep using and extending the work afterwards.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(10,1,0,'Video production and editing built for social feeds and boardrooms alike.','<p>Professional video production, corporate promos, engaging social media reels and high-quality post-production, delivered ready to publish rather than a rough cut you still have to finish yourself.</p>','Get a fixed-scope video quote','Video Editing & Production Singapore | Corediva','Professional video production, corporate promos and social media reels for Singapore SMEs, edited and delivered ready to publish.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(10,1,0,'feature_grid','What we produce','Built for the platform it\'s going on, not a single one-size-fits-all cut.',NULL,'[{\"icon\":\"las la-video\",\"title\":\"Corporate promos\",\"text\":\"Company and product videos built for a boardroom or a landing page, not a phone screen.\"},{\"icon\":\"las la-mobile-alt\",\"title\":\"Social media reels\",\"text\":\"Short-form edits built for the feed they\'re going on, captioned and cropped correctly.\"},{\"icon\":\"las la-cut\",\"title\":\"Post-production\",\"text\":\"Colour grading, sound and pacing handled to a broadcast-ready standard.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(10,1,0,'image_feature','Delivered ready to publish',NULL,'<p>A rough cut that still needs colour grading, captions and export formatting isn\'t a finished deliverable. Every project we hand off is publish-ready, in the formats your platforms actually need.</p>',NULL,'/assets/imgs/service-3.png','Video Editing at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(10,1,0,'process_steps','How a video project runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map the platforms, length and tone the footage needs to land on.\"},{\"title\":\"Build & integration\",\"text\":\"Filming or footage review, edit and revisions against a fixed scope.\"},{\"title\":\"Launch & support\",\"text\":\"Final export in the formats each platform needs, ready to publish.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(10,1,0,'deliverables','What is included',NULL,NULL,'[\"Edited, colour-graded footage ready to publish\",\"Exports formatted for the specific platforms you\'re posting to\",\"Captions and sound mixed to a broadcast-ready standard\",\"A fixed-scope quote before any work begins\",\"One accountable editor for the life of the project\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(10,1,0,'cta_banner','Ready to talk about Video Editing?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(10,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',10,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',10,1,'Do you handle filming, or just editing existing footage?','Both. Tell us what footage you already have and what you need, and we\'ll scope whichever combination of filming and post-production the project actually needs.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(11,1,0,'Corporate events run end to end, not just booked.','<p>End-to-end offline corporate program organisation, technical seminars, product launches and IT workshops, run by the same team from planning through to the day itself.</p>','Get a fixed-scope events quote','Corporate Events & IT Workshops Singapore | Corediva','End-to-end corporate program organisation for Singapore businesses: technical seminars, product launches and IT workshops, run by one accountable team.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(11,1,0,'feature_grid','What we organise','Programs run by people who understand the technical content, not just the logistics.',NULL,'[{\"icon\":\"las la-calendar-check\",\"title\":\"Corporate programs\",\"text\":\"Full event organisation, from venue and logistics through to run of show.\"},{\"icon\":\"las la-chalkboard-teacher\",\"title\":\"Technical seminars\",\"text\":\"Content and delivery handled by a team that understands the subject matter.\"},{\"icon\":\"las la-rocket\",\"title\":\"Product launches\",\"text\":\"Launch events built to land the product story, not just fill a room.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(11,1,0,'image_feature','Run by people who know the content',NULL,'<p>A logistics-only events company can book a venue, but not explain the product to an audience of engineers. We run technical seminars and launches with a team that understands the material, not just the seating chart.</p>',NULL,'/assets/imgs/service-4.png','Corporate Events at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(11,1,0,'process_steps','How an event runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map the audience, format and technical content the event needs to cover.\"},{\"title\":\"Build & integration\",\"text\":\"Logistics, content and run of show planned against a fixed scope.\"},{\"title\":\"Launch & support\",\"text\":\"On-the-day delivery, with a single accountable point of contact throughout.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(11,1,0,'deliverables','What is included',NULL,NULL,'[\"End-to-end logistics and venue coordination\",\"Technical content planning for seminars and workshops\",\"A single point of contact from planning through to the event\",\"A fixed-scope quote before any work begins\",\"On-the-day delivery support\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(11,1,0,'cta_banner','Ready to talk about Corporate Events?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(11,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',11,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',11,1,'Can you handle the technical content, not just logistics?','Yes. Our team runs the technical seminar or product launch content itself, not just the venue and run of show.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(12,1,0,'Managed IT support that answers before it becomes an outage.','<p>Complete IT infrastructure management, remote technical support, server administration and AMCs, so problems get caught by monitoring instead of by a frustrated employee.</p>','Get a fixed-scope managed IT quote','Managed IT Services & Support Singapore | Corediva','Complete IT infrastructure management and remote support for Singapore SMEs: server administration, monitoring and AMCs from one accountable team.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(12,1,0,'feature_grid','What we manage','Infrastructure looked after continuously, not only when something breaks.',NULL,'[{\"icon\":\"las la-server\",\"title\":\"Infrastructure management\",\"text\":\"Servers, networks and endpoints monitored and maintained proactively.\"},{\"icon\":\"las la-headset\",\"title\":\"Remote technical support\",\"text\":\"A real person to call when something breaks, not a ticket queue that goes quiet.\"},{\"icon\":\"las la-file-contract\",\"title\":\"AMCs\",\"text\":\"Annual maintenance contracts that keep infrastructure patched and supported year-round.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(12,1,0,'image_feature','Caught before it becomes an outage',NULL,'<p>Most IT problems announce themselves quietly, a server running low on disk, a certificate about to expire, before they become an outage. Continuous monitoring is how we catch those early instead of after your team is already offline.</p>',NULL,'/assets/imgs/about-service-5.jpg','Managed IT Services at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(12,1,0,'process_steps','How managed IT support runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We audit current infrastructure, backups and any existing support gaps.\"},{\"title\":\"Build & integration\",\"text\":\"Monitoring, patching schedules and support processes set up around your systems.\"},{\"title\":\"Launch & support\",\"text\":\"Ongoing remote support and maintenance under a clear AMC.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(12,1,0,'deliverables','What is included',NULL,NULL,'[\"Proactive server and network monitoring\",\"Remote technical support with a real point of contact\",\"Scheduled patching and maintenance under an AMC\",\"Server administration and backup verification\",\"One accountable team for the life of the contract\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(12,1,0,'cta_banner','Ready to talk about Managed IT Services?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(12,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',12,1,'Do you offer Annual Maintenance Contracts (AMC)?','Yes, we offer comprehensive Managed IT Services, server administration and AMCs to keep your digital infrastructure patched and supported year-round.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',12,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(13,1,0,'Security hardening and rapid response when something goes wrong.','<p>Website security hardening, rapid malware removal, vulnerability assessments and comprehensive audits, built for businesses that need protection now, not an audit report that arrives after the breach.</p>','Get a fixed-scope security quote','Cybersecurity Services for SMEs Singapore | Corediva','Website security hardening, malware removal and vulnerability assessments for Singapore SMEs. Rapid response, not just an annual audit report.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(13,1,0,'feature_grid','What we deliver','Response speed matters as much as the audit itself.',NULL,'[{\"icon\":\"las la-shield-alt\",\"title\":\"Security hardening\",\"text\":\"Websites and infrastructure hardened against the attacks actually targeting SMEs.\"},{\"icon\":\"las la-bug\",\"title\":\"Rapid malware removal\",\"text\":\"Infected sites cleaned and re-secured, not just patched over the symptom.\"},{\"icon\":\"las la-search\",\"title\":\"Vulnerability assessments\",\"text\":\"Audits that come with a prioritised fix list, not just a severity score.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(13,1,0,'image_feature','A prioritised fix list, not just a report',NULL,'<p>A vulnerability report that ranks fifty issues by CVSS score without telling you what to fix first isn\'t actionable. We hand back a prioritised list: what to fix now, what to schedule, and what\'s genuinely low risk.</p>',NULL,'/assets/imgs/about-service-2.png','Cybersecurity Services at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(13,1,0,'process_steps','How a security engagement runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We assess current exposure, from website configuration to server access controls.\"},{\"title\":\"Build & integration\",\"text\":\"Hardening applied, vulnerabilities patched in priority order.\"},{\"title\":\"Launch & support\",\"text\":\"Ongoing monitoring and rapid response available under an AMC.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(13,1,0,'deliverables','What is included',NULL,NULL,'[\"A vulnerability assessment with a prioritised fix list\",\"Website and server security hardening\",\"Rapid malware removal if you\'re already compromised\",\"Ongoing monitoring available under an AMC\",\"One accountable engineer for the life of the engagement\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(13,1,0,'cta_banner','Ready to talk about Cybersecurity Services?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(13,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',13,1,'Do you offer Annual Maintenance Contracts (AMC)?','Yes, we offer comprehensive Managed IT Services, server administration and AMCs to ensure your infrastructure runs securely.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',13,1,'How fast can you respond if our site is already compromised?','We treat active compromises as urgent and reply within 4 hours on business days to start containment and cleanup.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(14,1,0,'Native and cross-platform apps built to ship, not just demo.','<p>Native iOS and Android development, cross-platform Flutter applications and enterprise CRM mobile apps, built by the same team that scopes, ships and supports them after launch.</p>','Get a fixed-scope app development quote','Mobile App Development Singapore | Corediva Tech Solutions','Native iOS and Android development, cross-platform Flutter apps and enterprise CRM mobile apps for Singapore businesses, built and shipped by one team.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(14,1,0,'feature_grid','What we build','Native where it matters, cross-platform where it saves you time and budget.',NULL,'[{\"icon\":\"las la-apple-alt\",\"title\":\"Native iOS & Android\",\"text\":\"Built directly on each platform when performance or platform features demand it.\"},{\"icon\":\"las la-mobile\",\"title\":\"Cross-platform (Flutter)\",\"text\":\"One codebase for both platforms, when that trade-off genuinely fits the project.\"},{\"icon\":\"las la-id-badge\",\"title\":\"Enterprise CRM apps\",\"text\":\"Mobile apps that extend your CRM into the field, not a bolted-on afterthought.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(14,1,0,'image_feature','Shipped to the app stores, not just demoed',NULL,'<p>A working demo on a developer\'s phone isn\'t a shipped product. We take builds through app store review, signing and release, and stay on for the updates that follow once real users start finding edge cases.</p>',NULL,'/assets/imgs/service-5.png','Mobile App Development at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(14,1,0,'process_steps','How an app build runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map platform choice, core features and any backend or CRM integration needed.\"},{\"title\":\"Build & integration\",\"text\":\"Built in short sprints, connected to your CRM or backend systems.\"},{\"title\":\"Launch & support\",\"text\":\"App store submission handled, with support for the updates that follow.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(14,1,0,'deliverables','What is included',NULL,NULL,'[\"Native or cross-platform build, matched to what the project needs\",\"Integration with your CRM or existing backend systems\",\"App store submission and release management\",\"A fixed-scope quote before any work begins\",\"One accountable engineer for the life of the project\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(14,1,0,'cta_banner','Ready to talk about Mobile App Development?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(14,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',14,1,'How long does a typical app project take?','Timelines depend on scope and platform choice, but every engagement starts with a fixed-scope proposal so you know cost and delivery date before work begins.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',14,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(15,1,0,'AWS and Azure infrastructure, migrated and managed properly.','<p>AWS and Microsoft Azure hosting, seamless cloud migrations, cloud backup setups and DevOps engineering, so infrastructure scales with your business instead of becoming its own project to manage.</p>','Get a fixed-scope cloud quote','AWS & Azure Cloud Solutions Singapore | Corediva','AWS and Microsoft Azure hosting, cloud migrations, backup setup and DevOps engineering for Singapore SMEs, run by one accountable engineering team.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(15,1,0,'feature_grid','What we run','Infrastructure sized and monitored for what you actually run, not over-provisioned by default.',NULL,'[{\"icon\":\"las la-cloud\",\"title\":\"AWS & Azure hosting\",\"text\":\"Infrastructure sized correctly from the start, not scaled up after the first bill.\"},{\"icon\":\"las la-exchange-alt\",\"title\":\"Cloud migrations\",\"text\":\"Moved from legacy hosting with a tested rollback plan, not a one-way jump.\"},{\"icon\":\"las la-cogs\",\"title\":\"DevOps engineering\",\"text\":\"Deployment pipelines and backups that mean releases don\'t depend on one person.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(15,1,0,'image_feature','Migrated with a tested rollback plan',NULL,'<p>A cloud migration without a rollback plan is a bet, not a project. We test the migration path, verify backups, and keep the old environment available until the new one has proven itself under real traffic.</p>',NULL,'/assets/imgs/service-6.png','Cloud Solutions at work','right',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(15,1,0,'process_steps','How a cloud engagement runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We audit current hosting, traffic patterns and backup gaps before proposing a plan.\"},{\"title\":\"Build & integration\",\"text\":\"Infrastructure provisioned and tested, migration executed with a rollback path.\"},{\"title\":\"Launch & support\",\"text\":\"Monitoring and DevOps support once the new environment carries real traffic.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(15,1,0,'deliverables','What is included',NULL,NULL,'[\"AWS or Azure infrastructure sized to your actual load\",\"A tested migration plan with rollback available\",\"Automated backups verified, not just scheduled\",\"Deployment pipelines that don\'t depend on one person\",\"One accountable engineer for the life of the engagement\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(15,1,0,'cta_banner','Ready to talk about Cloud Solutions?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(15,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',15,1,'Do you offer ongoing support after the migration?','Yes, we offer Managed IT Services and AMCs alongside cloud migrations so infrastructure stays monitored and patched after go-live.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',15,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

INSERT INTO `service_content` (`service_id`,`country_id`,`region_id`,`h1`,`intro`,`cta_text`,`meta_title`,`meta_description`,`is_published`) VALUES
(16,1,0,'Workflow automation that removes the manual re-typing, not adds a new tool.','<p>End-to-end business process automation, CRM and HR workflow automation, and custom API integration services, built to remove the manual re-typing between systems, not add another dashboard to check.</p>','Get a fixed-scope automation quote','Business Process Automation Singapore | Corediva','End-to-end business process automation for Singapore SMEs: CRM/HR workflow automation and custom API integration that removes manual, repetitive work.',1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(16,1,0,'feature_grid','What we automate','The repetitive, manual steps between systems that already exist.',NULL,'[{\"icon\":\"las la-sync\",\"title\":\"Workflow automation\",\"text\":\"CRM and HR processes automated so data moves without someone re-typing it.\"},{\"icon\":\"las la-plug\",\"title\":\"API integration\",\"text\":\"Systems that already exist connected together, instead of replaced wholesale.\"},{\"icon\":\"las la-tasks\",\"title\":\"Process mapping\",\"text\":\"We identify what\'s actually worth automating before building anything.\"}]',NULL,NULL,'right',10,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(16,1,0,'image_feature','Removes work, doesn\'t add a dashboard',NULL,'<p>Automation that just adds another system to monitor isn\'t automation, it\'s more work. Every workflow we build is measured by what manual steps it removes from someone\'s day, not by how many new tools it introduces.</p>',NULL,'/assets/imgs/about-service-3.png','Automation Solutions at work','left',20,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(16,1,0,'process_steps','How an automation project runs',NULL,NULL,'[{\"title\":\"Discovery & scoping\",\"text\":\"We map the manual, repetitive steps actually costing your team time.\"},{\"title\":\"Build & integration\",\"text\":\"Workflows built and connected via API to the systems you already run.\"},{\"title\":\"Launch & support\",\"text\":\"Monitored once live, with support as processes change.\"}]',NULL,NULL,'right',30,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`subheading`,`body`,`items`,`media_url`,`media_alt`,`image_position`,`sort_order`,`is_published`) VALUES
(16,1,0,'deliverables','What is included',NULL,NULL,'[\"CRM or HR workflows automated end to end\",\"API integration between systems you already use\",\"A process map showing what was actually automated and why\",\"A fixed-scope quote before any work begins\",\"One accountable engineer for the life of the project\"]',NULL,NULL,'right',40,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`body`,`cta_label`,`cta_url`,`sort_order`,`is_published`) VALUES
(16,1,0,'cta_banner','Ready to talk about Automation Solutions?','<p>Tell us what you are running today and where it is costing you time. We reply within 4 hours on business days.</p>','Request a Quote','#rfq-form',50,1);

INSERT INTO `service_page_sections` (`service_id`,`country_id`,`region_id`,`section_type`,`heading`,`sort_order`,`is_published`) VALUES
(16,1,0,'faq','Frequently asked questions',60,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',16,1,'Can you integrate our existing CRM and HR systems?','Yes. We specialise in API integrations, so automation connects the CRM, HR or accounting systems you already run rather than replacing them.',10,1);

INSERT INTO `faqs` (`page_type`,`reference_id`,`country_id`,`question`,`answer`,`sort_order`,`is_active`) VALUES
('service',16,1,'How quickly can you respond to a new enquiry?','We reply within 4 hours on business days, usually over WhatsApp so the back-and-forth is fast.',20,1);

SET FOREIGN_KEY_CHECKS = 1;
