<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();

$phone = Security::clean(json_decode(file_get_contents('php://input'), true)['phone'] ?? '');
$phone = preg_replace('/[^0-9+]/', '', $phone);

if (!$phone || !preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
    Security::jsonError('Invalid phone number.');
}

// Rate limit per phone
if (!Security::checkRateLimit('otp_' . md5($phone), 3, 10)) {
    Security::jsonError('Too many OTP requests. Please wait before trying again.', 429);
}

// Generate OTP
$otp    = Security::generateOTP();
$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Store in session temporarily
$_SESSION['pending_otp']        = password_hash($otp, PASSWORD_BCRYPT, ['cost' => 10]);
$_SESSION['pending_otp_phone']  = $phone;
$_SESSION['pending_otp_expiry'] = $expiry;

/*
 * PRODUCTION: Integrate SMS gateway here
 * Options for Nigeria:
 *   - Termii: https://termii.com/
 *   - Africa's Talking: https://africastalking.com/
 *   - Twilio: https://twilio.com/
 *
 * Example with Termii:
 * $response = file_get_contents('https://api.ng.termii.com/api/sms/send', false, stream_context_create([
 *     'http' => [
 *         'method'  => 'POST',
 *         'header'  => 'Content-Type: application/json',
 *         'content' => json_encode([
 *             'to'      => $phone,
 *             'from'    => 'Campuslink',
 *             'sms'     => "Your Campuslink OTP is: {$otp}. Expires in 10 minutes.",
 *             'type'    => 'plain',
 *             'channel' => 'generic',
 *             'api_key' => 'YOUR_TERMII_API_KEY',
 *         ])
 *     ]
 * ]));
 */

// For development: log OTP to error log
error_log("[CL OTP] Phone: {$phone} | OTP: {$otp} | Expires: {$expiry}");

Security::jsonResponse([
    'success' => true,
    'message' => 'OTP sent to your phone number.',
    // Only include OTP in development for testing
    'dev_otp' => APP_CONFIG['app']['debug'] ? $otp : null,
]);