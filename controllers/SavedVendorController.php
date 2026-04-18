<?php
/**
 * CampusLink - Saved Vendor Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SavedVendorModel.php';

class SavedVendorController extends Controller
{
    private SavedVendorModel $savedModel;

    public function __construct()
    {
        parent::__construct();
        $this->savedModel = new SavedVendorModel();
    }

    // ============================================================
    // Toggle save/unsave vendor (AJAX POST)
    // ============================================================
    public function toggle(): void
    {
        $this->requirePost();

        if (!Auth::isLoggedIn()) {
            $this->jsonError('Please log in to save vendors.', 401);
            return;
        }

        $this->validateCSRF();

        $userId   = Auth::userId();
        $vendorId = (int)$this->post('vendor_id', 0);

        if (!$vendorId) {
            $this->jsonError('Invalid vendor.');
            return;
        }

        $result = $this->savedModel->toggle($userId, $vendorId);

        $this->jsonSuccess($result['message'], ['saved' => $result['saved']]);
    }
}