<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();

$body  = json_decode(file_get_contents('php://input'), true) ?? [];
$otp   = Security::clean($body['otp'] ?? '');
$phone = Security::clean($body['phone'] ?? '');

if (!$otp || strlen($otp) !== 6 || !ctype_digit($otp)) {
    Security::jsonError('Please enter a valid 6-digit OTP.');
}

if (!isset($_SESSION['pending_otp'], $_SESSION['pending_otp_expiry'], $_SESSION['pending_otp_phone'])) {
    Security::jsonError('No OTP found. Please request a new one.');
}

// Check expiry
if (strtotime($_SESSION['pending_otp_expiry']) < time()) {
    unset($_SESSION['pending_otp'], $_SESSION['pending_otp_phone'], $_SESSION['pending_otp_expiry']);
    Security::jsonError('OTP has expired. Please request a new one.');
}

// Check phone matches
if (!empty($phone) && $phone !== $_SESSION['pending_otp_phone']) {
    Security::jsonError('Phone number mismatch.');
}

// Verify OTP
if (!password_verify($otp, $_SESSION['pending_otp'])) {
    Security::jsonError('Invalid OTP. Please check and try again.');
}

// Mark as verified
$_SESSION['otp_verified']       = true;
$_SESSION['otp_verified_phone'] = $_SESSION['pending_otp_phone'];

unset($_SESSION['pending_otp'], $_SESSION['pending_otp_phone'], $_SESSION['pending_otp_expiry']);

Security::jsonResponse([
    'success' => true,
    'message' => 'Phone number verified successfully.',
]);