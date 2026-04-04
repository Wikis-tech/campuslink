<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    // Log the logout action
    Security::logActivity('logout', 'User logged out', $_SESSION['user']['id']);

    // Clear session
    session_destroy();

    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Logout failed']);
}
?>