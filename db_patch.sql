-- ══════════════════════════════════════════════════════════════════════
-- INCREDIBLE HEIGHTS — DATABASE PATCH
-- Run this on your database via phpMyAdmin → SQL tab
-- Safe to run multiple times (uses IF NOT EXISTS / ADD COLUMN IF NOT EXISTS)
-- ══════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '+05:30';

-- ──────────────────────────────────────────────────────────────────────
-- 1. Fix hr_admin_permissions table — add all perm_* columns
--    Old columns (can_manage_*) are kept for backward compat
-- ──────────────────────────────────────────────────────────────────────

ALTER TABLE `hr_admin_permissions`
  ADD COLUMN IF NOT EXISTS `perm_products`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_categories`    TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_services`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_plots`         TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_packages`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_orders`        TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_bookings`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_transactions`  TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_customers`     TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_enquiries`     TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_coupons`       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_reviews`       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_blogs`         TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_portfolio`     TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_gallery`       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_banners`       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_testimonials`  TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_faqs`          TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_reports`       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_notifications` TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_employees`     TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_add_employee`  TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_emp_roles`     TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_id_cards`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_attendance`    TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_salary`        TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_payslip`       TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_advances`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_holidays`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_off_days`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_work_settings` TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_accounts`      TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_admin_users`   TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `perm_settings`      TINYINT(1) DEFAULT 0;

-- ──────────────────────────────────────────────────────────────────────
-- 2. Fix hr_employee_permissions — add page_permissions JSON column
--    (may already exist — ADD COLUMN IF NOT EXISTS is safe)
-- ──────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `hr_employee_permissions` (
  `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id`             INT UNSIGNED NOT NULL UNIQUE,
  `page_permissions`        LONGTEXT DEFAULT NULL,
  `updated_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────────────────
-- 3. Ensure admins table has all required columns
-- ──────────────────────────────────────────────────────────────────────

ALTER TABLE `admins`
  ADD COLUMN IF NOT EXISTS `employee_ref` INT UNSIGNED DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `avatar`       VARCHAR(255)  DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `last_login`   DATETIME      DEFAULT NULL;

-- ──────────────────────────────────────────────────────────────────────
-- 4. Make sure site_settings table exists
-- ──────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default company settings
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('company_name',    'Incredible Heights'),
('company_tagline', 'Construction & Interior Solutions'),
('company_logo',    ''),
('company_phone',   '+91 9821130198'),
('company_email',   'info@ihindia.in'),
('company_address', 'New Delhi, India'),
('company_website', 'www.ihindia.in');

-- ──────────────────────────────────────────────────────────────────────
-- 5. Ensure hr_admin_permissions row exists for admin id=1
-- ──────────────────────────────────────────────────────────────────────

INSERT IGNORE INTO `hr_admin_permissions` (`admin_id`) VALUES (1);

-- Give all permissions to admin id=1 (superadmin should not need this, but just in case)
UPDATE `hr_admin_permissions` SET
  `perm_products`=1, `perm_categories`=1, `perm_services`=1, `perm_plots`=1,
  `perm_packages`=1, `perm_orders`=1, `perm_bookings`=1, `perm_transactions`=1,
  `perm_customers`=1, `perm_enquiries`=1, `perm_coupons`=1, `perm_reviews`=1,
  `perm_blogs`=1, `perm_portfolio`=1, `perm_gallery`=1, `perm_banners`=1,
  `perm_testimonials`=1, `perm_faqs`=1, `perm_reports`=1, `perm_notifications`=1,
  `perm_employees`=1, `perm_add_employee`=1, `perm_emp_roles`=1, `perm_id_cards`=1,
  `perm_attendance`=1, `perm_salary`=1, `perm_payslip`=1, `perm_advances`=1,
  `perm_holidays`=1, `perm_off_days`=1, `perm_work_settings`=1, `perm_accounts`=1,
  `perm_admin_users`=1, `perm_settings`=1
WHERE `admin_id` = 1;

-- ──────────────────────────────────────────────────────────────────────
-- 6. Ensure hr_advances table exists (used by payroll)
-- ──────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `hr_advances` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id`         INT UNSIGNED NOT NULL,
  `amount`              DECIMAL(10,2) NOT NULL,
  `reason`              VARCHAR(300) DEFAULT NULL,
  `advance_date`        DATE NOT NULL,
  `repayment_type`      ENUM('full_next','installments','manual') DEFAULT 'full_next',
  `installment_months`  TINYINT(3) UNSIGNED DEFAULT 1,
  `remaining_amount`    DECIMAL(10,2) DEFAULT NULL,
  `status`              ENUM('pending','partial','cleared') DEFAULT 'pending',
  `approved_by`         VARCHAR(100) DEFAULT 'Admin',
  `notes`               TEXT DEFAULT NULL,
  `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `k_emp` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ──────────────────────────────────────────────────────────────────────
-- 7. Ensure hr_work_settings table exists
-- ──────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `hr_work_settings` (
  `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `daily_working_hours`     DECIMAL(4,2) NOT NULL DEFAULT 8.00,
  `shift_start`             TIME NOT NULL DEFAULT '09:00:00',
  `shift_end`               TIME NOT NULL DEFAULT '18:00:00',
  `late_mark_after_minutes` INT NOT NULL DEFAULT 15,
  `half_day_after_minutes`  INT NOT NULL DEFAULT 120,
  `overtime_enabled`        TINYINT(1) NOT NULL DEFAULT 1,
  `overtime_rate_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.50,
  `min_overtime_minutes`    INT NOT NULL DEFAULT 30,
  `updated_by`              VARCHAR(100) DEFAULT 'Admin',
  `updated_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `hr_work_settings` (`id`, `daily_working_hours`) VALUES (1, 8.00);

-- ──────────────────────────────────────────────────────────────────────
-- 8. Ensure blogs table has all needed columns
-- ──────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `blogs` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(300) NOT NULL,
  `slug`        VARCHAR(320) NOT NULL UNIQUE,
  `excerpt`     TEXT DEFAULT NULL,
  `content`     LONGTEXT DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `author`      VARCHAR(120) DEFAULT 'Admin',
  `category`    VARCHAR(100) DEFAULT NULL,
  `tags`        VARCHAR(500) DEFAULT NULL,
  `status`      TINYINT(1) DEFAULT 1,
  `views`       INT DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ══════════════════════════════════════════════════════════════════════
-- DONE! Refresh your admin panel now.
-- ══════════════════════════════════════════════════════════════════════
