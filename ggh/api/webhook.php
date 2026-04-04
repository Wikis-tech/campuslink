<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';

$config = require __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$expected  = hash_hmac('sha512', $payload, $config['paystack']['secret_key']);

if (!hash_equals($expected, $signature)) {
    error_log('[Campuslink Webhook] Invalid signature from ' . ($_SERVER['REMOTE_ADDR'] ?? ''));
    http_response_code(401); exit;
}

$senderIp   = $_SERVER['REMOTE_ADDR'] ?? '';
$allowedIPs = $config['paystack']['webhook_ip_whitelist'];
if (!in_array($senderIp, $allowedIPs, true)) {
    error_log("[Campuslink Webhook] Unauthorized IP: {$senderIp}");
    http_response_code(403); exit;
}

$event = json_decode($payload, true);
if (!$event || empty($event['event'])) { http_response_code(400); exit; }

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['received' => true]);

if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
elseif (ob_get_level()) ob_end_flush();

try {
    $pdo = Database::getInstance();

    switch ($event['event']) {
        case 'charge.success':
            $txn  = $event['data'];
            $ref  = $txn['reference'];
            $meta = $txn['metadata'] ?? [];

            $stmt = $pdo->prepare('SELECT id, status FROM payments WHERE paystack_ref = ? LIMIT 1');
            $stmt->execute([$ref]);
            $existing = $stmt->fetch();
            if ($existing && $existing['status'] === 'success') break;

            $planSlug = $meta['plan'] ?? '';
            $stmt = $pdo->prepare('SELECT * FROM plans WHERE slug = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$planSlug]);
            $plan = $stmt->fetch();
            if (!$plan) break;

            $vendorId = (int)($meta['vendor_id'] ?? 0);
            if (!$vendorId) break;

            $amtKobo = (int)$txn['amount'];
            if ($amtKobo !== (int)$plan['price_kobo']) { error_log("[Campuslink Webhook] Amount mismatch ref={$ref}"); break; }

            $pdo->beginTransaction();
            $now       = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days"));
            $graceAt   = date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days +2 days"));

            $pdo->prepare('INSERT IGNORE INTO subscriptions (vendor_id,plan_id,payment_ref,amount_kobo,status,started_at,expires_at,grace_ends_at) VALUES (?,?,?,?,"active",?,?,?)')
                ->execute([$vendorId, $plan['id'], $ref, $amtKobo, $now, $expiresAt, $graceAt]);
            $subId = (int)$pdo->lastInsertId();

            $pdo->prepare('INSERT IGNORE INTO payments (vendor_id,plan_id,subscription_id,paystack_ref,amount_kobo,currency,channel,verified_at,status,raw_response) VALUES (?,?,?,?,?,"NGN",?,?,"success",?)')
                ->execute([$vendorId, $plan['id'], $subId, $ref, $amtKobo, $txn['channel'] ?? '', $now, json_encode($txn)]);

            $pdo->commit();
            break;

        default:
            error_log('[Campuslink Webhook] Ignored event: ' . $event['event']);
    }
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('[Campuslink Webhook] Error: ' . $e->getMessage());
}