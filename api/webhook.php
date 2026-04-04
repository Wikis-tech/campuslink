<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

// Webhook endpoint for payment processor
// This handles asynchronous payment confirmations

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? ''; // Example for Stripe

// Verify webhook signature (implementation depends on payment processor)
$verified = true; // Placeholder - implement actual verification

if (!$verified) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$data = json_decode($payload, true);

try {
    $db = Database::getInstance()->getConnection();

    if ($data['type'] === 'payment.succeeded') {
        $paymentId = $data['data']['object']['id'];
        $amount = $data['data']['object']['amount'] / 100; // Convert cents to dollars
        $customerId = $data['data']['object']['customer'];

        // Find user by customer ID or payment metadata
        $userStmt = $db->prepare("SELECT id FROM users WHERE email = ?"); // Adjust based on how you store customer ID
        // Implementation depends on your payment integration

        // Update transaction status
        $updateStmt = $db->prepare("UPDATE transactions SET status = 'completed', processed_at = NOW() WHERE transaction_id = ?");
        $updateStmt->execute([$paymentId]);

        // Activate subscription if applicable
        // Send confirmation email, etc.

        Security::logActivity('webhook_payment', "Webhook payment processed: $paymentId", null);
    }

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing failed']);
}
?>