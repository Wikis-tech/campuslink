-- ============================================================
-- CampusLink - Migration 001 (InfinityFree Compatible)
-- Description: Initial database setup record
-- Date: 2025-01-01
-- NOTE: Run schema.sql first, then seed.sql, then this file
-- ============================================================

-- ============================================================
-- RECORD THIS MIGRATION
-- ============================================================
CREATE TABLE IF NOT EXISTS `migrations` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename`   VARCHAR(255) NOT NULL,
    `ran_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_migration_filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LOG THE MIGRATION
-- ============================================================
INSERT IGNORE INTO `migrations` (`filename`) VALUES ('001_initial.sql');

-- ============================================================
-- VERIFY TABLES EXIST (InfinityFree compatible version)
-- Shows row counts from your actual tables instead of
-- querying information_schema which is blocked on shared hosts
-- ============================================================
SELECT 'categories'     AS table_name, COUNT(*) AS row_count FROM `categories`
UNION ALL
SELECT 'plans',          COUNT(*) FROM `plans`
UNION ALL
SELECT 'admin_users',    COUNT(*) FROM `admin_users`
UNION ALL
SELECT 'users',          COUNT(*) FROM `users`
UNION ALL
SELECT 'vendors',        COUNT(*) FROM `vendors`
UNION ALL
SELECT 'payments',       COUNT(*) FROM `payments`
UNION ALL
SELECT 'subscriptions',  COUNT(*) FROM `subscriptions`
UNION ALL
SELECT 'plan_change_requests', COUNT(*) FROM `plan_change_requests`
UNION ALL
SELECT 'reviews',        COUNT(*) FROM `reviews`
UNION ALL
SELECT 'review_reports', COUNT(*) FROM `review_reports`
UNION ALL
SELECT 'complaints',     COUNT(*) FROM `complaints`
UNION ALL
SELECT 'complaint_notes',COUNT(*) FROM `complaint_notes`
UNION ALL
SELECT 'notifications',  COUNT(*) FROM `notifications`
UNION ALL
SELECT 'saved_vendors',  COUNT(*) FROM `saved_vendors`
UNION ALL
SELECT 'blacklist',      COUNT(*) FROM `blacklist`
UNION ALL
SELECT 'login_logs',     COUNT(*) FROM `login_logs`
UNION ALL
SELECT 'terms_acceptance',COUNT(*) FROM `terms_acceptance`
UNION ALL
SELECT 'static_pages',   COUNT(*) FROM `static_pages`
UNION ALL
SELECT 'migrations',     COUNT(*) FROM `migrations`;