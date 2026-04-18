<?php
define('CAMPUSLINK', true);

echo '<h1>CampusLink — Post-Fix Diagnosis</h1><hr>';

echo '<h3>Core Files</h3>';
$files = [
    'core/bootstrap.php',
    'core/DB.php',
    'core/Router.php',
    'core/Session.php',
    'core/CSRF.php',
    'core/Auth.php',
    'core/AdminAuth.php',
    'core/Mailer.php',
    'core/helpers.php',
    'config/config.php',
    'config/database.php',
];
$allExist = true;
foreach ($files as $f) {
    $exists = file_exists(__DIR__ . '/' . $f);
    if (!$exists) $allExist = false;
    echo $exists
        ? "<span style='color:green'>✓ EXISTS: {$f}</span><br>"
        : "<span style='color:red'>✗ MISSING: {$f}</span><br>";
}

echo '<br><h3>Bootstrap Test</h3>';
try {
    require_once __DIR__ . '/core/bootstrap.php';
    echo '<span style="color:green">✓ Bootstrap loaded OK</span><br>';
    echo 'SITE_URL: <strong>' . SITE_URL . '</strong><br>';
    echo 'DB_HOST: <strong>' . DB_HOST . '</strong><br>';
    echo 'SCHOOL_NAME: <strong>' . SCHOOL_NAME . '</strong><br>';
} catch (Throwable $e) {
    echo '<span style="color:red">✗ Bootstrap ERROR: ' . $e->getMessage() . '</span><br>';
    echo 'File: ' . $e->getFile() . '<br>';
    echo 'Line: ' . $e->getLine() . '<br>';
    exit;
}

echo '<br><h3>Database Test</h3>';
try {
    $db = DB::getInstance();
    echo '<span style="color:green">✓ Database connected OK</span><br>';

    // Try a simple query
    $tables = $db->rows("SHOW TABLES");
    echo 'Tables found: <strong>' . count($tables) . '</strong><br>';
    if (!empty($tables)) {
        foreach ($tables as $t) {
            echo '&nbsp;&nbsp;→ ' . array_values($t)[0] . '<br>';
        }
    } else {
        echo '<span style="color:orange">⚠ No tables yet — run schema.sql in phpMyAdmin</span><br>';
    }
} catch (Throwable $e) {
    echo '<span style="color:red">✗ DB ERROR: ' . $e->getMessage() . '</span><br>';
}

echo '<br><h3>Controllers Check</h3>';
$controllers = [
    'controllers/HomeController.php',
    'controllers/AuthController.php',
    'controllers/VendorController.php',
    'controllers/UserController.php',
    'controllers/BrowseController.php',
    'controllers/PageController.php',
];
foreach ($controllers as $f) {
    echo file_exists(__DIR__ . '/' . $f)
        ? "<span style='color:green'>✓ {$f}</span><br>"
        : "<span style='color:red'>✗ MISSING: {$f}</span><br>";
}

echo '<br><h3>Views Check</h3>';
$views = [
    'views/layouts/main.php',
    'views/layouts/auth.php',
    'views/layouts/dashboard.php',
    'views/home/index.php',
    'views/auth/login.php',
    'views/auth/register.php',
    'views/vendor/register-select.php',
    'views/vendor/register-student.php',
    'views/admin/layouts/admin.php',
    'views/admin/login.php',
];
foreach ($views as $f) {
    echo file_exists(__DIR__ . '/' . $f)
        ? "<span style='color:green'>✓ {$f}</span><br>"
        : "<span style='color:red'>✗ MISSING: {$f}</span><br>";
}

echo '<hr><p style="color:gray;font-size:0.8rem;">Delete test.php when done.</p>';