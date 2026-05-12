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
    // Submit Complaint (API for vendor profile modal)
    // ============================================================
    public function submit(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->validateCSRF();

        $userId = Auth::userId();

        $vendorId = (int)$this->post('vendor_id', 0);
        $complaintType = $this->post('complaint_type', '');
        $description = Sanitizer::textarea($this->post('description', ''), MAX_COMPLAINT_LENGTH);

        // Validate
        $validator = Validator::make(
            ['vendor_id' => $vendorId, 'complaint_type' => $complaintType, 'description' => $description],
            [
                'vendor_id' => 'required|numeric',
                'complaint_type' => 'required|in:poor_service,overcharge,incomplete,fraud,other',
                'description' => 'required|min:10|max:' . MAX_COMPLAINT_LENGTH,
            ]
        );

        if ($validator->fails()) {
            $this->jsonError($validator->firstErrorMessage());
            return;
        }

        // Check vendor exists
        $vendor = $this->vendorModel->find($vendorId);
        if (!$vendor) {
            $this->jsonError('Vendor not found.');
            return;
        }

        // Rate limit: max 3 complaints per user per vendor
        $existingCount = $this->complaintModel->count(
            "user_id = ? AND vendor_id = ?",
            [$userId, $vendorId]
        );
        if ($existingCount >= 3) {
            $this->jsonError('You have already submitted 3 complaints against this vendor.');
            return;
        }

        $complaintId = $this->complaintModel->submit([
            'user_id' => $userId,
            'vendor_id' => $vendorId,
            'category' => $complaintType,
            'description' => $description,
        ]);

        if (!$complaintId) {
            $this->jsonError('Failed to submit complaint. Please try again.');
            return;
        }

        // Notify vendor
        $vendorEmail = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
        if ($vendorEmail) {
            $this->mailer->sendComplaintNotification(
                $vendorEmail,
                $vendor['full_name'],
                $vendor['business_name'],
                $complaintType,
                SITE_URL . '/vendor/complaints'
            );
        }

        Notification::sendToVendor(
            $vendorId,
            'New Complaint Received ⚠️',
            "A new complaint has been filed against {$vendor['business_name']}.",
            Notification::TYPE_COMPLAINT,
            'vendor/complaints'
        );

        $this->jsonSuccess('Complaint submitted successfully. Our team will review it shortly.');
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