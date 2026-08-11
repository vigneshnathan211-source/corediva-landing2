-- =====================================================================
-- Corediva Tech Solutions - database schema
-- MySQL 8.0+ / utf8mb4. Import via phpMyAdmin into `corediva_landing`.
--
-- Conventions:
--   * All tables InnoDB, utf8mb4_0900_ai_ci.
--   * `service_content.region_id` uses 0 (not NULL) for "country-level"
--     so the UNIQUE key actually enforces one row per service/country/region.
--     MySQL treats NULLs as distinct, which would silently allow duplicates.
--   * Admin auth is passwordless email OTP -- there is deliberately no
--     password column on admin_users.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Geography
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`            VARCHAR(5)   NOT NULL COMMENT 'URL folder: sg, ae, us, uk, au, in, my',
  `slug_suffix`     VARCHAR(40)  NOT NULL COMMENT 'Filename suffix: singapore, uae, usa...',
  `name`            VARCHAR(100) NOT NULL,
  `hreflang`        VARCHAR(10)  NOT NULL COMMENT 'en-sg, en-gb (note: uk folder != en-gb)',
  `locale`          VARCHAR(15)  NOT NULL DEFAULT 'en_US',
  `currency`        VARCHAR(5)   NOT NULL,
  `currency_symbol` VARCHAR(8)   NOT NULL DEFAULT '$',
  `phone`           VARCHAR(40)  DEFAULT NULL,
  `whatsapp`        VARCHAR(40)  DEFAULT NULL,
  `email`           VARCHAR(150) DEFAULT NULL,
  `address`         VARCHAR(255) DEFAULT NULL,
  `lat`             DECIMAL(10,7) DEFAULT NULL,
  `lng`             DECIMAL(10,7) DEFAULT NULL,
  `timezone`        VARCHAR(64)  NOT NULL DEFAULT 'UTC',
  `is_primary`      TINYINT(1)   NOT NULL DEFAULT 0,
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`      INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_country_code` (`code`),
  UNIQUE KEY `uq_country_suffix` (`slug_suffix`),
  KEY `idx_country_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id`  INT UNSIGNED NOT NULL,
  `code`        VARCHAR(40)  NOT NULL COMMENT 'texas',
  `slug_suffix` VARCHAR(40)  NOT NULL COMMENT 'texas',
  `name`        VARCHAR(100) NOT NULL,
  `cities`      VARCHAR(255) DEFAULT NULL COMMENT 'Austin, Dallas, Houston',
  `notes`       TEXT,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_region` (`country_id`, `code`),
  CONSTRAINT `fk_region_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Global site chrome (header / footer / schema.org source data)
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `setting_group` VARCHAR(50)  NOT NULL DEFAULT 'general',
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `offices`;
CREATE TABLE `offices` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id` INT UNSIGNED DEFAULT NULL,
  `city`       VARCHAR(100) NOT NULL,
  `badge`      VARCHAR(100) DEFAULT NULL COMMENT 'Primary Headquarters / Company Representative',
  `address`    VARCHAR(255) DEFAULT NULL,
  `phone`      VARCHAR(40)  DEFAULT NULL,
  `email`      VARCHAR(150) DEFAULT NULL,
  `lat`        DECIMAL(10,7) DEFAULT NULL,
  `lng`        DECIMAL(10,7) DEFAULT NULL,
  `is_hq`      TINYINT(1)   NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_office_country` (`country_id`),
  CONSTRAINT `fk_office_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stat_key`   VARCHAR(60)  NOT NULL,
  `value`      VARCHAR(20)  NOT NULL COMMENT 'numeric part, e.g. 550',
  `suffix`     VARCHAR(10)  NOT NULL DEFAULT '+',
  `label`      VARCHAR(150) NOT NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stat_key` (`stat_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150) NOT NULL,
  `logo`        VARCHAR(255) DEFAULT NULL,
  `logo_alt`    VARCHAR(255) DEFAULT NULL,
  `url`         VARCHAR(255) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `social_links`;
CREATE TABLE `social_links` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform`   VARCHAR(50)  NOT NULL,
  `url`        VARCHAR(255) NOT NULL,
  `icon`       VARCHAR(50)  NOT NULL DEFAULT 'link',
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_social_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `nav_items`;
CREATE TABLE `nav_items` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id`    INT UNSIGNED DEFAULT NULL,
  `label`        VARCHAR(120) NOT NULL,
  `url`          VARCHAR(255) DEFAULT NULL COMMENT 'country-relative, e.g. services/ai-solutions',
  `column_group` VARCHAR(80)  DEFAULT NULL COMMENT 'mega-menu column heading',
  `is_mega`      TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order`   INT          NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_nav_parent` (`parent_id`, `sort_order`),
  CONSTRAINT `fk_nav_parent` FOREIGN KEY (`parent_id`) REFERENCES `nav_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Hero
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `hero_feature_rows`;
CREATE TABLE `hero_feature_rows` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(150) NOT NULL,
  `subtitle`   VARCHAR(255) DEFAULT NULL,
  `icon`       VARCHAR(50)  NOT NULL DEFAULT 'star',
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `hero_slides`;
CREATE TABLE `hero_slides` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_id`        INT UNSIGNED DEFAULT NULL COMMENT 'NULL = shown on every country',
  `page_slug`         VARCHAR(60)  NOT NULL DEFAULT 'home',
  `badge`             VARCHAR(255) DEFAULT NULL,
  `heading`           VARCHAR(255) NOT NULL,
  `heading_highlight` VARCHAR(255) DEFAULT NULL COMMENT 'emphasised fragment within heading',
  `subheading`        TEXT,
  `image`             VARCHAR(255) DEFAULT NULL,
  `image_alt`         VARCHAR(255) DEFAULT NULL,
  `cta1_text`         VARCHAR(100) DEFAULT NULL,
  `cta1_url`          VARCHAR(255) DEFAULT NULL,
  `cta2_text`         VARCHAR(100) DEFAULT NULL,
  `cta2_url`          VARCHAR(255) DEFAULT NULL,
  `feature_row_id`    INT UNSIGNED DEFAULT NULL,
  `sort_order`        INT          NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_hero_lookup` (`page_slug`, `country_id`, `is_active`, `sort_order`),
  CONSTRAINT `fk_hero_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hero_feature` FOREIGN KEY (`feature_row_id`) REFERENCES `hero_feature_rows` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Services
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`              VARCHAR(120) NOT NULL COMMENT 'base slug; page files append -{country slug_suffix}',
  `title`             VARCHAR(150) NOT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL COMMENT 'one-line, used on cards/grids',
  `icon`              VARCHAR(50)  NOT NULL DEFAULT 'layout',
  `category`          VARCHAR(80)  DEFAULT NULL,
  `core_description`  MEDIUMTEXT COMMENT 'country-independent long copy (client-supplied)',
  `core_deliverables` MEDIUMTEXT,
  `sort_order`        INT          NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_slug` (`slug`),
  KEY `idx_service_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `service_content`;
CREATE TABLE `service_content` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id`       INT UNSIGNED NOT NULL,
  `country_id`       INT UNSIGNED NOT NULL,
  `region_id`        INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = country-level row',
  `title`            VARCHAR(150) DEFAULT NULL COMMENT 'nav/card label override; NULL falls back to services.title',
  `h1`               VARCHAR(255) DEFAULT NULL,
  `intro`            MEDIUMTEXT,
  `local_proof`      MEDIUMTEXT,
  `compliance_note`  MEDIUMTEXT,
  `cta_text`         VARCHAR(255) DEFAULT NULL,
  `meta_title`       VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(320) DEFAULT NULL,
  `is_published`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_service_country_region` (`service_id`, `country_id`, `region_id`),
  KEY `idx_sc_country` (`country_id`, `is_published`),
  CONSTRAINT `fk_sc_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sc_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Products
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`              VARCHAR(120) NOT NULL,
  `title`             VARCHAR(150) NOT NULL COMMENT 'base title; product_content.title overrides per country',
  `short_description` VARCHAR(500) DEFAULT NULL,
  `icon`              VARCHAR(50)  NOT NULL DEFAULT 'box',
  `category`          VARCHAR(80)  DEFAULT NULL,
  `core_description`  MEDIUMTEXT,
  `is_featured`       TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order`        INT          NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_slug` (`slug`),
  KEY `idx_product_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `product_content`;
CREATE TABLE `product_content` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id`       INT UNSIGNED NOT NULL,
  `country_id`       INT UNSIGNED NOT NULL,
  `title`            VARCHAR(150) DEFAULT NULL COMMENT 'e.g. "Invoice Singapore" vs base "Invoice"',
  `h1`               VARCHAR(255) DEFAULT NULL,
  `intro`            MEDIUMTEXT,
  `local_proof`      MEDIUMTEXT,
  `meta_title`       VARCHAR(255) DEFAULT NULL,
  `meta_description` VARCHAR(320) DEFAULT NULL,
  `is_published`     TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_country` (`product_id`, `country_id`),
  KEY `idx_pc_country` (`country_id`, `is_published`),
  CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Content engine
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`               VARCHAR(180) NOT NULL,
  `country_id`         INT UNSIGNED DEFAULT NULL COMMENT 'NULL = global post',
  `category`           VARCHAR(100) DEFAULT NULL,
  `title`              VARCHAR(255) NOT NULL,
  `excerpt`            TEXT,
  `body`               LONGTEXT,
  `featured_image`     VARCHAR(255) DEFAULT NULL,
  `featured_image_alt` VARCHAR(255) DEFAULT NULL,
  `author`             VARCHAR(100) DEFAULT NULL,
  `published_at`       DATETIME     DEFAULT NULL,
  `meta_title`         VARCHAR(255) DEFAULT NULL,
  `meta_description`   VARCHAR(320) DEFAULT NULL,
  `is_published`       TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_post_slug` (`slug`),
  KEY `idx_post_listing` (`country_id`, `is_published`, `published_at`),
  CONSTRAINT `fk_post_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `case_studies`;
CREATE TABLE `case_studies` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`               VARCHAR(180) NOT NULL,
  `country_id`         INT UNSIGNED DEFAULT NULL,
  `client_name`        VARCHAR(150) DEFAULT NULL,
  `industry`           VARCHAR(100) DEFAULT NULL,
  `summary`            TEXT,
  `body`               LONGTEXT,
  `results`            TEXT,
  `featured_image`     VARCHAR(255) DEFAULT NULL,
  `featured_image_alt` VARCHAR(255) DEFAULT NULL,
  `meta_title`         VARCHAR(255) DEFAULT NULL,
  `meta_description`   VARCHAR(320) DEFAULT NULL,
  `is_published`       TINYINT(1)   NOT NULL DEFAULT 0,
  `sort_order`         INT          NOT NULL DEFAULT 0,
  `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_case_slug` (`slug`),
  KEY `idx_case_listing` (`country_id`, `is_published`, `sort_order`),
  CONSTRAINT `fk_case_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_type`    VARCHAR(50)  NOT NULL DEFAULT 'home' COMMENT 'home|country|service|product|contact',
  `reference_id` INT UNSIGNED DEFAULT NULL COMMENT 'service_id / product_id when page_type is service/product',
  `country_id`   INT UNSIGNED DEFAULT NULL COMMENT 'NULL = all countries',
  `question`     VARCHAR(255) NOT NULL,
  `answer`       TEXT         NOT NULL,
  `sort_order`   INT          NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_faq_lookup` (`page_type`, `reference_id`, `country_id`, `is_active`),
  CONSTRAINT `fk_faq_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Leads
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `leads`;
CREATE TABLE `leads` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(150) NOT NULL,
  `email`            VARCHAR(190) NOT NULL,
  `phone`            VARCHAR(40)  DEFAULT NULL,
  `country_code`     VARCHAR(10)  DEFAULT NULL,
  `country_id`       INT UNSIGNED DEFAULT NULL,
  `service_interest` VARCHAR(120) DEFAULT NULL,
  `message`          TEXT,
  `source_url`       VARCHAR(255) DEFAULT NULL,
  `referrer`         VARCHAR(255) DEFAULT NULL,
  `ip`               VARBINARY(16) DEFAULT NULL COMMENT 'inet_pton form',
  `user_agent`       VARCHAR(255) DEFAULT NULL,
  `status`           ENUM('new','contacted','qualified','closed','spam') NOT NULL DEFAULT 'new',
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lead_status` (`status`, `created_at`),
  KEY `idx_lead_country` (`country_id`),
  KEY `idx_lead_ratelimit` (`ip`, `created_at`),
  CONSTRAINT `fk_lead_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- Admin: RBAC + passwordless email OTP
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(50)  NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_system`   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'system roles cannot be deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`   VARCHAR(80)  NOT NULL COMMENT 'e.g. services.edit, leads.export',
  `name`   VARCHAR(120) NOT NULL,
  `module` VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permission_slug` (`slug`),
  KEY `idx_permission_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id`       INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- No password column: authentication is email OTP only.
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`         VARCHAR(190) NOT NULL,
  `name`          VARCHAR(150) NOT NULL,
  `role_id`       INT UNSIGNED NOT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login_at` DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`),
  KEY `idx_admin_role` (`role_id`),
  CONSTRAINT `fk_admin_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- OTPs are stored hashed. Never store the 6-digit code in plaintext.
DROP TABLE IF EXISTS `admin_otp_codes`;
CREATE TABLE `admin_otp_codes` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id`  INT UNSIGNED NOT NULL,
  `code_hash`      VARCHAR(255) NOT NULL,
  `expires_at`     DATETIME     NOT NULL,
  `consumed_at`    DATETIME     DEFAULT NULL,
  `attempts`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `request_ip`     VARBINARY(16) DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_otp_user` (`admin_user_id`, `consumed_at`, `expires_at`),
  KEY `idx_otp_ratelimit` (`request_ip`, `created_at`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `admin_sessions`;
CREATE TABLE `admin_sessions` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` INT UNSIGNED NOT NULL,
  `token_hash`    CHAR(64)     NOT NULL COMMENT 'sha256 of the session token',
  `ip`            VARBINARY(16) DEFAULT NULL,
  `user_agent`    VARCHAR(255) DEFAULT NULL,
  `expires_at`    DATETIME     NOT NULL,
  `revoked_at`    DATETIME     DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_token` (`token_hash`),
  KEY `idx_session_user` (`admin_user_id`, `expires_at`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `admin_audit_log`;
CREATE TABLE `admin_audit_log` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_user_id` INT UNSIGNED DEFAULT NULL,
  `action`        VARCHAR(80)  NOT NULL COMMENT 'login, create, update, delete, export',
  `entity`        VARCHAR(80)  DEFAULT NULL,
  `entity_id`     INT UNSIGNED DEFAULT NULL,
  `detail`        TEXT,
  `ip`            VARBINARY(16) DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`admin_user_id`, `created_at`),
  KEY `idx_audit_entity` (`entity`, `entity_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET FOREIGN_KEY_CHECKS = 1;
