<?php
/**
 * CampusLink - Complaint Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Uploader.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/ComplaintModel.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class ComplaintController extends Controller
{
    private ComplaintModel $complaintModel;
    private VendorModel    $vendorModel;
    private Mailer         $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->complaintModel = new ComplaintModel();
        $this->vendorModel    = new VendorModel();
        $this->mailer         = new Mailer();
    }

    // ============================================================
    // Submit Complaint
    // ============================================================
    public function submit(): void
    {
        $this->requireLogin();

        $vendorId = (int)$this->get('vendor_id', 0);
        $vendor   = $vendorId ? $this->vendorModel->find($vendorId) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $userId = Auth::userId();

            $data = [
                'vendor_id'   => (int)$this->post('vendor_id', 0),
                'category'    => $this->post('category', ''),
                'description' => Sanitizer::textarea($this->post('description', ''), MAX_COMPLAINT_LENGTH),
            ];

            $validator = Validator::make($data, [
                'vendor_id'   => 'required|numeric',
                'category'    => 'required|in:' . implode(',', array_keys(ComplaintModel::getCategories())),
                'description' => 'required|min:30|max:' . MAX_COMPLAINT_LENGTH,
            ]);

            if ($validator->fails()) {
                $this->view('browse/complaint-form', [
                    'pageTitle'  => 'Submit Complaint - ' . SITE_NAME,
                    'vendor'     => $vendor,
                    'categories' => ComplaintModel::getCategories(),
                    'errors'     => $validator->errors(),
                    'old'        => $data,
                    'csrfField'  => CSRF::field(),
                ]);
                return;
            }

            // Check vendor exists
            $complaintVendor = $this->vendorModel->find($data['vendor_id']);
            if (!$complaintVendor) {
                $this->jsonError('Vendor not found.');
                return;
            }

            // Rate limit: max 3 complaints per user per vendor
            $existingCount = $this->complaintModel->count(
                "user_id = ? AND vendor_id = ?",
                [$userId, $data['vendor_id']]
            );
            if ($existingCount >= 3) {
                $this->redirectWith(
                    'user/my-complaints',
                    'error',
                    'You have already submitted 3 complaints against this vendor.'
                );
                return;
            }

            // Handle evidence upload (optional)
            $data['evidence_file'] = null;
            if (!empty($_FILES['evidence']['name'])) {
                $uploader = new Uploader(
                    UPLOAD_EVIDENCE,
                    array_merge(
                        unserialize(ALLOWED_IMAGE_TYPES),
                        ['application/pdf']
                    ),
                    UPLOAD_MAX_SIZE
                );
                $result = $uploader->upload($_FILES['evidence'], 'evidence_' . $userId);

                if ($uploader->hasError()) {
                    $this->view('browse/complaint-form', [
                        'pageTitle'  => 'Submit Complaint - ' . SITE_NAME,
                        'vendor'     => $complaintVendor,
                        'categories' => ComplaintModel::getCategories(),
                        'errors'     => ['evidence' => $uploader->getError()],
                        'old'        => $data,
                        'csrfField'  => CSRF::field(),
                    ]);
                    return;
                }

                $data['evidence_file'] = $result;
            }

            $data['user_id'] = $userId;

            $complaintId = $this->complaintModel->submit($data);

            if (!$complaintId) {
                $this->redirectWith('user/my-complaints', 'error', 'Failed to submit complaint. Please try again.');
                return;
            }

            // Notify vendor
            $vendorEmail = $complaintVendor['school_email'] ?? $complaintVendor['working_email'] ?? '';
            if ($vendorEmail) {
                $this->mailer->sendComplaintNotification(
                    $vendorEmail,
                    $complaintVendor['full_name'],
                    $complaintVendor['business_name'],
                    ComplaintModel::getCategories()[$data['category']] ?? $data['category'],
                    SITE_URL . '/vendor/complaints'
                );
            }

            // Notify vendor in-app
            Notification::sendToVendor(
                $data['vendor_id'],
                '⚠️ Complaint Filed Against Your Business',
                'A complaint has been filed under: ' . (ComplaintModel::getCategories()[$data['category']] ?? $data['category']),
                Notification::TYPE_COMPLAINT,
                'vendor/complaints'
            );

            // Notify admin
            Notification::sendToAdmin(
                'New Complaint Filed',
                "A complaint has been filed against vendor '{$complaintVendor['business_name']}'.",
                Notification::TYPE_COMPLAINT,
                'admin/complaints'
            );

            // Check complaint threshold for suspension review
            $verifiedCount = $this->vendorModel->getVerifiedComplaintCount($data['vendor_id']);
            if ($verifiedCount >= COMPLAINT_TRIGGER_COUNT) {
                Notification::sendToAdmin(
                    '🚨 Suspension Review Triggered',
                    "Vendor '{$complaintVendor['business_name']}' has reached $verifiedCount verified complaints. Review for suspension.",
                    Notification::TYPE_WARNING,
                    'admin/complaints'
                );
            }

            $this->redirectWith('user/my-complaints', 'success', 'Complaint submitted successfully. Ticket ID will appear in your complaints list.');
            return;
        }

        $this->view('browse/complaint-form', [
            'pageTitle'  => 'Submit a Complaint - ' . SITE_NAME,
            'vendor'     => $vendor,
            'categories' => ComplaintModel::getCategories(),
            'errors'     => [],
            'old'        => [],
            'csrfField'  => CSRF::field(),
        ]);
    }

    // ============================================================
    // Track Complaint Status
    // ============================================================
    public function track(): void
    {
        $this->requireLogin();

        $ticketId  = $this->get('ticket', '');
        $complaint = null;

        if (!empty($ticketId)) {
            $complaint = $this->complaintModel->findByTicket($ticketId);

            // Ensure only the complaint owner can track
            if ($complaint && (int)$complaint['user_id'] !== Auth::userId()) {
                $complaint = null;
            }
        }

        $this->view('browse/track-complaint', [
            'pageTitle'  => 'Track Complaint - ' . SITE_NAME,
            'ticketId'   => $ticketId,
            'complaint'  => $complaint,
            'categories' => ComplaintModel::getCategories(),
            'csrfField'  => CSRF::field(),
        ]);
    }
}