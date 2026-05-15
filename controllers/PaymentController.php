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
        // ── Handle receipt download ──
        $receiptId = (int)$this->get('receipt', 0);
        if ($receiptId > 0) {
            $payment = $this->paymentModel->getWithSubscription($receiptId);
            if ($payment && $payment['vendor_id'] == Auth::vendorId()) {
                $this->generateReceipt($payment);
                return;
            }
            $this->redirectWith('vendor/payment-history', 'error', 'Receipt not found or access denied.');
            return;
        }

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

        $vendorId            = $this->session->get('pending_vendor_id') ?: $this->session->get('payment_vendor_id');
        $vendorType          = $this->session->get('pending_vendor_type') ?: $this->session->get('payment_vendor_type');
        $planType            = $this->session->get('pending_plan') ?: $this->session->get('payment_plan', 'basic');
        $pendingRegistration = $this->session->get('pending_registration');
        $planQuery           = trim($this->get('plan', ''));
        $isRenew             = $this->get('renew', 0);
        $isInit              = $this->get('init', 0);

        if (!$pendingRegistration && $planQuery && Auth::isVendorLoggedIn()) {
            $loggedVendorId = Auth::vendorId();
            $vendor = $this->vendorModel->find($loggedVendorId);
            if ($vendor && isValidPlan($vendor['vendor_type'], $planQuery)) {
                $this->session->set('payment_plan', $planQuery);
                $this->session->set('payment_vendor_id', $loggedVendorId);
                $this->session->set('pre_payment_vendor_id', $loggedVendorId);
                $vendorId = $loggedVendorId;
                $vendorType = $vendorType ?: $vendor['vendor_type'];
                $planType = $planQuery;
            }
        }

        if ($pendingRegistration) {
            $vendorType = $vendorType ?: ($pendingRegistration['vendor_type'] ?? 'student');
            $planType   = $planType   ?: ($pendingRegistration['plan_type'] ?? 'basic');
        }

        // CRITICAL: For renewal, vendor MUST be authenticated
        if ($isRenew && !Auth::isVendorLoggedIn()) {
            $this->redirectWith('vendor/login', 'error', 'Please log in to renew your subscription.');
            return;
        }

        // Use logged-in vendor if session has no vendor_id
        if (($isRenew || $isInit) && Auth::isVendorLoggedIn() && !$vendorId) {
            $vendorId   = Auth::vendorId();
            $vendor     = $this->vendorModel->find($vendorId);
            $vendorType = $vendorType ?: $vendor['vendor_type'];
            if ($isRenew) {
                $planType = $vendor['plan_type'];
            }
        }

        if (!$vendorId && !$pendingRegistration) {
            $this->redirectWith('vendor/register', 'error', 'Please complete registration first.');
            return;
        }

        // Ensure vendorType is populated from vendor record before validation
        if ($vendorId && empty($vendorType)) {
            $vendor = $this->vendorModel->find((int)$vendorId);
            if ($vendor && isset($vendor['vendor_type'])) {
                $vendorType = $vendor['vendor_type'];
            }
        }

        // Validate vendor type before calling getPlanAmount
        if (empty($vendorType) || !in_array($vendorType, ['student', 'community'], true)) {
            $this->redirectWith('vendor/subscription', 'error', 'Unable to determine your vendor type. Please try again.');
            return;
        }

        $amount = getPlanAmount($vendorType, $planType);

        // ── If this is a free plan, process immediately ──
        if ((int)$amount === 0) {
            if ($pendingRegistration) {
                $this->processFreePlanRegistration($vendorId, $vendorType, $planType);
            } else {
                $this->processFreePlanRenewal($vendorId, $planType);
            }
            return;
        }

        $vendor = $this->vendorModel->find((int)$vendorId);
        if (!$vendor) {
            $this->redirectWith('vendor/subscription', 'error', 'Vendor account not found. Please try again.');
            return;
        }

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
    // Process Student Free Plan Registration
    // ============================================================
    private function processFreePlanRegistration(int $vendorId, string $vendorType, string $planType): void
    {
        try {
            $vendorId = $this->completeFreePlanActivation($vendorId, $vendorType, $planType, true);
            if (!$vendorId) {
                throw new Exception('Could not complete free plan activation.');
            }

            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                throw new Exception('Vendor not found after activation.');
            }

            $email = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
            if ($email) {
                if (in_array($vendor['status'], ['approved', 'active'], true)) {
                    $this->mailer->sendVendorApproved(
                        $email,
                        $vendor['full_name'],
                        $vendor['business_name'],
                        SITE_URL . '/vendor/dashboard'
                    );
                } else {
                    $this->mailer->sendVendorRegistrationReceived(
                        $email,
                        $vendor['full_name'],
                        $vendor['business_name']
                    );
                }
            }

            if ($vendor['status'] === 'approved') {
                Notification::sendToVendor(
                    $vendorId,
                    'Account Activated',
                    'Your ' . getPlanLabel($vendorType, $planType) .
                    ' account has been activated. Start listing your services now!',
                    Notification::TYPE_PAYMENT,
                    'vendor/dashboard'
                );
            } else {
                Notification::sendToVendor(
                    $vendorId,
                    'Registration Received',
                    'Your registration for ' . getPlanLabel($vendorType, $planType) .
                    ' has been received. Your account is pending admin approval. You will be notified once approved.',
                    Notification::TYPE_PAYMENT,
                    'vendor/dashboard'
                );
            }

            $this->session->loginVendor($vendor);
            $this->session->remove('pending_registration');
            $this->session->remove('pending_vendor_id');
            $this->session->remove('pending_vendor_type');
            $this->session->remove('pending_plan');
            $this->session->remove('pending_email');

            $this->redirectWith('vendor/dashboard', 'success', 'Welcome! Your ' . getPlanLabel($vendorType, $planType) . ' account is active.');
            return;

        } catch (Exception $e) {
            Logger::error('Free plan activation failed', $e->getMessage());
            $this->redirectWith('vendor/register?type=' . $vendorType, 'error', 'Could not complete registration: ' . $e->getMessage());
            return;
        }
    }

    private function completeFreePlanActivation(int $vendorId, string $vendorType, string $planType, bool $isRegistration): int
    {
        $pendingReg = $this->session->get('pending_registration');
        $this->db->beginTransaction();
        try {
            if ($isRegistration && !$vendorId) {
                if (!$pendingReg) {
                    throw new Exception('No pending registration data found.');
                }

                if ($pendingReg['vendor_type'] === 'student') {
                    $vendorId = $this->vendorModel->createStudentVendor($pendingReg);
                } else {
                    $vendorId = $this->vendorModel->createCommunityVendor($pendingReg);
                }

                if (!$vendorId) {
                    throw new Exception('Could not create vendor account.');
                }

                require_once __DIR__ . '/../models/TermsAcceptanceModel.php';
                $termsModel = new TermsAcceptanceModel();
                $termsModel->recordAll($vendorId, 'vendor', getClientIP());
            }

            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                throw new Exception('Vendor account not found.');
            }

            if ($vendorType === 'student' && $planType === 'basic') {
                $this->vendorModel->approve($vendorId);
                $this->vendorModel->activate($vendorId);
            }

            $reference = $isRegistration
                ? 'FREE_' . $vendorType . '_' . $vendorId . '_' . time()
                : 'FREE_RENEW_' . $vendorId . '_' . time();

            $paymentId = $this->paymentModel->createPending([
                'vendor_id'   => $vendorId,
                'vendor_type' => $vendorType,
                'reference'   => $reference,
                'amount'      => 0,
                'plan_type'   => $planType,
            ]);

            if (!$paymentId) {
                throw new Exception('Could not create payment record.');
            }

            $this->paymentModel->update($paymentId, [
                'status'  => 'success',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);

            $subId = $this->subModel->createSubscription([
                'vendor_id'   => $vendorId,
                'plan_type'   => $planType,
                'vendor_type' => $vendorType,
                'payment_id'  => $paymentId,
                'amount'      => 0,
            ]);

            if (!$subId) {
                throw new Exception('Could not create subscription.');
            }

            $this->vendorModel->updatePlan($vendorId, $planType);

            if ($isRegistration) {
                $this->session->remove('pending_registration');
            }

            $this->db->commit();
            return $vendorId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // ============================================================
    // Process Student Free Plan Renewal
    // ============================================================
    private function processFreePlanRenewal(int $vendorId, string $planType): void
    {
        // CRITICAL: Ensure vendor is authenticated before renewal
        if (!Auth::isVendorLoggedIn() || Auth::vendorId() !== $vendorId) {
            Logger::error('Free plan renewal auth check failed', "VendorID mismatch or not logged in: $vendorId");
            $this->redirectWith('vendor/login', 'error', 'Your session has expired. Please log in again.');
            return;
        }

        $this->db->beginTransaction();
        try {
            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                throw new Exception('Vendor not found.');
            }

            // Create payment record with amount 0
            $reference = 'FREE_RENEW_' . $vendorId . '_' . time();
            $paymentId = $this->paymentModel->createPending([
                'vendor_id'   => $vendorId,
                'vendor_type' => $vendor['vendor_type'],
                'reference'   => $reference,
                'amount'      => 0,
                'plan_type'   => $planType,
            ]);

            if (!$paymentId) {
                throw new Exception('Could not create payment record.');
            }

            // Mark as completed
            $this->paymentModel->update($paymentId, [
                'status'  => 'success',
                'paid_at' => date('Y-m-d H:i:s'),
            ]);

            // Create subscription
            $subId = $this->subModel->createSubscription([
                'vendor_id'   => $vendorId,
                'plan_type'   => $planType,
                'vendor_type' => $vendor['vendor_type'],
                'payment_id'  => $paymentId,
                'amount'      => 0,
            ]);

            if (!$subId) {
                throw new Exception('Could not create subscription.');
            }

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error('Free plan renewal failed', $e->getMessage());
            $this->redirectWith('vendor/subscription', 'error', 'Renewal failed: ' . $e->getMessage());
            return;
        }

        // CRITICAL: Preserve vendor session after renewal - do NOT regenerate or clear session
        // Update session with latest vendor info (keeps auth state intact)
        $this->session->set('vendor_name', $vendor['full_name']);
        $this->session->set('vendor_email', $vendor['email'] ?? $vendor['working_email']);
        $this->session->set('vendor_business', $vendor['business_name']);
        $this->session->set('vendor_plan', $planType);

        // Send notification
        Notification::sendToVendor(
            $vendorId,
            'Subscription Renewed',
            'Your ' . getPlanLabel($vendor['vendor_type'], $planType) . ' subscription has been renewed.',
            Notification::TYPE_PAYMENT,
            'vendor/subscription'
        );

        Logger::payment('SUCCESS_FREE_RENEW', $reference, 0, $vendorId, 'Free Plan Renewed');

        $this->redirectWith('vendor/subscription', 'success', 'Your subscription has been renewed successfully.');
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
    // Create vendor from pending registration data
    // ============================================================
    private function createVendorFromPendingRegistration(array $data): int|false
    {
        // Create vendor account
        if ($data['vendor_type'] === 'student') {
            $vendorId = $this->vendorModel->createStudentVendor($data);
        } elseif ($data['vendor_type'] === 'community') {
            $vendorId = $this->vendorModel->createCommunityVendor($data);
        } else {
            return false;
        }

        if (!$vendorId) {
            return false;
        }

        // Record terms acceptance
        require_once __DIR__ . '/../models/TermsAcceptanceModel.php';
        $termsModel = new TermsAcceptanceModel();
        $termsModel->recordAll((int)$vendorId, 'vendor', getClientIP());

        // Clear pending registration data
        $this->session->remove('pending_registration');

        return (int)$vendorId;
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

        // Check if we have pending registration data (new vendor)
        $pendingRegistration = $this->session->get('pending_registration');
        if ($pendingRegistration && !$vendorId) {
            // Create vendor from pending registration data
            $vendorId = $this->createVendorFromPendingRegistration($pendingRegistration);
            if (!$vendorId) {
                $this->jsonError('Could not create vendor account. Please try again.');
                return;
            }
            $vendorType = $pendingRegistration['vendor_type'];
            $planType   = $pendingRegistration['plan_type'];
        }

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

        $amount = getPlanAmount($vendorType, $planType);

        // ── SPECIAL CASE: FREE PLAN ──
        if ((int)$amount === 0) {
            try {
                $vendorId = $this->completeFreePlanActivation($vendorId, $vendorType, $planType, (bool)$pendingRegistration);
                if (!$vendorId) {
                    $this->jsonError('Could not activate free plan.');
                    return;
                }

                $vendor = $this->vendorModel->find($vendorId);
                if ($vendor && !Auth::isVendorLoggedIn()) {
                    $this->session->loginVendor($vendor);
                }

                if ($pendingRegistration) {
                    $this->session->remove('pending_registration');
                    $this->session->remove('pending_vendor_id');
                    $this->session->remove('pending_vendor_type');
                    $this->session->remove('pending_plan');
                    $this->session->remove('pending_email');
                }
            } catch (Exception $e) {
                Logger::error('Free plan initiation failed', $e->getMessage());
                $this->jsonError('Could not activate free plan. Please try again.');
                return;
            }

            $freshCsrf = CSRF::token();
            $this->jsonSuccess('Free plan activated.', [
                'is_free_plan'   => true,
                'reference'      => 'FREE_' . $vendorType . '_' . $vendorId . '_' . time(),
                'amount'         => 0,
                'new_csrf_token' => $freshCsrf,
            ]);
            return;
        }

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
            'email'           => $email,
            'amount'          => $amount,
            'reference'       => $reference,
            'callback_url'    => PAYSTACK_CALLBACK_URL,
            'channels'        => ['card', 'bank', 'ussd', 'qr', 'mobile_money'],
            'metadata'        => [
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

            // Only activate if vendor is approved (for new vendors, they need admin approval)
            $vendor = $this->vendorModel->find($payment['vendor_id']);
            if ($vendor && $vendor['status'] === 'approved') {
                $this->vendorModel->activate($payment['vendor_id']);
            }
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

        // Send appropriate notification based on vendor status
        if ($vendor['status'] === 'approved') {
            Notification::sendToVendor(
                $payment['vendor_id'],
                'Payment Confirmed',
                'Your ' . getPlanLabel($payment['vendor_type'], $payment['plan_type']) .
                ' subscription is now active. Expires: ' . ($sub['expiry_date'] ?? 'N/A'),
                Notification::TYPE_PAYMENT,
                'vendor/subscription'
            );
        } else {
            Notification::sendToVendor(
                $payment['vendor_id'],
                'Payment Confirmed - Pending Approval',
                'Your payment for ' . getPlanLabel($payment['vendor_type'], $payment['plan_type']) .
                ' has been received. Your account is pending admin approval. You will be notified once approved.',
                Notification::TYPE_PAYMENT,
                'vendor/subscription'
            );
        }

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

    // ============================================================
    // Generate Receipt
    // ============================================================
    private function generateReceipt(array $payment): void
    {
        $vendor = $this->vendorModel->find($payment['vendor_id']);

        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><title>Receipt - ' . SITE_NAME . '</title>';
        echo '<style>body{font-family:Arial,sans-serif;max-width:600px;margin:40px auto;padding:20px;}
              .header{background:#0b3d91;color:#fff;padding:20px;border-radius:8px 8px 0 0;text-align:center;}
              table{width:100%;border-collapse:collapse;margin:20px 0;}
              td{padding:10px;border-bottom:1px solid #eee;}
              .label{color:#555;width:40%;}
              .footer{background:#f8f9fa;padding:16px;text-align:center;font-size:12px;color:#888;}
              @media print{button{display:none}}</style></head><body>';
        echo '<div class="header"><h2>' . SITE_NAME . '</h2><p>Payment Receipt</p></div>';
        echo '<table>';
        echo '<tr><td class="label">Business</td><td>' . e($vendor['business_name'] ?? '') . '</td></tr>';
        echo '<tr><td class="label">Plan</td><td>' . e(ucfirst($payment['plan_type'])) . '</td></tr>';
        echo '<tr><td class="label">Amount</td><td>₦' . number_format($payment['amount'] / 100, 2) . '</td></tr>';
        echo '<tr><td class="label">Reference</td><td>' . e($payment['reference']) . '</td></tr>';
        echo '<tr><td class="label">Paid At</td><td>' . e($payment['paid_at'] ?? $payment['created_at']) . '</td></tr>';
        if (!empty($payment['start_date'])) {
            echo '<tr><td class="label">Subscription Start</td><td>' . e($payment['start_date']) . '</td></tr>';
            echo '<tr><td class="label">Subscription Expiry</td><td>' . e($payment['expiry_date']) . '</td></tr>';
        }
        echo '</table>';
        echo '<div class="footer">' . SITE_NAME . ' is a directory platform only. This is your official payment receipt.</div>';
        echo '<br><button onclick="window.print()">Print Receipt</button>';
        echo '</body></html>';
        exit;
    }
}