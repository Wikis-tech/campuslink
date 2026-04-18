<?php
/**
 * CampusLink - Review Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/CSRF.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class ReviewController extends Controller
{
    private ReviewModel  $reviewModel;
    private VendorModel  $vendorModel;
    private Mailer       $mailer;

    public function __construct()
    {
        parent::__construct();
        $this->reviewModel = new ReviewModel();
        $this->vendorModel = new VendorModel();
        $this->mailer      = new Mailer();
    }

    // ============================================================
    // Submit Review (POST only)
    // ============================================================
    public function submit(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->validateCSRF();

        $userId   = Auth::userId();
        $vendorId = (int)$this->post('vendor_id', 0);
        $rating   = (int)$this->post('rating', 0);
        $review   = Sanitizer::textarea($this->post('review', ''), MAX_REVIEW_LENGTH);

        // Validate
        $validator = Validator::make(
            ['vendor_id' => $vendorId, 'rating' => $rating, 'review' => $review],
            [
                'vendor_id' => 'required|numeric',
                'rating'    => 'required|rating',
                'review'    => 'required|min:10|max:' . MAX_REVIEW_LENGTH,
            ]
        );

        if ($validator->fails()) {
            $this->jsonError($validator->firstErrorMessage());
            return;
        }

        // Check vendor exists and is active
        $vendor = $this->vendorModel->find($vendorId);
        if (!$vendor || $vendor['status'] !== 'active') {
            $this->jsonError('Vendor not found or not active.');
            return;
        }

        // One review per vendor per user
        if ($this->reviewModel->hasReviewed($userId, $vendorId)) {
            $this->jsonError('You have already reviewed this vendor. You can edit your existing review.');
            return;
        }

        $reviewId = $this->reviewModel->submit([
            'vendor_id' => $vendorId,
            'user_id'   => $userId,
            'rating'    => $rating,
            'review'    => $review,
        ]);

        if (!$reviewId) {
            $this->jsonError('Failed to submit review. Please try again.');
            return;
        }

        // Notify vendor
        $vendorEmail = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
        if ($vendorEmail) {
            $this->mailer->sendNewReviewNotification(
                $vendorEmail,
                $vendor['full_name'],
                $vendor['business_name'],
                $rating,
                SITE_URL . '/vendor/reviews'
            );
        }

        Notification::sendToVendor(
            $vendorId,
            'New Review Submitted ⭐',
            "A new {$rating}-star review has been submitted for {$vendor['business_name']}. Pending admin approval.",
            Notification::TYPE_REVIEW,
            'vendor/reviews'
        );

        $this->jsonSuccess('Review submitted successfully. It will appear after moderation.');
    }

    // ============================================================
    // Edit Review (POST only)
    // ============================================================
    public function edit(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->validateCSRF();

        $userId   = Auth::userId();
        $reviewId = (int)$this->post('review_id', 0);
        $rating   = (int)$this->post('rating', 0);
        $review   = Sanitizer::textarea($this->post('review', ''), MAX_REVIEW_LENGTH);

        $validator = Validator::make(
            ['rating' => $rating, 'review' => $review],
            [
                'rating' => 'required|rating',
                'review' => 'required|min:10|max:' . MAX_REVIEW_LENGTH,
            ]
        );

        if ($validator->fails()) {
            $this->jsonError($validator->firstErrorMessage());
            return;
        }

        $result = $this->reviewModel->editReview($reviewId, $userId, [
            'rating' => $rating,
            'review' => $review,
        ]);

        if (!$result) {
            $this->jsonError('Could not edit this review. It may already be approved or does not belong to you.');
            return;
        }

        $this->jsonSuccess('Review updated. It will be re-moderated before appearing.');
    }

    // ============================================================
    // Delete Review (POST only)
    // ============================================================
    public function delete(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->validateCSRF();

        $userId   = Auth::userId();
        $reviewId = (int)$this->post('review_id', 0);

        $result = $this->reviewModel->deleteByUser($reviewId, $userId);

        if (!$result) {
            $this->jsonError('Could not delete this review. It may already be approved or does not belong to you.');
            return;
        }

        $this->jsonSuccess('Review deleted successfully.');
    }
}