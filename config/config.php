<?php
/**
 * CampusLink - Main Application Configuration
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

// Load environment variables from .env if present
if (!function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): void
    {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2) + ['', '']);
            if ($name === '') {
                continue;
            }

            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}
loadEnvFile(__DIR__ . '/../.env');

if (defined('VALID_PLANS')) { return; }

// ============================================================
// ENVIRONMENT
// ============================================================
define('APP_ENV',   'development');
define('APP_DEBUG', true);

// ============================================================
// SITE SETTINGS
// ============================================================
define('SITE_NAME',        'CampusLink');
define('SITE_TAGLINE',     'Find Trusted Campus Services Instantly');

$siteUrl = getenv('SITE_URL') ?: '';
if ($siteUrl === '' || (!str_starts_with($siteUrl, 'http://') && !str_starts_with($siteUrl, 'https://'))) {
    $scheme = 'http';
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
        (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')) {
        $scheme = 'https';
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = '';
    if (isset($_SERVER['SCRIPT_NAME'])) {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    }
    $siteUrl = $scheme . '://' . $host . ($basePath !== '' ? $basePath : '');
}
define('SITE_URL', rtrim($siteUrl, '/'));
define('SITE_DESCRIPTION', 'CampusLink connects students with verified vendors within the University Of Africa Toru-Orua. Browse campus services, contact vendors directly via phone or WhatsApp.');
define('SITE_KEYWORDS',    'campus services, university vendors, student services, campus directory, University Of Africa');
define('SITE_AUTHOR',      'CampusLink');
define('SITE_VERSION',     '1.0.0');

// ============================================================
// CONTACT & EMAIL
// ============================================================
define('SITE_EMAIL',    'campuslinkd@gmail.com');
define('CONTACT_EMAIL', 'campuslinkd@gmail.com');
define('ADMIN_EMAIL',   'campuslinkd@gmail.com');
define('SUPPORT_EMAIL', 'campuslinkd@gmail.com');
define('CONTACT_PHONE', '+2348137268006');
define('SITE_PHONE',    '+2349049096991');

// ============================================================
// URL / PATH CONSTANTS
// ============================================================
define('PUBLIC_PATH',   SITE_URL);
define('ASSETS_URL',    SITE_URL . '/assets');
define('UPLOADS_URL',   SITE_URL . '/assets/uploads');
define('IMAGES_URL',    SITE_URL . '/assets/images');

// ============================================================
// SOCIAL MEDIA
// ============================================================
define('FACEBOOK_URL',  '#');
define('TWITTER_URL',   '#');
define('INSTAGRAM_URL', '#');
define('WHATSAPP_URL',  '#');

// ============================================================
// SCHOOL SETTINGS
// ============================================================
define('SCHOOL_NAME',         'University Of Africa Toru-Orua');
define('SCHOOL_SHORT_NAME',   'UAT');
define('SCHOOL_DOMAIN',       'my.uat.ed.ng');
define('SCHOOL_EMAIL_DOMAIN', 'student.uat.edu.ng');
define('SCHOOL_EMAIL',        'registrar@uat.edu.ng');
define('SCHOOL_WEBSITE',      'www.uat.edu.ng');
define('SCHOOL_LOGO',         'school-logo.png');

// ============================================================
// DATABASE
// ============================================================
define('DB_HOST',    '127.0.0.1');
define('DB_NAME',    'campuslinkd');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    '3306');

// ============================================================
// PAYSTACK
// ============================================================
define('PAYSTACK_PUBLIC_KEY',     getenv('PAYSTACK_PUBLIC_KEY') ?: 'pk_test_your_public_key_here');
define('PAYSTACK_SECRET_KEY',     getenv('PAYSTACK_SECRET_KEY') ?: 'sk_test_your_secret_key_here');
define('PAYSTACK_BASE_URL',       'https://api.paystack.co');
define('PAYSTACK_CALLBACK_URL',   SITE_URL . '/vendor/payment/verify');
define('PAYSTACK_WEBHOOK_SECRET', getenv('PAYSTACK_WEBHOOK_SECRET') ?: 'your_webhook_secret_here');

// ============================================================
// SUBSCRIPTION PLAN AMOUNTS (in Kobo for Paystack)
// ============================================================
define('STUDENT_BASIC_AMOUNT',      0);
define('STUDENT_PREMIUM_AMOUNT',    250000);
define('STUDENT_FEATURED_AMOUNT',   500000);
define('COMMUNITY_BASIC_AMOUNT',    300000);
define('COMMUNITY_PREMIUM_AMOUNT',  600000);
define('COMMUNITY_FEATURED_AMOUNT',1000000);

define('VALID_PLANS', serialize([
    'student' => [
        'basic'    => ['amount' => 0,       'label' => 'Student Free',    'naira' => 0],
        'premium'  => ['amount' => 250000,  'label' => 'Student Boost',   'naira' => 2500],
        'featured' => ['amount' => 500000,  'label' => 'Student Featured','naira' => 5000],
    ],
    'community' => [
        'basic'    => ['amount' => 300000,  'label' => 'Community Basic',    'naira' => 3000],
        'premium'  => ['amount' => 600000,  'label' => 'Community Premium',  'naira' => 6000],
        'featured' => ['amount' => 1000000, 'label' => 'Community Featured', 'naira' => 10000],
    ],
]));

// ============================================================
// SUBSCRIPTION / SEMESTER
// ============================================================
define('SEMESTER_DAYS',      180);
define('GRACE_PERIOD_DAYS',    2);
define('REMINDER_DAYS_14',    14);
define('REMINDER_DAYS_7',      7);
define('REMINDER_DAYS_2',      2);

// ============================================================
// SESSION
// ============================================================
define('SESSION_NAME',       'campuslink_session');
define('SESSION_LIFETIME',    3600);
define('SESSION_INACTIVITY',  1800);
define('REMEMBER_ME_DAYS',      30);

// ============================================================
// FILE UPLOADS
// ============================================================
define('UPLOAD_MAX_SIZE',   5242880);
define('UPLOAD_BASE_PATH',  __DIR__ . '/../uploads/');

define('ALLOWED_IMAGE_TYPES', serialize([
    'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif',
]));

define('ALLOWED_DOC_TYPES', serialize([
    'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf',
]));

define('UPLOAD_LOGOS',    'assets/uploads/logos/');
define('UPLOAD_ID_CARDS', 'assets/uploads/id-cards/');
define('UPLOAD_SELFIES',  'assets/uploads/selfies/');
define('UPLOAD_SERVICE',  'assets/uploads/service-photos/');
define('UPLOAD_CAC',      'assets/uploads/cac-certificates/');
define('UPLOAD_GOV_IDS',  'uploads/gov-ids/');
define('UPLOAD_EVIDENCE', 'uploads/complaint-evidence/');

// ============================================================
// SECURITY
// ============================================================
define('CSRF_TOKEN_LENGTH',  64);
define('CSRF_TOKEN_EXPIRY',  3600);
define('BCRYPT_COST',        12);
define('LOGIN_MAX_ATTEMPTS',  5);
define('LOGIN_LOCKOUT_TIME',  900);
define('OTP_EXPIRY_SECONDS',  600);
define('OTP_RESEND_COOLDOWN',  60);
define('OTP_MAX_ATTEMPTS',     3);
define('OTP_LENGTH',           6);

// ============================================================
// EMAIL VERIFICATION
// ============================================================
define('EMAIL_VERIFY_EXPIRY', 86400); // 24 hours
define('PASSWORD_RESET_EXPIRY',  3600);

// ============================================================
// REVIEWS & COMPLAINTS
// ============================================================
define('MAX_REVIEW_LENGTH',       1000);
define('MAX_COMPLAINT_LENGTH',    2000);
define('COMPLAINT_TRIGGER_COUNT',    3);
define('MIN_REVIEW_RATING',          1);
define('MAX_REVIEW_RATING',          5);

// ============================================================
// PAGINATION
// ============================================================
define('VENDORS_PER_PAGE',      12);
define('REVIEWS_PER_PAGE',      10);
define('ADMIN_ITEMS_PER_PAGE',  25);

// ============================================================
// CURRENCY
// ============================================================
define('CURRENCY',        'NGN');
define('CURRENCY_SYMBOL', '₦');

// ============================================================
// TERMS & LEGAL
// ============================================================
define('TERMS_VERSION',   '1.0');
define('TERMS_DATE',      '2025-01-01');
define('PRIVACY_VERSION', '1.0');

// ============================================================
// LOGGING
// ============================================================
define('LOG_PATH',     __DIR__ . '/../logs/');
define('LOG_ERRORS',   LOG_PATH . 'error.log');
define('LOG_AUDIT',    LOG_PATH . 'audit.log');
define('LOG_PAYMENTS', LOG_PATH . 'payment.log');
define('LOG_ENABLED',  true);

// ============================================================
// SMTP
// ============================================================
define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',        587);
define('SMTP_USERNAME',   'campuslinkd@gmail.com');
define('SMTP_PASSWORD',   'fiof ofgd wxir akco');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'campuslinkd@gmail.com');
define('SMTP_FROM_NAME',  'CampusLink');
define('SMTP_ENABLED',    true);

// ============================================================
// SMS
// ============================================================
define('SMS_PROVIDER',  'termii');
define('SMS_API_KEY',   'your_sms_api_key_here');
define('SMS_SENDER_ID', 'CampusLink');
define('SMS_BASE_URL',  'https://api.ng.termii.com/api/sms/send');
define('AT_USERNAME',   'sandbox');
define('AT_API_KEY',    'your_at_api_key');

// ============================================================
// ERROR DISPLAY
// ============================================================
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_ERRORS);
}

// ============================================================
// TIMEZONE
// ============================================================
date_default_timezone_set('Africa/Lagos');

// ============================================================
// HELPER FUNCTIONS
// ============================================================

function getPlanAmount(string $vendorType, string $plan): int {
    if (empty($vendorType) || empty($plan)) return 0;
    $plans = unserialize(VALID_PLANS);
    return $plans[$vendorType][$plan]['amount'] ?? 0;
}

function getPlanNaira(string $vendorType, string $plan): int {
    if (empty($vendorType) || empty($plan)) return 0;
    $plans = unserialize(VALID_PLANS);
    return $plans[$vendorType][$plan]['naira'] ?? 0;
}

function getPlanLabel(string $vendorType, string $plan): string {
    if (empty($vendorType) || empty($plan)) return 'Unknown Plan';
    $plans = unserialize(VALID_PLANS);
    return $plans[$vendorType][$plan]['label'] ?? 'Unknown Plan';
}

function isValidPlan(string $vendorType, string $plan): bool {
    if (empty($vendorType) || empty($plan)) return false;
    $plans = unserialize(VALID_PLANS);
    return isset($plans[$vendorType][$plan]);
}

function formatWhatsAppNumber(string $raw): string {
    $digits = preg_replace('/[^0-9]/', '', $raw);
    
    // If already in international format (234XXXXXXXXX with 13+ digits), return as is
    if (str_starts_with($digits, '234') && strlen($digits) >= 13) {
        return substr($digits, 0, 13);
    }
    
    // Convert Nigerian format (0XXXXXXXXXX) to international (234XXXXXXXXX)
    if (str_starts_with($digits, '0') && strlen($digits) === 11) {
        return '234' . substr($digits, 1);
    }
    
    // Handle 10-digit numbers (assume Nigerian area code 9)
    if (strlen($digits) === 10) {
        return '234' . $digits;
    }
    
    // Return digits as-is for other formats
    return $digits;
}

function siteUrl(string $path = ''): string {
    $path = ltrim($path, '/');
    $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
    $basePath = ltrim($basePath, '/');

    if ($basePath !== '' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
        $path = ltrim($path, '/');
    }

    return SITE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path, int $code = 302): void {
    $url = (strpos($path, 'http') === 0) ? $path : siteUrl($path);
    header("Location: $url", true, $code);
    exit;
}

function generateToken(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}

function currentUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function getClientIP(): string {
    $keys = [
        'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR',
    ];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function slugify(string $str): string {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}