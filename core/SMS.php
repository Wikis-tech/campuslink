<?php
/**
 * CampusLink - OTP / SMS Handler
 * Supports Termii (primary) and Africa's Talking (fallback)
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class SMS
{
    // ============================================================
    // Send OTP via SMS
    // ============================================================
    public static function sendOTP(string $phone, string $otp): bool
    {
        $phone = Sanitizer::phone($phone);
        $message = "Your CampusLink verification code is: $otp\nDo not share this code. Expires in 10 minutes.";

        if (SMS_PROVIDER === 'termii') {
            return self::sendViaTermii($phone, $message);
        } elseif (SMS_PROVIDER === 'africastalking') {
            return self::sendViaAfricasTalking($phone, $message);
        }

        // Log that SMS provider not configured
        Logger::log('SMS_WARNING', "SMS provider not configured. OTP for $phone: $otp");
        return false;
    }

    // ============================================================
    // Generate a secure OTP
    // ============================================================
    public static function generateOTP(): string
    {
        // Cryptographically secure OTP
        $otp = '';
        for ($i = 0; $i < OTP_LENGTH; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    // ============================================================
    // Store OTP in session
    // ============================================================
    public static function storeOTP(string $phone, string $otp): void
    {
        $_SESSION['otp_data'] = [
            'otp'      => password_hash($otp, PASSWORD_BCRYPT),
            'phone'    => $phone,
            'expires'  => time() + OTP_EXPIRY_SECONDS,
            'attempts' => 0,
        ];
    }

    // ============================================================
    // Verify OTP from session
    // ============================================================
    public static function verifyOTP(string $inputOtp, string $phone): array
    {
        if (!isset($_SESSION['otp_data'])) {
            return ['success' => false, 'message' => 'No OTP session found. Please request a new code.'];
        }

        $otpData = $_SESSION['otp_data'];

        // Check phone matches
        if ($otpData['phone'] !== Sanitizer::phone($phone)) {
            return ['success' => false, 'message' => 'Phone number mismatch.'];
        }

        // Check expiry
        if (time() > $otpData['expires']) {
            unset($_SESSION['otp_data']);
            return ['success' => false, 'message' => 'OTP has expired. Please request a new code.'];
        }

        // Check max attempts
        if ($otpData['attempts'] >= OTP_MAX_ATTEMPTS) {
            unset($_SESSION['otp_data']);
            return ['success' => false, 'message' => 'Too many incorrect attempts. Please request a new OTP.'];
        }

        // Increment attempts
        $_SESSION['otp_data']['attempts']++;

        // Verify OTP
        if (!password_verify($inputOtp, $otpData['otp'])) {
            $remaining = OTP_MAX_ATTEMPTS - $_SESSION['otp_data']['attempts'];
            return [
                'success'   => false,
                'message'   => "Incorrect code. $remaining attempt(s) remaining.",
                'remaining' => $remaining,
            ];
        }

        // Success — clear OTP from session
        unset($_SESSION['otp_data']);
        $_SESSION['phone_verified'] = Sanitizer::phone($phone);

        return ['success' => true, 'message' => 'Phone number verified successfully.'];
    }

    // ============================================================
    // Check OTP resend cooldown
    // ============================================================
    public static function canResend(): bool
    {
        if (!isset($_SESSION['otp_last_sent'])) return true;
        return (time() - $_SESSION['otp_last_sent']) >= OTP_RESEND_COOLDOWN;
    }

    // ============================================================
    // Mark OTP as sent
    // ============================================================
    public static function markSent(): void
    {
        $_SESSION['otp_last_sent'] = time();
    }

    // ============================================================
    // Send via Termii
    // ============================================================
    private static function sendViaTermii(string $phone, string $message): bool
    {
        $payload = json_encode([
            'to'      => $phone,
            'from'    => SMS_SENDER_ID,
            'sms'     => $message,
            'type'    => 'plain',
            'api_key' => SMS_API_KEY,
            'channel' => 'generic',
        ]);

        $result = self::httpPost(SMS_BASE_URL, $payload, [
            'Content-Type: application/json',
        ]);

        if (!$result) {
            Logger::log('SMS_ERROR', "Termii failed for $phone");
            return false;
        }

        $response = json_decode($result, true);
        $success = isset($response['code']) && $response['code'] === 'ok';

        Logger::log($success ? 'SMS_SENT' : 'SMS_FAILED', "Termii | Phone: $phone | Response: " . ($response['message'] ?? 'N/A'));

        return $success;
    }

    // ============================================================
    // Send via Africa's Talking
    // ============================================================
    private static function sendViaAfricasTalking(string $phone, string $message): bool
    {
        $data = http_build_query([
            'username' => AT_USERNAME,
            'to'       => $phone,
            'message'  => $message,
            'from'     => SMS_SENDER_ID,
        ]);

        $atUrl = 'https://api.africastalking.com/version1/messaging';
        if (AT_USERNAME === 'sandbox') {
            $atUrl = 'https://api.sandbox.africastalking.com/version1/messaging';
        }

        $result = self::httpPost($atUrl, $data, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . AT_API_KEY,
        ]);

        if (!$result) {
            Logger::log('SMS_ERROR', "Africa's Talking failed for $phone");
            return false;
        }

        $response = json_decode($result, true);
        $success  = isset($response['SMSMessageData']['Recipients'][0]['status'])
            && $response['SMSMessageData']['Recipients'][0]['status'] === 'Success';

        Logger::log($success ? 'SMS_SENT' : 'SMS_FAILED', "AT | Phone: $phone");

        return $success;
    }

    // ============================================================
    // HTTP POST helper
    // ============================================================
    private static function httpPost(string $url, mixed $data, array $headers = []): string|false
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'CampusLink/1.0',
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Logger::log('SMS_CURL_ERROR', $curlError);
            return false;
        }

        return $response;
    }
}