CREATE DATABASE IF NOT EXISTS `campuslinkd`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `campuslinkd`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+01:00";

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `terms_acceptance`;
DROP TABLE IF EXISTS `review_reports`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `complaint_notes`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `saved_vendors`;
DROP TABLE IF EXISTS `plan_change_requests`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `vendors`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `plans`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `blacklist`;
DROP TABLE IF EXISTS `login_logs`;
DROP TABLE IF EXISTS `static_pages`;

CREATE TABLE `categories` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `slug`        VARCHAR(120)    NOT NULL,
    `icon`        VARCHAR(10)     NOT NULL DEFAULT '🏪',
    `description` TEXT            NULL,
    `sort_order`  TINYINT         NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_active` (`is_active`),
    KEY `idx_categories_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Service categories for vendor listings';

CREATE TABLE `plans` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `vendor_type` ENUM('student','community') NOT NULL,
    `plan_type`   ENUM('basic','premium','featured') NOT NULL,
    `label`       VARCHAR(100)    NOT NULL,
    `amount`      INT UNSIGNED    NOT NULL COMMENT 'Amount in kobo',
    `amount_naira`DECIMAL(10,2)   NOT NULL COMMENT 'Amount in naira for display',
    `duration_days` SMALLINT UNSIGNED NOT NULL DEFAULT 180,
    `features`    JSON            NULL COMMENT 'JSON array of plan features',
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_plans_type` (`vendor_type`, `plan_type`),
    KEY `idx_plans_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Subscription plan definitions';

CREATE TABLE `admin_users` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `full_name`   VARCHAR(150)    NOT NULL,
    `email`       VARCHAR(255)    NOT NULL,
    `password`    VARCHAR(255)    NOT NULL,
    `role`        ENUM('superadmin','admin','moderator') NOT NULL DEFAULT 'moderator',
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `last_login`  DATETIME        NULL,
    `last_ip`     VARCHAR(45)     NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admin_email` (`email`),
    KEY `idx_admin_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin panel user accounts';

