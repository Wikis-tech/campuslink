<?php
/**
 * CampusLink - Payment Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../config/paystack.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../models/SubscriptionModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class PaymentController extends Controller
{
    private VendorModel       $vendorModel;
    private PaymentModel      $paymentModel;
    private SubscriptionModel $subModel;
    private Paystack          $paystack;
    private Mailer            $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->vendorModel  = new VendorModel();
        $this->paymentModel = new PaymentModel();
        $this->subModel     = new SubscriptionModel();
        $this->paystack     = new Paystack();
        $this->mailer       = new Mailer();
    }

    // ============================================================
    // Payment page — FIXED
    // ============================================================
    public function index(): void
    {
        // ── CSRF refresh ping (called by JS every 10 min) ──
        if (
            isset($_GET['csrf_refresh'])
            && $_GET['csrf_refresh'] === '1'
            && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['token' => CSRF::token()]);
            exit;
        }

        $vendorId   = $this->session->get('pending_vendor_id') ?: $this->session->get('payment_vendor_id');
        $vendorType = $this->session->get('pending_vendor_type');
        $planType   = $this->session->get('pending_plan') ?: $this->session->get('payment_plan', 'basic');
        $isRenew    = $this->get('renew', 0);
        $isInit     = $this->get('init', 0);

        // Use logged-in vendor if session has no vendor_id
        if (($isRenew || $isInit) && Auth::isVendorLoggedIn() && !$vendorId) {
            $vendorId   = Auth::vendorId();
            $vendor     = $this->vendorModel->find($vendorId);
            $vendorType = $vendor['vendor_type'];
            if ($isRenew) {
                $planType = $vendor['plan_type'];
            }
        }

        if (!$vendorId) {
            $this->redirectWith('vendor/register', 'error', 'Please complete registration first.');
            return;
        }

        $vendor = $this->vendorModel->find((int)$vendorId);
        if (!$vendor) {
            $this->redirectWith('vendor/register', 'error', 'Vendor account not found.');
            return;
        }

        if (empty($vendorType) && isset($vendor['vendor_type'])) {
            $vendorType = $vendor['vendor_type'];
        }

        $amount = getPlanAmount($vendorType, $planType);

        require_once __DIR__ . '/../models/PlanModel.php';
        $planModel = new PlanModel();
        $plans     = $planModel->getByVendorType($vendorType);

        $this->view('vendor/payment', [
            'pageTitle'    => 'Complete Payment - ' . SITE_NAME,
            'vendor'       => $vendor,
            'vendorType'   => $vendorType,
            'selectedPlan' => $planType,
            'planType'     => $planType,
            'amount'       => $amount,
            'amountNaira'  => getPlanNaira($vendorType, $planType),
            'planLabel'    => getPlanLabel($vendorType, $planType),
            'plans'        => $plans,
            'paystackKey'  => $this->paystack->getPublicKey(),
            'isRenew'      => $isRenew,
            'isInit'       => $isInit,
            'csrfField'    => CSRF::field(),
        ]);
    }

    // ============================================================
    // CSRF token refresh endpoint (standalone GET)
    // Handles: /vendor/payment/csrf
    // ============================================================
    public function csrf(): void
    {
        if (
            !isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            http_response_code(403);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['token' => CSRF::token()]);
        exit;
    }

    // ============================================================
    // Initialize Paystack transaction (AJAX POST) — FIXED
    // ============================================================
    public function initiate(): void
    {
        $this->requirePost();
        $this->validateCSRF();

        $vendorId   = (int)($this->post('vendor_id', 0)
                      ?: $this->session->get('pending_vendor_id', 0)
                      ?: $this->session->get('payment_vendor_id', 0));
        $vendorType = $this->post('vendor_type', '')
                      ?: $this->session->get('pending_vendor_type', '');
        $planType   = $this->post('plan_type', '')
                      ?: $this->session->get('pending_plan', '')
                      ?: $this->session->get('payment_plan', '');

        // Fall back to logged-in vendor
        if (!$vendorId && Auth::isVendorLoggedIn()) {
            $vendorId = Auth::vendorId();
        }

        // Resolve vendor type from DB if still missing
        if ((!$vendorType) && $vendorId) {
            $v          = $this->vendorModel->find($vendorId);
            $vendorType = $v['vendor_type'] ?? '';
        }

        if (!$vendorId || !$vendorType || !$planType) {
            $this->jsonError('Missing payment details. Please go back to the subscription page and try again.');
            return;
        }

        if (!isValidPlan($vendorType, $planType)) {
            $this->jsonError('Invalid plan selected.');
            return;
        }

        $vendor = $this->vendorModel->find($vendorId);
        if (!$vendor) {
            $this->jsonError('Vendor account not found.');
            return;
        }

        $amount    = getPlanAmount($vendorType, $planType);
        $email     = $vendor['school_email'] ?? $vendor['working_email'] ?? $vendor['personal_email'] ?? '';
        $reference = Paystack::generateReference($vendorId);

        $paymentId = $this->paymentModel->createPending([
            'vendor_id'   => $vendorId,
            'vendor_type' => $vendorType,
            'reference'   => $reference,
            'amount'      => $amount,
            'plan_type'   => $planType,
        ]);

        if (!$paymentId) {
            $this->jsonError('Could not initialize payment record. Please try again.');
            return;
        }

        $this->session->set('payment_reference', $reference);
        $this->session->set('payment_id', (int)$paymentId);

        $result = $this->paystack->initializeTransaction([
            'email'        => $email,
            'amount'       => $amount,
            'reference'    => $reference,
            'callback_url' => PAYSTACK_CALLBACK_URL,
            'metadata'     => [
                'vendor_id'   => $vendorId,
                'vendor_type' => $vendorType,
                'plan'        => $planType,
                'payment_id'  => (int)$paymentId,
            ],
        ]);

        if (!$result['status']) {
            $this->jsonError($result['message'] ?? 'Paystack initialization failed. Please try again.');
            return;
        }

        // ── Generate a fresh CSRF token so the next request won't fail ──
        $freshCsrf = CSRF::token();

        // ── Return everything the JS needs in a flat structure ──
        // JS reads: data.reference, data.amount, data.new_csrf_token
        $this->jsonSuccess('Payment initialized.', [
            'reference'         => $reference,
            'amount'            => $amount,
            'authorization_url' => $result['authorization_url'],
            'new_csrf_token'    => $freshCsrf,
        ]);
    }

    // ============================================================
    // Verify payment — server-side only
    // ============================================================
    public function verify(): void
    {
        $reference = $this->get('reference', '') ?: $this->post('reference', '');
        $reference = preg_replace('/[^a-zA-Z0-9_\-]/', '', $reference);

        if (empty($reference)) {
            $this->redirectWith('vendor/payment/failed', 'error', 'No payment reference provided.');
            return;
        }

        if ($this->paymentModel->isAlreadyVerified($reference)) {
            $this->redirectWith('vendor/dashboard', 'success', 'Payment already verified. Your subscription is active.');
            return;
        }

        $result = $this->paystack->verifyTransaction($reference);

        if (!$result['status']) {
            Logger::payment('VERIFY_FAIL', $reference, 0, 0, $result['message'] ?? 'Unknown');
            $this->redirectWith('vendor/payment/failed', 'error', 'Payment verification failed: ' . ($result['message'] ?? 'Unknown error'));
            return;
        }

        if (!$result['success']) {
            Logger::payment('VERIFY_FAIL', $reference, $result['amount'] ?? 0, 0, $result['transaction_status']);
            $this->redirectWith('vendor/payment/failed', 'error', 'Payment was not successful. Status: ' . $result['transaction_status']);
            return;
        }

        $payment = $this->paymentModel->findByReference($reference);
        if (!$payment) {
            $this->redirectWith('vendor/payment/failed', 'error', 'Payment record not found.');
            return;
        }

        $expectedAmount = getPlanAmount($payment['vendor_type'], $payment['plan_type']);
        if ((int)$result['amount'] !== $expectedAmount) {
            Logger::payment('AMOUNT_MISMATCH', $reference, $result['amount'], $payment['vendor_id'],
                "Expected: $expectedAmount | Got: {$result['amount']}");
            $this->redirectWith('vendor/payment/failed', 'error', 'Payment amount mismatch. Please contact support.');
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->paymentModel->markSuccess($payment['id'], $result);

            $subId = $this->subModel->createSubscription([
                'vendor_id'   => $payment['vendor_id'],
                'plan_type'   => $payment['plan_type'],
                'vendor_type' => $payment['vendor_type'],
                'payment_id'  => $payment['id'],
                'amount'      => $result['amount'],
            ]);

            $this->vendorModel->activate($payment['vendor_id']);
            $this->vendorModel->updatePlan($payment['vendor_id'], $payment['plan_type']);

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error('Payment activation failed', $e->getMessage());
            $this->redirectWith('vendor/payment/failed', 'error', 'An error occurred while activating your subscription.');
            return;
        }

        $vendor = $this->vendorModel->find($payment['vendor_id']);
        $sub    = $this->subModel->find((int)$subId);

        $email = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
        if ($email && $vendor) {
            $this->mailer->sendPaymentReceipt(
                $email,
                $vendor['full_name'],
                $vendor['business_name'],
                $reference,
                getPlanLabel($payment['vendor_type'], $payment['plan_type']),
                $result['amount'],
                $sub['start_date']  ?? date('Y-m-d H:i:s'),
                $sub['expiry_date'] ?? ''
            );
        }

        Notification::sendToVendor(
            $payment['vendor_id'],
            'Payment Confirmed',
            'Your ' . getPlanLabel($payment['vendor_type'], $payment['plan_type']) .
            ' subscription is now active. Expires: ' . ($sub['expiry_date'] ?? 'N/A'),
            Notification::TYPE_PAYMENT,
            'vendor/subscription'
        );

        Logger::payment('SUCCESS', $reference, $result['amount'], $payment['vendor_id'], 'Activated');

        if (!Auth::isVendorLoggedIn()) {
            $this->session->loginVendor($vendor);
        }

        $this->session->remove('pending_vendor_id');
        $this->session->remove('pending_vendor_type');
        $this->session->remove('pending_plan');
        $this->session->remove('payment_reference');
        $this->session->remove('payment_id');

        $this->redirect('vendor/payment/success');
    }

    // ============================================================
    // Payment Success Page
    // ============================================================
    public function success(): void
    {
        $this->view('vendor/payment-success', [
            'pageTitle'    => 'Payment Successful - ' . SITE_NAME,
            'flashSuccess' => $this->session->getFlash('success'),
        ]);
    }

    // ============================================================
    // Payment Failed Page
    // ============================================================
    public function failed(): void
    {
        $this->view('vendor/payment-failed', [
            'pageTitle'  => 'Payment Failed - ' . SITE_NAME,
            'flashError' => $this->session->getFlash('error'),
        ]);
    }
}