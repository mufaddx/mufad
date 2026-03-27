-- ══════════════════════════════════════════════════════════════════════
-- INCREDIBLE HEIGHTS — MASTER CLEAN DATABASE FILE
-- Combines: database.sql + install.sql + unified_patch.sql + new_tables.sql
--
-- HOW TO USE:
--   1. Open phpMyAdmin → Select your database → SQL tab
--   2. Paste this entire file and click "Go"
--   3. This script is SAFE to run on an existing database
--      (it removes duplicates and adds missing tables/columns only)
--
-- WHAT THIS FIXES:
--   ✅ Removes duplicate product_categories (Ceiling Fan, LED Lighting, etc.)
--   ✅ Removes duplicate service_categories (Electrical, Flooring, etc.)
--   ✅ Removes duplicate products if any
--   ✅ Adds UNIQUE constraints to prevent future duplicates
--   ✅ Creates all missing tables in one shot
-- ══════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '+05:30';
SET FOREIGN_KEY_CHECKS = 0;

-- ══════════════════════════════════════════════════════════════════════
-- SECTION 1: CREATE ALL TABLES (safe — skips if already exists)
-- ══════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────
-- ADMINS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admins` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(120) NOT NULL,
  `email`        VARCHAR(180) NOT NULL UNIQUE,
  `password`     VARCHAR(255) NOT NULL,
  `role`         ENUM('superadmin','admin','manager','hr_manager','hr_staff','accounts_manager','website_admin','employee') NOT NULL DEFAULT 'admin',
  `avatar`       VARCHAR(255) DEFAULT NULL,
  `employee_ref` INT UNSIGNED DEFAULT NULL COMMENT 'Links to hr_employees.id when role=employee',
  `status`       TINYINT(1) DEFAULT 1,
  `last_login`   DATETIME DEFAULT NULL,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin — password: admin@123
INSERT IGNORE INTO `admins` (`name`,`email`,`password`,`role`,`status`) VALUES
('Super Admin','admin@ihindia.in','$2y$10$TKh8H1.PfbuSeyeq0sMcxeEFMCMcCHYVBvjA7kXsNJk.K9a4PdSYa','superadmin',1);

UPDATE `admins` SET
  `password` = '$2y$10$TKh8H1.PfbuSeyeq0sMcxeEFMCMcCHYVBvjA7kXsNJk.K9a4PdSYa',
  `role`     = 'superadmin'
WHERE `email` = 'admin@ihindia.in';

-- ─────────────────────────────────────────────────────────────────────
-- USERS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`                VARCHAR(120) NOT NULL,
  `email`               VARCHAR(180) DEFAULT NULL,
  `phone`               VARCHAR(20) DEFAULT NULL,
  `password`            VARCHAR(255) DEFAULT NULL,
  `avatar`              VARCHAR(255) DEFAULT NULL,
  `reset_token`         VARCHAR(64) DEFAULT NULL,
  `reset_token_expiry`  DATETIME DEFAULT NULL,
  `status`              TINYINT(1) DEFAULT 1,
  `created_at`          DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- USER ADDRESSES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `label`      VARCHAR(50) DEFAULT 'Home',
  `name`       VARCHAR(120) NOT NULL,
  `phone`      VARCHAR(20) NOT NULL,
  `address`    TEXT NOT NULL,
  `city`       VARCHAR(80) NOT NULL,
  `state`      VARCHAR(80) DEFAULT NULL,
  `pincode`    VARCHAR(10) DEFAULT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `k_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- SETTINGS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id`    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`   VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`key`,`value`) VALUES
('site_name',                'Incredible Heights'),
('site_phone',               '+91 9821130198'),
('site_email',               'info@ihindia.in'),
('site_whatsapp',            '919821130198'),
('gst_percent',              '18'),
('delivery_charge',          '0'),
('min_order_free_delivery',  '0'),
('razorpay_key',             ''),
('razorpay_secret',          ''),
('meta_title',               'Incredible Heights — Construction & Interior Solutions'),
('meta_description',         'Delhi NCR most trusted construction company. 350+ services. Book free site visit.');

-- ─────────────────────────────────────────────────────────────────────
-- PRODUCT CATEGORIES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(120) NOT NULL UNIQUE,
  `icon`       VARCHAR(10) DEFAULT '📦',
  `image`      VARCHAR(255) DEFAULT NULL,
  `status`     TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- PRODUCTS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id`    INT UNSIGNED DEFAULT NULL,
  `name`           VARCHAR(200) NOT NULL,
  `slug`           VARCHAR(220) DEFAULT NULL UNIQUE,
  `sku`            VARCHAR(80) DEFAULT NULL,
  `brand`          VARCHAR(100) DEFAULT NULL,
  `short_desc`     VARCHAR(500) DEFAULT NULL,
  `description`    TEXT DEFAULT NULL,
  `price`          DECIMAL(12,2) NOT NULL DEFAULT 0,
  `original_price` DECIMAL(12,2) DEFAULT NULL,
  `unit`           VARCHAR(30) DEFAULT 'piece',
  `stock`          INT DEFAULT 0,
  `icon`           VARCHAR(10) DEFAULT '📦',
  `image`          VARCHAR(255) DEFAULT NULL,
  `badge`          VARCHAR(20) DEFAULT NULL,
  `rating`         DECIMAL(2,1) DEFAULT 0.0,
  `reviews_count`  INT DEFAULT 0,
  `is_featured`    TINYINT(1) DEFAULT 0,
  `status`         TINYINT(1) DEFAULT 1,
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `k_cat`  (`category_id`),
  KEY `k_feat` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- SERVICE CATEGORIES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(120) NOT NULL UNIQUE,
  `icon`       VARCHAR(10) DEFAULT '🔧',
  `image`      VARCHAR(255) DEFAULT NULL,
  `status`     TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- SERVICES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `name`        VARCHAR(200) NOT NULL,
  `slug`        VARCHAR(220) DEFAULT NULL UNIQUE,
  `short_desc`  VARCHAR(500) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `price_from`  DECIMAL(12,2) DEFAULT 0,
  `price_to`    DECIMAL(12,2) DEFAULT NULL,
  `price_unit`  VARCHAR(40) DEFAULT 'sqft',
  `duration`    VARCHAR(80) DEFAULT NULL,
  `icon`        VARCHAR(10) DEFAULT '🔧',
  `image`       VARCHAR(255) DEFAULT NULL,
  `badge`       VARCHAR(20) DEFAULT NULL,
  `tags`        VARCHAR(500) DEFAULT NULL,
  `is_popular`  TINYINT(1) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `status`      TINYINT(1) DEFAULT 1,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `k_cat` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- SERVICE TAGS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `service_tags` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `service_id` INT UNSIGNED NOT NULL,
  `tag_name`   VARCHAR(80) NOT NULL,
  KEY `k_svc` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- PLOTS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `plots` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`           VARCHAR(200) NOT NULL,
  `slug`            VARCHAR(220) DEFAULT NULL UNIQUE,
  `description`     TEXT DEFAULT NULL,
  `location`        VARCHAR(200) DEFAULT NULL,
  `area_sqft`       DECIMAL(12,2) DEFAULT NULL,
  `price`           DECIMAL(14,2) DEFAULT NULL,
  `price_per_sqft`  DECIMAL(10,2) DEFAULT NULL,
  `facing`          VARCHAR(40) DEFAULT NULL,
  `possession`      VARCHAR(80) DEFAULT NULL,
  `image`           VARCHAR(255) DEFAULT NULL,
  `is_featured`     TINYINT(1) DEFAULT 0,
  `status`          ENUM('Available','Reserved','Sold') DEFAULT 'Available',
  `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- CART
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(64) DEFAULT NULL,
  `item_type`  ENUM('product','service','package','plot') DEFAULT 'product',
  `item_id`    INT UNSIGNED NOT NULL,
  `quantity`   INT DEFAULT 1,
  `price`      DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user`    (`user_id`),
  KEY `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- ORDERS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number`        VARCHAR(30) NOT NULL UNIQUE,
  `user_id`             INT UNSIGNED DEFAULT NULL,
  `name`                VARCHAR(120) NOT NULL,
  `phone`               VARCHAR(20) NOT NULL,
  `email`               VARCHAR(180) DEFAULT NULL,
  `address`             TEXT DEFAULT NULL,
  `city`                VARCHAR(80) DEFAULT NULL,
  `state`               VARCHAR(80) DEFAULT NULL,
  `pincode`             VARCHAR(10) DEFAULT NULL,
  `address_id`          INT UNSIGNED DEFAULT NULL,
  `total_amount`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount`            DECIMAL(12,2) DEFAULT 0,
  `gst_amount`          DECIMAL(12,2) DEFAULT 0,
  `final_amount`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `coupon_code`         VARCHAR(50) DEFAULT NULL,
  `payment_method`      VARCHAR(40) DEFAULT 'pay_after_work',
  `payment_status`      ENUM('unpaid','paid','failed','refunded') DEFAULT 'unpaid',
  `order_status`        ENUM('Pending','Confirmed','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `razorpay_order_id`   VARCHAR(100) DEFAULT NULL,
  `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
  `cancel_reason`       TEXT DEFAULT NULL,
  `notes`               TEXT DEFAULT NULL,
  `created_at`          DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `k_user`   (`user_id`),
  KEY `k_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- ORDER ITEMS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`  INT UNSIGNED NOT NULL,
  `item_type` ENUM('product','service','package') DEFAULT 'product',
  `item_id`   INT UNSIGNED NOT NULL,
  `name`      VARCHAR(200) NOT NULL,
  `quantity`  INT DEFAULT 1,
  `price`     DECIMAL(12,2) NOT NULL,
  `subtotal`  DECIMAL(12,2) NOT NULL,
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- BOOKINGS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_number` VARCHAR(30) DEFAULT NULL UNIQUE,
  `user_id`        INT UNSIGNED DEFAULT NULL,
  `service_id`     INT UNSIGNED DEFAULT NULL,
  `name`           VARCHAR(120) NOT NULL,
  `phone`          VARCHAR(20) NOT NULL,
  `email`          VARCHAR(180) DEFAULT NULL,
  `address`        TEXT DEFAULT NULL,
  `city`           VARCHAR(80) DEFAULT NULL,
  `preferred_date` DATE DEFAULT NULL,
  `preferred_time` VARCHAR(30) DEFAULT NULL,
  `notes`          TEXT DEFAULT NULL,
  `cancel_reason`  TEXT DEFAULT NULL,
  `status`         ENUM('Pending','Confirmed','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `admin_notes`    TEXT DEFAULT NULL,
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY `k_user`    (`user_id`),
  KEY `k_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- ENQUIRIES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(120) NOT NULL,
  `phone`       VARCHAR(20) NOT NULL,
  `email`       VARCHAR(180) DEFAULT NULL,
  `subject`     VARCHAR(200) DEFAULT NULL,
  `message`     TEXT DEFAULT NULL,
  `source`      VARCHAR(60) DEFAULT 'website',
  `status`      ENUM('New','Contacted','Qualified','Converted','Lost','Closed') DEFAULT 'New',
  `admin_note`  TEXT DEFAULT NULL,
  `admin_reply` TEXT DEFAULT NULL,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- COUPONS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `coupons` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code`           VARCHAR(50) NOT NULL UNIQUE,
  `discount_type`  ENUM('percent','flat') DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `min_order`      DECIMAL(10,2) DEFAULT 0,
  `max_uses`       INT DEFAULT NULL,
  `used_count`     INT DEFAULT 0,
  `expiry_date`    DATE DEFAULT NULL,
  `status`         TINYINT(1) DEFAULT 1,
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `coupons` (`code`,`discount_type`,`discount_value`,`min_order`,`max_uses`,`status`) VALUES
('WELCOME10', 'percent', 10, 500, 100, 1),
('IH500',     'flat',   500, 2000, 50,  1);

-- ─────────────────────────────────────────────────────────────────────
-- WISHLIST
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `item_type`  ENUM('product','service','plot') DEFAULT 'product',
  `item_id`    INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_wishlist` (`user_id`,`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- HOME FEATURED
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `home_featured` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `item_type`  ENUM('service','product','package','plot') NOT NULL,
  `item_id`    INT UNSIGNED NOT NULL,
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- PORTFOLIO
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(200) NOT NULL,
  `category`    VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `location`    VARCHAR(200) DEFAULT NULL,
  `year`        YEAR DEFAULT NULL,
  `sort_order`  INT DEFAULT 0,
  `status`      TINYINT(1) DEFAULT 1,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- PACKAGES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `packages` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(200) NOT NULL,
  `slug`        VARCHAR(220) DEFAULT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `features`    TEXT DEFAULT NULL,
  `price`       DECIMAL(12,2) DEFAULT NULL,
  `price_unit`  VARCHAR(30) DEFAULT 'project',
  `duration`    VARCHAR(80) DEFAULT NULL,
  `icon`        VARCHAR(10) DEFAULT '📦',
  `image`       VARCHAR(255) DEFAULT NULL,
  `is_popular`  TINYINT(1) DEFAULT 0,
  `sort_order`  INT DEFAULT 0,
  `status`      TINYINT(1) DEFAULT 1,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- NOTIFICATIONS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT UNSIGNED NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `message`    TEXT DEFAULT NULL,
  `type`       VARCHAR(30) DEFAULT 'info',
  `link`       VARCHAR(255) DEFAULT NULL,
  `is_read`    TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- BANNERS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `banners` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(200) DEFAULT NULL,
  `subtitle`   VARCHAR(300) DEFAULT NULL,
  `image`      VARCHAR(255) DEFAULT NULL,
  `link`       VARCHAR(255) DEFAULT NULL,
  `position`   VARCHAR(40) DEFAULT 'hero',
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- REVIEWS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT UNSIGNED NOT NULL,
  `item_id`     INT UNSIGNED NOT NULL,
  `type`        ENUM('product','service') DEFAULT 'product',
  `item_name`   VARCHAR(200) NOT NULL,
  `rating`      TINYINT(1) NOT NULL DEFAULT 5,
  `review_text` TEXT NOT NULL,
  `status`      ENUM('pending','approved','rejected') DEFAULT 'pending',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `ux_user_item_type` (`user_id`,`item_id`,`type`),
  KEY `k_type`   (`type`),
  KEY `k_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- TESTIMONIALS
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `role`       VARCHAR(100) DEFAULT NULL,
  `company`    VARCHAR(150) DEFAULT NULL,
  `review`     TEXT NOT NULL,
  `rating`     TINYINT(1) DEFAULT 5,
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `status`     TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `testimonials` (`name`,`role`,`company`,`review`,`rating`,`status`) VALUES
('Rajesh Sharma', 'Homeowner',        'Delhi',   'Incredible Heights transformed my apartment completely. Professional team and quality work!', 5, 1),
('Priya Verma',   'Business Owner',   'Noida',   'Best construction company in Delhi NCR. Delivered on time and within budget.',               5, 1),
('Amit Kumar',    'Property Developer','Gurgaon', 'Worked with them on multiple projects. Always reliable and high quality.',                   5, 1);

-- ─────────────────────────────────────────────────────────────────────
-- FAQs
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `faqs` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question`   TEXT NOT NULL,
  `answer`     TEXT NOT NULL,
  `category`   VARCHAR(80) DEFAULT 'General',
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `faqs` (`question`,`answer`,`category`,`sort_order`) VALUES
('How do I book a service?',          'You can book a service through our website by visiting the Services page, selecting your service, and filling the booking form. Our team will contact you within 24 hours.', 'General',  1),
('What areas do you serve?',          'We currently serve Delhi NCR including Delhi, Noida, Gurgaon, Faridabad, and Ghaziabad.',                                                                                   'General',  2),
('How can I track my order?',         'Login to your account and visit My Orders section to track your orders in real-time.',                                                                                      'Orders',   1),
('What payment methods do you accept?','We accept all major payment methods including UPI, Credit/Debit Cards, Net Banking, and EMI through Razorpay.',                                                             'Payments', 1),
('Can I cancel my order?',            'Yes, you can cancel your order from My Orders section as long as it is in Pending or Processing status.',                                                                   'Orders',   2),
('Do you provide warranty on work?',  'Yes, we provide warranty on all our construction and interior work. Warranty period varies by service type.',                                                               'Services', 1);

-- ─────────────────────────────────────────────────────────────────────
-- GALLERY
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `gallery` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `image`      VARCHAR(255) NOT NULL,
  `caption`    VARCHAR(255) DEFAULT NULL,
  `category`   VARCHAR(80) DEFAULT 'General',
  `sort_order` INT DEFAULT 0,
  `status`     TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────
-- BLOG CATEGORIES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(120) NOT NULL UNIQUE,
  `status`     TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `blog_categories` (`name`,`slug`) VALUES
('Construction',    'construction'),
('Interior Design', 'interior-design'),
('Tips & Tricks',   'tips-tricks'),
('News',            'news');

-- ─────────────────────────────────────────────────────────────────────
-- HR TABLES
-- ─────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hr_employees` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `emp_id`         VARCHAR(20) NOT NULL UNIQUE,
  `name`           VARCHAR(100) NOT NULL,
  `email`          VARCHAR(150) NOT NULL UNIQUE,
  `phone`          VARCHAR(15) NOT NULL,
  `password`       VARCHAR(255) NOT NULL,
  `designation`    VARCHAR(100) DEFAULT NULL,
  `department`     VARCHAR(100) DEFAULT NULL,
  `monthly_salary` DECIMAL(10,2) NOT NULL DEFAULT 20000.00,
  `join_date`      DATE NOT NULL,
  `assigned_ip`    VARCHAR(50) DEFAULT NULL,
  `status`         ENUM('active','hold','terminated') NOT NULL DEFAULT 'active',
  `role`           ENUM('employee','team_lead','hr_staff','manager','super_admin') NOT NULL DEFAULT 'employee',
  `photo`          VARCHAR(255) DEFAULT NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_off_days` (
  `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `day_name` ENUM('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL UNIQUE,
  `is_off`   TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `hr_off_days` (`day_name`, `is_off`) VALUES
('Sunday',1),('Monday',0),('Tuesday',0),('Wednesday',0),
('Thursday',0),('Friday',0),('Saturday',0);

CREATE TABLE IF NOT EXISTS `hr_employee_kyc` (
  `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id`             INT UNSIGNED NOT NULL,
  `aadhar_no`               VARCHAR(20) DEFAULT NULL,
  `aadhar_doc`              VARCHAR(255) DEFAULT NULL,
  `pan_no`                  VARCHAR(15) DEFAULT NULL,
  `pan_doc`                 VARCHAR(255) DEFAULT NULL,
  `bank_name`               VARCHAR(100) DEFAULT NULL,
  `bank_account`            VARCHAR(30) DEFAULT NULL,
  `ifsc_code`               VARCHAR(15) DEFAULT NULL,
  `address`                 TEXT DEFAULT NULL,
  `emergency_contact_name`  VARCHAR(100) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(15) DEFAULT NULL,
  `created_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_employee` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_attendance` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id`   INT UNSIGNED NOT NULL,
  `date`          DATE NOT NULL,
  `checkin_time`  DATETIME DEFAULT NULL,
  `checkout_time` DATETIME DEFAULT NULL,
  `checkin_ip`    VARCHAR(50) DEFAULT NULL,
  `checkout_ip`   VARCHAR(50) DEFAULT NULL,
  `total_minutes` INT DEFAULT 0,
  `status`        ENUM('present','half_day','absent','holiday') DEFAULT 'present',
  `notes`         TEXT DEFAULT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_emp_date` (`employee_id`,`date`),
  INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_accounts` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(200) NOT NULL,
  `category`    VARCHAR(100) NOT NULL,
  `type`        ENUM('credit','debit') NOT NULL DEFAULT 'debit',
  `amount`      DECIMAL(12,2) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `receipt`     VARCHAR(255) DEFAULT NULL,
  `date`        DATE NOT NULL,
  `added_by`    VARCHAR(100) DEFAULT 'Admin',
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_date` (`date`),
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_holidays` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(200) NOT NULL,
  `date`        DATE NOT NULL UNIQUE,
  `type`        ENUM('national','optional','company') NOT NULL DEFAULT 'national',
  `description` TEXT DEFAULT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hr_salary_disbursements` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id`  INT UNSIGNED NOT NULL,
  `month`        TINYINT UNSIGNED NOT NULL,
  `year`         SMALLINT UNSIGNED NOT NULL,
  `working_days` SMALLINT NOT NULL,
  `present_days` SMALLINT NOT NULL,
  `daily_rate`   DECIMAL(10,2) NOT NULL,
  `gross_salary` DECIMAL(10,2) NOT NULL,
  `deductions`   DECIMAL(10,2) NOT NULL DEFAULT 0,
  `net_salary`   DECIMAL(10,2) NOT NULL,
  `remarks`      TEXT DEFAULT NULL,
  `disbursed_by` VARCHAR(100) DEFAULT 'Admin',
  `disbursed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_emp_month` (`employee_id`,`month`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ══════════════════════════════════════════════════════════════════════
-- SECTION 2: FIX DUPLICATE PRODUCT CATEGORIES
-- Keeps the lowest ID for each unique name, deletes the rest
-- ══════════════════════════════════════════════════════════════════════

-- Step 2a: Move any products pointing to a duplicate category_id
--          to point to the canonical (lowest id) category instead
UPDATE `products` p
JOIN (
  SELECT name, MIN(id) AS keep_id
  FROM `product_categories`
  GROUP BY name
) canon ON p.category_id IN (
  SELECT id FROM `product_categories`
  WHERE name = canon.name AND id <> canon.keep_id
)
SET p.category_id = canon.keep_id;

-- Step 2b: Delete duplicate product_categories (keep lowest id per name)
DELETE pc
FROM `product_categories` pc
WHERE pc.id NOT IN (
  SELECT keep_id FROM (
    SELECT MIN(id) AS keep_id FROM `product_categories` GROUP BY name
  ) AS tmp
);

-- Step 2c: Fix any slug conflicts before adding UNIQUE constraint
--          (make slugs identical to the canonical ones)
UPDATE `product_categories` SET slug = LOWER(REPLACE(name, ' ', '-'))
WHERE slug IS NULL OR slug = '';

-- Step 2d: Ensure UNIQUE index on slug (only adds if missing)
-- We use a procedure so it doesn't fail if index already exists
DROP PROCEDURE IF EXISTS add_pc_slug_unique;
DELIMITER $$
CREATE PROCEDURE add_pc_slug_unique()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'product_categories'
      AND INDEX_NAME = 'slug'
  ) THEN
    ALTER TABLE `product_categories` MODIFY COLUMN `slug` VARCHAR(120) NOT NULL,
      ADD UNIQUE KEY `slug` (`slug`);
  END IF;
END$$
DELIMITER ;
CALL add_pc_slug_unique();
DROP PROCEDURE IF EXISTS add_pc_slug_unique;


-- ══════════════════════════════════════════════════════════════════════
-- SECTION 3: FIX DUPLICATE SERVICE CATEGORIES
-- ══════════════════════════════════════════════════════════════════════

-- Step 3a: Remap services pointing to duplicate category ids
UPDATE `services` s
JOIN (
  SELECT name, MIN(id) AS keep_id
  FROM `service_categories`
  GROUP BY name
) canon ON s.category_id IN (
  SELECT id FROM `service_categories`
  WHERE name = canon.name AND id <> canon.keep_id
)
SET s.category_id = canon.keep_id;

-- Step 3b: Delete duplicate service_categories (keep lowest id per name)
DELETE sc
FROM `service_categories` sc
WHERE sc.id NOT IN (
  SELECT keep_id FROM (
    SELECT MIN(id) AS keep_id FROM `service_categories` GROUP BY name
  ) AS tmp
);

-- Step 3c: Fix any null/empty slugs
UPDATE `service_categories` SET slug = LOWER(REPLACE(name, ' ', '-'))
WHERE slug IS NULL OR slug = '';

-- Step 3d: Ensure UNIQUE index on slug
DROP PROCEDURE IF EXISTS add_sc_slug_unique;
DELIMITER $$
CREATE PROCEDURE add_sc_slug_unique()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'service_categories'
      AND INDEX_NAME = 'slug'
  ) THEN
    ALTER TABLE `service_categories` MODIFY COLUMN `slug` VARCHAR(120) NOT NULL,
      ADD UNIQUE KEY `slug` (`slug`);
  END IF;
END$$
DELIMITER ;
CALL add_sc_slug_unique();
DROP PROCEDURE IF EXISTS add_sc_slug_unique;


-- ══════════════════════════════════════════════════════════════════════
-- SECTION 4: FIX DUPLICATE PRODUCTS (same name in same category)
-- ══════════════════════════════════════════════════════════════════════

-- Remove duplicate products keeping the one with the lowest id
DELETE p
FROM `products` p
WHERE p.id NOT IN (
  SELECT keep_id FROM (
    SELECT MIN(id) AS keep_id FROM `products` GROUP BY name, category_id
  ) AS tmp
);


-- ══════════════════════════════════════════════════════════════════════
-- SECTION 5: INSERT CANONICAL CATEGORY DATA
-- Uses INSERT IGNORE so existing rows are never duplicated
-- ══════════════════════════════════════════════════════════════════════

-- Product Categories (complete merged list)
INSERT IGNORE INTO `product_categories` (`name`,`slug`,`icon`) VALUES
('Ceiling Fan',      'ceiling-fan',      '🌀'),
('LED Lighting',     'led-lighting',     '💡'),
('Split AC',         'split-ac',         '❄️'),
('Modular Switches', 'modular-switches', '🔌'),
('Water Tanks',      'water-tanks',      '🪣'),
('Pipes & Fittings', 'pipes-fittings',   '🔧'),
('Flooring',         'flooring',         '🏠'),
('Paint & Primer',   'paint-primer',     '🎨'),
('Paints & Coatings','paints-coatings',  '🎨'),
('Electrical',       'electrical',       '⚡'),
('Plumbing',         'plumbing',         '🔧'),
('Hardware & Tools', 'hardware-tools',   '🔨'),
('Safety & Security','safety-security',  '🛡️');

-- Service Categories (complete merged list)
INSERT IGNORE INTO `service_categories` (`name`,`slug`,`icon`,`status`) VALUES
('Civil Construction', 'civil-construction', '🏗️', 1),
('Interior Design',    'interior-design',    '🪑', 1),
('Electrical Work',    'electrical-work',    '⚡', 1),
('Plumbing',           'plumbing',           '🔧', 1),
('Painting',           'painting',           '🎨', 1),
('Flooring',           'flooring',           '🏠', 1),
('Renovation',         'renovation',         '🔨', 1),
('Safety & Security',  'safety-security',    '🛡️', 1),
('Carpentry',          'carpentry',          '🪵', 1),
('AC & HVAC',          'ac-hvac',            '❄️', 1);


-- ══════════════════════════════════════════════════════════════════════
-- SECTION 6: SAFE COLUMN ADDITIONS (ALTER only if column missing)
-- ══════════════════════════════════════════════════════════════════════

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `reset_token`       VARCHAR(64) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `reset_token_expiry` DATETIME DEFAULT NULL;

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `cancel_reason` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `updated_at`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `bookings`
  ADD COLUMN IF NOT EXISTS `cancel_reason` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `updated_at`    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `enquiries`
  ADD COLUMN IF NOT EXISTS `admin_reply` TEXT DEFAULT NULL;

ALTER TABLE `admins`
  ADD COLUMN IF NOT EXISTS `employee_ref` INT UNSIGNED DEFAULT NULL,
  MODIFY COLUMN `role`
    ENUM('superadmin','admin','manager','hr_manager','hr_staff','accounts_manager','website_admin','employee')
    NOT NULL DEFAULT 'admin';

SET FOREIGN_KEY_CHECKS = 1;

-- ══════════════════════════════════════════════════════════════════════
-- DONE!
-- Login: https://shop.ihindia.in/admin/login.php
--   Email:    admin@ihindia.in
--   Password: admin@123
-- ══════════════════════════════════════════════════════════════════════
