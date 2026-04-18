<?php
/**
 * CampusLink - Paystack Payment Gateway Configuration
 * Handles all Paystack API communication server-side.
 * Never expose secret key publicly.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Paystack
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = PAYSTACK_SECRET_KEY;
        $this->publicKey = PAYSTACK_PUBLIC_KEY;
        $this->baseUrl   = PAYSTACK_BASE_URL;
    }

    // ============================================================
    // Initialize a Paystack transaction
    // Returns authorization URL and reference
    // ============================================================
    public function initializeTransaction(array $data): array
    {
        $required = ['email', 'amount', 'reference', 'callback_url'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['status' => false, 'message' => "Missing required field: $field"];
            }
        }

        // Ensure amount is in kobo (integer)
        $data['amount'] = (int)$data['amount'];

        // Add metadata if not present
        if (!isset($data['metadata'])) {
            $data['metadata'] = [];
        }
        $data['metadata']['cancel_action'] = SITE_URL . '/vendor/payment?status=cancelled';

        $response = $this->makeRequest('POST', '/transaction/initialize', $data);

        if ($response && $response['status'] === true) {
            $this->logPayment('INIT', $data['reference'], $data['amount'], 'initialized');
            return [
                'status'            => true,
                'authorization_url' => $response['data']['authorization_url'],
                'access_code'       => $response['data']['access_code'],
                'reference'         => $response['data']['reference'],
            ];
        }

        $message = $response['message'] ?? 'Failed to initialize transaction';
        $this->logPayment('INIT_FAIL', $data['reference'] ?? 'N/A', $data['amount'] ?? 0, 'failed: ' . $message);

        return ['status' => false, 'message' => $message];
    }

    // ============================================================
    // Verify a Paystack transaction by reference
    // MUST be called server-side only
    // ============================================================
    public function verifyTransaction(string $reference): array
    {
        if (empty($reference)) {
            return ['status' => false, 'message' => 'Transaction reference is required'];
        }

        // Sanitize reference — only allow alphanumeric and hyphens/underscores
        $reference = preg_replace('/[^a-zA-Z0-9_\-]/', '', $reference);

        if (empty($reference)) {
            return ['status' => false, 'message' => 'Invalid transaction reference format'];
        }

        $response = $this->makeRequest('GET', '/transaction/verify/' . $reference);

        if (!$response) {
            $this->logPayment('VERIFY_FAIL', $reference, 0, 'No response from Paystack');
            return ['status' => false, 'message' => 'Could not connect to payment gateway'];
        }

        if ($response['status'] !== true) {
            $msg = $response['message'] ?? 'Verification failed';
            $this->logPayment('VERIFY_FAIL', $reference, 0, $msg);
            return ['status' => false, 'message' => $msg];
        }

        $data = $response['data'];
        $txStatus = $data['status'];

        $this->logPayment(
            'VERIFY',
            $reference,
            $data['amount'] ?? 0,
            $txStatus . ' | Gateway: ' . ($data['gateway_response'] ?? '')
        );

        return [
            'status'           => true,
            'transaction_status' => $txStatus,
            'success'          => ($txStatus === 'success'),
            'amount'           => $data['amount'],            // in kobo
            'reference'        => $data['reference'],
            'paid_at'          => $data['paid_at'] ?? null,
            'channel'          => $data['channel'] ?? null,
            'currency'         => $data['currency'] ?? 'NGN',
            'customer_email'   => $data['customer']['email'] ?? null,
            'customer_code'    => $data['customer']['customer_code'] ?? null,
            'gateway_response' => $data['gateway_response'] ?? null,
            'metadata'         => $data['metadata'] ?? [],
            'plan'             => $data['metadata']['plan'] ?? null,
            'vendor_id'        => $data['metadata']['vendor_id'] ?? null,
            'vendor_type'      => $data['metadata']['vendor_type'] ?? null,
        ];
    }

    // ============================================================
    // Validate Paystack Webhook Signature
    // Call this in the webhook endpoint
    // ============================================================
    public function validateWebhook(string $rawBody, string $signature): bool
    {
        $computed = hash_hmac('sha512', $rawBody, $this->secretKey);
        return hash_equals($computed, $signature);
    }

    // ============================================================
    // Fetch transaction list (for reports)
    // ============================================================
    public function listTransactions(int $perPage = 50, int $page = 1): array
    {
        $response = $this->makeRequest('GET', "/transaction?perPage=$perPage&page=$page");

        if ($response && $response['status'] === true) {
            return ['status' => true, 'data' => $response['data'], 'meta' => $response['meta'] ?? []];
        }

        return ['status' => false, 'message' => $response['message'] ?? 'Failed to fetch transactions'];
    }

    // ============================================================
    // Validate that the paid amount matches expected plan amount
    // ============================================================
    public function validateAmount(int $paidAmount, string $vendorType, string $plan): bool
    {
        $expected = getPlanAmount($vendorType, $plan);
        return $paidAmount === $expected;
    }

    // ============================================================
    // Generate a unique, unpredictable transaction reference
    // ============================================================
    public static function generateReference(int $vendorId): string
    {
        return sprintf(
            'CL-%d-%s-%s',
            $vendorId,
            strtoupper(bin2hex(random_bytes(6))),
            time()
        );
    }

    // ============================================================
    // Internal: Make cURL request to Paystack API
    // ============================================================
    private function makeRequest(string $method, string $endpoint, array $data = []): ?array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'CampusLink/1.0',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logPayment('CURL_ERROR', 'N/A', 0, "cURL Error: $curlError | HTTP: $httpCode");
            return null;
        }

        if (empty($response)) {
            $this->logPayment('EMPTY_RESPONSE', 'N/A', 0, "HTTP Code: $httpCode");
            return null;
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logPayment('JSON_ERROR', 'N/A', 0, 'Invalid JSON response from Paystack');
            return null;
        }

        return $decoded;
    }

    // ============================================================
    // Log payment events to payment log file
    // ============================================================
    private function logPayment(string $event, string $reference, int $amount, string $note): void
    {
        if (!LOG_ENABLED || !defined('LOG_PAYMENTS')) return;

        $logDir = dirname(LOG_PAYMENTS);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] [%s] Ref: %s | Amount: %s | IP: %s | %s\n",
            date('Y-m-d H:i:s'),
            $event,
            $reference,
            formatNaira($amount),
            $_SERVER['REMOTE_ADDR'] ?? 'CLI',
            $note
        );

        file_put_contents(LOG_PAYMENTS, $entry, FILE_APPEND | LOCK_EX);
    }

    // ============================================================
    // Getters
    // ============================================================
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}