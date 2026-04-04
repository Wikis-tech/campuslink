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

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$fields = ['first_name','last_name','school_email','personal_email','phone','department','level','password'];
$missing = array_filter($fields, fn($f) => empty(trim($body[$f] ?? '')));
if ($missing) Security::jsonError('Missing required fields: ' . implode(', ', $missing));

$firstName     = Security::clean($body['first_name']);
$lastName      = Security::clean($body['last_name']);
$schoolEmail   = strtolower(trim($body['school_email']));
$personalEmail = strtolower(trim($body['personal_email']));
$phone         = preg_replace('/[^0-9+]/', '', $body['phone']);
$department    = Security::clean($body['department']);
$level         = Security::clean($body['level']);
$password      = $body['password'];

if (!filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) Security::jsonError('Invalid school email address.');
if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) Security::jsonError('Invalid personal email address.');
if ($schoolEmail === $personalEmail) Security::jsonError('School and personal email must be different.');
if (strlen($password) < 8) Security::jsonError('Password must be at least 8 characters.');
if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) Security::jsonError('Invalid phone number format.');
if (empty($body['terms_accepted'])) Security::jsonError('You must accept the Terms & Conditions.');
if (empty($body['otp_verified'])) Security::jsonError('Phone number OTP verification is required.');

$rlKey = 'register_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
if (!Security::checkRateLimit($rlKey, 3, 60)) Security::jsonError('Too many registration attempts. Please wait.', 429);

$pdo  = Database::getInstance();
$stmt = $pdo->prepare('SELECT id FROM users WHERE school_email = ? OR personal_email = ? OR phone = ? LIMIT 1');
$stmt->execute([$schoolEmail, $personalEmail, $phone]);
if ($stmt->fetch()) Security::jsonError('An account with this email or phone already exists.');

try {
    $pdo->beginTransaction();
    $passwordHash = Security::hashPassword($password);
    $emailToken   = bin2hex(random_bytes(32));
    $pdo->prepare('INSERT INTO users (first_name,last_name,school_email,personal_email,phone,phone_verified,department,level,password_hash,email_token) VALUES (?,?,?,?,?,1,?,?,?,?)')
        ->execute([$firstName, $lastName, $schoolEmail, $personalEmail, $phone, $department, $level, $passwordHash, $emailToken]);
    $userId = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO terms_acceptance (user_id,terms_type,terms_version,ip_address,user_agent) VALUES (?,?,?,?,?)')
        ->execute([$userId, 'user', $config['app']['terms_version'], $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)]);
    $pdo->commit();
    Security::jsonResponse(['success' => true, 'message' => 'Account created! Please check your email to verify your account.'], 201);
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('[Campuslink] Registration failed: ' . $e->getMessage());
    Security::jsonError('Registration failed. Please try again.', 500);
}