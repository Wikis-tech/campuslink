<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$csrf = $_POST['csrf_token'] ?? '';
if (!Security::verifyCSRF($csrf)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Start transaction
    $db->beginTransaction();

    // Create user account
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $verificationToken = bin2hex(random_bytes(32));

    $userStmt = $db->prepare("
        INSERT INTO users (email, password, first_name, last_name, role, verification_token)
        VALUES (?, ?, ?, ?, 'vendor', ?)
    ");
    $userStmt->execute([
        $_POST['email'],
        $hashedPassword,
        $_POST['first_name'],
        $_POST['last_name'],
        $verificationToken
    ]);

    $userId = $db->lastInsertId();

    // Create vendor profile
    $vendorStmt = $db->prepare("
        INSERT INTO vendors (user_id, name, description, category_id, location)
        VALUES (?, ?, ?, ?, ?)
    ");
    $vendorStmt->execute([
        $userId,
        $_POST['business_name'],
        $_POST['description'],
        $_POST['category_id'],
        $_POST['location']
    ]);

    $vendorId = $db->lastInsertId();

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logoPath = Security::handleFileUpload($_FILES['logo'], 'logos', $userId);
        if ($logoPath) {
            $db->prepare("UPDATE vendors SET logo = ? WHERE id = ?")->execute([$logoPath, $vendorId]);
        }
    }

    // Send verification email
    $verificationLink = BASE_URL . "/verify-email?token=" . $verificationToken;
    $subject = "Verify Your Campuslink Vendor Account";
    $message = "
    <h2>Welcome to Campuslink!</h2>
    <p>Hi {$_POST['first_name']},</p>
    <p>Thank you for registering as a vendor. Please verify your email address to activate your account.</p>
    <p><a href='{$verificationLink}' style='background: #0b3d91; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
    <p>Best regards,<br>Campuslink Team</p>
    ";

    $mailer = new Mailer();
    $mailer->sendMail($_POST['email'], $subject, $message);

    $db->commit();

    Security::logActivity('vendor_registration', 'New vendor registered', $userId);

    echo json_encode(['success' => true, 'message' => 'Vendor account created successfully. Please check your email to verify your account.']);

} catch (Exception $e) {
    $db->rollBack();
    error_log("Vendor registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>