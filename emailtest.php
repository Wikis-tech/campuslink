<?php
define('CAMPUSLINK', true);
require_once 'core/bootstrap.php';

// Check if PHPMailer files exist
echo '<h3>File Check</h3>';
$files = [
    'vendor-composer/phpmailer/src/PHPMailer.php',
    'vendor-composer/phpmailer/src/SMTP.php',
    'vendor-composer/phpmailer/src/Exception.php',
];
foreach ($files as $f) {
    echo file_exists(__DIR__ . '/' . $f)
        ? "✅ Found: {$f}<br>"
        : "❌ Missing: {$f}<br>";
}

// Try sending with full error output
echo '<h3>Send Test</h3>';

try {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        require_once __DIR__ . '/vendor-composer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/vendor-composer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/vendor-composer/phpmailer/src/Exception.php';
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    $mail->SMTPDebug  = 2; // Show full debug output
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME); // Send to yourself as test
    $mail->Subject = 'CampusLink Email Test';
    $mail->Body    = '<h2>It works!</h2><p>CampusLink email is configured correctly.</p>';
    $mail->isHTML(true);

    $mail->send();
    echo '<br>✅ <strong>Email sent successfully! Check your inbox.</strong>';

} catch (Exception $e) {
    echo '❌ <strong>Error: ' . $e->getMessage() . '</strong>';
}