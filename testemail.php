
<?php
define('CAMPUSLINK', true);
require_once 'core/bootstrap.php';

$testEmail = 'francis.gabriel@student.uat.edu.ng';
$testName  = 'Test Student';
$token     = bin2hex(random_bytes(32));

// Build the verification URL exactly like the real system does
$verifyUrl = SITE_URL . '/verify-email?token=' . urlencode($token);

echo '<h3>SMTP Config Check</h3>';
echo 'SMTP_HOST: '       . SMTP_HOST       . '<br>';
echo 'SMTP_PORT: '       . SMTP_PORT       . '<br>';
echo 'SMTP_USERNAME: '   . SMTP_USERNAME   . '<br>';
echo 'SMTP_FROM_EMAIL: ' . SMTP_FROM_EMAIL . '<br>';
echo 'SMTP_ENABLED: '    . (SMTP_ENABLED ? 'true' : 'false') . '<br>';
echo '<br>';

echo '<h3>PHPMailer Files</h3>';
$files = [
    'vendor-composer/phpmailer/src/PHPMailer.php',
    'vendor-composer/phpmailer/src/SMTP.php',
    'vendor-composer/phpmailer/src/Exception.php',
];
foreach ($files as $f) {
    echo file_exists(__DIR__ . '/' . $f)
        ? '<i data-feather="check-circle" aria-hidden="true"></i> ' . $f . '<br>'
        : '<i data-feather="x-circle" aria-hidden="true"></i> MISSING: ' . $f . '<br>';
}
echo '<br>';

echo '<h3>Send Test</h3>';

try {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        require_once __DIR__ . '/vendor-composer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/vendor-composer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/vendor-composer/phpmailer/src/Exception.php';
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->SMTPDebug  = 2;
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($testEmail, $testName);

    $mail->isHTML(true);
    $mail->Subject = 'CampusLink — Verify Your Email';
    $mail->Body    = '
        <p>Hi <strong>' . $testName . '</strong>,</p>
        <p>Click the button below to verify your email:</p>
        <p>
            <a href="' . $verifyUrl . '"
               style="background:#0b3d91;color:#fff;padding:14px 28px;
                      border-radius:6px;text-decoration:none;font-weight:700;
                      display:inline-block;">
                Verify My Email Address
            </a>
        </p>
        <p>Or copy this link: ' . $verifyUrl . '</p>
        <p>This link expires in 24 hours.</p>
    ';
    $mail->AltBody = 'Verify your email: ' . $verifyUrl;

    $mail->send();
    echo '<br><i data-feather="check-circle" aria-hidden="true"></i> Email sent successfully to ' . $testEmail;

} catch (Exception $e) {
    echo '<br><i data-feather="x-circle" aria-hidden="true"></i> Error: ' . $e->getMessage();
}
