<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

$token = Security::clean($_GET['token'] ?? '');

if (!$token || strlen($token) < 32) {
    header('Location: /pages/login?msg=invalid_token');
    exit;
}

try {
    $pdo  = Database::getInstance();
    $stmt = $pdo->prepare(
        'SELECT id, first_name, personal_email, email_verified FROM users
         WHERE email_token = ? LIMIT 1'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: /pages/login?msg=invalid_token');
        exit;
    }

    if ((int)$user['email_verified'] === 1) {
        header('Location: /pages/login?msg=already_verified');
        exit;
    }

    $pdo->prepare(
        'UPDATE users SET email_verified = 1, email_token = NULL WHERE id = ?'
    )->execute([$user['id']]);

    // Send welcome email
    Mailer::sendWelcome($user['personal_email'], $user['first_name']);

    header('Location: /pages/login?msg=verified');
    exit;

} catch (\Throwable $e) {
    error_log('[CL VerifyEmail] ' . $e->getMessage());
    header('Location: /pages/login?msg=error');
    exit;
}