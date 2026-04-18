<?php
/**
 * CampusLink - Notification Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController extends Controller
{
    private NotificationModel $notifModel;

    public function __construct()
    {
        parent::__construct();
        $this->notifModel = new NotificationModel();
    }

    // ============================================================
    // Mark single notification as read (AJAX)
    // ============================================================
    public function markRead(): void
    {
        $this->requirePost();
        $this->validateCSRF();

        $notifId     = (int)$this->post('notification_id', 0);
        $recipientId = Auth::userId() ?: Auth::vendorId();

        if (!$notifId || !$recipientId) {
            $this->jsonError('Invalid request.');
            return;
        }

        $this->notifModel->markRead($notifId, $recipientId);
        $this->jsonSuccess('Marked as read.');
    }

    // ============================================================
    // Mark all as read (AJAX)
    // ============================================================
    public function markAllRead(): void
    {
        $this->requirePost();

        if (Auth::isLoggedIn()) {
            $this->notifModel->markAllRead('user', Auth::userId());
        } elseif (Auth::isVendorLoggedIn()) {
            $this->notifModel->markAllRead('vendor', Auth::vendorId());
        }

        $this->jsonSuccess('All notifications marked as read.');
    }

    // ============================================================
    // Get unread count (AJAX)
    // ============================================================
    public function count(): void
    {
        $type = Auth::isLoggedIn() ? 'user' : 'vendor';
        $id   = Auth::userId() ?: Auth::vendorId();

        if (!$id) {
            $this->jsonSuccess('', ['count' => 0]);
            return;
        }

        $count = $this->notifModel->countUnread($type, $id);
        $this->jsonSuccess('', ['count' => $count]);
    }
}