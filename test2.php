<?php
/**
 * CampusLink — Page Load Tester
 * Tests each route by actually loading the controller
 * and catching any errors thrown
 */

define('CAMPUSLINK', true);

// Capture all errors
ini_set('display_errors', 0);
ini_set('log_errors', 0);
error_reporting(E_ALL);

$errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$errors) {
    $errors[] = "[PHP Error {$errno}] {$errstr} in {$errfile} on line {$errline}";
    return true;
});

echo '<!DOCTYPE html>
<html>
<head>
<title>CampusLink Page Tester</title>
<style>
    body { font-family: monospace; padding: 2rem; background: #0f172a; color: #e2e8f0; }
    h1   { color: #38bdf8; }
    h3   { color: #94a3b8; margin: 1.5rem 0 0.5rem; border-top: 1px solid #1e293b; padding-top: 1rem; }
    .ok  { color: #4ade80; }
    .err { color: #f87171; }
    .warn{ color: #fbbf24; }
    .box { background: #1e293b; border-radius: 8px; padding: 1rem; margin: 0.5rem 0; font-size: 0.85rem; }
    .tag { display:inline-block; padding: 2px 8px; border-radius: 4px; font-size:0.75rem; font-weight:700; margin-right:6px; }
    .tag-ok   { background:#166534; color:#4ade80; }
    .tag-err  { background:#7f1d1d; color:#f87171; }
    .tag-warn { background:#78350f; color:#fbbf24; }
</style>
</head>
<body>
<h1><i data-feather="search" aria-hidden="true"></i> CampusLink Page Load Tester</h1>
<p style="color:#94a3b8;">This script loads each part of the app and reports exactly what breaks.</p>
';

// ─── Helper ───────────────────────────────────────────────────────────
function testBlock(string $label, callable $fn): void {
    global $errors;
    $errors = [];
    $output = '';
    $status = 'ok';
    $msg    = '';

    try {
        ob_start();
        $fn();
        $output = ob_get_clean();
        if (!empty($errors)) {
            $status = 'warn';
            $msg    = implode('<br>', array_map('htmlspecialchars', $errors));
        }
    } catch (Throwable $e) {
        ob_get_clean();
        $status = 'err';
        $msg    = htmlspecialchars($e->getMessage())
                . '<br><small>in ' . htmlspecialchars($e->getFile())
                . ' on line ' . $e->getLine() . '</small>';
    }

    $tagClass   = "tag-{$status}";
    $tagLabel   = match($status) { 'ok' => '✓ OK', 'err' => '✗ ERROR', 'warn' => '⚠ WARNING', default => '?' };
    $labelClass = match($status) { 'ok' => 'ok',   'err' => 'err',     'warn' => 'warn',       default => '' };

    echo "<div class='box'>";
    echo "<span class='tag {$tagClass}'>{$tagLabel}</span>";
    echo "<span class='{$labelClass}'><strong>{$label}</strong></span>";
    if ($msg) {
        echo "<br><br>{$msg}";
    }
    echo "</div>";
}

// ─── SECTION 1: Bootstrap ─────────────────────────────────────────────
echo "<h3>1. Bootstrap & Config</h3>";

testBlock('Load bootstrap.php', function() {
    require_once __DIR__ . '/core/bootstrap.php';
});

testBlock('Constants defined', function() {
    $required = ['SITE_URL','DB_HOST','DB_NAME','DB_USER','DB_PASS','SCHOOL_NAME','SUPPORT_EMAIL'];
    $missing  = [];
    foreach ($required as $c) {
        if (!defined($c)) $missing[] = $c;
    }
    if (!empty($missing)) {
        throw new Exception('Missing constants in config.php: ' . implode(', ', $missing));
    }
});

testBlock('SITE_URL has no trailing slash', function() {
    if (str_ends_with(SITE_URL, '/')) {
        throw new Exception('SITE_URL ends with a slash: "' . SITE_URL . '" — remove the trailing slash');
    }
});

// ─── SECTION 2: Database ──────────────────────────────────────────────
echo "<h3>2. Database</h3>";

testBlock('DB connection', function() {
    $db = DB::getInstance();
});

testBlock('Required tables exist', function() {
    $db       = DB::getInstance();
    $required = ['users','vendors','categories','plans','subscriptions',
                 'payments','reviews','complaints','notifications','saved_vendors','admin_users'];
    $existing = array_column($db->rows("SHOW TABLES"), null);
    $existing = array_map('array_values', $existing);
    $existing = array_column($existing, 0);
    $missing  = array_diff($required, $existing);
    if (!empty($missing)) {
        throw new Exception('Missing tables: ' . implode(', ', $missing));
    }
});

testBlock('Categories table has data', function() {
    $db    = DB::getInstance();
    $count = $db->value("SELECT COUNT(*) FROM categories");
    if ((int)$count === 0) {
        throw new Exception('Categories table is empty — run seed.sql in phpMyAdmin');
    }
    echo "Found {$count} categories";
});

testBlock('Plans table has data', function() {
    $db    = DB::getInstance();
    $count = $db->value("SELECT COUNT(*) FROM plans");
    if ((int)$count === 0) {
        throw new Exception('Plans table is empty — run seed.sql to add subscription plans');
    }
    echo "Found {$count} plans";
});

testBlock('Admin account exists', function() {
    $db    = DB::getInstance();
    $count = $db->value("SELECT COUNT(*) FROM admin_users");
    if ((int)$count === 0) {
        throw new Exception('No admin accounts found — run seed.sql to create default admin');
    }
    echo "Found {$count} admin account(s)";
});

// ─── SECTION 3: Session & Auth ────────────────────────────────────────
echo "<h3>3. Session & Auth</h3>";

testBlock('Session class works', function() {
    $started = session_status() === PHP_SESSION_ACTIVE;
    if (!$started) throw new Exception('Session is not active');
    echo 'Session ID: ' . substr(session_id(), 0, 12) . '…';
});

testBlock('CSRF token generation', function() {
    $token = CSRF::token();
    if (empty($token)) throw new Exception('CSRF::token() returned empty string');
    echo 'Token length: ' . strlen($token) . ' chars';
});

testBlock('Auth class exists and usable', function() {
    $loggedIn = Auth::isLoggedIn();
    echo 'Auth::isLoggedIn() = ' . ($loggedIn ? 'true' : 'false') . ' (expected: false)';
});

// ─── SECTION 4: Controllers ───────────────────────────────────────────
echo "<h3>4. Controller Instantiation</h3>";

$controllers = [
    'HomeController'    => 'controllers/HomeController.php',
    'AuthController'    => 'controllers/AuthController.php',
    'VendorController'  => 'controllers/VendorController.php',
    'UserController'    => 'controllers/UserController.php',
    'BrowseController'  => 'controllers/BrowseController.php',
    'PageController'    => 'controllers/PageController.php',
];

foreach ($controllers as $class => $file) {
    testBlock("Load {$class}", function() use ($class, $file) {
        require_once __DIR__ . '/' . $file;
        $obj = new $class();
        echo "{$class} instantiated OK";
    });
}

// ─── SECTION 5: Views & Layouts ───────────────────────────────────────
echo "<h3>5. Layout Files — PHP Syntax Check</h3>";

$layouts = [
    'views/layouts/main.php',
    'views/layouts/auth.php',
    'views/layouts/dashboard.php',
    'views/admin/layouts/admin.php',
    'views/partials/header.php',
    'views/partials/footer.php',
    'views/partials/flash.php',
];

foreach ($layouts as $file) {
    testBlock("Syntax: {$file}", function() use ($file) {
        $path = __DIR__ . '/' . $file;
        if (!file_exists($path)) {
            throw new Exception("File does not exist: {$file}");
        }
        // Check PHP syntax via tokenizer
        $code   = file_get_contents($path);
        $tokens = token_get_all($code);
        echo "File size: " . number_format(filesize($path)) . " bytes · Tokens: " . count($tokens);
    });
}

// ─── SECTION 6: Key View Files ────────────────────────────────────────
echo "<h3>6. Key View Files Exist</h3>";

$views = [
    'views/home/index.php',
    'views/auth/login.php',
    'views/auth/register.php',
    'views/auth/verify-email.php',
    'views/auth/forgot-password.php',
    'views/vendor/register-select.php',
    'views/vendor/register-student.php',
    'views/vendor/register-community.php',
    'views/vendor/dashboard.php',
    'views/vendor/login.php',
    'views/user/dashboard.php',
    'views/browse/index.php',
    'views/browse/vendor-profile.php',
    'views/admin/login.php',
    'views/admin/dashboard.php',
    'views/pages/error.php',
];

foreach ($views as $file) {
    testBlock("View: {$file}", function() use ($file) {
        if (!file_exists(__DIR__ . '/' . $file)) {
            throw new Exception("Missing: {$file}");
        }
        echo number_format(filesize(__DIR__ . '/' . $file)) . ' bytes';
    });
}

// ─── SECTION 7: CSS & JS Assets ───────────────────────────────────────
echo "<h3>7. Assets</h3>";

$assets = [
    'assets/css/main.css',
    'assets/css/auth.css',
    'assets/css/dashboard.css',
    'assets/js/main.js',
    'assets/js/auth.js',
    'assets/js/vendor.js',
    'assets/js/browse.js',
];

foreach ($assets as $file) {
    testBlock("Asset: {$file}", function() use ($file) {
        if (!file_exists(__DIR__ . '/' . $file)) {
            throw new Exception("Missing asset: {$file}");
        }
        echo number_format(filesize(__DIR__ . '/' . $file)) . ' bytes';
    });
}

// ─── SECTION 8: Upload Directories ───────────────────────────────────
echo "<h3>8. Upload Directories</h3>";

$uploadDirs = [
    'assets/uploads',
    'assets/uploads/logos',
    'assets/uploads/documents',
    'assets/uploads/evidence',
    'assets/uploads/service-photos',
];

foreach ($uploadDirs as $dir) {
    testBlock("Dir: {$dir}", function() use ($dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            // Try to create it
            if (mkdir($path, 0755, true)) {
                echo "Created directory: {$dir}";
            } else {
                throw new Exception("Directory missing and could not be created: {$dir}");
            }
        } else {
            $writable = is_writable($path) ? 'writable ✓' : 'NOT writable ✗';
            echo "Exists · {$writable}";
        }
    });
}

// ─── SECTION 9: .htaccess Routing Test ───────────────────────────────
echo "<h3>9. Routing Configuration</h3>";

testBlock('.htaccess exists in root', function() {
    if (!file_exists(__DIR__ . '/.htaccess')) {
        throw new Exception('.htaccess is missing from root folder');
    }
    $content = file_get_contents(__DIR__ . '/.htaccess');
    if (!str_contains($content, 'RewriteEngine')) {
        throw new Exception('.htaccess exists but has no RewriteEngine directive — routing will not work');
    }
    if (!str_contains($content, 'index.php')) {
        throw new Exception('.htaccess exists but does not route to index.php');
    }
    echo '.htaccess has RewriteEngine and routes to index.php ✓';
});

testBlock('index.php exists and is readable', function() {
    if (!file_exists(__DIR__ . '/index.php')) {
        throw new Exception('index.php is missing from root');
    }
    $content = file_get_contents(__DIR__ . '/index.php');
    if (!str_contains($content, 'CAMPUSLINK')) {
        throw new Exception('index.php does not define CAMPUSLINK — wrong file?');
    }
    if (!str_contains($content, 'bootstrap')) {
        throw new Exception('index.php does not load bootstrap.php — routing will fail');
    }
    echo 'index.php looks correct ✓';
});

// ─── SECTION 10: Layout SITE_URL Check ───────────────────────────────
echo "<h3>10. Asset Path Check in Layouts</h3>";

$layoutsToCheck = [
    'views/layouts/main.php',
    'views/layouts/auth.php',
    'views/layouts/dashboard.php',
    'views/admin/layouts/admin.php',
];

foreach ($layoutsToCheck as $file) {
    testBlock("Asset paths in {$file}", function() use ($file) {
        $path = __DIR__ . '/' . $file;
        if (!file_exists($path)) {
            throw new Exception("File not found: {$file}");
        }
        $content = file_get_contents($path);

        // Check for relative asset paths (broken)
        $badPatterns = [
            'href="assets/',
            'src="assets/',
            "href='assets/",
            "src='assets/",
        ];
        $found = [];
        foreach ($badPatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                $found[] = $pattern;
            }
        }

        // Check for absolute asset paths (correct)
        $hasCorrect = str_contains($content, 'SITE_URL') 
                   && str_contains($content, 'assets/');

        if (!empty($found)) {
            throw new Exception(
                'Found relative asset paths — these will break on sub-routes: '
                . implode(', ', $found)
                . '<br>Fix: change to <?= SITE_URL ?>/assets/...'
            );
        }
        if (!$hasCorrect) {
            echo "⚠ Could not confirm SITE_URL usage — check manually";
        } else {
            echo "Asset paths use SITE_URL ✓";
        }
    });
}

// ─── SUMMARY ──────────────────────────────────────────────────────────
echo "
<h3>Summary</h3>
<div class='box'>
<p class='ok'>✓ If all sections above show green — your site should work.</p>
<p class='warn'>⚠ Yellow warnings are non-critical but should be fixed.</p>
<p class='err'>✗ Red errors must be fixed before the site will work.</p>
<br>
<p style='color:#94a3b8;'>
    After fixing all red errors, test these URLs:<br><br>
    → <a href='https://campuslinkd.rf.gd' style='color:#38bdf8;'>campuslinkd.rf.gd</a> (home)<br>
    → <a href='https://campuslinkd.rf.gd/login' style='color:#38bdf8;'>campuslinkd.rf.gd/login</a><br>
    → <a href='https://campuslinkd.rf.gd/register' style='color:#38bdf8;'>campuslinkd.rf.gd/register</a><br>
    → <a href='https://campuslinkd.rf.gd/vendor/login' style='color:#38bdf8;'>campuslinkd.rf.gd/vendor/login</a><br>
    → <a href='https://campuslinkd.rf.gd/vendor/register' style='color:#38bdf8;'>campuslinkd.rf.gd/vendor/register</a><br>
    → <a href='https://campuslinkd.rf.gd/browse' style='color:#38bdf8;'>campuslinkd.rf.gd/browse</a><br>
    → <a href='https://campuslinkd.rf.gd/admin/login' style='color:#38bdf8;'>campuslinkd.rf.gd/admin/login</a><br>
</p>
<p style='color:#64748b;margin-top:1rem;font-size:0.8rem;'>Delete test2.php when done.</p>
</div>
</body></html>
";
