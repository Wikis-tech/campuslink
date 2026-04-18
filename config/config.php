<?php
/**
 * CampusLink - Main Application Configuration
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');
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
define('SITE_URL',         'http://localhost/campuslink');
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
define('SITE_PHONE',    '+2349070725772');

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
define('SCHOOL_LOGO',         'uploads/school-logo/school-logo.webp');

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
define('PAYSTACK_PUBLIC_KEY',     'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxx');
define('PAYSTACK_SECRET_KEY',     'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxx');
define('PAYSTACK_BASE_URL',       'https://api.paystack.co');
define('PAYSTACK_CALLBACK_URL',   SITE_URL . '/vendor/payment/verify');
define('PAYSTACK_WEBHOOK_SECRET', 'your_webhook_secret_here');

// ============================================================
// SUBSCRIPTION PLAN AMOUNTS (in Kobo for Paystack)
// ============================================================
define('STUDENT_BASIC_AMOUNT',      200000);
define('STUDENT_PREMIUM_AMOUNT',    500000);
define('STUDENT_FEATURED_AMOUNT',  1000000);
define('COMMUNITY_BASIC_AMOUNT',    400000);
define('COMMUNITY_PREMIUM_AMOUNT',  700000);
define('COMMUNITY_FEATURED_AMOUNT',1200000);

define('VALID_PLANS', serialize([
    'student' => [
        'basic'    => ['amount' => 200000,  'label' => 'Student Basic',    'naira' => 2000],
        'premium'  => ['amount' => 500000,  'label' => 'Student Premium',  'naira' => 5000],
        'featured' => ['amount' => 1000000, 'label' => 'Student Featured', 'naira' => 10000],
    ],
    'community' => [
        'basic'    => ['amount' => 400000,  'label' => 'Community Basic',    'naira' => 4000],
        'premium'  => ['amount' => 700000,  'label' => 'Community Premium',  'naira' => 7000],
        'featured' => ['amount' => 1200000, 'label' => 'Community Featured', 'naira' => 12000],
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

define('UPLOAD_LOGOS',    'uploads/logos/');
define('UPLOAD_ID_CARDS', 'uploads/id-cards/');
define('UPLOAD_SELFIES',  'uploads/selfies/');
define('UPLOAD_SERVICE',  'uploads/service-photos/');
define('UPLOAD_CAC',      'uploads/cac-certificates/');
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
    $plans = unserialize(VALID_PLANS);
    return $plans[$vendorType][$plan]['amount'] ?? 0;
}

function getPlanNaira(string $vendorType, string $plan): int {
    $plans = unserialize(VALID_PLANS);
    return $plans[$vendorType][$plan]['naira'] ?? 0;
}

function getPlanLabel(string $vendorType, string $plan): string {
    $plans = unserialize(VALID_PLANS);
    return $plans[$vendorType][$plan]['label'] ?? 'Unknown Plan';
}

function isValidPlan(string $vendorType, string $plan): bool {
    $plans = unserialize(VALID_PLANS);
    return isset($plans[$vendorType][$plan]);
}

function siteUrl(string $path = ''): string {
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