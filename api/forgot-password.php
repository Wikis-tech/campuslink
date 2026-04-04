<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Check if user exists
    $stmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Don't reveal if email exists or not for security
        echo json_encode(['success' => true, 'message' => 'If an account with that email exists, a password reset link has been sent.']);
        exit;
    }

    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Update user with reset token
    $updateStmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
    $updateStmt->execute([$resetToken, $expires, $user['id']]);

    // Send reset email
    $resetLink = BASE_URL . "/reset-password?token=" . $resetToken;
    $subject = "Reset Your Campuslink Password";
    $message = "
    <h2>Password Reset Request</h2>
    <p>Hi {$user['first_name']},</p>
    <p>You requested a password reset for your Campuslink account.</p>
    <p>Click the link below to reset your password:</p>
    <p><a href='{$resetLink}' style='background: #0b3d91; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
    <p>This link will expire in 1 hour.</p>
    <p>If you didn't request this reset, please ignore this email.</p>
    <p>Best regards,<br>Campuslink Team</p>
    ";

    $mailer = new Mailer();
    $mailSent = $mailer->sendMail($email, $subject, $message);

    if ($mailSent) {
        Security::logActivity('password_reset_requested', 'Password reset email sent', $user['id']);
        echo json_encode(['success' => true, 'message' => 'If an account with that email exists, a password reset link has been sent.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send reset email. Please try again.']);
    }

} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>