# Campuslink — Complete Setup & Edit Guide
## Fix Error 500 + Everything You Need to Configure

---

## URGENT: Why You're Getting Error 500

Error 500 is almost always caused by one of these. Check in this order:

### Step 1 — Enable PHP Error Display (Temporarily)
Create a file called `test.php` in your root with:
```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "PHP is working. Version: " . phpversion();
```
Visit it in your browser. If it shows, PHP works. Now visit your index.php — any fatal errors will now show instead of blank 500.

**Delete `test.php` when done.**

### Step 2 — Most Common 500 Causes

| Cause | Fix |
|-------|-----|
| Wrong DB credentials | Edit `includes/config.php` — db section |
| Database doesn't exist | Create it in phpMyAdmin |
| PHPMailer not found | PHPMailer files missing from `vendor_libs/phpmailer/src/` |
| Session path not writable | Contact InfinityFree support or use `/tmp` |
| PHP version too old | InfinityFree uses PHP 8.x — check extensions |
| `.htaccess` rewrite error | Comment out rewrite rules one by one |
| Missing `uploads/` folder | Create it manually with 755 permissions |
| `bootstrap.php` path wrong | Check all `require_once` paths |

### Step 3 — InfinityFree Specific
InfinityFree has restrictions:
- No `exec()`, `shell_exec()`, `system()` — we don't use these ✓
- Session path may be restricted — add to config.php `session_save_path('/tmp')`
- `.htaccess` rewrites may need `Options +FollowSymLinks`

Add to TOP of `.htaccess`:
```apache
Options +FollowSymLinks
Options -Indexes
```

---

## PART 1 — REQUIRED EDITS BEFORE ANYTHING WORKS

### 1.1 — `includes/config.php`

Open this file and edit every value marked EDIT:
```php
'app' => [
    'url'   => 'http://campuslinkd.infinityfreeapp.com', // EDIT: your InfinityFree URL
    'env'   => 'development',   // EDIT to 'production' when ready
    'debug' => true,            // EDIT to false in production
],

'db' => [
    'host' => 'sql304.epizy.com',  // EDIT: InfinityFree gives you this in cPanel
    'port' => 3306,
    'name' => 'epiz_XXXXXXX_campuslink', // EDIT: InfinityFree DB name format
    'user' => 'epiz_XXXXXXX',           // EDIT: InfinityFree DB username
    'pass' => 'yourpassword',            // EDIT: DB password you set
],

'mail' => [
    'password' => 'xxxx xxxx xxxx xxxx', // EDIT: Gmail App Password (see section 3)
],

'admin' => [
    'password_hash' => '$2y$12$...',  // EDIT: Generate this (see section 4)
],
```

### 1.2 — InfinityFree Database Details

Log in to your InfinityFree account:
1. Go to **Control Panel → MySQL Databases**
2. Create a database — note the full name (format: `epiz_XXXXXXX_campuslink`)
3. Create a MySQL user and assign all privileges
4. Note the MySQL hostname shown on that page (e.g., `sql304.epizy.com`)
5. These are your `host`, `name`, `user`, `pass` values

### 1.3 — Import the Database Schema

1. In InfinityFree cPanel → **phpMyAdmin**
2. Select your database from the left panel
3. Click **Import** tab
4. Select `includes/schema.sql`
5. Click **Go**

---

## PART 2 — PHPMAILER INSTALLATION

You need PHPMailer files in `vendor_libs/phpmailer/src/`

### Option A — Manual Download (Recommended for InfinityFree)

1. Go to: https://github.com/PHPMailer/PHPMailer
2. Click **Code → Download ZIP**
3. Extract the ZIP
4. Inside the extracted folder, find the `src/` folder
5. Copy these 3 files:
   - `PHPMailer.php`
   - `SMTP.php`
   - `Exception.php`
6. Upload them to your server at: `vendor_libs/phpmailer/src/`

Your folder should look like: