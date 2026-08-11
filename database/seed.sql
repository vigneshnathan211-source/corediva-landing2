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

TRUNCATE TABLE `role_permissions`;
TRUNCATE TABLE `admin_audit_log`;
TRUNCATE TABLE `admin_sessions`;
TRUNCATE TABLE `admin_otp_codes`;
TRUNCATE TABLE `admin_users`;
TRUNCATE TABLE `permissions`;
TRUNCATE TABLE `roles`;
TRUNCATE TABLE `faqs`;
TRUNCATE TABLE `product_content`;
TRUNCATE TABLE `products`;
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
INSERT INTO `partners` (`name`,`logo`,`logo_alt`,`url`,`description`,`sort_order`,`is_active`) VALUES
('NxtGen',                 NULL,                            NULL,                'https://www.nxtgen.com',      'Cloud infrastructure partner',10,1),
('Razorpay International','imgs/partners/razorpay.svg',    'Razorpay logo',      'https://razorpay.com',        'Payments partner',20,1),
('PayPal',                'imgs/partners/paypal.svg',      'PayPal logo',        'https://www.paypal.com',      'Payments partner',30,1),
('Payoneer',              'imgs/partners/payoneer.svg',    'Payoneer logo',      'https://www.payoneer.com',    'Payments partner',40,1),
('Bank of Baroda',         NULL,                            NULL,                'https://www.bankofbaroda.in', 'Banking partner',50,1);

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
-- "What We Do" uses mega_type='services', so its panel is built from the
-- services table grouped by `category` -- the five category values are
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
(2,NULL,'Who We Are','#about',NULL,0,NULL,20,1),
(3,NULL,'What We Do',NULL,NULL,1,'services',30,1),
(4,NULL,'Company',NULL,NULL,0,NULL,40,1),
(5,NULL,'Products','#products',NULL,0,NULL,50,1),
(6,NULL,'Partners','#partners',NULL,0,NULL,60,1),
-- No case studies exist yet (0 rows); activate once content lands.
(7,NULL,'Case Studies','case-studies',NULL,0,NULL,70,0),

-- Company dropdown. Only FAQs resolves today (it is an on-page anchor);
-- the rest show as text until their pages are built.
(10,4,'How We Work','company/our-process','Our Process',0,NULL,10,1),
(11,4,'Leadership Team','company/leadership-team','Our Team',0,NULL,20,1),
(12,4,'Pricing','company/pricing','Investment',0,NULL,30,1),
(13,4,'FAQs Support','#faq','Knowledge',0,NULL,40,1),
-- Deliberately inactive: the only testimonial in the source DB is flagged
-- "do not publish placeholder testimonials live".
(14,4,'Client Testimonials','company/testimonials','Evaluation',0,NULL,50,0);

-- ---------------------------------------------------------------------
-- Hero
-- ---------------------------------------------------------------------
INSERT INTO `hero_feature_rows` (`id`,`title`,`subtitle`,`icon`,`sort_order`,`is_active`) VALUES
(1,'WhatsApp & AI Bots','Automated lead qualification 24/7','las la-robot',10,1),
(2,'Custom ERP & CRM','Streamlining finance, inventory & payroll','las la-database',20,1),
(3,'Data-Driven Global SEO','High-conversion Google campaigns','las la-chart-line',30,1),
(4,'PDPA-Ready Data Handling','Your customer data stays compliant, always','las la-shield-alt',40,1);

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
(14,'admins.manage','Manage admin users and roles','admin');

-- super_admin -> everything
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 1, `id` FROM `permissions`;

-- editor -> content modules + read-only leads
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 2, `id` FROM `permissions`
WHERE `slug` IN ('services.view','services.edit','products.view','products.edit',
                 'blog.view','blog.edit','case_studies.view','case_studies.edit',
                 'leads.view','settings.view');

-- lead_manager -> leads only
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 3, `id` FROM `permissions`
WHERE `slug` IN ('leads.view','leads.edit','leads.export');

-- viewer -> every *.view permission
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 4, `id` FROM `permissions` WHERE `slug` LIKE '%.view';

-- First super admin. CHANGE THIS EMAIL before deploying -- whoever owns this
-- inbox can log in, because auth is passwordless email OTP.
INSERT INTO `admin_users` (`email`,`name`,`role_id`,`is_active`) VALUES
('vigneshnathan211@gmail.com','Vignesh Nathan',1,1);

SET FOREIGN_KEY_CHECKS = 1;
