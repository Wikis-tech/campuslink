<?php
declare(strict_types=1);

if (php_sapi_name() !== 'cli') exit('CLI access only.');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$config = require __DIR__ . '/config.php';
$pdo    = Database::getInstance();
$now    = new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos'));
$nowStr = $now->format('Y-m-d H:i:s');
$today  = $now->format('Y-m-d');

$notifyDays = $config['subscription']['notify_days_before'];

echo "[Campuslink Cron] Started: {$nowStr}\n";

foreach ($notifyDays as $days) {
    $targetDate = $now->modify("+{$days} days")->format('Y-m-d');
    $notifType  = "sub_expiring_{$days}d";

    $stmt = $pdo->prepare("
        SELECT s.id AS sub_id, s.vendor_id, s.expires_at, v.business_name, v.personal_email
        FROM subscriptions s
        JOIN vendors v ON v.id = s.vendor_id
        WHERE s.status = 'active'
          AND DATE(s.expires_at) = ?
          AND NOT EXISTS (
              SELECT 1 FROM notifications n
              WHERE n.recipient_type = 'vendor'
                AND n.recipient_id = s.vendor_id
                AND n.type = ?
                AND DATE(n.created_at) = ?
          )
    ");
    $stmt->execute([$targetDate, $notifType, $today]);
    $expiring = $stmt->fetchAll();

    foreach ($expiring as $row) {
        $pdo->prepare("INSERT INTO notifications (recipient_type,recipient_id,type,title,message) VALUES ('vendor',?,?,?,?)")
            ->execute([$row['vendor_id'], $notifType, "Subscription expiring in {$days} day" . ($days > 1 ? 's' : ''), "Your Campuslink subscription for {$row['business_name']} expires on " . substr($row['expires_at'], 0, 10) . ". Renew now to keep your listing active."]);
        echo "[NOTIFY] Vendor #{$row['vendor_id']} ({$row['business_name']}) — {$days}d warning sent\n";
    }
}

$stmt = $pdo->prepare("
    SELECT s.id AS sub_id, s.vendor_id, v.business_name
    FROM subscriptions s
    JOIN vendors v ON v.id = s.vendor_id
    WHERE s.status = 'active' AND s.grace_ends_at < ?
");
$stmt->execute([$nowStr]);
$graceExpired = $stmt->fetchAll();

foreach ($graceExpired as $row) {
    $pdo->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?")->execute([$row['sub_id']]);
    $pdo->prepare("UPDATE vendors SET status = 'expired' WHERE id = ? AND status = 'active'")->execute([$row['vendor_id']]);
    $pdo->prepare("INSERT INTO notifications (recipient_type,recipient_id,type,title,message) VALUES ('vendor',?,'sub_expired','Subscription Expired',?)")
        ->execute([$row['vendor_id'], "Your subscription for {$row['business_name']} has expired. Renew to reactivate your listing."]);
    echo "[EXPIRE] Vendor #{$row['vendor_id']} ({$row['business_name']}) — deactivated\n";
}

$pdo->query("UPDATE vendors v SET complaint_count = (SELECT COUNT(*) FROM complaints c WHERE c.vendor_id = v.id AND c.status IN ('open','under_review','resolved'))");

echo "[Campuslink Cron] Completed: " . date('Y-m-d H:i:s') . "\n";