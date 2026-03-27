# Incredible Heights — Installation Guide

## 3-Step Setup

### Step 1 — Upload Files
Upload ALL files to your hosting root (public_html or httpdocs).
Make sure `config/` folder is uploaded — this was the main cause of HTTP 500 errors.

### Step 2 — Create Database
1. Open cPanel → MySQL Databases
2. Create a new database (e.g. `ihindia_db`)
3. Create a database user and assign ALL PRIVILEGES
4. Open phpMyAdmin → select your database → Import → upload `install.sql`

### Step 3 — Configure
Open `config/db.php` and update:
```php
define('DB_NAME', 'your_actual_database_name');
define('DB_USER', 'your_actual_database_user');
define('DB_PASS', 'your_actual_password');
```

Open `config/config.php` and update Razorpay keys if needed:
```php
define('RAZORPAY_KEY_ID',     'rzp_live_YOUR_KEY');
define('RAZORPAY_KEY_SECRET', 'YOUR_SECRET');
```

## Login
- **Admin Panel:** https://shop.ihindia.in/admin/login.php
- **Email:** admin@ihindia.in
- **Password:** Admin@123 ← CHANGE THIS IMMEDIATELY

## Bugs Fixed in This Version
1. ✅ config.php — Added missing ADMIN_SESSION_KEY, USER_SESSION_KEY, CURRENCY, WHATSAPP_NUMBER constants
2. ✅ footer.php — Fixed WHATSAPP_NUMBER constant reference
3. ✅ cart.php — Fixed `formatPriceFull()` → `formatPrice()` (function didn't exist)
4. ✅ payment.php — Fixed `payment_status='pending'` → `'unpaid'` (wrong value)
5. ✅ api/coupon-apply.php — Fixed remove coupon (cart sends `remove:true`, API wasn't handling it)
6. ✅ api/cart-remove.php — Fixed SQL injection (now uses prepared statements)
7. ✅ api/cart-update.php — Fixed SQL injection (now uses prepared statements)
8. ✅ admin/service-add.php — Was broken (5 lines, just redirected). Now a full working form
9. ✅ admin/service-edit.php — Created (was completely missing)
10. ✅ install.sql — Created (was completely missing — all database tables)
