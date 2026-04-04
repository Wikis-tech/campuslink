<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();
$session  = Security::requireAuth('user');
$userId   = (int)$session['user_id'];

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$vendorId = Security::int($body['vendor_id'] ?? 0);
$rating   = Security::int($body['rating']    ?? 0);
$text     = Security::clean($body['text']    ?? '');

if (!$vendorId) Security::jsonError('Invalid vendor.');
if ($rating < 1 || $rating > 5) Security::jsonError('Rating must be between 1 and 5.');
if (strlen($text) < 20) Security::jsonError('Review must be at least 20 characters.');
if (strlen($text) > 1000) Security::jsonError('Review must not exceed 1000 characters.');

// Rate limit
if (!Security::checkRateLimit('review_' . $userId, 5, 60)) {
    Security::jsonError('You are submitting reviews too quickly.', 429);
}

try {
    $pdo = Database::getInstance();

    // Check vendor exists and is active
    $stmt = $pdo->prepare("SELECT id FROM vendors WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$vendorId]);
    if (!$stmt->fetch()) Security::jsonError('Vendor not found.', 404);

    // One review per user per vendor
    $check = $pdo->prepare('SELECT id FROM reviews WHERE user_id = ? AND vendor_id = ? LIMIT 1');
    $check->execute([$userId, $vendorId]);
    if ($check->fetch()) {
        Security::jsonError('You have already submitted a review for this vendor.');
    }

    $pdo->prepare(
        'INSERT INTO reviews (user_id, vendor_id, rating, body, status) VALUES (?,?,?,?,?)'
    )->execute([$userId, $vendorId, $rating, $text, 'pending']);

    Security::jsonResponse([
        'success' => true,
        'message' => 'Review submitted! It will appear after admin approval.',
    ]);

} catch (\Throwable $e) {
    error_log('[CL SubmitReview] ' . $e->getMessage());
    Security::jsonError('Failed to submit review.', 500);
}