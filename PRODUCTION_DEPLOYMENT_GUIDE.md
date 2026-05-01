# CampusLink Production Deployment Guide

## Version: 1.0.0
## Target Environment: InfinityFree or Similar Shared Hosting
## Last Updated: April 2026

---

## TABLE OF CONTENTS
1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Hosting Compatibility](#hosting-compatibility)
3. [Configuration for Production](#configuration-for-production)
4. [Migration Steps](#migration-steps)
5. [Environment Variables](#environment-variables)
6. [Database Setup](#database-setup)
7. [File Permissions](#file-permissions)
8. [Common Issues & Fixes](#common-issues--fixes)
9. [Security Checklist](#security-checklist)
10. [Performance Optimization](#performance-optimization)
11. [Monitoring & Maintenance](#monitoring--maintenance)

---

## PRE-DEPLOYMENT CHECKLIST

### Critical Fixes Applied (April 2026)

✅ **Fixed:** Sanitizer class missing `text()` and `email()` methods  
✅ **Fixed:** Admin users page query missing review/complaint/saved counts  
✅ **Fixed:** Vendor profile edit form lacking $categories variable  
✅ **Fixed:** Missing Sanitizer methods causing fatal errors  
✅ **Fixed:** WhatsApp button formatting  
✅ **Fixed:** Notification URL routing issues  
✅ **Fixed:** Form submission handlers  
✅ **Fixed:** UI/UX upgraded to 2026 standards  

### Pre-Deployment Tasks

- [ ] Update `config/config.php` with production values
- [ ] Create `.env` file with production secrets
- [ ] Test all forms locally before deployment
- [ ] Backup current database
- [ ] Run security scan on code
- [ ] Compress images to WebP format
- [ ] Test file uploads on hosting
- [ ] Verify email sending works
- [ ] Test payment gateway (Paystack) on live
- [ ] Verify HTTPS certificate
- [ ] Set up automatic backups
- [ ] Configure error logging
- [ ] Test cron jobs if applicable

---

## HOSTING COMPATIBILITY

### Recommended Providers (2026)

#### **⭐ Primary Recommendation: Heroku / Railway / Render**
- **Pros:** Better PHP/MySQL versions, more reliable, better support
- **Cons:** Slightly higher cost ($5-20/month vs free)
- **Best for:** Production applications

#### **✅ Secondary: InfinityFree**
- **Pros:** Free tier available, adequate for small apps
- **Cons:** Limited resources, restricted functions, occasional downtime
- **PHP Version:** 7.4 - 8.0
- **MySQL Version:** 5.7
- **Supported:** File uploads, cron jobs, SSL

#### **⚠️ Alternatives to Avoid**
- 000webhost (unreliable)
- FreeHosting.com (poor performance)
- AwardSpace (limited features)

### PHP Version Requirements

```
Minimum: PHP 7.4
Recommended: PHP 8.1+
Current Code: PHP 8.0+ (uses union types, named arguments)
```

### MySQL Requirements

```
Minimum: MySQL 5.7
Recommended: MySQL 8.0+
Current Code: Full compatibility with both
```

### InfinityFree Specific Limitations

1. **Restricted Functions** (Not available):
   - `proc_open`, `proc_close`
   - `exec`, `shell_exec`, `system`
   - `fopen` for remote URLs
   - Some mail() features

2. **Folder Restrictions:**
   - `/admin` folder accessible via web (requires .htaccess protection)
   - Session folder must be writable
   - Upload folders must allow write

3. **Resource Limits:**
   - 50MB total disk space (free tier)
   - 10GB bandwidth (free tier)
   - Max execution time: 30 seconds
   - Memory limit: 64MB

---

## CONFIGURATION FOR PRODUCTION

### Step 1: Update config/config.php

```php
<?php
// In config/config.php, change these lines:

// CHANGE FROM (development):
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('SITE_URL', 'https://localhost/campuslink');

// CHANGE TO (production):
define('APP_ENV', 'production');
define('APP_DEBUG', false);  // Hide errors from public
define('SITE_URL', 'https://yourdomain.com');  // Your actual domain
```

### Step 2: Create .env File

Create a `.env` file in the root directory:

```env
# Environment
APP_ENV=production
APP_DEBUG=false

# Database - Use your hosting provider's credentials
DB_HOST=sql.infinityfree.com
DB_NAME=if0_12345678_campuslinkd
DB_USER=if0_12345678_user
DB_PASS=YourComplexPasswordHere
DB_CHARSET=utf8mb4
DB_PORT=3306

# Email Configuration
MAIL_HOST=smtp.infinityfree.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=YourAppPasswordHere
MAIL_FROM=noreply@yourdomain.com

# Paystack (Production)
PAYSTACK_PUBLIC_KEY=pk_live_your_live_public_key
PAYSTACK_SECRET_KEY=sk_live_your_live_secret_key

# Site Configuration
SITE_EMAIL=support@yourdomain.com
CONTACT_EMAIL=contact@yourdomain.com
ADMIN_EMAIL=admin@yourdomain.com

# Security
BCRYPT_COST=12
SESSION_TIMEOUT=3600
```

### Step 3: Update bootstrap.php for Production

```php
<?php
// In core/bootstrap.php

// CHANGE FROM:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CHANGE TO:
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

---

## MIGRATION STEPS

### Step 1: Prepare Your Hosting Account

1. **Access your hosting control panel** (cPanel for InfinityFree)
2. **Create a MySQL database:**
   - Name it something like `campuslink_prod`
   - Note the database name, username, and password
3. **Create an FTP account** if not already done
4. **Note your domain name** (e.g., yourdomain.com)

### Step 2: Backup Current Database

```bash
# On your local machine, export the database:
mysqldump -u root campuslinkd > campuslink_backup_$(date +%Y%m%d).sql
```

### Step 3: Upload Files to Hosting

**Option A: Using FTP**
1. Connect to your hosting via FTP (FileZilla recommended)
2. Upload all files EXCEPT `.git`, `node_modules`, `.env.local`
3. Upload the `.env` file with production credentials

**Option B: Using Git (if supported)**
```bash
git push origin main
```

### Step 4: Import Database

1. **Export from local:**
```bash
mysqldump -u root campuslinkd > production_export.sql
```

2. **In hosting control panel:**
   - Go to phpMyAdmin
   - Select your production database
   - Import the SQL file

3. **Update configuration in uploaded `.env` file**

### Step 5: Verify Installation

```bash
# Test by visiting your domain:
https://yourdomain.com

# Test admin panel:
https://yourdomain.com/admin/login

# Check uploads folder exists:
/assets/uploads/

# Verify .htaccess is working:
https://yourdomain.com/config/config.php (should return 404)
```

---

## ENVIRONMENT VARIABLES

### Required Variables

| Variable | Type | Example | Notes |
|----------|------|---------|-------|
| `DB_HOST` | string | `sql.infinityfree.com` | Hosting provider's MySQL host |
| `DB_NAME` | string | `if0_12345678_campuslink` | Database name from control panel |
| `DB_USER` | string | `if0_12345678_user` | Database user from control panel |
| `DB_PASS` | string | `ComplexPassword123!` | Database password |
| `SITE_URL` | string | `https://yourdomain.com` | Your actual domain with HTTPS |
| `SITE_EMAIL` | string | `support@yourdomain.com` | Support email address |
| `APP_ENV` | string | `production` | Environment mode |

### Optional Variables

| Variable | Type | Default | Notes |
|----------|------|---------|-------|
| `PAYSTACK_PUBLIC_KEY` | string | - | For live payments |
| `PAYSTACK_SECRET_KEY` | string | - | For live payments |
| `MAIL_HOST` | string | - | SMTP server for emails |
| `MAIL_PORT` | int | `587` | SMTP port |
| `BCRYPT_COST` | int | `12` | Password hashing cost |
| `APP_DEBUG` | bool | `false` | Show debug info |

---

## DATABASE SETUP

### Auto-Setup (Recommended)

The application includes MASTER_SETUP.sql which creates all tables:

```sql
-- In hosting phpMyAdmin, import:
database/MASTER_SETUP.sql

-- This creates:
- users table
- vendors table
- categories table
- subscriptions table
- payments table
- reviews table
- complaints table
- notifications table
- settings table
```

### Manual Setup

If auto-setup fails:

```bash
# On your local machine:
mysql -u root campuslinkd < database/MASTER_SETUP.sql

# Export after creation:
mysqldump -u root campuslinkd > complete_schema.sql

# Then import to hosting
```

### Seed Data

After creating tables, insert categories:

```sql
INSERT INTO categories (name, icon, description, is_active, sort_order)
VALUES
('Web Design', 'globe', 'Website design and development', 1, 1),
('Graphics Design', 'palette', 'Logo and graphics design', 1, 2),
('Photography', 'camera', 'Photo and videography services', 1, 3),
('Content Writing', 'pen', 'Blog posts and content creation', 1, 4),
('Social Media', 'share2', 'Social media management', 1, 5),
('Coaching', 'book', 'Academic and skill coaching', 1, 6);
```

---

## FILE PERMISSIONS

### Critical Folders Must Be Writable

```bash
# Via FTP/SFTP, set permissions (755 for folders, 644 for files):

chmod 755 assets/uploads/
chmod 755 assets/uploads/documents/
chmod 755 assets/uploads/evidence/
chmod 755 assets/uploads/logos/
chmod 755 assets/uploads/service-photos/
chmod 755 logs/
chmod 755 storage/

# Or through cPanel File Manager:
- Right-click folder → Permissions
- Set to 755 (rwxr-xr-x)
```

### Check in Code

```php
// In bootstrap.php, verify:
if (!is_writable(ROOT_PATH . '/logs')) {
    error_log('WARNING: Logs folder not writable');
}
if (!is_writable(ROOT_PATH . '/assets/uploads')) {
    error_log('WARNING: Uploads folder not writable');
}
```

---

## COMMON ISSUES & FIXES

### Issue 1: Database Connection Error

**Error:** "Database connection failed"

**Fixes:**
1. Verify credentials in `.env` match hosting control panel
2. Check database server is running
3. Verify port 3306 is accessible
4. Create test connection file:

```php
<?php
$host = getenv('DB_HOST');
$db = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
echo "✅ Connection successful";
```

### Issue 2: .htaccess Not Working (404 errors)

**Error:** All routes show 404

**Fixes:**
1. Verify `.htaccess` is uploaded to root
2. Check RewriteBase matches your path:

```apache
# For root domain:
RewriteBase /

# For subdomain:
RewriteBase /campuslink/
```

3. Enable mod_rewrite in hosting (usually enabled by default)

### Issue 3: File Uploads Fail

**Error:** "Permission denied" or "Upload failed"

**Fixes:**
1. Set upload folder permissions to 755
2. Verify upload folders exist:
   ```
   /assets/uploads/
   /assets/uploads/logos/
   /assets/uploads/documents/
   /assets/uploads/service-photos/
   /assets/uploads/evidence/
   ```
3. Check `php.ini` upload_max_filesize (should be ≥5MB)

### Issue 4: Email Not Sending

**Error:** "Failed to send email"

**Fixes:**
1. Configure SMTP in `.env`:
   ```env
   MAIL_HOST=smtp.infinityfree.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@domain.com
   MAIL_PASSWORD=app-specific-password
   ```
2. Test with simple mail():
   ```php
   mail('test@example.com', 'Test', 'Test message');
   ```
3. Check hosting email settings in cPanel

### Issue 5: Session Not Persisting

**Error:** Users logged out after 5 minutes

**Fixes:**
1. Verify session folder is writable
2. Check PHP session.save_path in hosting
3. Increase session timeout:
   ```php
   // In config/config.php:
   ini_set('session.gc_maxlifetime', 86400);  // 24 hours
   ```

### Issue 6: Payments Not Processing

**Error:** Paystack redirects don't work

**Fixes:**
1. Update Paystack keys in `.env` (use LIVE keys, not test keys)
2. Configure webhook URL in Paystack dashboard:
   ```
   https://yourdomain.com/webhook/paystack
   ```
3. Verify `PaymentController::verify()` is correct

---

## SECURITY CHECKLIST

### Before Going Live ✅

- [ ] Change all default passwords
- [ ] Remove `.git`, `.env.local`, sensitive files
- [ ] Enable HTTPS (SSL certificate)
- [ ] Update admin username (not "admin")
- [ ] Set strong database password (20+ chars, mixed)
- [ ] Disable `APP_DEBUG` in production
- [ ] Hide error messages from users
- [ ] Log errors to file, not display
- [ ] Set proper file permissions (no 777)
- [ ] Backup database regularly
- [ ] Monitor logs for suspicious activity
- [ ] Test CSRF protection
- [ ] Verify input sanitization
- [ ] Check SQL injection protection
- [ ] Enable rate limiting
- [ ] Configure firewall rules

### .htaccess Security

```apache
# Block access to sensitive files
<FilesMatch "\.(env|sql|log|md|txt)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block directory listing
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

# Disable PHP execution in upload folders
<Directory "/home/user/public_html/assets/uploads">
    php_flag engine off
    AddType text/plain .php .php3 .php4 .php5 .phtml .phar
</Directory>
```

### Database Security

```sql
-- Create read-only user for backups
CREATE USER 'backup'@'localhost' IDENTIFIED BY 'BackupPass123!';
GRANT SELECT ON campuslink.* TO 'backup'@'localhost';

-- Remove test users
DELETE FROM mysql.user WHERE User='test';
DELETE FROM mysql.user WHERE User='';

-- Update root password
ALTER USER 'root'@'localhost' IDENTIFIED BY 'RootPass123!';
```

---

## PERFORMANCE OPTIMIZATION

### Image Optimization

```bash
# Convert images to WebP (smaller file size)
cwebp image.jpg -o image.webp

# Compress further
imagemin *.png --out-dir=optimized/

# Result: ~30% smaller files
```

### Browser Caching

```apache
# In .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
</IfModule>
```

### Database Optimization

```sql
-- Add indexes for faster queries
ALTER TABLE vendors ADD INDEX idx_status (status);
ALTER TABLE vendors ADD INDEX idx_category (category_id);
ALTER TABLE vendors ADD INDEX idx_vendor_type (vendor_type);
ALTER TABLE reviews ADD INDEX idx_vendor (vendor_id);
ALTER TABLE reviews ADD INDEX idx_status (status);
ALTER TABLE complaints ADD INDEX idx_vendor (vendor_id);
```

### PHP Optimization

```php
// In config/config.php for production:
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600);  // 1 hour

// Use opcache
if (extension_loaded('zend opcache')) {
    opcache_reset();
}
```

---

## MONITORING & MAINTENANCE

### Weekly Tasks

- [ ] Check error logs: `/logs/error.log`
- [ ] Verify backups are running
- [ ] Monitor disk usage
- [ ] Test critical functions (login, payment, upload)

### Monthly Tasks

- [ ] Review user complaints
- [ ] Check database size
- [ ] Optimize database tables
- [ ] Update vendor status (expired subscriptions)
- [ ] Archive old logs
- [ ] Security audit

### Setup Log Monitoring

```php
// In logs/ directory, create .htaccess to hide:
<FilesMatch "\.log$">
    Order allow,deny
    Deny from all
</FilesMatch>

// Setup log rotation (via cron):
0 0 * * * gzip /home/user/public_html/logs/error.log
```

### Automated Backups

```bash
# Create backup script: backup.sh
#!/bin/bash
DB_NAME="campuslink_prod"
DB_USER="if0_12345678_user"
DB_PASS="password"
DATE=$(date +%Y%m%d_%H%M%S)

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > /home/user/backups/db_$DATE.sql

# Add to crontab to run daily:
0 2 * * * /home/user/backup.sh
```

---

## CRON JOBS SETUP

### Subscription Expiry Check

```php
// File: scripts/subscription_cron.php
php /home/user/public_html/scripts/subscription_cron.php

// Add to cPanel Cron Jobs:
# Run every day at 2 AM
0 2 * * * php /home/user/public_html/scripts/subscription_cron.php >> /home/user/logs/cron.log 2>&1
```

---

## DOMAIN & DNS SETUP

### Transfer Domain to Truehost

1. **Order domain** on Truehost (₦2000-5000/year)
2. **Point DNS to hosting:**
   ```
   A Record: yourdomain.com → Hosting IP (from cPanel)
   CNAME: www → yourdomain.com
   MX Record: (Mail server if using hosting email)
   ```
3. **Setup SSL certificate:**
   - cPanel → AutoSSL (automatic)
   - Or use Let's Encrypt (free)

### Propagation Time

- DNS changes take 24-48 hours to fully propagate
- Test with: `nslookup yourdomain.com`

---

## TROUBLESHOOTING QUICK REFERENCE

| Problem | Solution |
|---------|----------|
| 404 errors | Check .htaccess, verify RewriteBase |
| Database connection | Verify .env credentials, test connection |
| Uploads fail | Check folder permissions (755), verify size limits |
| Emails not sending | Configure SMTP in .env, test mail() function |
| Session issues | Clear browser cookies, check session folder writable |
| Slow site | Optimize images, add database indexes, enable caching |
| HTTPS not working | Purchase SSL, point DNS, restart Apache |
| Admin panel 403 | Check .htaccess isn't blocking, verify permissions |

---

## SUPPORT & RESOURCES

- **Hosting Support:** InfinityFree Forum (infinityfree.net)
- **PHP Issues:** PHP.net documentation
- **MySQL Help:** MySQL official docs
- **Paystack Issues:** Paystack Help Center
- **Domain Issues:** Truehost Support

---

## VERSION HISTORY

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Apr 2026 | Initial production setup guide |

---

**Last Reviewed:** April 30, 2026  
**Next Review:** July 30, 2026  
**Maintained By:** CampusLink Development Team
