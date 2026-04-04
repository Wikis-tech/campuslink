<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if (empty($action) || !$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid action or ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    switch ($action) {
        case 'approve_vendor':
            $stmt = $db->prepare("UPDATE vendors SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            Security::logActivity('admin_action', "Approved vendor ID: $id", $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'message' => 'Vendor approved successfully']);
            break;

        case 'reject_vendor':
            $stmt = $db->prepare("UPDATE vendors SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);
            Security::logActivity('admin_action', "Rejected vendor ID: $id", $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'message' => 'Vendor rejected']);
            break;

        case 'approve_review':
            $stmt = $db->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            Security::logActivity('admin_action', "Approved review ID: $id", $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'message' => 'Review approved']);
            break;

        case 'reject_review':
            $stmt = $db->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);
            Security::logActivity('admin_action', "Rejected review ID: $id", $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'message' => 'Review rejected']);
            break;

        case 'resolve_complaint':
            $stmt = $db->prepare("UPDATE complaints SET status = 'resolved', resolved_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            Security::logActivity('admin_action', "Resolved complaint ID: $id", $_SESSION['user']['id']);
            echo json_encode(['success' => true, 'message' => 'Complaint resolved']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }

} catch (Exception $e) {
    error_log("Admin action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Action failed']);
}
?>