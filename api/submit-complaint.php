<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Security::jsonError('Method not allowed.', 405);
Security::requireCSRF();
$session  = Security::requireAuth('user');
$userId   = (int)$session['user_id'];

$vendorId    = Security::int($_POST['vendor_id'] ?? 0);
$category    = Security::clean($_POST['category'] ?? '');
$description = Security::clean($_POST['description'] ?? '');

$validCats = ['fraud','service_quality','no_show','harassment','false_info','overcharging','other'];
if (!$vendorId) Security::jsonError('Invalid vendor.');
if (!in_array($category, $validCats, true)) Security::jsonError('Invalid complaint category.');
if (strlen($description) < 50) Security::jsonError('Description must be at least 50 characters.');
if (strlen($description) > 2000) Security::jsonError('Description must not exceed 2000 characters.');

// Rate limit: 3 complaints per hour
if (!Security::checkRateLimit('complaint_' . $userId, 3, 60)) {
    Security::jsonError('You are submitting complaints too quickly.', 429);
}

try {
    $pdo = Database::getInstance();

    // Check vendor exists
    $stmt = $pdo->prepare("SELECT id FROM vendors WHERE id = ? AND status IN ('active','pending') LIMIT 1");
    $stmt->execute([$vendorId]);
    if (!$stmt->fetch()) Security::jsonError('Vendor not found.', 404);

    // Handle evidence file uploads
    $evidencePaths = [];
    $cfg           = APP_CONFIG;
    $allowedMime   = $cfg['upload']['allowed_mime'];
    $maxSize       = $cfg['upload']['max_size'];
    $uploadDir     = UPLOAD_PATH . 'complaints/';

    for ($i = 0; $i < 3; $i++) {
        if (!empty($_FILES["evidence_{$i}"]) && $_FILES["evidence_{$i}"]['error'] !== UPLOAD_ERR_NO_FILE) {
            $valid = Security::validateUpload($_FILES["evidence_{$i}"], $allowedMime, $maxSize);
            if (!$valid['valid']) Security::jsonError("File " . ($i+1) . ": " . $valid['error']);
            $saved = Security::saveUpload($_FILES["evidence_{$i}"], $uploadDir, 'complaint_');
            if ($saved) $evidencePaths[] = 'complaints/' . $saved;
        }
    }

    $pdo->prepare(
        'INSERT INTO complaints (user_id, vendor_id, category, description, evidence_paths, status)
         VALUES (?,?,?,?,?,?)'
    )->execute([
        $userId, $vendorId, $category, $description,
        !empty($evidencePaths) ? json_encode($evidencePaths) : null,
        'open',
    ]);

    // Update vendor complaint count
    $pdo->prepare(
        'UPDATE vendors SET complaint_count = complaint_count + 1 WHERE id = ?'
    )->execute([$vendorId]);

    $refNum = 'CL-' . str_pad((string)($pdo->lastInsertId()), 5, '0', STR_PAD_LEFT);

    Security::jsonResponse([
        'success'    => true,
        'message'    => 'Complaint submitted. Reference: ' . $refNum,
        'reference'  => $refNum,
    ]);

} catch (\Throwable $e) {
    error_log('[CL SubmitComplaint] ' . $e->getMessage());
    Security::jsonError('Failed to submit complaint.', 500);
}