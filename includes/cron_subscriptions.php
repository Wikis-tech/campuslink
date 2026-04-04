<?php
/**
 * Daily Cron Job for Subscription Management
 * Run this script daily via cron: 0 0 * * * php /path/to/cron_subscriptions.php
 */

require_once __DIR__ . '/bootstrap.php';

// Check for expired subscriptions
try {
    $db = Database::getInstance()->getConnection();

    // Get expired subscriptions
    $stmt = $db->prepare("
        SELECT s.id, s.vendor_id, s.end_date, v.name as vendor_name, u.email
        FROM subscriptions s
        JOIN vendors v ON s.vendor_id = v.id
        JOIN users u ON v.user_id = u.id
        WHERE s.status = 'active' AND s.end_date < NOW()
    ");
    $stmt->execute();
    $expiredSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expiredSubscriptions as $subscription) {
        // Update subscription status
        $updateStmt = $db->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?");
        $updateStmt->execute([$subscription['id']]);

        // Downgrade vendor to free plan
        $vendorStmt = $db->prepare("UPDATE vendors SET subscription_status = 'free', subscription_expires = NULL WHERE id = ?");
        $vendorStmt->execute([$subscription['vendor_id']]);

        // Send notification email
        $subject = "Your CampusLink Subscription Has Expired";
        $message = "
        <h2>Subscription Expired</h2>
        <p>Dear {$subscription['vendor_name']},</p>
        <p>Your subscription has expired on " . date('F j, Y', strtotime($subscription['end_date'])) . ".</p>
        <p>Your vendor profile has been downgraded to the free plan. To continue enjoying premium features, please renew your subscription.</p>
        <p><a href='" . BASE_URL . "/vendor/subscription.php'>Renew Subscription</a></p>
        <p>Best regards,<br>CampusLink Team</p>
        ";

        $mailer = new Mailer();
        $mailer->sendMail($subscription['email'], $subject, $message);

        // Log the action
        Security::logActivity('subscription_expired', "Subscription ID: {$subscription['id']} expired for vendor: {$subscription['vendor_name']}", $subscription['vendor_id']);
    }

    // Check for subscriptions expiring in 3 days
    $stmt = $db->prepare("
        SELECT s.id, s.vendor_id, s.end_date, v.name as vendor_name, u.email
        FROM subscriptions s
        JOIN vendors v ON s.vendor_id = v.id
        JOIN users u ON v.user_id = u.id
        WHERE s.status = 'active' AND s.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
    ");
    $stmt->execute();
    $expiringSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($expiringSubscriptions as $subscription) {
        // Send reminder email
        $subject = "Your CampusLink Subscription Expires Soon";
        $message = "
        <h2>Subscription Expiring Soon</h2>
        <p>Dear {$subscription['vendor_name']},</p>
        <p>Your subscription will expire on " . date('F j, Y', strtotime($subscription['end_date'])) . ".</p>
        <p>To avoid interruption of premium features, please renew your subscription before it expires.</p>
        <p><a href='" . BASE_URL . "/vendor/subscription.php'>Renew Subscription</a></p>
        <p>Best regards,<br>CampusLink Team</p>
        ";

        $mailer = new Mailer();
        $mailer->sendMail($subscription['email'], $subject, $message);
    }

    echo "Cron job completed successfully. Processed " . count($expiredSubscriptions) . " expired subscriptions and sent " . count($expiringSubscriptions) . " reminders.\n";

} catch (Exception $e) {
    error_log("Cron job error: " . $e->getMessage());
    echo "Cron job failed: " . $e->getMessage() . "\n";
}
?>