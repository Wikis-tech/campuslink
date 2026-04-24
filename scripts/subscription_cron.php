<?php
/**
 * Cron job to send expiry reminders and deactivate expired subscriptions
 * Run this daily at 9 AM: 0 9 * * * php /path/to/this/script.php
 */

define('CAMPUSLINK', true);
require_once __DIR__ . '/../core/bootstrap.php';

require_once __DIR__ . '/../core/Logger.php';

$vendorModel = new VendorModel();
$subModel = new SubscriptionModel();

// Send expiry reminders (10 days before expiry)
Logger::log('CRON_START', 'Starting expiry reminder cron job');
$vendorModel->sendExpiryReminders(10);
Logger::log('CRON_REMINDERS', 'Expiry reminders sent');

// Deactivate expired subscriptions
Logger::log('CRON_CHECK', 'Checking for expired subscriptions');
$expiredVendors = $vendorModel->getExpired();

foreach ($expiredVendors as $vendor) {
    Logger::log('CRON_DEACTIVATE', "Deactivating expired vendor: {$vendor['business_name']} (ID: {$vendor['id']})");

    // Update vendor status to inactive (but keep account accessible)
    $vendorModel->deactivate($vendor['id']);

    // Send notification
    Notification::sendToVendor(
        $vendor['id'],
        'Subscription Expired',
        'Your subscription has expired. Your business is no longer visible in browse and category listings. ' .
        'Please renew your subscription to regain visibility.',
        Notification::TYPE_EXPIRY,
        'vendor/subscription'
    );

    // Send email if available
    $email = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
    if ($email) {
        $mailer = new Mailer();
        $mailer->sendExpiryNotification(
            $email,
            $vendor['full_name'],
            $vendor['business_name'],
            $vendor['expiry_date']
        );
    }
}

Logger::log('CRON_COMPLETE', 'Expired subscriptions processed. Cron job completed.');