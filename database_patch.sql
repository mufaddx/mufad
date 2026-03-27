-- ══════════════════════════════════════════════════════════════════════
-- INCREDIBLE HEIGHTS — DATABASE PATCH v2.0
-- Adds: Work Settings, Advances/Loans, Overtime columns, Permissions
-- HOW TO USE: Run this file AFTER the main database.sql
-- ══════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────────────────────────────
-- WORK SETTINGS — Configure working hours, late mark, overtime
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hr_work_settings` (
  `id`                       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `daily_working_hours`      DECIMAL(4,2) NOT NULL DEFAULT 8.00
    COMMENT 'Standard working hours per day (e.g. 8.00)',
  `shift_start`              TIME NOT NULL DEFAULT '09:00:00'
    COMMENT 'Official shift start time',
  `shift_end`                TIME NOT NULL DEFAULT '18:00:00'
    COMMENT 'Official shift end time',
  `late_mark_after_minutes`  INT NOT NULL DEFAULT 15
    COMMENT 'Grace period in minutes after shift_start before marking late',
  `half_day_after_minutes`   INT NOT NULL DEFAULT 120
    COMMENT 'Minutes late after shift_start to count as half-day (e.g. 120 = 2 hrs)',
  `overtime_enabled`         TINYINT(1) NOT NULL DEFAULT 1,
  `overtime_rate_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.50
    COMMENT 'E.g. 1.5 = 1.5x hourly rate for overtime',
  `min_overtime_minutes`     INT NOT NULL DEFAULT 30
    COMMENT 'Minimum minutes beyond daily_working_hours to qualify as overtime',
  `updated_by`               VARCHAR(100) DEFAULT 'Admin',
  `updated_at`               TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default work settings (only if empty)
INSERT IGNORE INTO `hr_work_settings`
  (`id`, `daily_working_hours`, `shift_start`, `shift_end`,
   `late_mark_after_minutes`, `half_day_after_minutes`,
   `overtime_enabled`, `overtime_rate_multiplier`, `min_overtime_minutes`)
VALUES
  (1, 8.00, '09:00:00', '18:00:00', 15, 120, 1, 1.50, 30);