CREATE TABLE `users` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `full_name`           VARCHAR(150)    NOT NULL,
    `school_email`        VARCHAR(255)    NOT NULL,
    `personal_email`      VARCHAR(255)    NULL,
    `phone`               VARCHAR(20)     NOT NULL,
    `matric_number`       VARCHAR(30)     NULL,
    `level`               VARCHAR(10)     NULL COMMENT '100,200,300,400,500,600,PG',
    `department`          VARCHAR(150)    NULL,
    `password`            VARCHAR(255)    NOT NULL,
    `email_verified`      TINYINT(1)      NOT NULL DEFAULT 0,
    `phone_verified`      TINYINT(1)      NOT NULL DEFAULT 0,
    `email_verify_token`  VARCHAR(128)    NULL,
    `token_expires_at`    DATETIME        NULL,
    `reset_token`         VARCHAR(128)    NULL,
    `reset_token_expires` DATETIME        NULL,
    `status`              ENUM('inactive','active','blacklisted','suspended') NOT NULL DEFAULT 'inactive',
    `blacklist_reason`    TEXT            NULL,
    `blacklisted_at`      DATETIME        NULL,
    `terms_accepted`      TINYINT(1)      NOT NULL DEFAULT 0,
    `terms_version`       VARCHAR(10)     NOT NULL DEFAULT '1.0',
    `last_login`          DATETIME        NULL,
    `last_ip`             VARCHAR(45)     NULL,
    `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_school_email` (`school_email`),
    UNIQUE KEY `uq_users_phone` (`phone`),
    UNIQUE KEY `uq_users_matric` (`matric_number`),
    KEY `idx_users_personal_email` (`personal_email`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_email_token` (`email_verify_token`),
    KEY `idx_users_reset_token` (`reset_token`),
    KEY `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registered student and community user accounts';

CREATE TABLE `vendors` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `vendor_type`         ENUM('student','community') NOT NULL,
    `full_name`           VARCHAR(150)    NOT NULL,
    `phone`               VARCHAR(20)     NOT NULL,
    `whatsapp_number`     VARCHAR(20)     NOT NULL,
    `business_name`       VARCHAR(150)    NOT NULL,
    `slug`                VARCHAR(180)    NOT NULL,
    `category_id`         INT UNSIGNED    NOT NULL,
    `description`         TEXT            NOT NULL,
    `price_range`         VARCHAR(100)    NULL,
    `logo`                VARCHAR(255)    NULL,
    `service_photo`       VARCHAR(255)    NULL,
    `plan_type`           ENUM('basic','premium','featured') NOT NULL DEFAULT 'basic',
    `password`            VARCHAR(255)    NOT NULL,
    `email_verified`      TINYINT(1)      NOT NULL DEFAULT 0,
    `phone_verified`      TINYINT(1)      NOT NULL DEFAULT 0,
    `terms_accepted`     TINYINT(1)      NOT NULL DEFAULT 0,
    `terms_version`       VARCHAR(10)     NOT NULL DEFAULT '1.0',
    `status`              ENUM('pending','approved','active','inactive','rejected','suspended','banned','grace_period') NOT NULL DEFAULT 'pending',
    `matric_number`       VARCHAR(30)     NULL,
    `school_email`        VARCHAR(255)    NULL,
    `personal_email`      VARCHAR(255)    NULL,
    `level`               VARCHAR(10)     NULL,
    `department`          VARCHAR(150)    NULL,
    `years_experience`    TINYINT UNSIGNED NULL DEFAULT 0,
    `operating_location`  VARCHAR(255)    NULL,
    `id_card_file`        VARCHAR(255)    NULL,
    `selfie_file`         VARCHAR(255)    NULL,
    `working_email`       VARCHAR(255)    NULL,
    `business_address`    VARCHAR(350)    NULL,
    `years_operation`     TINYINT UNSIGNED NULL DEFAULT 0,
    `cac_certificate`     VARCHAR(255)    NULL,
    `gov_id_file`         VARCHAR(255)    NULL,
    `approved_at`         DATETIME        NULL,
    `approved_by`         INT UNSIGNED    NULL,
    `rejection_reason`    TEXT            NULL,
    `suspension_reason`   TEXT            NULL,
    `suspended_at`        DATETIME        NULL,
    `suspended_by`        INT UNSIGNED    NULL,
    `ban_reason`          TEXT            NULL,
    `banned_at`           DATETIME        NULL,
    `banned_by`          INT UNSIGNED    NULL,
    `reset_token`         VARCHAR(128)    NULL,
    `reset_token_expires` DATETIME        NULL,
    `last_login`          DATETIME        NULL,
    `last_ip`             VARCHAR(45)     NULL,
    `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_vendors_slug` (`slug`),
    UNIQUE KEY `uq_vendors_matric` (`matric_number`),
    UNIQUE KEY `uq_vendors_school_email` (`school_email`),
    UNIQUE KEY `uq_vendors_working_email` (`working_email`),
    UNIQUE KEY `uq_vendors_phone` (`phone`),
    KEY `idx_vendors_category` (`category_id`),
    KEY `idx_vendors_status` (`status`),
    KEY `idx_vendors_type` (`vendor_type`),
    KEY `idx_vendors_plan` (`plan_type`),
    KEY `idx_vendors_created` (`created_at`),
    KEY `idx_vendors_reset_token` (`reset_token`),
    CONSTRAINT `fk_vendors_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_vendors_approved_by`
        FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Student and community vendor accounts and profiles';

CREATE TABLE `payments` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `vendor_id`      INT UNSIGNED    NOT NULL,
    `vendor_type`    ENUM('student','community') NOT NULL,
    `reference`      VARCHAR(100)    NOT NULL COMMENT 'Paystack transaction reference',
    `amount`         INT UNSIGNED    NOT NULL COMMENT 'Amount in kobo',
    `plan_type`      ENUM('basic','premium','featured') NOT NULL,
    `status`         ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    `channel`        VARCHAR(50)     NULL COMMENT 'card, bank, ussd etc',
    `currency`       VARCHAR(5)      NOT NULL DEFAULT 'NGN',
    `gateway_ref`    VARCHAR(100)    NULL COMMENT 'Paystack internal reference',
    `customer_email` VARCHAR(255)    NULL,
    `fail_reason`    TEXT            NULL,
    `paid_at`        DATETIME        NULL,
    `verified_at`    DATETIME        NULL,
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payments_reference` (`reference`),
    KEY `idx_payments_vendor` (`vendor_id`),
    KEY `idx_payments_status` (`status`),
    KEY `idx_payments_paid_at` (`paid_at`),
    KEY `idx_payments_created` (`created_at`),
    CONSTRAINT `fk_payments_vendor`
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='All payment transactions via Paystack';

CREATE TABLE `subscriptions` (
    `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `vendor_id`          INT UNSIGNED    NOT NULL,
    `plan_type`          ENUM('basic','premium','featured') NOT NULL,
    `vendor_type`        ENUM('student','community') NOT NULL,
    `payment_id`         INT UNSIGNED    NOT NULL,
    `amount`             INT UNSIGNED    NOT NULL COMMENT 'Amount in kobo',
    `status`             ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
    `start_date`         DATETIME        NOT NULL,
    `expiry_date`        DATETIME        NOT NULL,
    `reminder_14_sent`   TINYINT(1)      NOT NULL DEFAULT 0,
    `reminder_7_sent`    TINYINT(1)      NOT NULL DEFAULT 0,
    `reminder_2_sent`    TINYINT(1)      NOT NULL DEFAULT 0,
    `grace_notif_sent`   TINYINT(1)      NOT NULL DEFAULT 0,
    `expired_notif_sent` TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_subscriptions_vendor` (`vendor_id`),
    KEY `idx_subscriptions_status` (`status`),
    KEY `idx_subscriptions_expiry` (`expiry_date`),
    KEY `idx_subscriptions_payment` (`payment_id`),
    CONSTRAINT `fk_subscriptions_vendor`
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_subscriptions_payment`
        FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Vendor subscription periods and lifecycle';

CREATE TABLE `plan_change_requests` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `vendor_id`      INT UNSIGNED    NOT NULL,
    `type`           ENUM('upgrade','downgrade') NOT NULL,
    `current_plan`   ENUM('basic','premium','featured') NOT NULL,
    `requested_plan` ENUM('basic','premium','featured') NOT NULL,
    `status`         ENUM('pending','approved','rejected','scheduled','completed') NOT NULL DEFAULT 'pending',
    `admin_note`     TEXT            NULL,
    `handled_by`     INT UNSIGNED    NULL,
    `handled_at`     DATETIME        NULL,
    `takes_effect_at`DATETIME        NULL COMMENT 'For downgrades: when it takes effect',
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pcr_vendor` (`vendor_id`),
    KEY `idx_pcr_status` (`status`),
    KEY `idx_pcr_type` (`type`),
    CONSTRAINT `fk_pcr_vendor`
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pcr_admin`
        FOREIGN KEY (`handled_by`) REFERENCES `admin_users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Vendor plan upgrade and downgrade requests';

CREATE TABLE `reviews` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `vendor_id`       INT UNSIGNED    NOT NULL,
    `user_id`         INT UNSIGNED    NOT NULL,
    `rating`          TINYINT UNSIGNED NOT NULL COMMENT '1 to 5 stars',
    `review`          TEXT            NOT NULL,
    `status`          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `rejection_reason`VARCHAR(255)    NULL,
    `vendor_reply`    TEXT            NULL,
    `vendor_reply_at` DATETIME        NULL,
    `moderated_by`    INT UNSIGNED    NULL,
    `moderated_at`    DATETIME        NULL,
    `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_reviews_user_vendor` (`user_id`, `vendor_id`),
    KEY `idx_reviews_vendor` (`vendor_id`),
    KEY `idx_reviews_user` (`user_id`),
    KEY `idx_reviews_status` (`status`),
    KEY `idx_reviews_rating` (`rating`),
    CONSTRAINT `fk_reviews_vendor`
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reviews_moderator`
        FOREIGN KEY (`moderated_by`) REFERENCES `admin_users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `chk_reviews_rating`
        CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User ratings and reviews for vendors';

CREATE TABLE `review_reports` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `review_id`   INT UNSIGNED    NOT NULL,
    `reported_by` INT UNSIGNED    NOT NULL,
    `reason`      VARCHAR(255)    NOT NULL,
    `status`      ENUM('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_rr_review_user` (`review_id`, `reported_by`),
    KEY `idx_rr_review` (`review_id`),
    KEY `idx_rr_status` (`status`),
    CONSTRAINT `fk_rr_review`
        FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_user`
        FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Reports of abusive or fake reviews';

CREATE TABLE `complaints` (
    `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `ticket_id`            VARCHAR(20)     NOT NULL COMMENT 'Human-readable ticket ID e.g. CL-AB12CD34',
    `vendor_id`            INT UNSIGNED    NOT NULL,
    `user_id`              INT UNSIGNED    NOT NULL,
    `category`             VARCHAR(50)     NOT NULL,
    `description`          TEXT            NOT NULL,
    `evidence_file`        VARCHAR(255)    NULL,
    `status`               ENUM('submitted','under_review','verified','resolved','dismissed') NOT NULL DEFAULT 'submitted',
    `admin_note`           TEXT            NULL,
    `vendor_response`      TEXT            NULL,
    `vendor_responded_at`  DATETIME        NULL,
    `handled_by`           INT UNSIGNED    NULL,
    `handled_at`           DATETIME        NULL,
    `resolved_at`          DATETIME        NULL,
    `created_at`           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_complaints_ticket` (`ticket_id`),
    KEY `idx_complaints_vendor` (`vendor_id`),
    KEY `idx_complaints_user` (`user_id`),
    KEY `idx_complaints_status` (`status`),
    KEY `idx_complaints_created` (`created_at`),
    CONSTRAINT `fk_complaints_vendor`
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_complaints_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_complaints_admin`
        FOREIGN KEY (`handled_by`) REFERENCES `admin_users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User complaints against vendors with evidence';

CREATE TABLE `complaint_notes` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `complaint_id` INT UNSIGNED    NOT NULL,
    `admin_id`     INT UNSIGNED    NOT NULL,
    `note`         TEXT            NOT NULL,
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cn_complaint` (`complaint_id`),
    CONSTRAINT `fk_cn_complaint`
        FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cn_admin`
        FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Internal admin notes on complaint investigations';

CREATE TABLE `notifications` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `recipient_type` ENUM('user','vendor','admin') NOT NULL,
    `recipient_id`   INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '0 = broadcast to all admins',
    `title`          VARCHAR(255)    NOT NULL,
    `message`        TEXT            NOT NULL,
    `type`           VARCHAR(30)     NOT NULL DEFAULT 'info' COMMENT 'info,success,warning,error,payment,review,complaint,approval,reminder,system',
    `link`           VARCHAR(500)    NULL,
    `is_read`        TINYINT(1)      NOT NULL DEFAULT 0,
    `read_at`        DATETIME        NULL,
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_recipient` (`recipient_type`, `recipient_id`),
    KEY `idx_notif_read` (`is_read`),
    KEY `idx_notif_created` (`created_at`),
    KEY `idx_notif_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='In-app notifications for users, vendors, and admins';

CREATE TABLE `saved_vendors` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED    NOT NULL,
    `vendor_id`  INT UNSIGNED    NOT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_saved_user_vendor` (`user_id`, `vendor_id`),
    KEY `idx_saved_user` (`user_id`),
    KEY `idx_saved_vendor` (`vendor_id`),
    CONSTRAINT `fk_saved_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_saved_vendor`
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User bookmarked/saved vendor listings';

CREATE TABLE `blacklist` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED    NULL DEFAULT 0,
    `vendor_id`      INT UNSIGNED    NULL DEFAULT 0,
    `type`           ENUM('user','vendor','ip','email','phone') NOT NULL,
    `reason`         TEXT            NOT NULL,
    `email`          VARCHAR(255)    NULL,
    `phone`          VARCHAR(20)     NULL,
    `ip_address`     VARCHAR(45)     NULL,
    `blacklisted_by` INT UNSIGNED    NOT NULL,
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
    `expires_at`     DATETIME        NULL COMMENT 'NULL = permanent',
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bl_email` (`email`),
    KEY `idx_bl_phone` (`phone`),
    KEY `idx_bl_ip` (`ip_address`),
    KEY `idx_bl_type` (`type`),
    KEY `idx_bl_active` (`is_active`),
    CONSTRAINT `fk_bl_admin`
        FOREIGN KEY (`blacklisted_by`) REFERENCES `admin_users` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Blacklisted users, vendors, IPs, emails, and phones';

CREATE TABLE `login_logs` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event`       VARCHAR(30)     NOT NULL COMMENT 'LOGIN,LOGOUT,FAILED_LOGIN,PASSWORD_RESET etc',
    `identifier`  VARCHAR(255)    NOT NULL COMMENT 'email or username',
    `user_type`   ENUM('user','vendor','admin') NOT NULL DEFAULT 'user',
    `user_id`     INT UNSIGNED    NULL DEFAULT 0,
    `success`     TINYINT(1)      NOT NULL DEFAULT 0,
    `ip_address`  VARCHAR(45)     NOT NULL,
    `user_agent`  VARCHAR(255)    NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ll_identifier` (`identifier`),
    KEY `idx_ll_ip` (`ip_address`),
    KEY `idx_ll_event` (`event`),
    KEY `idx_ll_user_type` (`user_type`),
    KEY `idx_ll_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Authentication audit trail for all account types';

CREATE TABLE `terms_acceptance` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    NOT NULL DEFAULT 0,
    `vendor_id`     INT UNSIGNED    NOT NULL DEFAULT 0,
    `entity_type`   ENUM('user','vendor') NOT NULL,
    `terms_type`    ENUM('general','user','vendor','privacy') NOT NULL,
    `terms_version` VARCHAR(10)     NOT NULL,
    `ip_address`    VARCHAR(45)     NOT NULL,
    `user_agent`    VARCHAR(255)    NULL,
    `accepted_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ta_user` (`user_id`),
    KEY `idx_ta_vendor` (`vendor_id`),
    KEY `idx_ta_entity` (`entity_type`),
    KEY `idx_ta_type` (`terms_type`),
    KEY `idx_ta_version` (`terms_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Legal record of all terms and policy acceptances';

CREATE TABLE `static_pages` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `slug`        VARCHAR(100)    NOT NULL,
    `title`       VARCHAR(255)    NOT NULL,
    `content`     LONGTEXT        NOT NULL,
    `meta_desc`   VARCHAR(300)    NULL,
    `last_updated_by` INT UNSIGNED NULL,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pages_slug` (`slug`),
    KEY `idx_pages_active` (`is_active`),
    CONSTRAINT `fk_pages_admin`
        FOREIGN KEY (`last_updated_by`) REFERENCES `admin_users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin-editable static and legal pages';

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;