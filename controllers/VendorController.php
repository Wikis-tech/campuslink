<?php
/**
 * CampusLink - Vendor Dashboard Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Uploader.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/SubscriptionModel.php';
require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/../models/ComplaintModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class VendorController extends BaseController {
    private VendorModel       $vendorModel;
    private SubscriptionModel $subModel;
    private PaymentModel      $paymentModel;
    private ReviewModel       $reviewModel;
    private ComplaintModel    $complaintModel;
    private NotificationModel $notifModel;

    public function __construct()
    {
        parent::__construct();
        $this->vendorModel   = new VendorModel();
        $this->subModel      = new SubscriptionModel();
        $this->paymentModel  = new PaymentModel();
        $this->reviewModel   = new ReviewModel();
        $this->complaintModel = new ComplaintModel();
        $this->notifModel    = new NotificationModel();
    }

    public function registerSelect(): void {
        $db = DB::getInstance();

        $categories = $db->rows(
            "SELECT * FROM categories ORDER BY sort_order ASC, name ASC"
        );

        $studentPlans = [
            ['plan_type' => 'basic',    'amount' => 200000,  'vendor_type' => 'student'],
            ['plan_type' => 'premium',  'amount' => 500000,  'vendor_type' => 'student'],
            ['plan_type' => 'featured', 'amount' => 1000000, 'vendor_type' => 'student'],
        ];

        $communityPlans = [
            ['plan_type' => 'basic',    'amount' => 400000,  'vendor_type' => 'community'],
            ['plan_type' => 'premium',  'amount' => 700000,  'vendor_type' => 'community'],
            ['plan_type' => 'featured', 'amount' => 1200000, 'vendor_type' => 'community'],
        ];

        $this->render('vendor/register-select', compact(
            'categories', 'studentPlans', 'communityPlans'
        ));
    }

public function login(): void {

        if (Auth::isVendorLoggedIn()) {

            $this->redirect('vendor/dashboard');

        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->verifyCsrf();

            $email    = clean($_POST['email']    ?? '');

            $password = $_POST['password']       ?? '';

            if (!$email || !$password) {

                Session::setFlash('error', 'Email and password are required.');

                $this->redirect('vendor/login');

            }

            $db     = DB::getInstance();

            $vendor = $db->row(

                "SELECT * FROM vendors WHERE working_email = ? LIMIT 1",

                [$email]

            );

            if (!$vendor || !password_verify($password, $vendor['password'])) {

                Session::setFlash('error', 'Invalid email or password.');

                $this->redirect('vendor/login');

            }

            if ($vendor['status'] === 'pending') {

                Session::setFlash('error', 'Your account is pending approval. You will be notified by email once approved.');

                $this->redirect('vendor/login');

            }

            if ($vendor['status'] === 'suspended') {

                Session::setFlash('error', 'Your account has been suspended. Contact support.');

                $this->redirect('vendor/login');

            }

            Session::regenerate();

            Session::set('vendor_logged_in', true);

            Session::set('vendor_id',       (int)$vendor['id']);

            Session::set('vendor_name',     $vendor['full_name']);

            Session::set('vendor_email',    $vendor['working_email']);

            Session::set('vendor_business', $vendor['business_name']);

            Session::set('vendor_type',     $vendor['vendor_type']);

            Session::set('vendor_plan',     $vendor['plan_type'] ?? 'basic');

            Session::set('vendor_status',   $vendor['status']);

            Session::setFlash('success', 'Welcome back, ' . $vendor['business_name'] . '!');

            $this->redirect('vendor/dashboard');

        }

        $this->render('vendor/login');

    }

    public function logout(): void {

        Session::delete('vendor_logged_in');

        Session::delete('vendor_id');

        Session::delete('vendor_name');

        Session::delete('vendor_email');

        Session::delete('vendor_business');

        Session::delete('vendor_type');

        Session::delete('vendor_plan');

        Session::delete('vendor_status');

        Session::setFlash('success', 'You have been logged out successfully.');

        $this->redirect('vendor/login');

    }    
    
    
    public function registerStudent(): void {
        $db = DB::getInstance();

    $categories = $db->rows(
        "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
    );

    $studentPlans = [
        ['plan_type' => 'basic',    'amount' => 200000,  'vendor_type' => 'student'],
        ['plan_type' => 'premium',  'amount' => 500000,  'vendor_type' => 'student'],
        ['plan_type' => 'featured', 'amount' => 1000000, 'vendor_type' => 'student'],
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handleStudentRegistration();
        return;
    }

    $this->render('vendor/register-student', compact('categories', 'studentPlans'));
}

public function registerCommunity(): void {
    $db = DB::getInstance();

    $categories = $db->rows(
        "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
    );

    $communityPlans = [
        ['plan_type' => 'basic',    'amount' => 400000,  'vendor_type' => 'community'],
        ['plan_type' => 'premium',  'amount' => 700000,  'vendor_type' => 'community'],
        ['plan_type' => 'featured', 'amount' => 1200000, 'vendor_type' => 'community'],
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handleCommunityRegistration();
        return;
    }

    $this->render('vendor/register-community', compact('categories', 'communityPlans'));
}

private function handleStudentRegistration(): void {
    $this->verifyCsrf();

    $data = [
        'full_name'      => clean($_POST['full_name']      ?? ''),
        'matric_number'  => clean($_POST['matric_number']  ?? ''),
        'school_email'   => clean($_POST['school_email']   ?? ''),
        'phone'          => clean($_POST['phone']          ?? ''),
        'business_name'  => clean($_POST['business_name']  ?? ''),
        'category_id'    => (int)($_POST['category_id']    ?? 0),
        'description'    => clean($_POST['description']    ?? ''),
        'price_range'    => clean($_POST['price_range']    ?? ''),
        'whatsapp_number'=> clean($_POST['whatsapp_number']?? ''),
        'plan_type'      => clean($_POST['plan_type']      ?? 'basic'),
        'password'       => $_POST['password']             ?? '',
        'password_confirmation' => $_POST['password_confirmation'] ?? '',
    ];

    // Basic validation
    if (!$data['full_name'] || !$data['school_email'] || !$data['business_name'] || !$data['password'] || !$data['category_id']) {
        Session::setFlash('error', 'Please fill in all required fields.');
        $this->redirect('vendor/register?type=student');
    }

    if ($data['password'] !== $data['password_confirmation']) {
        Session::setFlash('error', 'Passwords do not match.');
        $this->redirect('vendor/register?type=student');
    }

    if (strlen($data['password']) < 8) {
        Session::setFlash('error', 'Password must be at least 8 characters.');
        $this->redirect('vendor/register?type=student');
    }

    $db = DB::getInstance();

    // Check email not already registered
    if ($db->exists('vendors', 'working_email = ?', [$data['school_email']])) {
        Session::setFlash('error', 'This email is already registered as a vendor.');
        $this->redirect('vendor/register?type=student');
    }
    
   if ($db->exists('vendors', 'phone = ?', [$data['phone']])) {
    Session::setFlash('error', 'This phone number is already registered. Please use a different number or login to your existing account.');
    $this->redirect('vendor/register?type=student');
}

    // Validate category exists
    if (!$db->exists('categories', 'id = ? AND is_active = 1', [$data['category_id']])) {
        Session::setFlash('error', 'Please select a valid category.');
        $this->redirect('vendor/register?type=student');
    }

    // Handle logo upload
    $logo = '';
    if (!empty($_FILES['logo']['name'])) {
        try {
            $logo = uploadFile($_FILES['logo'], 'logos', ['image/jpeg','image/png','image/webp'], 2);
        } catch (Exception $e) {
            Session::setFlash('error', 'Logo upload failed: ' . $e->getMessage());
            $this->redirect('vendor/register?type=student');
        }
    }

    // Handle ID document upload
    $idDocument = '';
    if (!empty($_FILES['id_document']['name'])) {
        try {
            $idDocument = uploadFile($_FILES['id_document'], 'documents', ['image/jpeg','image/png','application/pdf'], 3);
        } catch (Exception $e) {
            Session::setFlash('error', 'ID upload failed: ' . $e->getMessage());
            $this->redirect('vendor/register?type=student');
        }
    }

    // Insert vendor
    $vendorId = $db->insert('vendors', [
        'full_name'      => $data['full_name'],
        'working_email'  => $data['school_email'],
        'phone'          => $data['phone'],
        'business_name'  => $data['business_name'],
        'slug'           => uniqueSlug($data['business_name'], 'vendors'),
        'category_id'    => $data['category_id'],
        'description'    => $data['description'],
        'price_range'    => $data['price_range'],
        'whatsapp_number'=> $data['whatsapp_number'],
        'plan_type'      => $data['plan_type'],
        'vendor_type'    => 'student',
        'matric_number'  => $data['matric_number'],
        'school_email'   => $data['school_email'],
        'logo'           => $logo,
        'gov_id_file'    => $idDocument,
        'password'       => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
        'status'         => 'pending',
        'created_at'     => date('Y-m-d H:i:s'),
    ]);

    Session::setFlash('success', 'Registration submitted! Our team will review and approve your listing within 24-48 hours. You will be notified by email.');
    $this->redirect('vendor/login');
}

private function handleCommunityRegistration(): void {
    $this->verifyCsrf();

    $data = [
        'full_name'       => clean($_POST['full_name']       ?? ''),
        'working_email'   => clean($_POST['working_email']   ?? ''),
        'phone'           => clean($_POST['phone']           ?? ''),
        'business_name'   => clean($_POST['business_name']   ?? ''),
        'business_address'=> clean($_POST['business_address']?? ''),
        'category_id'     => (int)($_POST['category_id']     ?? 0),
        'description'     => clean($_POST['description']     ?? ''),
        'price_range'     => clean($_POST['price_range']     ?? ''),
        'whatsapp_number' => clean($_POST['whatsapp_number'] ?? ''),
        'id_type'         => clean($_POST['id_type']         ?? ''),
        'id_number'       => clean($_POST['id_number']       ?? ''),
        'plan_type'       => clean($_POST['plan_type']       ?? 'basic'),
        'password'        => $_POST['password']              ?? '',
        'password_confirmation' => $_POST['password_confirmation'] ?? '',
    ];

    if (!$data['full_name'] || !$data['working_email'] || !$data['business_name'] || !$data['password'] || !$data['category_id']) {
        Session::setFlash('error', 'Please fill in all required fields.');
        $this->redirect('vendor/register?type=community');
    }

    $db = DB::getInstance();

    // Validate category exists
    if (!$db->exists('categories', 'id = ? AND is_active = 1', [$data['category_id']])) {
        Session::setFlash('error', 'Please select a valid category.');
        $this->redirect('vendor/register?type=community');
    }

    if ($db->exists('vendors', 'working_email = ?', [$data['working_email']])) {
        Session::setFlash('error', 'This email is already registered as a vendor.');
        $this->redirect('vendor/register?type=community');
    }
    
    if ($db->exists('vendors', 'phone = ?', [$data['phone']])) {
    Session::setFlash('error', 'This phone number is already registered. Please use a different number or login to your existing account.');
    $this->redirect('vendor/register?type=community');
}

    $logo = '';
    if (!empty($_FILES['logo']['name'])) {
        try {
            $logo = uploadFile($_FILES['logo'], 'logos', ['image/jpeg','image/png','image/webp'], 2);
        } catch (Exception $e) {
            Session::setFlash('error', 'Logo upload failed: ' . $e->getMessage());
            $this->redirect('vendor/register?type=community');
        }
    }

    $idDocument = '';
    if (!empty($_FILES['id_document']['name'])) {
        try {
            $idDocument = uploadFile($_FILES['id_document'], 'documents', ['image/jpeg','image/png','application/pdf'], 3);
        } catch (Exception $e) {
            Session::setFlash('error', 'ID upload failed: ' . $e->getMessage());
            $this->redirect('vendor/register?type=community');
        }
    }

    $vendorId = $db->insert('vendors', [
        'full_name'        => $data['full_name'],
        'working_email'    => $data['working_email'],
        'phone'            => $data['phone'],
        'business_name'    => $data['business_name'],
        'slug'             => uniqueSlug($data['business_name'], 'vendors'),
        'business_address' => $data['business_address'],
        'category_id'      => $data['category_id'],
        'description'      => $data['description'],
        'price_range'      => $data['price_range'],
        'whatsapp_number'  => $data['whatsapp_number'],
        'plan_type'        => $data['plan_type'],
        'vendor_type'      => 'community',
        'logo'             => $logo,
        'gov_id_file'      => $idDocument,
        'password'         => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
        'status'           => 'pending',
        'created_at'       => date('Y-m-d H:i:s'),
    ]);

    Session::setFlash('success', 'Registration submitted! Our team will review and approve your listing within 24-48 hours.');
    $this->redirect('vendor/login');
}
   

    // ============================================================
    // Vendor Dashboard
    // ============================================================
    public function dashboard(): void
    {
        $this->requireVendorLogin();

        $vendorId    = Auth::vendorId();
        $vendor      = $this->vendorModel->find($vendorId);
        $subInfo     = $this->subModel->getExpiryInfo($vendorId);
        $reviewCount = $this->reviewModel->countApprovedForVendor($vendorId);
        $avgRating   = $this->reviewModel->getAverageRating($vendorId);
        $complaints  = $this->complaintModel->getForVendor($vendorId);
        $unreadNotifs = $this->notifModel->countUnread('vendor', $vendorId);
        $recentReviews = $this->reviewModel->getForVendorDashboard($vendorId);

        $this->view('vendor/dashboard', [
            'pageTitle'     => 'Vendor Dashboard - ' . SITE_NAME,
            'vendor'        => $vendor,
            'subInfo'       => $subInfo,
            'reviewCount'   => $reviewCount,
            'avgRating'     => $avgRating,
            'complaints'    => array_slice($complaints, 0, 3),
            'unreadNotifs'  => $unreadNotifs,
            'recentReviews' => array_slice($recentReviews, 0, 5),
            'flashSuccess'  => $this->session->getFlash('success'),
            'flashError'    => $this->session->getFlash('error'),
            'flashWarning'  => $this->session->getFlash('warning'),
        ]);
    }
    

    // ============================================================
    // Edit Vendor Profile
    // ============================================================
    public function profile(): void
    {
        $this->requireVendorLogin();

        $vendorId = Auth::vendorId();
        $vendor   = $this->vendorModel->find($vendorId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $data = [
                'business_name'      => $this->post('business_name', ''),
                'description'        => Sanitizer::textarea($this->post('description', ''), 1000),
                'price_range'        => $this->post('price_range', ''),
                'whatsapp_number'    => Sanitizer::phone($this->post('whatsapp_number', '')),
                'phone'              => Sanitizer::phone($this->post('phone', '')),
                'operating_location' => $this->post('operating_location', ''),
                'business_address'   => $this->post('business_address', ''),
                'years_experience'   => (int)$this->post('years_experience', 0),
                'years_operation'    => (int)$this->post('years_operation', 0),
            ];

            $validator = Validator::make($data, [
                'business_name'   => 'required|min:3|max:100',
                'description'     => 'required|min:20|max:1000',
                'whatsapp_number' => 'required|phone',
                'phone'           => 'required|phone',
            ]);

            if ($validator->fails()) {
                $this->view('vendor/profile-edit', [
                    'pageTitle' => 'Edit Profile - ' . SITE_NAME,
                    'vendor'    => array_merge($vendor, $data),
                    'errors'    => $validator->errors(),
                    'csrfField' => CSRF::field(),
                ]);
                return;
            }

            // Handle logo upload
            if (!empty($_FILES['logo']['name'])) {
                $uploader = new Uploader(UPLOAD_LOGOS);
                $newLogo  = $uploader->upload($_FILES['logo'], 'logo_' . $vendorId);

                if ($uploader->hasError()) {
                    $this->view('vendor/profile-edit', [
                        'pageTitle' => 'Edit Profile - ' . SITE_NAME,
                        'vendor'    => array_merge($vendor, $data),
                        'errors'    => ['logo' => $uploader->getError()],
                        'csrfField' => CSRF::field(),
                    ]);
                    return;
                }

                // Delete old logo
                if (!empty($vendor['logo'])) {
                    @unlink(UPLOAD_LOGOS . $vendor['logo']);
                }
                $data['logo'] = $newLogo;
            }

            $this->vendorModel->updateProfile($vendorId, $data);
            $this->redirectWith('vendor/profile', 'success', 'Profile updated successfully.');
            return;
        }

        $this->view('vendor/profile-edit', [
            'pageTitle' => 'Edit Vendor Profile - ' . SITE_NAME,
            'vendor'    => $vendor,
            'errors'    => [],
            'csrfField' => CSRF::field(),
            'flashSuccess' => $this->session->getFlash('success'),
        ]);
    }

    // ============================================================
    // Reviews & Replies
    // ============================================================
    public function reviews(): void
    {
        $this->requireVendorLogin();

        $vendorId = Auth::vendorId();
        $reviews  = $this->reviewModel->getForVendorDashboard($vendorId);
        $avgRating = $this->reviewModel->getAverageRating($vendorId);
        $dist      = $this->reviewModel->getRatingDistribution($vendorId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $reviewId = (int)$this->post('review_id', 0);
            $reply    = Sanitizer::textarea($this->post('reply', ''), 500);

            if (empty($reply)) {
                $this->redirectWith('vendor/reviews', 'error', 'Reply cannot be empty.');
                return;
            }

            $this->reviewModel->addVendorReply($reviewId, $vendorId, $reply);
            $this->redirectWith('vendor/reviews', 'success', 'Reply posted successfully.');
            return;
        }

        $this->view('vendor/reviews', [
            'pageTitle'  => 'My Reviews - ' . SITE_NAME,
            'reviews'    => $reviews,
            'avgRating'  => $avgRating,
            'dist'       => $dist,
            'csrfField'  => CSRF::field(),
            'flashSuccess' => $this->session->getFlash('success'),
            'flashError'   => $this->session->getFlash('error'),
        ]);
    }

    // ============================================================
    // Complaints
    // ============================================================
    public function complaints(): void
    {
        $this->requireVendorLogin();

        $vendorId   = Auth::vendorId();
        $complaints = $this->complaintModel->getForVendor($vendorId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $complaintId = (int)$this->post('complaint_id', 0);
            $response    = Sanitizer::textarea($this->post('response', ''), 1000);

            if (empty($response)) {
                $this->redirectWith('vendor/complaints', 'error', 'Response cannot be empty.');
                return;
            }

            $this->complaintModel->addVendorResponse($complaintId, $vendorId, $response);
            $this->redirectWith('vendor/complaints', 'success', 'Response submitted successfully.');
            return;
        }

        $this->view('vendor/complaints', [
            'pageTitle'    => 'My Complaints - ' . SITE_NAME,
            'complaints'   => $complaints,
            'categories'   => ComplaintModel::getCategories(),
            'csrfField'    => CSRF::field(),
            'flashSuccess' => $this->session->getFlash('success'),
            'flashError'   => $this->session->getFlash('error'),
        ]);
    }

    // ============================================================
    // Subscription Status & Renewal
    // ============================================================
    public function subscription(): void
    {
        $this->requireVendorLogin();

        $vendorId = Auth::vendorId();
        $vendor   = $this->vendorModel->find($vendorId);
        $subInfo  = $this->subModel->getExpiryInfo($vendorId);
        $allSubs  = $this->subModel->getAllForVendor($vendorId);

        // Handle upgrade/downgrade request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $action  = $this->post('action', '');
            $newPlan = $this->post('plan', '');

            if (!isValidPlan($vendor['vendor_type'], $newPlan)) {
                $this->redirectWith('vendor/subscription', 'error', 'Invalid plan selected.');
                return;
            }

            $currentPlan = $vendor['plan_type'];

            if ($action === 'upgrade') {
                $planLevels = ['basic' => 1, 'premium' => 2, 'featured' => 3];
                if (($planLevels[$newPlan] ?? 0) <= ($planLevels[$currentPlan] ?? 0)) {
                    $this->redirectWith('vendor/subscription', 'error', 'Please select a higher plan to upgrade.');
                    return;
                }
                $this->subModel->requestUpgrade($vendorId, $currentPlan, $newPlan);
                Notification::sendToAdmin(
                    'Upgrade Request',
                    "Vendor #{$vendorId} ({$vendor['business_name']}) requested upgrade from $currentPlan to $newPlan.",
                    Notification::TYPE_APPROVAL,
                    'admin/subscriptions/upgrades'
                );
                $this->redirectWith('vendor/subscription', 'success', 'Upgrade request submitted. Admin will review and activate your new plan.');
                return;
            }

            if ($action === 'downgrade') {
                $planLevels = ['basic' => 1, 'premium' => 2, 'featured' => 3];
                if (($planLevels[$newPlan] ?? 0) >= ($planLevels[$currentPlan] ?? 0)) {
                    $this->redirectWith('vendor/subscription', 'error', 'Please select a lower plan to downgrade.');
                    return;
                }
                $this->subModel->requestDowngrade($vendorId, $currentPlan, $newPlan);
                $this->redirectWith('vendor/subscription', 'success', 'Downgrade scheduled. Your plan will change after the current subscription expires.');
                return;
            }
            
            

            // Renew — redirect to payment
            if ($action === 'renew') {
                $this->session->set('renew_vendor_id', $vendorId);
                $this->redirect('vendor/payment?renew=1');
                return;
            }
        }

        $this->view('vendor/subscription', [
            'pageTitle'    => 'My Subscription - ' . SITE_NAME,
            'vendor'       => $vendor,
            'subInfo'      => $subInfo,
            'allSubs'      => $allSubs,
            'csrfField'    => CSRF::field(),
            'flashSuccess' => $this->session->getFlash('success'),
            'flashError'   => $this->session->getFlash('error'),
        ]);
    }

    // ============================================================
    // Payment History & Receipts
    // ============================================================
    public function paymentHistory(): void
    {
        $this->requireVendorLogin();

        $vendorId = Auth::vendorId();
        $payments = $this->paymentModel->getForVendor($vendorId);

        // Receipt download
        $receiptId = (int)$this->get('receipt', 0);
        if ($receiptId > 0) {
            $payment = $this->paymentModel->getWithSubscription($receiptId);
            if ($payment && (int)$payment['vendor_id'] === $vendorId) {
                $this->generateReceipt($payment);
                return;
            }
        }

        $this->view('vendor/payment-history', [
            'pageTitle' => 'Payment History - ' . SITE_NAME,
            'payments'  => $payments,
        ]);
    }

    // ============================================================
    // Notifications
    // ============================================================
    public function notifications(): void
    {
        $this->requireVendorLogin();

        $vendorId = Auth::vendorId();
        $page     = max(1, (int)$this->get('page', 1));
        $result   = $this->notifModel->getAllForRecipient('vendor', $vendorId, $page, 20);

        $this->notifModel->markAllRead('vendor', $vendorId);

        $this->view('vendor/notifications', [
            'pageTitle'     => 'Notifications - ' . SITE_NAME,
            'notifications' => $result['data'],
            'pagination'    => $result,
        ]);
    }

    // ============================================================
    // Generate basic receipt output
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
        echo '<br><button onclick="window.print()">🖨️ Print Receipt</button>';
        echo '</body></html>';
        exit;
    }
}