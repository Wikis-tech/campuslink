<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

class Mailer
{
    private static array $config = [];
    private static bool  $available = false;

    public static function init(array $config): void
    {
        self::$config    = $config['mail'];
        self::$available = class_exists('PHPMailer\PHPMailer\PHPMailer');
    }

    private static function make(): PHPMailer
    {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host       = self::$config['host'];
        $m->SMTPAuth   = true;
        $m->Username   = self::$config['username'];
        $m->Password   = self::$config['password'];
        $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $m->Port       = (int)self::$config['port'];
        $m->CharSet    = 'UTF-8';
        $m->isHTML(true);
        $m->setFrom(
            self::$config['from_email'],
            self::$config['from_name']
        );
        $m->addReplyTo(
            self::$config['reply_to'] ?? self::$config['from_email'],
            self::$config['from_name']
        );

        // Disable debug output in production
        $cfg = require __DIR__ . '/config.php';
        $m->SMTPDebug = $cfg['app']['debug'] ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

        return $m;
    }

    private static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $body
    ): bool {
        if (!self::$available) {
            error_log('[CL Mailer] PHPMailer not available — skipping email to ' . $toEmail);
            return false;
        }

        try {
            $m = self::make();
            $m->addAddress($toEmail, $toName);
            $m->Subject = $subject;
            $m->Body    = self::wrapTemplate($subject, $body);
            $m->AltBody = strip_tags($body);
            $m->send();
            return true;
        } catch (MailException $e) {
            error_log('[CL Mailer] Failed to ' . $toEmail . ': ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log('[CL Mailer] Unexpected error: ' . $e->getMessage());
            return false;
        }
    }

    // ===== PUBLIC METHODS =====

    public static function sendVerification(
        string $toEmail,
        string $toName,
        string $token
    ): bool {
        $cfg  = require __DIR__ . '/config.php';
        $link = rtrim($cfg['app']['url'], '/') . '/api/verify-email?token=' . urlencode($token);
        $body = "
            <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Thank you for registering on Campuslink. Please click the button below to verify your email address.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$link}' style='display:inline-block;background:#0b3d91;color:white;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;font-family:sans-serif'>
                    Verify My Email
                </a>
            </p>
            <p>This link expires in <strong>24 hours</strong>.</p>
            <p style='color:#999;font-size:13px'>If you did not create an account, please ignore this email.</p>
            <p style='color:#999;font-size:12px;word-break:break-all'>Link: {$link}</p>
        ";
        return self::send($toEmail, $toName, 'Verify your Campuslink account', $body);
    }

    public static function sendPasswordReset(
        string $toEmail,
        string $toName,
        string $token
    ): bool {
        $cfg  = require __DIR__ . '/config.php';
        $link = rtrim($cfg['app']['url'], '/') . '/pages/reset-password?token=' . urlencode($token);
        $body = "
            <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>We received a request to reset your Campuslink password. Click below to create a new one.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$link}' style='display:inline-block;background:#0b3d91;color:white;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;font-family:sans-serif'>
                    Reset Password
                </a>
            </p>
            <p>This link expires in <strong>1 hour</strong>.</p>
            <p style='color:#999;font-size:13px'>If you didn't request this, please ignore this email.</p>
        ";
        return self::send($toEmail, $toName, 'Reset your Campuslink password', $body);
    }

    public static function sendWelcome(string $toEmail, string $toName): bool
    {
        $cfg  = require __DIR__ . '/config.php';
        $url  = rtrim($cfg['app']['url'], '/');
        $body = "
            <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Welcome to Campuslink! Your account is now active. You can browse hundreds of verified campus vendors, leave reviews, and connect with service providers across campus.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$url}/pages/browse' style='display:inline-block;background:#1ea952;color:white;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;font-family:sans-serif'>
                    Start Browsing
                </a>
            </p>
        ";
        return self::send($toEmail, $toName, 'Welcome to Campuslink!', $body);
    }

    public static function sendVendorApproved(
        string $toEmail,
        string $businessName
    ): bool {
        $cfg  = require __DIR__ . '/config.php';
        $url  = rtrim($cfg['app']['url'], '/');
        $body = "
            <p>Congratulations!</p>
            <p>Your vendor listing <strong>" . htmlspecialchars($businessName) . "</strong> has been reviewed and approved by our admin team. Your listing is now live!</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$url}/vendor/dashboard' style='display:inline-block;background:#0b3d91;color:white;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;font-family:sans-serif'>
                    Go to Dashboard
                </a>
            </p>
        ";
        return self::send(
            $toEmail, $businessName,
            'Your Campuslink vendor listing is approved!',
            $body
        );
    }

    public static function sendVendorRejected(
        string $toEmail,
        string $businessName,
        string $reason = ''
    ): bool {
        $reasonBlock = $reason
            ? "<p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>"
            : '';
        $body = "
            <p>Hi <strong>" . htmlspecialchars($businessName) . "</strong>,</p>
            <p>After reviewing your vendor application, we were unable to approve your listing at this time.</p>
            {$reasonBlock}
            <p>You may re-apply after addressing the reason above. Contact us at campuslinkd@gmail.com if you have questions.</p>
        ";
        return self::send(
            $toEmail, $businessName,
            'Campuslink Vendor Application Update',
            $body
        );
    }

    public static function sendSubscriptionExpiry(
        string $toEmail,
        string $businessName,
        string $expiryDate,
        int $daysLeft
    ): bool {
        $cfg   = require __DIR__ . '/config.php';
        $url   = rtrim($cfg['app']['url'], '/');
        $label = $daysLeft === 1 ? '1 day' : "{$daysLeft} days";
        $body  = "
            <p>Hi <strong>" . htmlspecialchars($businessName) . "</strong>,</p>
            <p>Your Campuslink subscription expires on <strong>{$expiryDate}</strong> — that's in <strong>{$label}</strong>.</p>
            <p>Renew now to keep your listing active and avoid the 2-day grace period.</p>
            <p style='text-align:center;margin:32px 0'>
                <a href='{$url}/vendor/subscription' style='display:inline-block;background:#f59e0b;color:white;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:700;font-size:15px;font-family:sans-serif'>
                    Renew Subscription
                </a>
            </p>
        ";
        return self::send(
            $toEmail, $businessName,
            "Your subscription expires in {$label} — Campuslink",
            $body
        );
    }

    public static function sendComplaintUpdate(
        string $toEmail,
        string $toName,
        string $refNum,
        string $status,
        string $message
    ): bool {
        $body = "
            <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Your complaint <strong>{$refNum}</strong> has been updated.</p>
            <p><strong>Status:</strong> " . htmlspecialchars($status) . "</p>
            <blockquote style='border-left:4px solid #0b3d91;padding:12px 16px;margin:16px 0;color:#555;background:#f8f9fa;border-radius:0 8px 8px 0'>
                " . htmlspecialchars($message) . "
            </blockquote>
            <p>Log in to view full details.</p>
        ";
        return self::send(
            $toEmail, $toName,
            "Complaint {$refNum} Update — Campuslink",
            $body
        );
    }

    // ===== EMAIL TEMPLATE =====

    private static function wrapTemplate(string $title, string $body): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;color:#1f2937">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f1f5f9;padding:40px 20px">
  <tr>
    <td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#0b3d91,#1e5bb8);padding:32px 40px;border-radius:16px 16px 0 0;text-align:center">
            <p style="margin:0 0 8px;font-family:'Segoe UI',Arial,sans-serif;font-size:24px;font-weight:900;color:white;letter-spacing:-0.5px">
              Campus<strong>link</strong>
            </p>
            <p style="margin:0;color:rgba(255,255,255,0.7);font-size:13px">Campus Service Directory · UAT</p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="background:white;padding:40px;font-size:15px;line-height:1.7;color:#374151">
            {$body}
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8fafc;padding:24px 40px;border-radius:0 0 16px 16px;border-top:1px solid #e5e7eb;text-align:center">
            <p style="margin:0 0 8px;font-size:12px;color:#9ca3af">
              Campuslink · University of Abuja Technology
            </p>
            <p style="margin:0;font-size:12px;color:#9ca3af">
              <a href="https://campuslinkd.infinityfreeapp.com" style="color:#0b3d91;text-decoration:none">campuslinkd.infinityfreeapp.com</a>
              &nbsp;·&nbsp;
              <a href="mailto:campuslinkd@gmail.com" style="color:#0b3d91;text-decoration:none">campuslinkd@gmail.com</a>
            </p>
            <p style="margin:8px 0 0;font-size:11px;color:#d1d5db">&copy; {$year} Campuslink. All rights reserved.</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }
}