<?php
/**
 * CampusLink - Payment Controller
 * Handles Paystack payment initiation and server-side verification.
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
    // Payment / Plan selection page
    // ============================================================
    public function index(): void
    {
        $vendorId   = $this->session->get('pending_vendor_id');
        $vendorType = $this->session->get('pending_vendor_type');
        $planType   = $this->session->get('pending_plan', 'basic');
        $isRenew    = $this->get('renew', 0);

        // For renewal, use logged-in vendor
        if ($isRenew && Auth::isVendorLoggedIn()) {
            $vendorId   = Auth::vendorId();
            $vendor     = $this->vendorModel->find($vendorId);
            $vendorType = $vendor['vendor_type'];
            $planType   = $vendor['plan_type'];
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

        // Get amount
        $amount = getPlanAmount($vendorType, $planType);
        $plans  = unserialize(VALID_PLANS);

        $this->view('vendor/payment', [
            'pageTitle'   => 'Complete Payment - ' . SITE_NAME,
            'vendor'      => $vendor,
            'vendorType'  => $vendorType,
            'planType'    => $planType,
            'amount'      => $amount,
            'amountNaira' => getPlanNaira($vendorType, $planType),
            'planLabel'   => getPlanLabel($vendorType, $planType),
            'plans'       => $plans[$vendorType] ?? [],
            'paystackKey' => $this->paystack->getPublicKey(),
            'isRenew'     => $isRenew,
            'csrfField'   => CSRF::field(),
        ]);
    }

    // ============================================================
    // Initialize Paystack transaction (AJAX POST)
    // ============================================================
    public function initiate(): void
    {
        $this->requirePost();
        $this->validateCSRF();

        $vendorId   = (int)($this->post('vendor_id', 0) ?: $this->session->get('pending_vendor_id', 0));
        $vendorType = $this->post('vendor_type', '') ?: $this->session->get('pending_vendor_type', '');
        $planType   = $this->post('plan_type', '') ?: $this->session->get('pending_plan', '');

        if (!$vendorId || !$vendorType || !$planType) {
            $this->jsonError('Missing payment details. Please restart registration.');
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
        $email     = $vendor['school_email'] ?? $vendor['working_email'] ?? $vendor['personal_email'];
        $reference = Paystack::generateReference($vendorId);

        // Create pending payment record
        $paymentId = $this->paymentModel->createPending([
            'vendor_id'   => $vendorId,
            'vendor_type' => $vendorType,
            'reference'   => $reference,
            'amount'      => $amount,
            'plan_type'   => $planType,
        ]);

        if (!$paymentId) {
            $this->jsonError('Could not initialize payment. Please try again.');
            return;
        }

        // Store reference in session for verification
        $this->session->set('payment_reference', $reference);
        $this->session->set('payment_id', (int)$paymentId);

        // Initialize with Paystack
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
            $this->jsonError($result['message'] ?? 'Payment initialization failed.');
            return;
        }

        $this->jsonSuccess('Payment initialized.', [
            'authorization_url' => $result['authorization_url'],
            'reference'         => $reference,
        ]);
    }

    // ============================================================
    // Verify payment (called after Paystack redirect)
    // SERVER-SIDE verification only
    // ============================================================
    public function verify(): void
    {
        $reference = $this->get('reference', '') ?: $this->post('reference', '');
        $reference = preg_replace('/[^a-zA-Z0-9_\-]/', '', $reference);

        if (empty($reference)) {
            $this->redirectWith('vendor/payment/failed', 'error', 'No payment reference provided.');
            return;
        }

        // Check if already verified (prevent duplicate)
        if ($this->paymentModel->isAlreadyVerified($reference)) {
            $this->redirectWith('vendor/dashboard', 'success', 'Payment already verified. Your subscription is active.');
            return;
        }

        // Verify with Paystack
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

        // Get payment record
        $payment = $this->paymentModel->findByReference($reference);
        if (!$payment) {
            $this->redirectWith('vendor/payment/failed', 'error', 'Payment record not found.');
            return;
        }

        // STRICT: Validate amount matches expected plan amount
        $expectedAmount = getPlanAmount($payment['vendor_type'], $payment['plan_type']);
        if ((int)$result['amount'] !== $expectedAmount) {
            Logger::payment('AMOUNT_MISMATCH', $reference, $result['amount'], $payment['vendor_id'],
                "Expected: $expectedAmount | Got: {$result['amount']}");
            $this->redirectWith('vendor/payment/failed', 'error', 'Payment amount mismatch. Please contact support.');
            return;
        }

        // Begin transaction
        $this->db->beginTransaction();
        try {
            // Mark payment success
            $this->paymentModel->markSuccess($payment['id'], $result);

            // Create subscription
            $subId = $this->subModel->createSubscription([
                'vendor_id'   => $payment['vendor_id'],
                'plan_type'   => $payment['plan_type'],
                'vendor_type' => $payment['vendor_type'],
                'payment_id'  => $payment['id'],
                'amount'      => $result['amount'],
            ]);

            // Activate vendor
            $this->vendorModel->activate($payment['vendor_id']);
            $this->vendorModel->updatePlan($payment['vendor_id'], $payment['plan_type']);

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error('Payment activation failed', $e->getMessage());
            $this->redirectWith('vendor/payment/failed', 'error', 'An error occurred while activating your subscription.');
            return;
        }

        // Get vendor for email
        $vendor = $this->vendorModel->find($payment['vendor_id']);
        $sub    = $this->subModel->find((int)$subId);

        // Send receipt email
        $email = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
        if ($email && $vendor) {
            $this->mailer->sendPaymentReceipt(
                $email,
                $vendor['full_name'],
                $vendor['business_name'],
                $reference,
                getPlanLabel($payment['vendor_type'], $payment['plan_type']),
                $result['amount'],
                $sub['start_date'] ?? date('Y-m-d H:i:s'),
                $sub['expiry_date'] ?? ''
            );
        }

        // Send in-app notification
        Notification::sendToVendor(
            $payment['vendor_id'],
            'Payment Confirmed ✅',
            'Your ' . getPlanLabel($payment['vendor_type'], $payment['plan_type']) .
            ' subscription is now active. Expires: ' . ($sub['expiry_date'] ?? 'N/A'),
            Notification::TYPE_PAYMENT,
            'vendor/subscription'
        );

        Logger::payment('SUCCESS', $reference, $result['amount'], $payment['vendor_id'], 'Activated');

        // Log vendor into session if not already
        if (!Auth::isVendorLoggedIn()) {
            $this->session->loginVendor($vendor);
        }

        // Clear session payment data
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
            'pageTitle' => 'Payment Successful - ' . SITE_NAME,
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