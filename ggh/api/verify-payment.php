<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Security.php';

$config = require __DIR__ . '/../includes/config.php';
Security::init($config);
Security::setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);

$session  = Security::requireAuth('vendor');
$vendorId = (int)$session['vendor_id'];

Security::requireCSRF();

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || empty($body['reference'])) Security::jsonError('Payment reference is required.');

$reference = Security::clean($body['reference']);
if (!preg_match('/^[A-Za-z0-9_\-]{5,100}$/', $reference)) Security::jsonError('Invalid payment reference format.');

$pdo = Database::getInstance();

$stmt = $pdo->prepare('SELECT id, status FROM payments WHERE paystack_ref = ? LIMIT 1');
$stmt->execute([$reference]);
$existing = $stmt->fetch();
if ($existing && $existing['status'] === 'success') Security::jsonError('This reference has already been processed.', 409);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $config['paystack']['base_url'] . '/transaction/verify/' . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $config['paystack']['secret_key'], 'Cache-Control: no-cache'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) Security::jsonError('Payment gateway verification failed.', 502);

$ps = json_decode($response, true);
if (!$ps || !$ps['status'] || $ps['data']['status'] !== 'success') {
    $pdo->prepare('INSERT INTO payments (vendor_id,plan_id,paystack_ref,amount_kobo,status,ip_address,raw_response) VALUES (?,0,?,0,"failed",?,?) ON DUPLICATE KEY UPDATE status="failed"')
        ->execute([$vendorId, $reference, $_SERVER['REMOTE_ADDR'] ?? '', json_encode($ps)]);
    Security::jsonError('Payment was not successful. Status: ' . ($ps['data']['status'] ?? 'unknown'));
}

$txn     = $ps['data'];
$amtKobo = (int)$txn['amount'];
$channel = $txn['channel'] ?? 'card';
$meta    = $txn['metadata'] ?? [];

$planSlug = Security::clean((string)($meta['plan'] ?? ''));
$stmt = $pdo->prepare('SELECT * FROM plans WHERE slug = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$planSlug]);
$plan = $stmt->fetch();
if (!$plan) Security::jsonError('Plan not found or inactive.');

if ((int)$plan['price_kobo'] !== $amtKobo) {
    error_log("[Campuslink] Amount mismatch ref={$reference}: expected {$plan['price_kobo']}, got {$amtKobo}");
    Security::jsonError('Payment amount does not match the selected plan.');
}

$stmt = $pdo->prepare('SELECT id, status FROM vendors WHERE id = ? LIMIT 1');
$stmt->execute([$vendorId]);
$vendor = $stmt->fetch();
if (!$vendor) Security::jsonError('Vendor account not found.', 404);

try {
    $pdo->beginTransaction();
    $now          = date('Y-m-d H:i:s');
    $durationDays = (int)$plan['duration_days'];
    $expiresAt    = date('Y-m-d H:i:s', strtotime("+{$durationDays} days"));
    $graceEndsAt  = date('Y-m-d H:i:s', strtotime("+{$durationDays} days +2 days"));

    $pdo->prepare('INSERT INTO subscriptions (vendor_id,plan_id,payment_ref,amount_kobo,status,started_at,expires_at,grace_ends_at) VALUES (?,?,?,?,"active",?,?,?)')
        ->execute([$vendorId, $plan['id'], $reference, $amtKobo, $now, $expiresAt, $graceEndsAt]);
    $subId = (int)$pdo->lastInsertId();

    $pdo->prepare('INSERT INTO payments (vendor_id,plan_id,subscription_id,paystack_ref,amount_kobo,currency,channel,ip_address,verified_at,status,raw_response) VALUES (?,?,?,?,?,"NGN",?,?,?,"success",?)')
        ->execute([$vendorId, $plan['id'], $subId, $reference, $amtKobo, $channel, $_SERVER['REMOTE_ADDR'] ?? '', $now, json_encode($txn)]);

    $pdo->prepare("INSERT INTO notifications (recipient_type,recipient_id,type,title,message) VALUES ('vendor',?,?,?,?)")
        ->execute([$vendorId, 'payment_confirmed', 'Payment Confirmed', "Your {$plan['name']} plan payment of ₦" . number_format($amtKobo / 100) . " confirmed. Active until {$expiresAt}."]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('[Campuslink] Subscription creation failed: ' . $e->getMessage());
    Security::jsonError('Server error activating subscription. Reference: ' . $reference, 500);
}

Security::jsonResponse(['success' => true, 'message' => 'Payment verified and subscription activated.', 'plan' => $plan['name'], 'expires_at' => $expiresAt, 'reference' => $reference]);