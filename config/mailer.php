<?php
/**
 * CampusLink - Email / Mailer Configuration
 * Handles all outbound emails using PHPMailer via SMTP
 * or native PHP mail() as fallback.
 *
 * To use PHPMailer: composer require phpmailer/phpmailer
 * OR download PHPMailer manually to vendor-composer/phpmailer/
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Mailer
{
    private string $fromEmail;
    private string $fromName;
    private bool   $useSmtp;

    // ============================================================
    // Constructor
    // ============================================================
public function __construct()
{
    // Load PHPMailer before checking if it exists
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $manualPath = __DIR__ . '/../vendor-composer/phpmailer/src/PHPMailer.php';
        if (file_exists($manualPath)) {
            require_once $manualPath;
            require_once __DIR__ . '/../vendor-composer/phpmailer/src/SMTP.php';
            require_once __DIR__ . '/../vendor-composer/phpmailer/src/Exception.php';
        }
    }

    $this->fromEmail = SMTP_FROM_EMAIL;
    $this->fromName  = SMTP_FROM_NAME;
    $this->useSmtp   = SMTP_ENABLED && class_exists('PHPMailer\PHPMailer\PHPMailer');
}

    // ============================================================
    // Send an email
    // ============================================================
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $plainBody = ''
    ): bool {
        if ($this->useSmtp) {
            return $this->sendViaSmtp($toEmail, $toName, $subject, $htmlBody, $plainBody);
        }
        return $this->sendViaMail($toEmail, $toName, $subject, $htmlBody);
    }

    // ============================================================
    // SMTP send using PHPMailer
    // ============================================================
    private function sendViaSmtp(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $plainBody
    ): bool {
        try {
            // Try loading PHPMailer from Composer autoload or manual path
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $manualPath = __DIR__ . '/../vendor-composer/phpmailer/src/PHPMailer.php';
                if (file_exists($manualPath)) {
                    require_once $manualPath;
                    require_once __DIR__ . '/../vendor-composer/phpmailer/src/SMTP.php';
                    require_once __DIR__ . '/../vendor-composer/phpmailer/src/Exception.php';
                } else {
                    // Fall back to native mail
                    return $this->sendViaMail($toEmail, $toName, $subject, $htmlBody);
                }
            }

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // SMTP settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION === 'tls'
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;

            // Sender
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addReplyTo(CONTACT_EMAIL, SITE_NAME . ' Support');

            // Recipient
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '[' . SITE_NAME . '] ' . $subject;
            $mail->Body    = $this->wrapInTemplate($subject, $htmlBody);
            $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

            $mail->send();

            $this->logEmail($toEmail, $subject, 'sent');
            return true;

        } catch (Exception $e) {
            $this->logEmail($toEmail, $subject, 'failed: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // Native PHP mail() fallback
    // ============================================================
    private function sendViaMail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: " . CONTACT_EMAIL . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        $fullHtml = $this->wrapInTemplate($subject, $htmlBody);
        $fullSubject = '[' . SITE_NAME . '] ' . $subject;

        $result = mail($toEmail, $fullSubject, $fullHtml, $headers);

        $this->logEmail($toEmail, $subject, $result ? 'sent' : 'failed');
        return $result;
    }

    // ============================================================
    // TEMPLATE: Email verification
    // ============================================================
    public function sendEmailVerification(
        string $email,
        string $name,
        string $token
    ): bool {
        $verifyUrl = SITE_URL . '/verify-email?token=' . urlencode($token);

        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>Welcome to <strong>" . SITE_NAME . "</strong>! Please verify your email address to activate your account.</p>
        <p style='margin: 30px 0;'>
            <a href='" . e($verifyUrl) . "' 
               style='background:#0b3d91;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                Verify My Email Address
            </a>
        </p>
        <p>Or copy this link into your browser:</p>
        <p style='word-break:break-all;color:#0b3d91;'>" . e($verifyUrl) . "</p>
        <p><strong>This link expires in 24 hours.</strong></p>
        <p>If you did not create a CampusLink account, please ignore this email.</p>
        ";

        return $this->send($email, $name, 'Verify Your Email Address', $html);
    }

    // ============================================================
    // TEMPLATE: OTP Email (fallback if SMS fails)
    // ============================================================
    public function sendOTPEmail(string $email, string $name, string $otp): bool
    {
        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>Your CampusLink phone verification code is:</p>
        <div style='text-align:center;margin:30px 0;'>
            <span style='font-size:40px;font-weight:800;letter-spacing:12px;color:#0b3d91;'>$otp</span>
        </div>
        <p><strong>This code expires in 10 minutes.</strong> Do not share it with anyone.</p>
        <p>If you did not request this code, please ignore this email.</p>
        ";

        return $this->send($email, $name, 'Your Phone Verification Code', $html);
    }

    // ============================================================
    // TEMPLATE: Password reset
    // ============================================================
    public function sendPasswordReset(string $email, string $name, string $token): bool
    {
        $resetUrl = SITE_URL . '/reset-password?token=' . urlencode($token);

        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>We received a request to reset your CampusLink password.</p>
        <p style='margin: 30px 0;'>
            <a href='" . e($resetUrl) . "'
               style='background:#0b3d91;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                Reset My Password
            </a>
        </p>
        <p>Or copy this link:</p>
        <p style='word-break:break-all;color:#0b3d91;'>" . e($resetUrl) . "</p>
        <p><strong>This link expires in 1 hour.</strong></p>
        <p>If you did not request a password reset, please ignore this email and ensure your account is secure.</p>
        ";

        return $this->send($email, $name, 'Reset Your Password', $html);
    }

    // ============================================================
    // TEMPLATE: Vendor registration received
    // ============================================================
    public function sendVendorRegistrationReceived(
        string $email,
        string $name,
        string $businessName
    ): bool {
        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>Thank you for registering <strong>" . e($businessName) . "</strong> on " . SITE_NAME . "!</p>
        <p>Your application has been received and is currently <strong>under review</strong>. 
           Our admin team will verify your documents and respond within <strong>48 hours</strong>.</p>
        <p>You will receive an email notification once your vendor account is approved or if any 
           additional information is required.</p>
        <div style='background:#f0f4ff;border-left:4px solid #0b3d91;padding:16px;margin:24px 0;border-radius:4px;'>
            <p style='margin:0;'><strong>What happens next?</strong></p>
            <ul style='margin:8px 0 0 0;padding-left:20px;'>
                <li>Admin reviews your submitted documents</li>
                <li>You receive approval or rejection notification</li>
                <li>Upon approval, your listing goes live on CampusLink</li>
            </ul>
        </div>
        <p>Questions? Contact us at <a href='mailto:" . CONTACT_EMAIL . "'>" . CONTACT_EMAIL . "</a></p>
        ";

        return $this->send($email, $name, 'Registration Received - Under Review', $html);
    }

    // ============================================================
    // TEMPLATE: Vendor approved
    // ============================================================
    public function sendVendorApproved(
        string $email,
        string $name,
        string $businessName,
        string $dashboardUrl
    ): bool {
        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>🎉 Congratulations! <strong>" . e($businessName) . "</strong> has been <strong>approved</strong> 
           and is now live on " . SITE_NAME . "!</p>
        <p style='margin: 30px 0;'>
            <a href='" . e($dashboardUrl) . "'
               style='background:#1ea952;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                Go to My Dashboard
            </a>
        </p>
        <p>Students and campus community members can now discover your business on CampusLink.</p>
        ";

        return $this->send($email, $name, 'Your Vendor Account is Approved! 🎉', $html);
    }

    // ============================================================
    // TEMPLATE: Vendor rejected
    // ============================================================
    public function sendVendorRejected(
        string $email,
        string $name,
        string $businessName,
        string $reason
    ): bool {
        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>We have reviewed your application for <strong>" . e($businessName) . "</strong> 
           on " . SITE_NAME . ".</p>
        <p>Unfortunately, your application was <strong>not approved</strong> for the following reason:</p>
        <div style='background:#fff3f3;border-left:4px solid #e53e3e;padding:16px;margin:20px 0;border-radius:4px;'>
            " . e($reason) . "
        </div>
        <p>You are welcome to reapply after addressing the issues mentioned above. 
           If you believe this is a mistake, please contact us at 
           <a href='mailto:" . CONTACT_EMAIL . "'>" . CONTACT_EMAIL . "</a>.</p>
        ";

        return $this->send($email, $name, 'Application Update - Action Required', $html);
    }

    // ============================================================
    // TEMPLATE: Subscription expiry reminder
    // ============================================================
    public function sendSubscriptionReminder(
        string $email,
        string $name,
        string $businessName,
        string $expiryDate,
        int    $daysLeft,
        string $renewUrl
    ): bool {
        $urgency = $daysLeft <= 2 ? '🚨 URGENT: ' : ($daysLeft <= 7 ? '⚠️ ' : '');

        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>This is a reminder that your CampusLink subscription for <strong>" . e($businessName) . "</strong> 
           is expiring in <strong>$daysLeft day" . ($daysLeft !== 1 ? 's' : '') . "</strong>.</p>
        <div style='background:#fff8e1;border-left:4px solid #f59e0b;padding:16px;margin:20px 0;border-radius:4px;'>
            <strong>Expiry Date:</strong> " . e($expiryDate) . "<br>
            <strong>Days Remaining:</strong> $daysLeft
        </div>
        <p>Renew your subscription now to keep your listing active and avoid being hidden from students.</p>
        <p style='margin: 30px 0;'>
            <a href='" . e($renewUrl) . "'
               style='background:#f59e0b;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                Renew My Subscription
            </a>
        </p>
        <p><small>If you do not renew, your listing will be automatically deactivated after 
           the 2-day grace period following expiry.</small></p>
        ";

        return $this->send($email, $name, $urgency . 'Subscription Expiring in ' . $daysLeft . ' Day(s)', $html);
    }

    // ============================================================
    // TEMPLATE: Subscription expired notice
    // ============================================================
    public function sendSubscriptionExpired(
        string $email,
        string $name,
        string $businessName,
        string $renewUrl
    ): bool {
        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>Your CampusLink subscription for <strong>" . e($businessName) . "</strong> has 
           <strong>expired</strong>.</p>
        <p>Your listing has been <strong>deactivated</strong> and is no longer visible to students 
           on CampusLink.</p>
        <p style='margin: 30px 0;'>
            <a href='" . e($renewUrl) . "'
               style='background:#0b3d91;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                Renew Subscription Now
            </a>
        </p>
        <p>Renew today to reactivate your listing and get back in front of campus customers.</p>
        ";

        return $this->send($email, $name, '⛔ Your Subscription Has Expired', $html);
    }

    // ============================================================
    // TEMPLATE: Payment success receipt
    // ============================================================
    public function sendPaymentReceipt(
        string $email,
        string $name,
        string $businessName,
        string $reference,
        string $plan,
        int    $amount,
        string $startDate,
        string $expiryDate
    ): bool {
        $nairaAmount = '₦' . number_format($amount / 100, 2);

        $html = "
        <p>Hi <strong>" . e($name) . "</strong>,</p>
        <p>Your payment has been confirmed. Here is your receipt:</p>
        <div style='background:#f8f9fa;border:1px solid #e9ecef;border-radius:8px;padding:24px;margin:20px 0;'>
            <table style='width:100%;border-collapse:collapse;'>
                <tr><td style='padding:8px 0;color:#555;'>Business Name</td><td style='padding:8px 0;font-weight:600;'>" . e($businessName) . "</td></tr>
                <tr><td style='padding:8px 0;color:#555;'>Plan</td><td style='padding:8px 0;font-weight:600;'>" . e($plan) . "</td></tr>
                <tr><td style='padding:8px 0;color:#555;'>Amount Paid</td><td style='padding:8px 0;font-weight:600;color:#1ea952;'>$nairaAmount</td></tr>
                <tr><td style='padding:8px 0;color:#555;'>Reference</td><td style='padding:8px 0;font-family:monospace;'>" . e($reference) . "</td></tr>
                <tr><td style='padding:8px 0;color:#555;'>Start Date</td><td style='padding:8px 0;'>" . e($startDate) . "</td></tr>
                <tr><td style='padding:8px 0;color:#555;'>Expiry Date</td><td style='padding:8px 0;'>" . e($expiryDate) . "</td></tr>
            </table>
        </div>
        <p>Your subscription is now <strong>active</strong>. Log in to your dashboard to manage your listing.</p>
        <p><small>Please save this email as your payment receipt.</small></p>
        ";

        return $this->send($email, $name, 'Payment Confirmed - Subscription Active ✅', $html);
    }

    // ============================================================
    // TEMPLATE: New review notification to vendor
    // ============================================================
    public function sendNewReviewNotification(
        string $email,
        string $vendorName,
        string $businessName,
        int    $rating,
        string $dashboardUrl
    ): bool {
        $stars = str_repeat('⭐', $rating);

        $html = "
        <p>Hi <strong>" . e($vendorName) . "</strong>,</p>
        <p>A new review has been submitted for <strong>" . e($businessName) . "</strong>.</p>
        <div style='background:#f0f4ff;border-left:4px solid #0b3d91;padding:16px;margin:20px 0;border-radius:4px;'>
            <strong>Rating:</strong> $stars ($rating/5)
        </div>
        <p>The review is pending admin moderation. Once approved, it will appear on your public profile.</p>
        <p>
            <a href='" . e($dashboardUrl) . "'
               style='background:#0b3d91;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                View Dashboard
            </a>
        </p>
        ";

        return $this->send($email, $vendorName, 'New Review Submitted for ' . $businessName, $html);
    }

    // ============================================================
    // TEMPLATE: Complaint filed against vendor
    // ============================================================
    public function sendComplaintNotification(
        string $email,
        string $vendorName,
        string $businessName,
        string $complaintCategory,
        string $dashboardUrl
    ): bool {
        $html = "
        <p>Hi <strong>" . e($vendorName) . "</strong>,</p>
        <p>A complaint has been filed against <strong>" . e($businessName) . "</strong> on " . SITE_NAME . ".</p>
        <div style='background:#fff3f3;border-left:4px solid #e53e3e;padding:16px;margin:20px 0;border-radius:4px;'>
            <strong>Complaint Category:</strong> " . e($complaintCategory) . "
        </div>
        <p>Our admin team will investigate this complaint. You may be contacted for your response.</p>
        <p>Please log in to your dashboard to view and respond to this complaint.</p>
        <p>
            <a href='" . e($dashboardUrl) . "'
               style='background:#0b3d91;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;'>
                View Complaint
            </a>
        </p>
        ";

        return $this->send($email, $vendorName, '⚠️ Complaint Filed Against Your Business', $html);
    }

    // ============================================================
    // HTML Email wrapper template
    // ============================================================
    private function wrapInTemplate(string $subject, string $content): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . e($subject) . '</title>
</head>
<body style="margin:0;padding:0;font-family:Inter,-apple-system,BlinkMacSystemFont,Segoe UI,Arial,sans-serif;background:#f8f9fa;color:#1f2937;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                <!-- Header -->
                <tr>
                    <td style="background:linear-gradient(135deg,#0b3d91 0%,#1e5bb8 100%);border-radius:12px 12px 0 0;padding:32px 40px;text-align:center;">
                        <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:800;letter-spacing:-0.5px;">
                            Campus<span style="color:#93c5fd;">Link</span>
                        </h1>
                        <p style="margin:6px 0 0;color:rgba(255,255,255,0.8);font-size:13px;">
                            ' . SCHOOL_NAME . '
                        </p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="background:#ffffff;padding:40px;border-left:1px solid #e9ecef;border-right:1px solid #e9ecef;">
                        <div style="font-size:15px;line-height:1.7;color:#1f2937;">
                            ' . $content . '
                        </div>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f8f9fa;border:1px solid #e9ecef;border-top:none;border-radius:0 0 12px 12px;padding:24px 40px;text-align:center;">
                        <p style="margin:0 0 8px;font-size:12px;color:#888;">
                            This email was sent by <strong>' . SITE_NAME . '</strong> — ' . SCHOOL_NAME . '
                        </p>
                        <p style="margin:0 0 8px;font-size:12px;color:#888;">
                            CampusLink is a directory platform only. We do not provide services or process transactions.
                        </p>
                        <p style="margin:0;font-size:12px;color:#888;">
                            Questions? <a href="mailto:' . CONTACT_EMAIL . '" style="color:#0b3d91;">' . CONTACT_EMAIL . '</a>
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>';
    }

    // ============================================================
    // Log email events
    // ============================================================
    private function logEmail(string $to, string $subject, string $status): void
    {
        if (!LOG_ENABLED || !defined('LOG_AUDIT')) return;

        $logDir = dirname(LOG_AUDIT);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] [EMAIL] To: %s | Subject: %s | Status: %s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $status
        );

        file_put_contents(LOG_AUDIT, $entry, FILE_APPEND | LOCK_EX);
    }
}