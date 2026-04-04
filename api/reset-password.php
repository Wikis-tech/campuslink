<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$newPassword = trim($input['password'] ?? '');

if (empty($token) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Token and password are required']);
    exit;
}

if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Find user with valid reset token
    $stmt = $db->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
        exit;
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password and clear reset token
    $updateStmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    $updateStmt->execute([$hashedPassword, $user['id']]);

    Security::logActivity('password_reset', 'Password reset completed', $user['id']);

    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);

} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>