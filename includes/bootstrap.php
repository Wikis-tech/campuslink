<?php
declare(strict_types=1);

// ============================================================
// CAMPUSLINK — BOOTSTRAP
// Initializes config, classes, session, timezone
// ============================================================

// Prevent direct access to sensitive dirs
$allowedPaths = ['/api/', '/pages/', '/user/', '/vendor/', '/admin/', '/'];
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Load config first
$config = require __DIR__ . '/config.php';

// Define base URL for routing (handles subdirectory installations like /campuslink/)
$basePath = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
if ($basePath === '/' || $basePath === '\\') $basePath = '';
define('BASE_PATH', rtrim($basePath, '/'));

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Debug mode
if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Autoload classes
spl_autoload_register(function(string $class): void {
    $classFile = __DIR__ . "/{$class}.php";
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// PHPMailer manual includes
$mailerBase = __DIR__ . '/../vendor_libs/phpmailer/src/';
if (is_dir($mailerBase)) {
    require_once $mailerBase . 'Exception.php';
    require_once $mailerBase . 'PHPMailer.php';
    require_once $mailerBase . 'SMTP.php';
}

// Load classes
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/helpers.php';

// Initialize security (starts session, sets headers)
Security::init($config);
Security::setHeaders();

// Initialize mailer
Mailer::init($config);

// Global constants
define('APP_CONFIG', $config);
define('UPLOAD_PATH', rtrim(__DIR__ . '/../uploads', '/') . '/');
define('CACHE_PATH',  UPLOAD_PATH . 'cache/webp/');
define('IS_AJAX',    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');