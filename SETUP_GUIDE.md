# CampusLink Complete Setup Guide

## 📋 Table of Contents
1. Local Development Setup
2. Starting the Website Locally
3. Hosting Platform Setup
4. Database Setup
5. Configuration Files
6. Cron Job Setup by Platform

---

## Part 1: Local Development Setup (XAMPP)

### Prerequisites
- XAMPP installed (https://www.apachefriends.org/)
- PHP 7.4+ with cURL and JSON extensions
- MySQL 5.7+ or MariaDB

### Step 1: Start XAMPP Services
**Windows:**
1. Open XAMPP Control Panel
2. Click "Start" next to **Apache** (must be running)
3. Click "Start" next to **MySQL** (must be running)
4. Check that both say "Running" in green

**Linux/Mac:**
```bash
sudo /opt/lampp/lampp start
# Or for brew installation
brew services start mysql
brew services start httpd
```

### Step 2: Access phpMyAdmin
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Login (default: username = `root`, no password)

### Step 3: Create Database
1. In phpMyAdmin, click "New" on left sidebar
2. Database name: `campuslink_master`
3. Collation: `utf8mb4_unicode_ci`
4. Click "Create"

### Step 4: Import Master SQL File
1. Select your new `campuslink_master` database
2. Click "Import" tab at top
3. Choose file: `database/MASTER_SETUP.sql`
4. Click "Go"

**✅ Database is now ready!**

---

## Part 2: Starting the Website Locally

### Quick Start
1. **Start XAMPP** (Apache + MySQL running)
2. **Navigate to site:**
   - URL: `http://localhost/campuslink`
   - OR: `http://localhost/campuslink/index.php`

### Troubleshooting Won't Start
| Issue | Solution |
|-------|----------|
| Blank page | Check that Apache is running in XAMPP |
| "Database connection failed" | Make sure MySQL is running and `config/database.php` has correct credentials |
| "File not found" | Make sure your files are in `C:\xampp\htdocs\campuslink\` |
| Port 80 already in use | Change Apache port in XAMPP settings or stop other services |

### Admin Login (Local)
- **URL:** `http://localhost/campuslink/admin`
- **Email:** `admin@campuslink.com`
- **Password:** `Admin@CampusLink2025`

⚠️ **CHANGE THIS PASSWORD IMMEDIATELY in production!**

---

## Part 3: Hosting Platform Setup

### Compatibility Matrix

| Platform | PHP | MySQL | SSH | Cron | Performance |
|----------|-----|-------|-----|------|-------------|
| **InfinityFree** | 7.4-8.1 | 5.7 | ❌ | Limited | ⭐⭐ |
| **TrueHost** | 7.4-8.2 | 5.7+ | ✅ | ✅ | ⭐⭐⭐ |
| **HostNigeria** | 7.4-8.2 | 5.7+ | ✅ | ✅ | ⭐⭐⭐ |
| **NYFree** | 7.4-8.1 | 5.7 | ❌ | Limited | ⭐⭐ |

### Setup Steps (All Platforms)

#### 1. Create Database
**Via cPanel:**
1. Log into cPanel
2. Find "MySQL Databases" or "Database Wizard"
3. Create new database (name it: `campuslink_prod`)
4. Create database user
5. Grant all privileges
6. Note: database name, username, password

#### 2. Upload Files
**Via FTP/SFTP:**
1. Download FileZilla (free FTP client)
2. Connect using FTP credentials from hosting
3. Upload entire `campuslink` folder to `public_html/` or `www/`
4. Directory structure should be:
   ```
   public_html/
   └── campuslink/
       ├── index.php
       ├── config/
       ├── controllers/
       ├── models/
       ├── views/
       └── ... (other folders)
   ```

#### 3. Update Configuration Files
Edit `config/config.php`:
```php
// Update domain and URL
define('SITE_URL', 'https://yourdomain.com/campuslink');
define('SITE_DOMAIN', 'yourdomain.com');

// Update to use environment-based database
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'campuslink_prod');
define('DB_USER', $_ENV['DB_USER'] ?? 'db_username');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'db_password');
```

#### 4. Set File Permissions
**Via SSH:**
```bash
cd public_html/campuslink
chmod 755 .
chmod 755 assets/uploads/
chmod 755 logs/
chmod 644 config/*.php
```

#### 5. Import Database
**Option A: Via cPanel phpMyAdmin**
1. Log into phpMyAdmin
2. Select your database
3. Click "Import"
4. Choose `database/MASTER_SETUP.sql`
5. Click "Go"

**Option B: Via SSH**
```bash
cd public_html/campuslink
mysql -h localhost -u db_username -p db_password campuslink_prod < database/MASTER_SETUP.sql
```
(Replace credentials with your actual database credentials)

#### 6. Update Admin Password
**Via phpMyAdmin:**
1. Select your database
2. Go to `admin_users` table
3. Click Edit on the admin row
4. Generate new password hash:
   - Go to https://www.php.net/password_hash (or use online tool)
   - Enter your new password
   - Cost: 12
   - Copy the hash
5. Paste into password field
6. Click Go

**Or via SSH:**
```bash
mysql -h localhost -u db_username -p db_password campuslink_prod -e "UPDATE admin_users SET password='[NEW_HASH]' WHERE id=1;"
```

---

## Part 4: Platform-Specific Guides

### InfinityFree Setup
✅ **Best for:** Testing, learning, small projects
❌ **Limitations:** No SSH, limited cron, ads

**Setup:**
1. Create free account at ifastnet.com
2. Use File Manager in panel to upload files
3. Import database via phpMyAdmin
4. Edit `config/config.php`:
```php
define('LOG_ENABLED', false); // Disable logging due to storage limits
define('SITE_URL', 'https://username.infinityfree.app/campuslink');
```
5. **Cron Job:** Use their scheduled tasks feature (limited to 1x per day)

### TrueHost Setup
✅ **Best for:** Serious projects, good performance
✅ **Has:** SSH access, flexible cron, better support

**Setup:**
1. Create account at truehost.com.ng
2. SSH into server:
```bash
ssh username@server.truehost.com.ng
```
3. Upload files via SFTP:
```bash
sftp username@server.truehost.com.ng
put -r /path/to/campuslink /home/username/public_html/
```
4. Import database via cPanel phpMyAdmin
5. Set up cron (see Cron Setup section)

### HostNigeria Setup
✅ **Best for:** Nigeria-based hosting, local support
✅ **Has:** Good uptime, fast local servers

**Setup:**
Same as TrueHost - follow SSH/SFTP steps above

### NYFree Setup
✅ **Best for:** Free alternative with decent performance
⚠️ **Note:** Email verification may go to spam

**Setup:**
Same as InfinityFree approach

---

## Part 5: Cron Job Setup by Platform

### TrueHost / HostNigeria (With SSH)
```bash
# SSH into server
ssh username@server

# Edit crontab
crontab -e

# Add this line (runs daily at 9 AM):
0 9 * * * /usr/bin/php /home/username/public_html/campuslink/scripts/subscription_cron.php
```

### InfinityFree / NYFree (Limited Cron)
1. Log into hosting panel
2. Find "Cron Jobs" or "Scheduled Tasks"
3. Create new task:
   - **Time:** Daily at 9 AM
   - **Command:** `php /home/username/public_html/campuslink/scripts/subscription_cron.php`
4. Save

### Manual Alternative (If No Cron Available)
Create file: `trigger_cron.php`
```php
<?php
// Trigger expiry reminders when someone loads homepage
// This works but is not ideal - use actual cron when possible
if (file_exists('scripts/subscription_cron.php')) {
    include 'scripts/subscription_cron.php';
}
?>
```

---

## Part 6: .htaccess Configuration

Create `.htaccess` in campuslink root:
```apache
# Enable mod_rewrite
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /campuslink/
    
    # Disable directory listing
    Options -Indexes
    
    # Force HTTPS (uncomment in production)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Redirect all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Upload restrictions
<FilesMatch "\.(php|php3|php4|php5|phtml|exe|sh|bat|cmd)$">
    Deny from all
</FilesMatch>
```

---

## Part 7: Environment Configuration

Create `.env.local` (never commit to git):
```env
# Database
DB_HOST=localhost
DB_NAME=campuslink_prod
DB_USER=campuslink_user
DB_PASS=your_secure_password

# Email / SMTP
SMTP_ENABLED=true
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_ENCRYPTION=tls

# Paystack
PAYSTACK_SECRET_KEY=sk_live_xxxxxxxxxxxxx
PAYSTACK_PUBLIC_KEY=pk_live_xxxxxxxxxxxxx

# Site
SITE_URL=https://yourdomain.com
LOG_ENABLED=true
```

---

## Troubleshooting

### "Fatal error: Uncaught Exception: Database Error"
**Solution:**
1. Check database credentials in `config/database.php`
2. Verify database exists and user has privileges
3. Check MySQL is running

### "403 Forbidden" error
**Solution:**
1. Check .htaccess syntax
2. Verify file permissions (755 for folders, 644 for files)
3. Contact hosting provider about mod_rewrite

### "COR error" when loading from mobile
**Solution:** Update `SITE_URL` in config to use full domain with https://

### Uploads not working
**Solution:**
1. Check `assets/uploads/` folder permissions (755)
2. Verify PHP upload limits in hosting control panel
3. Check disk space

### Database import fails
**Solution:**
1. Increase PHP timeout: contact support
2. Import step-by-step (Schema first, then Seed data)
3. Try via SSH if available

---

## Security Checklist ✅

- [ ] Change admin password immediately
- [ ] Enable HTTPS (SSL certificate - usually free)
- [ ] Set `define('DEBUG', false);` in production
- [ ] Disable direct SQL access from frontend
- [ ] Configure firewall rules
- [ ] Regular backups enabled
- [ ] Paystack keys are in .env, not in code
- [ ] Upload folder outside web root if possible
- [ ] Remove /admin path from search engines

---

## Next Steps

1. ✅ Set up local development (XAMPP)
2. ✅ Test all features locally
3. ✅ Get hosting account & domain
4. ✅ Upload files to hosting
5. ✅ Import database
6. ✅ Configure .htaccess
7. ✅ Set up cron job
8. ✅ Test in production
9. ✅ Launch! 🚀