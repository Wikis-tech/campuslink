<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();
$session  = Security::requireAuth('user');
$userId   = (int)$session['user_id'];

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$vendorId = Security::int($body['vendor_id'] ?? 0);

if (!$vendorId) Security::jsonError('Invalid vendor.');

try {
    $pdo = Database::getInstance();

    // Check vendor exists
    $stmt = $pdo->prepare('SELECT id FROM vendors WHERE id = ? AND status = "active" LIMIT 1');
    $stmt->execute([$vendorId]);
    if (!$stmt->fetch()) Security::jsonError('Vendor not found.', 404);

    // Toggle save
    $check = $pdo->prepare('SELECT id FROM saved_vendors WHERE user_id = ? AND vendor_id = ? LIMIT 1');
    $check->execute([$userId, $vendorId]);
    $existing = $check->fetch();

    if ($existing) {
        $pdo->prepare('DELETE FROM saved_vendors WHERE user_id = ? AND vendor_id = ?')
            ->execute([$userId, $vendorId]);
        Security::jsonResponse(['success' => true, 'saved' => false, 'message' => 'Vendor removed from saved list.']);
    } else {
        $pdo->prepare('INSERT INTO saved_vendors (user_id, vendor_id) VALUES (?,?)')
            ->execute([$userId, $vendorId]);
        Security::jsonResponse(['success' => true, 'saved' => true, 'message' => 'Vendor saved to your list.']);
    }

} catch (\Throwable $e) {
    error_log('[CL SaveVendor] ' . $e->getMessage());
    Security::jsonError('Failed to update saved vendors.', 500);
}