-- ─────────────────────────────────────────────────────────────────────
-- ADVANCES / LOANS — Track advance payments to employees
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hr_advances` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id`         INT UNSIGNED NOT NULL,
  `amount`              DECIMAL(10,2) NOT NULL,
  `reason`              VARCHAR(300) DEFAULT NULL,
  `advance_date`        DATE NOT NULL,
  `repayment_type`      ENUM('full_next','installments','manual') DEFAULT 'full_next'
    COMMENT 'full_next=deduct full in next payroll; installments=spread across months',
  `installment_months`  TINYINT UNSIGNED DEFAULT 1,
  `remaining_amount`    DECIMAL(10,2) DEFAULT NULL
    COMMENT 'Remaining amount to be deducted (null = same as amount)',
  `status`              ENUM('pending','partial','cleared') DEFAULT 'pending',
  `approved_by`         VARCHAR(100) DEFAULT 'Admin',
  `notes`               TEXT DEFAULT NULL,
  `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_emp` (`employee_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- ADVANCE DEDUCTION RECORDS — Track which month each advance was deducted
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hr_advance_deductions` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `advance_id`  INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `month`       TINYINT UNSIGNED NOT NULL,
  `year`        SMALLINT UNSIGNED NOT NULL,
  `amount`      DECIMAL(10,2) NOT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_adv_month` (`advance_id`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- ADD OVERTIME COLUMNS TO hr_attendance (safe — skips if exists)
-- ─────────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS patch_attendance_overtime;
DELIMITER $$
CREATE PROCEDURE patch_attendance_overtime()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'hr_attendance'
      AND COLUMN_NAME  = 'overtime_minutes'
  ) THEN
    ALTER TABLE `hr_attendance`
      ADD COLUMN `overtime_minutes`  INT DEFAULT 0        AFTER `total_minutes`,
      ADD COLUMN `late_minutes`      INT DEFAULT 0        AFTER `overtime_minutes`,
      ADD COLUMN `attendance_status` VARCHAR(20) DEFAULT NULL AFTER `late_minutes`
      COMMENT 'on_time | late | half_day | absent';
  END IF;
END$$
DELIMITER ;
CALL patch_attendance_overtime();
DROP PROCEDURE IF EXISTS patch_attendance_overtime;

-- ─────────────────────────────────────────────────────────────────────
-- ADD advance_deduction TO hr_salary_disbursements
-- ─────────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS patch_salary_advance;
DELIMITER $$
CREATE PROCEDURE patch_salary_advance()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'hr_salary_disbursements'
      AND COLUMN_NAME  = 'advance_deduction'
  ) THEN
    ALTER TABLE `hr_salary_disbursements`
      ADD COLUMN `advance_deduction`  DECIMAL(10,2) DEFAULT 0  AFTER `deductions`,
      ADD COLUMN `overtime_amount`    DECIMAL(10,2) DEFAULT 0  AFTER `advance_deduction`,
      ADD COLUMN `overtime_hours`     DECIMAL(6,2)  DEFAULT 0  AFTER `overtime_amount`;
  END IF;
END$$
DELIMITER ;
CALL patch_salary_advance();
DROP PROCEDURE IF EXISTS patch_salary_advance;

-- ─────────────────────────────────────────────────────────────────────
-- ADD city/state TO hr_employees (safe)
-- ─────────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS patch_employees_fields;
DELIMITER $$
CREATE PROCEDURE patch_employees_fields()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'hr_employees'
      AND COLUMN_NAME  = 'city'
  ) THEN
    ALTER TABLE `hr_employees`
      ADD COLUMN `city`   VARCHAR(100) DEFAULT NULL AFTER `phone`,
      ADD COLUMN `state`  VARCHAR(100) DEFAULT NULL AFTER `city`,
      ADD COLUMN `dob`    DATE DEFAULT NULL         AFTER `state`,
      ADD COLUMN `gender` ENUM('Male','Female','Other') DEFAULT NULL AFTER `dob`;
  END IF;
END$$
DELIMITER ;
CALL patch_employees_fields();
DROP PROCEDURE IF EXISTS patch_employees_fields;

-- ─────────────────────────────────────────────────────────────────────
-- GRANULAR ADMIN PERMISSIONS — Each admin user gets specific page access
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hr_admin_permissions` (
  `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id`             INT UNSIGNED NOT NULL UNIQUE,
  -- Website Management
  `perm_products`        TINYINT(1) DEFAULT 0,
  `perm_categories`      TINYINT(1) DEFAULT 0,
  `perm_services`        TINYINT(1) DEFAULT 0,
  `perm_plots`           TINYINT(1) DEFAULT 0,
  `perm_packages`        TINYINT(1) DEFAULT 0,
  `perm_orders`          TINYINT(1) DEFAULT 0,
  `perm_bookings`        TINYINT(1) DEFAULT 0,
  `perm_transactions`    TINYINT(1) DEFAULT 0,
  `perm_customers`       TINYINT(1) DEFAULT 0,
  `perm_enquiries`       TINYINT(1) DEFAULT 0,
  `perm_coupons`         TINYINT(1) DEFAULT 0,
  `perm_reviews`         TINYINT(1) DEFAULT 0,
  `perm_blogs`           TINYINT(1) DEFAULT 0,
  `perm_portfolio`       TINYINT(1) DEFAULT 0,
  `perm_gallery`         TINYINT(1) DEFAULT 0,
  `perm_banners`         TINYINT(1) DEFAULT 0,
  `perm_testimonials`    TINYINT(1) DEFAULT 0,
  `perm_faqs`            TINYINT(1) DEFAULT 0,
  `perm_reports`         TINYINT(1) DEFAULT 0,
  `perm_notifications`   TINYINT(1) DEFAULT 0,
  -- HR / Company Management
  `perm_employees`       TINYINT(1) DEFAULT 0,
  `perm_add_employee`    TINYINT(1) DEFAULT 0,
  `perm_emp_roles`       TINYINT(1) DEFAULT 0,
  `perm_id_cards`        TINYINT(1) DEFAULT 0,
  `perm_attendance`      TINYINT(1) DEFAULT 0,
  `perm_salary`          TINYINT(1) DEFAULT 0,
  `perm_payslip`         TINYINT(1) DEFAULT 0,
  `perm_advances`        TINYINT(1) DEFAULT 0,
  `perm_holidays`        TINYINT(1) DEFAULT 0,
  `perm_off_days`        TINYINT(1) DEFAULT 0,
  `perm_work_settings`   TINYINT(1) DEFAULT 0,
  `perm_accounts`        TINYINT(1) DEFAULT 0,
  `perm_admin_users`     TINYINT(1) DEFAULT 0,
  `perm_settings`        TINYINT(1) DEFAULT 0,
  `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
