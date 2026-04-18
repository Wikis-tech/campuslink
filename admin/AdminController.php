<?php
/**
 * CampusLink - Admin Controller
 * All admin actions live here.
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

class AdminController {

    private DB     $db;
    private Mailer $mailer;

    public function __construct() {
        $this->db     = DB::getInstance();
        $this->mailer = new Mailer();
    }

    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════

    private function render(string $view, array $data = []): void {
        extract($data);
        $content = '';
        ob_start();
        require VIEWS_PATH . '/admin/' . $view . '.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/admin/layouts/admin.php';
    }

    private function redirect(string $path, string $msg = '', string $type = 'success'): void {
        if ($msg) Session::setFlash($type, $msg);
        header('Location: ' . SITE_URL . '/admin/' . ltrim($path, '/'));
        exit;
    }

    private function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard');
        }
        CSRF::verify($_POST['csrf_token'] ?? '');
    }

    private function paginate(int $total, int $perPage = 20): array {
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $pages = max(1, (int)ceil($total / $perPage));
        return [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'total_pages'  => $pages,
            'offset'       => ($page - 1) * $perPage,
            'has_prev'     => $page > 1,
            'has_next'     => $page < $pages,
        ];
    }

    // ════════════════════════════════════════════════════════════
    // DASHBOARD
    // ════════════════════════════════════════════════════════════

    public function dashboard(): void {
        AdminAuth::guard();

        $stats = [
            'total_vendors'      => $this->db->value('SELECT COUNT(*) FROM vendors'),
            'pending_vendors'    => $this->db->value("SELECT COUNT(*) FROM vendors WHERE status='pending'"),
            'active_vendors'     => $this->db->value("SELECT COUNT(*) FROM vendors WHERE status='active'"),
            'suspended_vendors'  => $this->db->value("SELECT COUNT(*) FROM vendors WHERE status='suspended'"),
            'total_users'        => $this->db->value('SELECT COUNT(*) FROM users'),
            'active_users'       => $this->db->value("SELECT COUNT(*) FROM users WHERE status='active'"),
            'open_complaints'    => $this->db->value("SELECT COUNT(*) FROM complaints WHERE status IN ('submitted','under_review')"),
            'pending_reviews'    => $this->db->value("SELECT COUNT(*) FROM reviews WHERE status='pending'"),
            'total_revenue'      => $this->db->value("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success'"),
            'month_revenue'      => $this->db->value("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"),
        ];

        // Recent activity feed
        $recentActivity = $this->db->rows(
            "SELECT 'vendor_registered' AS type, business_name AS label, created_at
               FROM vendors
              UNION ALL
             SELECT 'complaint_filed', ticket_id, created_at
               FROM complaints
              UNION ALL
             SELECT 'payment_received', paystack_reference, created_at
               FROM payments WHERE status='success'
             ORDER BY created_at DESC LIMIT 15"
        );

        // Revenue chart data (last 6 months)
        $revenueChart = $this->db->rows(
            "SELECT DATE_FORMAT(created_at,'%b %Y') AS month,
                    SUM(amount) AS total
               FROM payments
              WHERE status='success'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY YEAR(created_at), MONTH(created_at)
              ORDER BY created_at ASC"
        );

        // Pending approvals
        $pendingVendors = $this->db->rows(
            "SELECT v.*, c.name AS category_name
               FROM vendors v
          LEFT JOIN categories c ON v.category_id = c.id
              WHERE v.status = 'pending'
              ORDER BY v.created_at ASC
              LIMIT 5"
        );

        // Open complaints
        $openComplaints = $this->db->rows(
            "SELECT c.*, v.business_name
               FROM complaints c
          LEFT JOIN vendors v ON c.vendor_id = v.id
              WHERE c.status IN ('submitted','under_review')
              ORDER BY c.created_at DESC
              LIMIT 5"
        );

        $this->render('dashboard', compact(
            'stats', 'recentActivity', 'revenueChart',
            'pendingVendors', 'openComplaints'
        ));
    }

    // ════════════════════════════════════════════════════════════
    // VENDORS
    // ════════════════════════════════════════════════════════════

    public function vendorsIndex(): void {
        AdminAuth::guard();

        $search  = trim($_GET['q'] ?? '');
        $status  = $_GET['status'] ?? '';
        $type    = $_GET['type']   ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = '(v.business_name LIKE ? OR v.full_name LIKE ? OR v.school_email LIKE ? OR v.working_email LIKE ?)';
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s,$s,$s]);
        }
        if ($status) { $where[] = 'v.status = ?';      $params[] = $status; }
        if ($type)   { $where[] = 'v.vendor_type = ?'; $params[] = $type;   }

        $whereStr = implode(' AND ', $where);
        $total    = $this->db->value(
            "SELECT COUNT(*) FROM vendors v WHERE {$whereStr}", $params
        );
        $pag      = $this->paginate($total);

        $vendors = $this->db->rows(
            "SELECT v.*, c.name AS category_name,
                    (SELECT COUNT(*) FROM reviews r WHERE r.vendor_id=v.id) AS review_count,
                    (SELECT COUNT(*) FROM complaints cp WHERE cp.vendor_id=v.id AND cp.status IN ('submitted','under_review','verified')) AS open_complaints,
                    s.plan_type AS sub_plan, s.expiry_date AS sub_expiry
               FROM vendors v
          LEFT JOIN categories c ON v.category_id=c.id
          LEFT JOIN subscriptions s ON s.vendor_id=v.id AND s.status='active'
              WHERE {$whereStr}
              ORDER BY v.created_at DESC
              LIMIT {$pag['per_page']} OFFSET {$pag['offset']}",
            $params
        );

        $this->render('vendors/index', compact(
            'vendors', 'pag', 'search', 'status', 'type'
        ));
    }

    public function vendorsPending(): void {
        AdminAuth::guard();

        $vendors = $this->db->rows(
            "SELECT v.*, c.name AS category_name
               FROM vendors v
          LEFT JOIN categories c ON v.category_id = c.id
              WHERE v.status = 'pending'
              ORDER BY v.created_at ASC"
        );

        $this->render('vendors/pending', compact('vendors'));
    }

    public function vendorDetail(?int $id): void {
        AdminAuth::guard();
        if (!$id) $this->redirect('vendors');

        $vendor = $this->db->row(
            "SELECT v.*, c.name AS category_name, c.icon AS category_icon
               FROM vendors v
          LEFT JOIN categories c ON v.category_id = c.id
              WHERE v.id = ?", [$id]
        );
        if (!$vendor) $this->redirect('vendors', 'Vendor not found.', 'error');

        $subscription = $this->db->row(
            "SELECT * FROM subscriptions WHERE vendor_id=? ORDER BY created_at DESC LIMIT 1",
            [$id]
        );
        $payments = $this->db->rows(
            "SELECT * FROM payments WHERE vendor_id=? ORDER BY created_at DESC LIMIT 10",
            [$id]
        );
        $reviews = $this->db->rows(
            "SELECT r.*, u.full_name AS user_name FROM reviews r
             LEFT JOIN users u ON r.user_id=u.id
              WHERE r.vendor_id=? ORDER BY r.created_at DESC LIMIT 5",
            [$id]
        );
        $complaints = $this->db->rows(
            "SELECT * FROM complaints WHERE vendor_id=? ORDER BY created_at DESC LIMIT 5",
            [$id]
        );

        $this->render('vendors/detail', compact(
            'vendor','subscription','payments','reviews','complaints'
        ));
    }

    public function vendorApprove(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('vendors/pending');

        $vendor = $this->db->row("SELECT * FROM vendors WHERE id=?", [$id]);
        if (!$vendor || $vendor['status'] !== 'pending') {
            $this->redirect('vendors/pending', 'Vendor not found or already processed.', 'error');
        }

        $this->db->execute(
            "UPDATE vendors SET status='active', approved_at=NOW(), approved_by=? WHERE id=?",
            [AdminAuth::id(), $id]
        );

        // Notify vendor
        $this->mailer->sendVendorApproval($vendor);

        // Create notification
        (new NotificationModel())->create([
            'recipient_type' => 'vendor',
            'recipient_id'   => $id,
            'type'           => 'approval',
            'title'          => 'Account Approved! 🎉',
            'message'        => 'Your CampusLink vendor account has been approved. Proceed to payment to go live.',
            'link'           => '/vendor/payment',
        ]);

        $this->redirect(
            'vendors/pending',
            "✅ {$vendor['business_name']} approved and notified."
        );
    }

    public function vendorReject(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('vendors/pending');

        $reason = trim($_POST['reason'] ?? '');
        if (!$reason) {
            $this->redirect("vendors/detail/{$id}", 'Rejection reason is required.', 'error');
        }

        $vendor = $this->db->row("SELECT * FROM vendors WHERE id=?", [$id]);
        if (!$vendor) $this->redirect('vendors/pending', 'Vendor not found.', 'error');

        $this->db->execute(
            "UPDATE vendors SET status='rejected', rejection_reason=?, rejected_at=NOW(), rejected_by=? WHERE id=?",
            [$reason, AdminAuth::id(), $id]
        );

        $this->mailer->sendVendorRejection($vendor, $reason);

        $this->redirect(
            'vendors/pending',
            "❌ {$vendor['business_name']} rejected."
        );
    }

    public function vendorSuspend(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('vendors');

        $reason = trim($_POST['reason'] ?? 'Policy violation.');
        $vendor = $this->db->row("SELECT * FROM vendors WHERE id=?", [$id]);
        if (!$vendor) $this->redirect('vendors', 'Vendor not found.', 'error');

        $this->db->execute(
            "UPDATE vendors SET status='suspended', suspension_reason=?, suspended_at=NOW(), suspended_by=? WHERE id=?",
            [$reason, AdminAuth::id(), $id]
        );

        $this->mailer->sendVendorSuspension($vendor, $reason);

        (new NotificationModel())->create([
            'recipient_type' => 'vendor',
            'recipient_id'   => $id,
            'type'           => 'warning',
            'title'          => 'Account Suspended',
            'message'        => "Your account has been suspended. Reason: {$reason}",
            'link'           => '/vendor/dashboard',
        ]);

        $this->redirect(
            "vendors/detail/{$id}",
            "⚠️ {$vendor['business_name']} suspended."
        );
    }

    public function vendorReinstate(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('vendors');

        $vendor = $this->db->row("SELECT * FROM vendors WHERE id=?", [$id]);
        if (!$vendor) $this->redirect('vendors', 'Vendor not found.', 'error');

        $this->db->execute(
            "UPDATE vendors SET status='active', suspension_reason=NULL, suspended_at=NULL WHERE id=?",
            [$id]
        );

        (new NotificationModel())->create([
            'recipient_type' => 'vendor',
            'recipient_id'   => $id,
            'type'           => 'success',
            'title'          => 'Account Reinstated ✅',
            'message'        => 'Your vendor account has been reinstated. Welcome back!',
            'link'           => '/vendor/dashboard',
        ]);

        $this->redirect(
            "vendors/detail/{$id}",
            "✅ {$vendor['business_name']} reinstated."
        );
    }

    // ════════════════════════════════════════════════════════════
    // USERS
    // ════════════════════════════════════════════════════════════

    public function usersIndex(): void {
        AdminAuth::guard();

        $search = trim($_GET['q']      ?? '');
        $status = $_GET['status']      ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = '(full_name LIKE ? OR school_email LIKE ? OR matric_number LIKE ?)';
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s,$s]);
        }
        if ($status) { $where[] = 'status = ?'; $params[] = $status; }

        $whereStr = implode(' AND ', $where);
        $total    = $this->db->value("SELECT COUNT(*) FROM users WHERE {$whereStr}", $params);
        $pag      = $this->paginate($total);

        $users = $this->db->rows(
            "SELECT u.*,
                    (SELECT COUNT(*) FROM reviews r   WHERE r.user_id=u.id)    AS review_count,
                    (SELECT COUNT(*) FROM complaints c WHERE c.user_id=u.id)   AS complaint_count,
                    (SELECT COUNT(*) FROM saved_vendors s WHERE s.user_id=u.id) AS saved_count
               FROM users u
              WHERE {$whereStr}
              ORDER BY u.created_at DESC
              LIMIT {$pag['per_page']} OFFSET {$pag['offset']}",
            $params
        );

        $this->render('users/index', compact('users','pag','search','status'));
    }

    public function userSuspend(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('users');

        $reason = trim($_POST['reason'] ?? 'Terms of service violation.');
        $this->db->execute(
            "UPDATE users SET status='suspended', suspension_reason=? WHERE id=?",
            [$reason, $id]
        );

        $this->redirect('users', '⚠️ User suspended.');
    }

    public function userReinstate(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('users');

        $this->db->execute(
            "UPDATE users SET status='active', suspension_reason=NULL WHERE id=?",
            [$id]
        );

        $this->redirect('users', '✅ User reinstated.');
    }

    // ════════════════════════════════════════════════════════════
    // COMPLAINTS
    // ════════════════════════════════════════════════════════════

    public function complaintsIndex(): void {
        AdminAuth::guard();

        $status = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? '');

        $where  = ['1=1'];
        $params = [];

        if ($status) { $where[] = 'c.status = ?'; $params[] = $status; }
        if ($search) {
            $where[]  = '(c.ticket_id LIKE ? OR v.business_name LIKE ?)';
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s]);
        }

        $whereStr = implode(' AND ', $where);
        $total    = $this->db->value(
            "SELECT COUNT(*) FROM complaints c LEFT JOIN vendors v ON c.vendor_id=v.id WHERE {$whereStr}",
            $params
        );
        $pag = $this->paginate($total, 25);

        $complaints = $this->db->rows(
            "SELECT c.*, v.business_name, v.slug AS vendor_slug,
                    u.full_name AS user_name, u.level AS user_level
               FROM complaints c
          LEFT JOIN vendors v ON c.vendor_id  = v.id
          LEFT JOIN users   u ON c.user_id    = u.id
              WHERE {$whereStr}
              ORDER BY c.created_at DESC
              LIMIT {$pag['per_page']} OFFSET {$pag['offset']}",
            $params
        );

        $statusCounts = $this->db->rows(
            "SELECT status, COUNT(*) AS cnt FROM complaints GROUP BY status"
        );

        $this->render('complaints/index', compact(
            'complaints','pag','status','search','statusCounts'
        ));
    }

    public function complaintDetail(?int $id): void {
        AdminAuth::guard();
        if (!$id) $this->redirect('complaints');

        $complaint = $this->db->row(
            "SELECT c.*, v.business_name, v.slug AS vendor_slug, v.id AS vid,
                    u.full_name AS user_name, u.school_email AS user_email,
                    u.level AS user_level
               FROM complaints c
          LEFT JOIN vendors v ON c.vendor_id = v.id
          LEFT JOIN users   u ON c.user_id   = u.id
              WHERE c.id = ?", [$id]
        );
        if (!$complaint) $this->redirect('complaints','Complaint not found.','error');

        // Count verified complaints against this vendor
        $verifiedCount = $this->db->value(
            "SELECT COUNT(*) FROM complaints
              WHERE vendor_id=? AND status='verified' AND id != ?",
            [$complaint['vendor_id'], $id]
        );

        $this->render('complaints/detail', compact('complaint','verifiedCount'));
    }

    public function complaintVerify(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('complaints');

        $note  = trim($_POST['admin_note'] ?? '');
        $compl = $this->db->row(
            "SELECT c.*, v.business_name, v.id AS vid FROM complaints c
             LEFT JOIN vendors v ON c.vendor_id=v.id WHERE c.id=?", [$id]
        );
        if (!$compl) $this->redirect('complaints','Not found.','error');

        $this->db->execute(
            "UPDATE complaints SET status='verified', admin_note=?, reviewed_at=NOW(), reviewed_by=? WHERE id=?",
            [$note, AdminAuth::id(), $id]
        );

        // Check if 3+ verified complaints — auto-flag vendor
        $verifiedCount = $this->db->value(
            "SELECT COUNT(*) FROM complaints WHERE vendor_id=? AND status='verified'",
            [$compl['vendor_id']]
        );

        if ($verifiedCount >= 3) {
            $this->db->execute(
                "UPDATE vendors SET flagged_for_review=1 WHERE id=?",
                [$compl['vendor_id']]
            );
        }

        // Notify user
        (new NotificationModel())->create([
            'recipient_type' => 'user',
            'recipient_id'   => $compl['user_id'],
            'type'           => 'complaint',
            'title'          => 'Complaint Verified ✅',
            'message'        => "Your complaint against {$compl['business_name']} has been verified.",
            'link'           => "/complaints/track?ticket={$compl['ticket_id']}",
        ]);

        $this->redirect(
            "complaints/detail/{$id}",
            '✅ Complaint verified.'
        );
    }

    public function complaintDismiss(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('complaints');

        $note = trim($_POST['admin_note'] ?? 'Insufficient evidence.');
        $compl = $this->db->row("SELECT * FROM complaints WHERE id=?", [$id]);
        if (!$compl) $this->redirect('complaints','Not found.','error');

        $this->db->execute(
            "UPDATE complaints SET status='dismissed', admin_note=?, reviewed_at=NOW(), reviewed_by=? WHERE id=?",
            [$note, AdminAuth::id(), $id]
        );

        (new NotificationModel())->create([
            'recipient_type' => 'user',
            'recipient_id'   => $compl['user_id'],
            'type'           => 'info',
            'title'          => 'Complaint Dismissed',
            'message'        => "Your complaint (#{$compl['ticket_id']}) has been dismissed: {$note}",
            'link'           => "/complaints/track?ticket={$compl['ticket_id']}",
        ]);

        $this->redirect("complaints/detail/{$id}", '❌ Complaint dismissed.');
    }

    public function complaintResolve(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('complaints');

        $note  = trim($_POST['admin_note'] ?? 'Resolved by admin.');
        $compl = $this->db->row(
            "SELECT c.*, v.business_name FROM complaints c
             LEFT JOIN vendors v ON c.vendor_id=v.id WHERE c.id=?", [$id]
        );
        if (!$compl) $this->redirect('complaints','Not found.','error');

        $this->db->execute(
            "UPDATE complaints SET status='resolved', admin_note=?, reviewed_at=NOW(), reviewed_by=? WHERE id=?",
            [$note, AdminAuth::id(), $id]
        );

        (new NotificationModel())->create([
            'recipient_type' => 'user',
            'recipient_id'   => $compl['user_id'],
            'type'           => 'success',
            'title'          => 'Complaint Resolved ✅',
            'message'        => "Your complaint against {$compl['business_name']} has been resolved.",
            'link'           => "/complaints/track?ticket={$compl['ticket_id']}",
        ]);

        $this->redirect("complaints/detail/{$id}", '✅ Complaint resolved.');
    }

    // ════════════════════════════════════════════════════════════
    // REVIEWS
    // ════════════════════════════════════════════════════════════

    public function reviewsIndex(): void {
        AdminAuth::guard();

        $status = $_GET['status'] ?? 'pending';
        $search = trim($_GET['q']  ?? '');

        $where  = ['1=1'];
        $params = [];

        if ($status) { $where[] = 'r.status = ?'; $params[] = $status; }
        if ($search) {
            $where[]  = '(v.business_name LIKE ? OR u.full_name LIKE ?)';
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s]);
        }

        $whereStr = implode(' AND ', $where);
        $total    = $this->db->value(
            "SELECT COUNT(*) FROM reviews r
             LEFT JOIN vendors v ON r.vendor_id=v.id
             LEFT JOIN users u ON r.user_id=u.id
             WHERE {$whereStr}", $params
        );
        $pag = $this->paginate($total, 25);

        $reviews = $this->db->rows(
            "SELECT r.*, v.business_name, v.slug AS vendor_slug,
                    u.full_name AS user_name, u.level AS user_level
               FROM reviews r
          LEFT JOIN vendors v ON r.vendor_id = v.id
          LEFT JOIN users   u ON r.user_id   = u.id
              WHERE {$whereStr}
              ORDER BY r.created_at DESC
              LIMIT {$pag['per_page']} OFFSET {$pag['offset']}",
            $params
        );

        $this->render('reviews/index', compact('reviews','pag','status','search'));
    }

    public function reviewApprove(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('reviews');

        $review = $this->db->row("SELECT * FROM reviews WHERE id=?", [$id]);
        if (!$review) $this->redirect('reviews','Not found.','error');

        $this->db->execute(
            "UPDATE reviews SET status='approved', reviewed_at=NOW(), reviewed_by=? WHERE id=?",
            [AdminAuth::id(), $id]
        );

        // Update vendor average rating
        $this->updateVendorRating($review['vendor_id']);

        $this->redirect('reviews?status=pending', '✅ Review approved.');
    }

    public function reviewReject(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        if (!$id) $this->redirect('reviews');

        $reason = trim($_POST['reason'] ?? 'Does not meet community guidelines.');
        $review = $this->db->row("SELECT * FROM reviews WHERE id=?", [$id]);
        if (!$review) $this->redirect('reviews','Not found.','error');

        $this->db->execute(
            "UPDATE reviews SET status='rejected', rejection_reason=?, reviewed_at=NOW(), reviewed_by=? WHERE id=?",
            [$reason, AdminAuth::id(), $id]
        );

        (new NotificationModel())->create([
            'recipient_type' => 'user',
            'recipient_id'   => $review['user_id'],
            'type'           => 'warning',
            'title'          => 'Review Not Approved',
            'message'        => "Your review was not approved. Reason: {$reason}",
            'link'           => '/user/my-reviews',
        ]);

        $this->redirect('reviews?status=pending', '❌ Review rejected.');
    }

    private function updateVendorRating(int $vendorId): void {
        $this->db->execute(
            "UPDATE vendors v
                SET avg_rating   = (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE vendor_id=? AND status='approved'),
                    review_count = (SELECT COUNT(*) FROM reviews WHERE vendor_id=? AND status='approved')
              WHERE id = ?",
            [$vendorId, $vendorId, $vendorId]
        );
    }

    // ════════════════════════════════════════════════════════════
    // PAYMENTS
    // ════════════════════════════════════════════════════════════

    public function paymentsIndex(): void {
        AdminAuth::guard();

        $search = trim($_GET['q']      ?? '');
        $status = $_GET['status']      ?? '';
        $from   = $_GET['from']        ?? '';
        $to     = $_GET['to']          ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = '(p.paystack_reference LIKE ? OR v.business_name LIKE ?)';
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s]);
        }
        if ($status) { $where[] = 'p.status = ?';       $params[] = $status; }
        if ($from)   { $where[] = 'DATE(p.created_at) >= ?'; $params[] = $from; }
        if ($to)     { $where[] = 'DATE(p.created_at) <= ?'; $params[] = $to;   }

        $whereStr = implode(' AND ', $where);
        $total    = $this->db->value(
            "SELECT COUNT(*) FROM payments p LEFT JOIN vendors v ON p.vendor_id=v.id WHERE {$whereStr}",
            $params
        );
        $pag = $this->paginate($total, 25);

        $payments = $this->db->rows(
            "SELECT p.*, v.business_name, v.vendor_type
               FROM payments p
          LEFT JOIN vendors v ON p.vendor_id = v.id
              WHERE {$whereStr}
              ORDER BY p.created_at DESC
              LIMIT {$pag['per_page']} OFFSET {$pag['offset']}",
            $params
        );

        $totals = $this->db->row(
            "SELECT
                SUM(CASE WHEN status='success' THEN amount ELSE 0 END) AS total_success,
                SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) AS total_pending,
                COUNT(CASE WHEN status='success' THEN 1 END)           AS count_success
               FROM payments p
          LEFT JOIN vendors v ON p.vendor_id=v.id
              WHERE {$whereStr}",
            $params
        );

        $this->render('payments/index', compact(
            'payments','pag','search','status','from','to','totals'
        ));
    }

    // ════════════════════════════════════════════════════════════
    // NOTIFICATIONS
    // ════════════════════════════════════════════════════════════

    public function notificationsIndex(): void {
        AdminAuth::guard();
        $this->render('notifications/send', [
            'recipientTypes' => ['all_users','all_vendors','single_user','single_vendor'],
        ]);
    }

    public function notificationSend(): void {
        AdminAuth::guard();
        $this->requirePost();

        $recipientType = $_POST['recipient_type'] ?? '';
        $recipientId   = (int)($_POST['recipient_id'] ?? 0);
        $title         = trim($_POST['title']   ?? '');
        $message       = trim($_POST['message'] ?? '');
        $type          = $_POST['notif_type']   ?? 'info';
        $link          = trim($_POST['link']    ?? '');

        if (!$title || !$message) {
            $this->redirect('notifications', 'Title and message are required.', 'error');
        }

        $nm = new NotificationModel();

        if ($recipientType === 'all_users') {
            $users = $this->db->rows("SELECT id FROM users WHERE status='active'");
            foreach ($users as $u) {
                $nm->create([
                    'recipient_type' => 'user',
                    'recipient_id'   => $u['id'],
                    'type'           => $type,
                    'title'          => $title,
                    'message'        => $message,
                    'link'           => $link ?: null,
                ]);
            }
            $this->redirect('notifications', '✅ Notification sent to all students.');
        }

        if ($recipientType === 'all_vendors') {
            $vendors = $this->db->rows("SELECT id FROM vendors WHERE status='active'");
            foreach ($vendors as $v) {
                $nm->create([
                    'recipient_type' => 'vendor',
                    'recipient_id'   => $v['id'],
                    'type'           => $type,
                    'title'          => $title,
                    'message'        => $message,
                    'link'           => $link ?: null,
                ]);
            }
            $this->redirect('notifications', '✅ Notification sent to all vendors.');
        }

        if (in_array($recipientType, ['single_user','single_vendor']) && $recipientId) {
            $nm->create([
                'recipient_type' => str_replace('single_', '', $recipientType),
                'recipient_id'   => $recipientId,
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'link'           => $link ?: null,
            ]);
            $this->redirect('notifications', '✅ Notification sent.');
        }

        $this->redirect('notifications', 'Invalid recipient type.', 'error');
    }

    // ════════════════════════════════════════════════════════════
    // MISC
    // ════════════════════════════════════════════════════════════

    public function logout(): void {
        AdminAuth::logout();
        header('Location: ' . SITE_URL . '/admin/login');
        exit;
    }

    public function notFound(): void {
        http_response_code(404);
        $this->render('dashboard');
    }
}