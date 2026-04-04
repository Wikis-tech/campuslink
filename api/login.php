<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';
Security::setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();

$config = APP_CONFIG;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

$email     = strtolower(trim(Security::clean($body['email'] ?? '')));
$password  = $body['password'] ?? '';
$loginType = Security::clean($body['login_type'] ?? 'user');
$remember  = !empty($body['remember']);

if (!$email || !$password) Security::jsonError('Email and password are required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) Security::jsonError('Invalid email format.');

// Rate limit
$rl = 'login_' . md5(($loginType ?? '') . ($email ?? '') . ($_SERVER['REMOTE_ADDR'] ?? ''));
if (!Security::checkRateLimit($rl, $config['security']['max_login_attempts'], $config['security']['lockout_minutes'])) {
    Security::jsonError('Too many login attempts. Please wait ' . $config['security']['lockout_minutes'] . ' minutes.', 429);
}

$pdo    = Database::getInstance();
$entity = null;

switch ($loginType) {
    case 'user':
        $stmt = $pdo->prepare('SELECT id, password_hash, is_active, is_blacklisted, first_name, last_name, email_verified FROM users WHERE (school_email = ? OR personal_email = ?) LIMIT 1');
        $stmt->execute([$email, $email]);
        $entity = $stmt->fetch();
        if ($entity && !$entity['email_verified']) {
            Security::jsonError('Please verify your email address before logging in. Check your personal email for the verification link.');
        }
        if ($entity && !$entity['is_active']) Security::jsonError('Your account has been deactivated. Contact support.', 403);
        if ($entity && $entity['is_blacklisted']) Security::jsonError('Your account has been suspended. Contact support.', 403);
        break;

    case 'vendor':
        $stmt = $pdo->prepare('SELECT id, password_hash, status, business_name, email_verified FROM vendors WHERE (school_email = ? OR personal_email = ?) LIMIT 1');
        $stmt->execute([$email, $email]);
        $entity = $stmt->fetch();
        if ($entity && $entity['status'] === 'suspended') Security::jsonError('Your vendor account is suspended. Contact support.', 403);
        if ($entity && $entity['status'] === 'rejected') Security::jsonError('Your vendor application was rejected. Contact support.', 403);
        break;

    case 'admin':
        // Admin uses config credentials only
        if ($email !== $config['admin']['email']) {
            usleep(500000); // Timing protection
            Security::jsonError('Invalid admin credentials.', 401);
        }
        $dummyHash    = '$2y$12$aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $hashToVerify = $config['admin']['password'] ?: $dummyHash;
        if (!Security::verifyPassword($password, $hashToVerify)) {
            Security::jsonError('Invalid admin credentials.', 401);
        }
        session_regenerate_id(true);
        $_SESSION['admin_id']    = 1;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_role']  = 'super_admin';
        $_SESSION['admin_name']  = 'Super Admin';
        $_SESSION['role']        = 'admin';
        Security::jsonResponse(['success' => true, 'redirect' => '/admin/dashboard']);
        break;
}

// Constant-time comparison
$dummyHash    = '$2y$12$aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
$hashToVerify = $entity['password_hash'] ?? $dummyHash;

if (!$entity || !Security::verifyPassword($password, $hashToVerify)) {
    // Log failed attempt
    try {
        $pdo->prepare('INSERT INTO login_logs (entity_type, entity_id, email, ip_address, user_agent, status, fail_reason) VALUES (?,?,?,?,?,?,?)')
            ->execute([$loginType, $entity['id'] ?? null, $email, $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), 'failed', 'Bad credentials']);
    } catch (Throwable) {}
    Security::jsonError('Invalid email or password.', 401);
}

// Build session
session_regenerate_id(true);
$now = date('Y-m-d H:i:s');
$ip  = $_SERVER['REMOTE_ADDR'] ?? '';

if ($loginType === 'user') {
    $_SESSION['user_id']   = $entity['id'];
    $_SESSION['user_name'] = $entity['first_name'] . ' ' . $entity['last_name'];
    $_SESSION['role']      = 'user';
    $pdo->prepare('UPDATE users SET last_login_at=?, last_login_ip=? WHERE id=?')->execute([$now, $ip, $entity['id']]);
    $redirect = '/user/dashboard';
} else {
    $_SESSION['vendor_id']   = $entity['id'];
    $_SESSION['vendor_name'] = $entity['business_name'];
    $_SESSION['role']        = 'vendor';
    $pdo->prepare('UPDATE vendors SET last_login_at=?, last_login_ip=? WHERE id=?')->execute([$now, $ip, $entity['id']]);
    $redirect = '/vendor/dashboard';
}

// Remember me
if ($remember) {
    setcookie('cl_remember', Security::generateToken(32), [
        'expires'  => time() + (86400 * $config['session']['remember_days']),
        'path'     => '/',
        'secure'   => $config['session']['secure'],
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Log success
try {
    $pdo->prepare('INSERT INTO login_logs (entity_type, entity_id, email, ip_address, user_agent, status) VALUES (?,?,?,?,?,?)')
        ->execute([$loginType, $entity['id'], $email, $ip, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), 'success']);
} catch (Throwable) {}

Security::jsonResponse(['success' => true, 'redirect' => $redirect]);