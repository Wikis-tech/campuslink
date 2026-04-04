<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';
Security::setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();

$config = APP_CONFIG;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

$firstName     = Security::clean($body['first_name'] ?? '');
$lastName      = Security::clean($body['last_name'] ?? '');
$schoolEmail   = strtolower(trim($body['school_email'] ?? ''));
$personalEmail = strtolower(trim($body['personal_email'] ?? ''));
$phone         = preg_replace('/[^0-9+]/', '', $body['phone'] ?? '');
$department    = Security::clean($body['department'] ?? '');
$level         = Security::clean($body['level'] ?? '');
$password      = $body['password'] ?? '';

// Validate required
$required = ['first_name' => $firstName, 'last_name' => $lastName, 'school_email' => $schoolEmail, 'personal_email' => $personalEmail, 'phone' => $phone, 'department' => $department, 'level' => $level, 'password' => $password];
foreach ($required as $field => $val) {
    if (!$val) Security::jsonError("Field '{$field}' is required.");
}

// School email format: surname.firstname@student.uat.edu.ng
if (!Security::validateSchoolEmail($schoolEmail, $config['school'])) {
    Security::jsonError('School email must follow the format: surname.firstname@student.uat.edu.ng (e.g. doe.john@student.uat.edu.ng)');
}

// Personal email
if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
    Security::jsonError('Enter a valid personal email address.');
}
if ($schoolEmail === $personalEmail) {
    Security::jsonError('School email and personal email must be different.');
}

// Password
if (strlen($password) < 8) Security::jsonError('Password must be at least 8 characters.');

// Phone
if (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
    Security::jsonError('Enter a valid phone number (10-15 digits, with or without country code).');
}

// Terms
if (empty($body['terms_accepted'])) Security::jsonError('You must accept the Terms & Conditions.');
if (empty($body['otp_verified']))   Security::jsonError('Phone number OTP verification is required.');

// Rate limit
if (!Security::checkRateLimit('register_' . md5($_SERVER['REMOTE_ADDR'] ?? ''), 3, 60)) {
    Security::jsonError('Too many registration attempts. Please wait before trying again.', 429);
}

$pdo = Database::getInstance();

// Duplicate check
$stmt = $pdo->prepare('SELECT id FROM users WHERE school_email = ? OR personal_email = ? OR phone = ? LIMIT 1');
$stmt->execute([$schoolEmail, $personalEmail, $phone]);
if ($stmt->fetch()) {
    Security::jsonError('An account with this email or phone number already exists.');
}

// Create user
try {
    $pdo->beginTransaction();

    $passwordHash = Security::hashPassword($password);
    $emailToken   = Security::generateToken(32);

    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, school_email, personal_email, phone, phone_verified, department, level, password_hash, email_token) VALUES (?,?,?,?,?,1,?,?,?,?)');
    $stmt->execute([$firstName, $lastName, $schoolEmail, $personalEmail, $phone, $department, $level, $passwordHash, $emailToken]);
    $userId = (int)$pdo->lastInsertId();

    // Terms acceptance log
    $pdo->prepare('INSERT INTO terms_acceptance (user_id, terms_type, terms_version, ip_address, user_agent) VALUES (?,?,?,?,?)')
        ->execute([$userId, 'user', $config['app']['terms_version'], $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)]);

    $pdo->commit();

    // Send verification email to PERSONAL email (not school email)
    $fullName = $firstName . ' ' . $lastName;
    $mailSent = Mailer::sendVerification($personalEmail, $fullName, $emailToken);

    if (!$mailSent) {
        error_log("[CL Register] Verification email failed for user #{$userId} to {$personalEmail}");
    }

    Security::jsonResponse([
        'success' => true,
        'message' => 'Account created successfully! A verification link has been sent to ' . $personalEmail . '. Please verify your email before logging in.',
    ], 201);

} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('[CL Register] Failed: ' . $e->getMessage());
    Security::jsonError('Registration failed due to a server error. Please try again.', 500);
}