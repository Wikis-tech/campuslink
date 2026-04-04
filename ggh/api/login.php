<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';

$config = require __DIR__ . '/../includes/config.php';
Security::init($config);
Security::setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$email     = strtolower(trim(Security::clean($body['email'] ?? '')));
$password  = $body['password'] ?? '';
$loginType = Security::clean($body['login_type'] ?? 'user');
$remember  = !empty($body['remember']);

if (!$email || !$password) Security::jsonError('Email and password are required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) Security::jsonError('Invalid email format.');

$rateLimitKey = 'login_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
$sec = $config['security'];
if (!Security::checkRateLimit($rateLimitKey, $sec['max_login_attempts'], $sec['lockout_minutes'])) {
    logLoginAttempt(null, $email, $loginType, 'locked', 'Rate limited');
    Security::jsonError("Too many login attempts. Please wait {$sec['lockout_minutes']} minutes.", 429);
}

$pdo    = Database::getInstance();
$entity = null;

if ($loginType === 'user') {
    $stmt = $pdo->prepare('SELECT id, password_hash, is_active, is_blacklisted, first_name, last_name FROM users WHERE (school_email = ? OR personal_email = ?) AND email_verified = 1 LIMIT 1');
    $stmt->execute([$email, $email]);
    $entity = $stmt->fetch();
} elseif ($loginType === 'vendor') {
    $stmt = $pdo->prepare("SELECT id, password_hash, status, business_name FROM vendors WHERE (school_email = ? OR personal_email = ?) AND email_verified = 1 LIMIT 1");
    $stmt->execute([$email, $email]);
    $entity = $stmt->fetch();
    if ($entity && $entity['status'] === 'suspended') {
        logLoginAttempt($entity['id'], $email, $loginType, 'failed', 'Account suspended');
        Security::jsonError('Your vendor account has been suspended.', 403);
    }
} elseif ($loginType === 'admin') {
    $stmt = $pdo->prepare('SELECT id, password_hash, is_active, role, full_name FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $entity = $stmt->fetch();
    if ($entity && !$entity['is_active']) Security::jsonError('Admin account is inactive.', 403);
}

$dummyHash    = '$2y$12$invalidhashfortimingnnnnnnnnnnnnnnnnnnnnn';
$hashToVerify = $entity['password_hash'] ?? $dummyHash;

if (!$entity || !Security::verifyPassword($password, $hashToVerify)) {
    logLoginAttempt($entity['id'] ?? null, $email, $loginType, 'failed', 'Bad credentials');
    Security::jsonError('Invalid email or password.', 401);
}

if ($loginType === 'user') {
    if (!$entity['is_active']) Security::jsonError('Your account has been deactivated.', 403);
    if ($entity['is_blacklisted']) Security::jsonError('Your account has been blacklisted.', 403);
}

session_regenerate_id(true);
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';
$now = date('Y-m-d H:i:s');

if ($loginType === 'user') {
    $_SESSION['user_id']   = $entity['id'];
    $_SESSION['user_name'] = $entity['first_name'] . ' ' . $entity['last_name'];
    $_SESSION['role']      = 'user';
    $pdo->prepare('UPDATE users SET last_login_at=?, last_login_ip=? WHERE id=?')->execute([$now, $ip, $entity['id']]);
    $redirect = '/user/dashboard.html';
} elseif ($loginType === 'vendor') {
    $_SESSION['vendor_id']   = $entity['id'];
    $_SESSION['vendor_name'] = $entity['business_name'];
    $_SESSION['role']        = 'vendor';
    $pdo->prepare('UPDATE vendors SET last_login_at=?, last_login_ip=? WHERE id=?')->execute([$now, $ip, $entity['id']]);
    $redirect = '/vendor/dashboard.html';
} else {
    $_SESSION['admin_id']   = $entity['id'];
    $_SESSION['admin_name'] = $entity['full_name'];
    $_SESSION['admin_role'] = $entity['role'];
    $_SESSION['role']       = 'admin';
    $pdo->prepare('UPDATE admin_users SET last_login_at=? WHERE id=?')->execute([$now, $entity['id']]);
    $redirect = '/admin/dashboard.html';
}

if ($remember) {
    $token = bin2hex(random_bytes(32));
    setcookie('cl_remember', $token, ['expires' => time() + (86400 * $config['session']['remember_days']), 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);
}

logLoginAttempt($entity['id'], $email, $loginType, 'success', null);
Security::jsonResponse(['success' => true, 'redirect' => $redirect]);

function logLoginAttempt(?int $entityId, string $email, string $role, string $status, ?string $reason): void {
    try {
        $pdo = Database::getInstance();
        $pdo->prepare('INSERT INTO login_logs (entity_type,entity_id,email,ip_address,user_agent,status,fail_reason) VALUES (?,?,?,?,?,?,?)')
            ->execute([$role, $entityId, $email, $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), $status, $reason]);
    } catch (Throwable $e) {
        error_log('[Campuslink] Login log failed: ' . $e->getMessage());
    }
}