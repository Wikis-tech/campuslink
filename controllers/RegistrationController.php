<?php
/**
 * CampusLink - Vendor Registration Controller
 * Multi-step registration for student and community vendors.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Uploader.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../core/SMS.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BlacklistModel.php';
require_once __DIR__ . '/../models/TermsAcceptanceModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class RegistrationController extends Controller
{
    private VendorModel          $vendorModel;
    private CategoryModel        $categoryModel;
    private BlacklistModel       $blacklistModel;
    private TermsAcceptanceModel $termsModel;
    private Mailer               $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->vendorModel   = new VendorModel();
        $this->categoryModel = new CategoryModel();
        $this->blacklistModel = new BlacklistModel();
        $this->termsModel    = new TermsAcceptanceModel();
        $this->mailer        = new Mailer();
    }

    // ============================================================
    // Registration type selection page
    // ============================================================
    public function index(): void
    {
        $type = $this->get('type', '');

        if ($type === 'student') {
            $this->student();
            return;
        }

        if ($type === 'community') {
            $this->community();
            return;
        }

        $this->view('vendor/register-select', [
            'pageTitle' => 'Register as Vendor - ' . SITE_NAME,
        ]);
    }

    // ============================================================
    // Student Vendor Registration
    // ============================================================
    public function student(): void
    {
        if (Auth::isVendorLoggedIn()) {
            $this->redirect('vendor/dashboard');
            return;
        }

        $categories = $this->categoryModel->getForSelect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $ip   = getClientIP();
            $data = [
                'full_name'          => $this->post('full_name', ''),
                'matric_number'      => strtoupper($this->post('matric_number', '')),
                'school_email'       => Sanitizer::email($this->post('school_email', '')),
                'personal_email'     => Sanitizer::email($this->post('personal_email', '')),
                'phone'              => Sanitizer::phone($this->post('phone', '')),
                'whatsapp_number'    => Sanitizer::phone($this->post('whatsapp_number', '')),
                'level'              => $this->post('level', ''),
                'department'         => $this->post('department', ''),
                'business_name'      => $this->post('business_name', ''),
                'category_id'        => (int)$this->post('category_id', 0),
                'description'        => Sanitizer::textarea($this->post('description', ''), 1000),
                'price_range'        => $this->post('price_range', ''),
                'years_experience'   => (int)$this->post('years_experience', 0),
                'operating_location' => $this->post('operating_location', ''),
                'plan_type'          => $this->post('plan_type', 'basic'),
                'password'           => $_POST['password'] ?? '',
                'password_confirmation' => $_POST['password_confirmation'] ?? '',
                'terms_accepted'     => $this->post('terms_accepted', ''),
                'age_confirmed'      => $this->post('age_confirmed', ''),
            ];

            $validator = Validator::make($data, [
                'full_name'       => 'required|min:3|max:100',
                'matric_number'   => 'required|matric',
                'school_email'    => 'required|email|school_email',
                'personal_email'  => 'required|email',
                'phone'           => 'required|phone',
                'whatsapp_number' => 'required|phone',
                'level'           => 'required|in:100,200,300,400,500,600,PG',
                'department'      => 'required|min:3|max:100',
                'business_name'   => 'required|min:3|max:100',
                'category_id'     => 'required|numeric',
                'description'     => 'required|min:30|max:1000',
                'plan_type'       => 'required|in:basic,premium,featured',
                'password'        => 'required|password|confirmed',
                'terms_accepted'  => 'accepted',
                'age_confirmed'   => 'accepted',
            ]);

            if ($validator->fails()) {
                $this->view('vendor/register-student', [
                    'pageTitle'  => 'Student Vendor Registration - ' . SITE_NAME,
                    'categories' => $categories,
                    'errors'     => $validator->errors(),
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // Uniqueness checks
            $errors = $this->checkVendorUniqueness($data, 'student');
            if (!empty($errors)) {
                $this->view('vendor/register-student', [
                    'pageTitle'  => 'Student Vendor Registration - ' . SITE_NAME,
                    'categories' => $categories,
                    'errors'     => $errors,
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // Blacklist check
            if ($this->blacklistModel->isEmailBlacklisted($data['school_email']) ||
                $this->blacklistModel->isIPBlacklisted($ip)) {
                $this->redirectWith('vendor/register?type=student', 'error', 'Registration not available.');
                return;
            }

            // File uploads
            $uploadErrors = [];
            $data = $this->handleVendorUploads($data, $uploadErrors, 'student');

            if (!empty($uploadErrors)) {
                $this->view('vendor/register-student', [
                    'pageTitle'  => 'Student Vendor Registration - ' . SITE_NAME,
                    'categories' => $categories,
                    'errors'     => $uploadErrors,
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // Create vendor
            $vendorId = $this->vendorModel->createStudentVendor($data);

            if (!$vendorId) {
                $this->redirectWith('vendor/register?type=student', 'error', 'Registration failed. Please try again.');
                return;
            }

            // Record terms
            $this->termsModel->recordAll((int)$vendorId, 'vendor', $ip);

            // Send emails
            $this->mailer->sendVendorRegistrationReceived(
                $data['personal_email'],
                $data['full_name'],
                $data['business_name']
            );

            // Notify admin
            Notification::sendToAdmin(
                'New Vendor Registration',
                "Student vendor '{$data['business_name']}' by {$data['full_name']} is pending review.",
                Notification::TYPE_APPROVAL,
                'admin/vendors/pending'
            );

            // Store for payment
            $this->session->set('pending_vendor_id', (int)$vendorId);
            $this->session->set('pending_vendor_type', 'student');
            $this->session->set('pending_plan', $data['plan_type']);
            $this->session->set('pending_phone', $data['phone']);

            $this->redirect('vendor/payment');
            return;
        }

        $this->view('vendor/register-student', [
            'pageTitle'  => 'Student Vendor Registration - ' . SITE_NAME,
            'categories' => $categories,
            'errors'     => [],
            'old'        => [],
            'csrfField'  => CSRF::field(),
        ]);
    }

    // ============================================================
    // Community Vendor Registration
    // ============================================================
    public function community(): void
    {
        if (Auth::isVendorLoggedIn()) {
            $this->redirect('vendor/dashboard');
            return;
        }

        $categories = $this->categoryModel->getForSelect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $ip   = getClientIP();
            $data = [
                'full_name'        => $this->post('full_name', ''),
                'business_name'    => $this->post('business_name', ''),
                'working_email'    => Sanitizer::email($this->post('working_email', '')),
                'phone'            => Sanitizer::phone($this->post('phone', '')),
                'whatsapp_number'  => Sanitizer::phone($this->post('whatsapp_number', '')),
                'business_address' => $this->post('business_address', ''),
                'category_id'      => (int)$this->post('category_id', 0),
                'description'      => Sanitizer::textarea($this->post('description', ''), 1000),
                'price_range'      => $this->post('price_range', ''),
                'years_operation'  => (int)$this->post('years_operation', 0),
                'plan_type'        => $this->post('plan_type', 'basic'),
                'password'         => $_POST['password'] ?? '',
                'password_confirmation' => $_POST['password_confirmation'] ?? '',
                'terms_accepted'   => $this->post('terms_accepted', ''),
                'age_confirmed'    => $this->post('age_confirmed', ''),
            ];

            $validator = Validator::make($data, [
                'full_name'        => 'required|min:3|max:100',
                'business_name'    => 'required|min:3|max:100',
                'working_email'    => 'required|email',
                'phone'            => 'required|phone',
                'whatsapp_number'  => 'required|phone',
                'business_address' => 'required|min:10|max:255',
                'category_id'      => 'required|numeric',
                'description'      => 'required|min:30|max:1000',
                'plan_type'        => 'required|in:basic,premium,featured',
                'password'         => 'required|password|confirmed',
                'terms_accepted'   => 'accepted',
                'age_confirmed'    => 'accepted',
            ]);

            if ($validator->fails()) {
                $this->view('vendor/register-community', [
                    'pageTitle'  => 'Community Vendor Registration - ' . SITE_NAME,
                    'categories' => $categories,
                    'errors'     => $validator->errors(),
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // Check uniqueness
            $errors = $this->checkVendorUniqueness($data, 'community');
            if (!empty($errors)) {
                $this->view('vendor/register-community', [
                    'pageTitle'  => 'Community Vendor Registration - ' . SITE_NAME,
                    'categories' => $categories,
                    'errors'     => $errors,
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // File uploads
            $uploadErrors = [];
            $data = $this->handleVendorUploads($data, $uploadErrors, 'community');

            if (!empty($uploadErrors)) {
                $this->view('vendor/register-community', [
                    'pageTitle'  => 'Community Vendor Registration - ' . SITE_NAME,
                    'categories' => $categories,
                    'errors'     => $uploadErrors,
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // Create vendor
            $vendorId = $this->vendorModel->createCommunityVendor($data);

            if (!$vendorId) {
                $this->redirectWith('vendor/register?type=community', 'error', 'Registration failed. Please try again.');
                return;
            }

            // Record terms
            $this->termsModel->recordAll((int)$vendorId, 'vendor', $ip);

            // Notify
            $this->mailer->sendVendorRegistrationReceived(
                $data['working_email'],
                $data['full_name'],
                $data['business_name']
            );

            Notification::sendToAdmin(
                'New Community Vendor',
                "Community vendor '{$data['business_name']}' by {$data['full_name']} needs review.",
                Notification::TYPE_APPROVAL,
                'admin/vendors/pending'
            );

            $this->session->set('pending_vendor_id', (int)$vendorId);
            $this->session->set('pending_vendor_type', 'community');
            $this->session->set('pending_plan', $data['plan_type']);
            $this->session->set('pending_phone', $data['phone']);

            $this->redirect('vendor/payment');
            return;
        }

        $this->view('vendor/register-community', [
            'pageTitle'  => 'Community Vendor Registration - ' . SITE_NAME,
            'categories' => $categories,
            'errors'     => [],
            'old'        => [],
            'csrfField'  => CSRF::field(),
        ]);
    }

    // ============================================================
    // Handle file uploads for both vendor types
    // ============================================================
    private function handleVendorUploads(
        array  $data,
        array  &$errors,
        string $vendorType
    ): array {
        $docTypes = unserialize(ALLOWED_DOC_TYPES);

        // Logo (required)
        if (empty($_FILES['logo']['name'])) {
            $errors['logo'] = 'Business logo is required.';
        } else {
            $uploader = new Uploader(UPLOAD_LOGOS, unserialize(ALLOWED_IMAGE_TYPES));
            $result   = $uploader->upload($_FILES['logo'], 'logo');
            if ($uploader->hasError()) {
                $errors['logo'] = $uploader->getError();
            } else {
                $data['logo'] = $result;
            }
        }

        // Service photo (required for both)
        if (empty($_FILES['service_photo']['name'])) {
            $errors['service_photo'] = 'Service photo is required.';
        } else {
            $uploader = new Uploader(UPLOAD_SERVICE, unserialize(ALLOWED_IMAGE_TYPES));
            $result   = $uploader->upload($_FILES['service_photo'], 'service');
            if ($uploader->hasError()) {
                $errors['service_photo'] = $uploader->getError();
            } else {
                $data['service_photo'] = $result;
            }
        }

        if ($vendorType === 'student') {
            // Student ID Card (required)
            if (empty($_FILES['id_card']['name'])) {
                $errors['id_card'] = 'Student ID card upload is required.';
            } else {
                $uploader = new Uploader(UPLOAD_ID_CARDS, $docTypes);
                $result   = $uploader->upload($_FILES['id_card'], 'idcard');
                if ($uploader->hasError()) {
                    $errors['id_card'] = $uploader->getError();
                } else {
                    $data['id_card_file'] = $result;
                }
            }

            // Selfie with ID (required)
            if (empty($_FILES['selfie']['name'])) {
                $errors['selfie'] = 'Selfie holding your ID card is required.';
            } else {
                $uploader = new Uploader(UPLOAD_SELFIES, unserialize(ALLOWED_IMAGE_TYPES));
                $result   = $uploader->upload($_FILES['selfie'], 'selfie');
                if ($uploader->hasError()) {
                    $errors['selfie'] = $uploader->getError();
                } else {
                    $data['selfie_file'] = $result;
                }
            }
        }

        if ($vendorType === 'community') {
            // CAC Certificate (optional but recommended)
            if (!empty($_FILES['cac_certificate']['name'])) {
                $uploader = new Uploader(UPLOAD_CAC, $docTypes);
                $result   = $uploader->upload($_FILES['cac_certificate'], 'cac');
                if ($uploader->hasError()) {
                    $errors['cac_certificate'] = $uploader->getError();
                } else {
                    $data['cac_certificate'] = $result;
                }
            }

            // Government ID (required)
            if (empty($_FILES['gov_id']['name'])) {
                $errors['gov_id'] = 'Government-issued ID is required.';
            } else {
                $uploader = new Uploader(UPLOAD_GOV_IDS, $docTypes);
                $result   = $uploader->upload($_FILES['gov_id'], 'govid');
                if ($uploader->hasError()) {
                    $errors['gov_id'] = $uploader->getError();
                } else {
                    $data['gov_id_file'] = $result;
                }
            }
        }

        return $data;
    }

    // ============================================================
    // Check vendor uniqueness
    // ============================================================
    private function checkVendorUniqueness(array $data, string $type): array
    {
        $errors = [];
        $email  = $data['school_email'] ?? $data['working_email'] ?? '';

        if (!empty($email) && $this->vendorModel->findByEmail($email)) {
            $key           = isset($data['school_email']) ? 'school_email' : 'working_email';
            $errors[$key]  = 'This email is already registered as a vendor.';
        }

        if (!empty($data['matric_number']) && !$this->vendorModel->matricAvailable($data['matric_number'])) {
            $errors['matric_number'] = 'This matric number is already registered.';
        }

        if (!empty($data['phone']) && $this->vendorModel->findByPhone($data['phone'])) {
            $errors['phone'] = 'This phone number is already registered.';
        }

        return $errors;
    }
}