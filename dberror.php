<?php
define('CAMPUSLINK', true);
require_once 'core/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = DB::getInstance();
    try {
        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 86400);
        $userId = $db->insert('users', [
            'full_name'          => 'Test User',
            'school_email'       => 'test_' . time() . '@test.com',
            'personal_email'     => 'test_' . time() . '@test.com',
            'phone'              => '0800000' . rand(1000,9999),
            'matric_number'      => 'TEST/001',
            'password'           => password_hash('test1234', PASSWORD_BCRYPT),
            'email_verified'     => 0,
            'status'             => 'inactive',
            'email_verify_token' => $token,
            'token_expires_at'   => $expiry,
            'terms_accepted'     => 1,
            'terms_version'      => '1.0',
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
        echo '<i data-feather="check-circle" aria-hidden="true"></i> Insert worked! User ID: ' . $userId;
        // Clean up test record
        $db->execute("DELETE FROM users WHERE id = ?", [$userId]);
        echo '<br><i data-feather="check-circle" aria-hidden="true"></i> Test record cleaned up.';
    } catch (Exception $e) {
        echo '<i data-feather="x-circle" aria-hidden="true"></i> Error: ' . $e->getMessage();
    }
} else {
    echo '<form method="POST"><button type="submit">Test Insert</button></form>';
}