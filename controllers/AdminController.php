<?php
defined('CAMPUSLINK') or die('Direct access not permitted.');

class AdminController extends BaseController {

    // ── Login ────────────────────────────────────────────────
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }
        $this->renderAdmin('login', ['pageTitle' => 'Admin Login']);
    }

  public function notifications(): void {
    $db         = DB::getInstance();
    $page       = max(1, (int)($_GET['page']   ?? 1));
    $filterType = clean($_GET['type']          ?? '');
    $filterRead = $_GET['read']                ?? '';
    $limit      = ADMIN_ITEMS_PER_PAGE;
    $offset     = ($page - 1) * $limit;

    $where  = ['1=1'];
    $params = [];

    if ($filterType) {
        $where[]  = 'recipient_type = ?';
        $params[] = $filterType;
    }

    if ($filterRead !== '') {
        $where[]  = 'is_read = ?';
        $params[] = (int)$filterRead;
    }

    $whereStr = implode(' AND ', $where);

    $total = $db->value(
        "SELECT COUNT(*) FROM notifications WHERE {$whereStr}",
        $params
    );

    $pag = paginate($total, $limit);

    $notifications = $db->rows(
        "SELECT * FROM notifications
         WHERE {$whereStr}
         ORDER BY created_at DESC
         LIMIT {$limit} OFFSET {$offset}",
        $params
    );

    $unreadCount = $db->value(
        "SELECT COUNT(*) FROM notifications WHERE is_read = 0"
    );

    $todayCount = $db->value(
        "SELECT COUNT(*) FROM notifications
         WHERE DATE(created_at) = CURDATE()"
    );

    $this->renderAdmin('notifications/index', compact(
        'notifications', 'pag', 'total',
        'unreadCount', 'todayCount', 'filterType'
    ));
}
    
    public function notificationRecipients(): void {
    $db   = DB::getInstance();
    $type = clean($_GET['type'] ?? '');

    if ($type === 'vendor') {
        $rows = $db->rows(
            "SELECT id, business_name AS name, working_email AS email
             FROM vendors WHERE status = 'active'
             ORDER BY business_name ASC"
        );
    } elseif ($type === 'user') {
        $rows = $db->rows(
            "SELECT id, full_name AS name, email
             FROM users WHERE status = 'active'
             ORDER BY full_name ASC"
        );
    } else {
        $rows = [];
    }

    header('Content-Type: application/json');
    echo json_encode($rows);
    exit;
}

public function notificationSend(): void {
    $this->verifyCsrf();

    $db            = DB::getInstance();
    $recipientType = clean($_POST['recipient_type'] ?? '');
    $recipientId   = (int)($_POST['recipient_id'] ?? 0);
    $type          = clean($_POST['type']          ?? 'info');
    $title         = clean($_POST['title']         ?? '');
    $message       = clean($_POST['message']       ?? '');
    $link          = clean($_POST['link']          ?? '');

    if (!$title || !$message || !$recipientType) {
        Session::setFlash('error', 'Please fill in all required fields.');
        $this->redirect('admin/notifications');
    }

    $now = date('Y-m-d H:i:s');

    if ($recipientType === 'all_vendors') {
        $vendors = $db->rows("SELECT id FROM vendors WHERE status = 'active'");
        foreach ($vendors as $v) {
            $db->insert('notifications', [
                'recipient_type' => 'vendor',
                'recipient_id'   => $v['id'],
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'link'           => $link ?: null,
                'is_read'        => 0,
                'created_at'     => $now,
            ]);
        }
        $count = count($vendors);
        Session::setFlash('success', "Notification sent to {$count} vendors.");

    } elseif ($recipientType === 'all_users') {
        $users = $db->rows("SELECT id FROM users WHERE status = 'active'");
        foreach ($users as $u) {
            $db->insert('notifications', [
                'recipient_type' => 'user',
                'recipient_id'   => $u['id'],
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'link'           => $link ?: null,
                'is_read'        => 0,
                'created_at'     => $now,
            ]);
        }
        $count = count($users);
        Session::setFlash('success', "Notification sent to {$count} students.");

    } elseif ($recipientType === 'all') {
        $vendors = $db->rows("SELECT id FROM vendors WHERE status = 'active'");
        $users   = $db->rows("SELECT id FROM users WHERE status = 'active'");
        foreach ($vendors as $v) {
            $db->insert('notifications', [
                'recipient_type' => 'vendor',
                'recipient_id'   => $v['id'],
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'link'           => $link ?: null,
                'is_read'        => 0,
                'created_at'     => $now,
            ]);
        }
        foreach ($users as $u) {
            $db->insert('notifications', [
                'recipient_type' => 'user',
                'recipient_id'   => $u['id'],
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'link'           => $link ?: null,
                'is_read'        => 0,
                'created_at'     => $now,
            ]);
        }
        $total = count($vendors) + count($users);
        Session::setFlash('success', "Notification sent to {$total} people.");

    } else {
        if (!$recipientId) {
            Session::setFlash('error', 'Please select a recipient.');
            $this->redirect('admin/notifications');
        }
        $db->insert('notifications', [
            'recipient_type' => $recipientType,
            'recipient_id'   => $recipientId,
            'type'           => $type,
            'title'          => $title,
            'message'        => $message,
            'link'           => $link ?: null,
            'is_read'        => 0,
            'created_at'     => $now,
        ]);
        Session::setFlash('success', 'Notification sent successfully.');
    }

    $this->redirect('admin/notifications');
}

public function notificationDelete(?int $id): void {
    $this->verifyCsrf();
    if (!$id) $this->redirect('admin/notifications');

    $db = DB::getInstance();
    $db->execute("DELETE FROM notifications WHERE id = ?", [$id]);

    Session::setFlash('success', 'Notification deleted.');
    $this->redirect('admin/notifications');
}

public function notificationEdit(?int $id): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handleNotificationEdit($id);
        return;
    }

    $db = DB::getInstance();
    $notification = $db->row("SELECT * FROM notifications WHERE id = ?", [$id]);
    if (!$notification) {
        Session::setFlash('error', 'Notification not found.');
        $this->redirect('admin/notifications');
    }

    $this->renderAdmin('notifications/edit', compact('notification'));
}

private function handleNotificationEdit(?int $id): void {
    $this->verifyCsrf();
    if (!$id) $this->redirect('admin/notifications');

    $db      = DB::getInstance();
    $type    = clean($_POST['type']    ?? 'info');
    $title   = clean($_POST['title']   ?? '');
    $message = clean($_POST['message'] ?? '');
    $link    = clean($_POST['link']    ?? '');

    if (!$title || !$message) {
        Session::setFlash('error', 'Please fill in all required fields.');
        $this->redirect('admin/notifications/edit/' . $id);
    }

    $db->execute(
        "UPDATE notifications SET type = ?, title = ?, message = ?, link = ? WHERE id = ?",
        [$type, $title, $message, $link ?: null, $id]
    );

    Session::setFlash('success', 'Notification updated.');
    $this->redirect('admin/notifications');
}

    public function reviewApprove(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        $this->verifyCsrf();

        if (!$id) {
            $this->redirect('admin/reviews');
        }

        $review = $this->db->row("SELECT * FROM reviews WHERE id = ?", [$id]);
        if (!$review) {
            $this->redirect('admin/reviews', 'Review not found.', 'error');
        }

        $this->db->execute(
            "UPDATE reviews SET status='approved', reviewed_at=NOW(), reviewed_by=? WHERE id=?",
            [AdminAuth::id(), $id]
        );

        $this->updateVendorRating($review['vendor_id']);

        $this->redirect('admin/reviews?status=pending', '✅ Review approved.');
    }

    public function reviewReject(?int $id): void {
        AdminAuth::guard();
        $this->requirePost();
        $this->verifyCsrf();

        if (!$id) {
            $this->redirect('admin/reviews');
        }

        $reason = trim($_POST['reason'] ?? 'Does not meet community guidelines.');
        $review = $this->db->row("SELECT * FROM reviews WHERE id = ?", [$id]);
        if (!$review) {
            $this->redirect('admin/reviews', 'Review not found.', 'error');
        }

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
            'is_read'        => 0,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->redirect('admin/reviews?status=pending', '❌ Review rejected.');
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
    
    private function handleLogin(): void {
        $email    = clean($_POST['email']    ?? '');
        $password = clean($_POST['password'] ?? '');

        if (!$email || !$password) {
            Session::setFlash('error', 'Email and password are required.');
            redirect('admin/login');
        }

        $db    = DB::getInstance();
        $admin = $db->row(
            "SELECT * FROM admin_users WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );

        if (!$admin || !password_verify($password, $admin['password'])) {
            Session::setFlash('error', 'Invalid email or password.');
            redirect('admin/login');
        }

        // Start admin session
        Session::set('admin_logged_in', true);
        Session::set('admin_id',        $admin['id']);
        Session::set('admin_name',      $admin['full_name']);
        Session::set('admin_role',      $admin['role']);
        Session::regenerate();

        redirect('admin/dashboard');
    }

    // ── Logout ───────────────────────────────────────────────
    public function logout(): void {
        Session::delete('admin_logged_in');
        Session::delete('admin_id');
        Session::delete('admin_name');
        Session::delete('admin_role');
        redirect('admin/login');
    }

    // ── Dashboard ─────────────────────────────────────────────
    public function dashboard(): void {
        $db = DB::getInstance();

        $stats = [
    'total_vendors'    => $db->value("SELECT COUNT(*) FROM vendors"),
    'total_users'      => $db->value("SELECT COUNT(*) FROM users"),
    'pending_vendors'  => $db->value("SELECT COUNT(*) FROM vendors WHERE status = 'pending'"),
    'active_vendors'   => $db->value("SELECT COUNT(*) FROM vendors WHERE status = 'active'"),
    'suspended_vendors'=> $db->value("SELECT COUNT(*) FROM vendors WHERE status = 'suspended'"),
    'total_revenue'    => $db->value("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'success'"),
    'month_revenue'    => $db->value("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'success' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())"),
    'open_complaints'  => $db->value("SELECT COUNT(*) FROM complaints WHERE status IN ('open','investigating')"),
    'avg_rating'       => $db->value("SELECT COALESCE(ROUND(AVG(rating),1),0) FROM reviews WHERE status = 'approved'"),
    'pending_reviews'  => $db->value("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"),
];

        $pendingVendors = $db->rows(
    "SELECT v.*, v.working_email AS email
     FROM vendors v
     WHERE v.status = 'pending'
     ORDER BY v.created_at DESC LIMIT 10"
);

        $recentComplaints = $db->rows(
            "SELECT c.*, v.business_name as vendor_name
             FROM complaints c
             LEFT JOIN vendors v ON v.id = c.vendor_id
             ORDER BY c.created_at DESC LIMIT 8"
        );

        $this->renderAdmin('dashboard', compact('stats', 'pendingVendors', 'recentComplaints'));
    }

    // ── Vendors ───────────────────────────────────────────────
    public function vendors(): void {
        $db     = DB::getInstance();
        $status = $_GET['status'] ?? '';
        $search = clean($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = ADMIN_ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($status) {
            $where[]  = 'v.status = ?';
            $params[] = $status;
        }
        if ($search) {
            $where[]  = '(v.business_name LIKE ? OR v.email LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereStr = implode(' AND ', $where);
        $total    = $db->value("SELECT COUNT(*) FROM vendors v WHERE {$whereStr}", $params);
        $pag      = paginate($total, $limit);

        $vendors = $db->rows(
            "SELECT v.*, c.name AS category_name
               FROM vendors v
          LEFT JOIN categories c ON c.id = v.category_id
              WHERE {$whereStr}
              ORDER BY v.created_at DESC
              LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $this->renderAdmin('vendors/index', compact('vendors', 'pag', 'status', 'search', 'total'));
    }

    public function vendorView(int $id): void {
        $db     = DB::getInstance();
        $vendor = $db->row(
            "SELECT v.*, c.name AS category_name
               FROM vendors v
          LEFT JOIN categories c ON c.id = v.category_id
              WHERE v.id = ?",
            [$id]
        );

        if (!$vendor) { $this->notFound(); return; }

        $reviews    = $db->rows("SELECT * FROM reviews WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        $complaints = $db->rows("SELECT * FROM complaints WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        $payments   = $db->rows("SELECT * FROM payments WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        $sub        = $db->row("SELECT * FROM subscriptions WHERE vendor_id = ? ORDER BY created_at DESC LIMIT 1", [$id]);

        $this->renderAdmin('vendors/view', compact('vendor', 'reviews', 'complaints', 'payments', 'sub'));
    }

    public function vendorApprove(int $id): void {
        $db = DB::getInstance();
        $db->execute("UPDATE vendors SET status = 'active', approved_at = NOW() WHERE id = ?", [$id]);
        Session::setFlash('success', 'Vendor approved successfully.');
        redirect('admin/vendors');
    }

    public function vendorSuspend(int $id): void {
        $db     = DB::getInstance();
        $reason = clean($_POST['reason'] ?? 'Suspended by admin');
        $db->execute("UPDATE vendors SET status = 'suspended', suspension_reason = ? WHERE id = ?", [$reason, $id]);
        Session::setFlash('success', 'Vendor suspended.');
        redirect('admin/vendors');
    }

    public function vendorDelete(int $id): void {
        $db = DB::getInstance();
        $db->execute("DELETE FROM vendors WHERE id = ?", [$id]);
        Session::setFlash('success', 'Vendor deleted.');
        redirect('admin/vendors');
    }

    // ── Users ─────────────────────────────────────────────────
    public function users(): void {
        $db     = DB::getInstance();
        $search = clean($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = ADMIN_ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

       if ($search) {
    $where[]  = '(full_name LIKE ? OR email LIKE ?)';
    $s        = "%{$search}%";
    $params   = [$s, $s];
}

        $whereStr = implode(' AND ', $where);
        $total    = $db->value("SELECT COUNT(*) FROM users WHERE {$whereStr}", $params);
        $pag      = paginate($total, $limit);

        $users = $db->rows(
            "SELECT * FROM users WHERE {$whereStr}
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $this->renderAdmin('users/index', compact('users', 'pag', 'search', 'total'));
    }

    public function userView(int $id): void {
        $db   = DB::getInstance();
        $user = $db->row("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) { $this->notFound(); return; }

        $reviews    = $db->rows("SELECT r.*, v.business_name FROM reviews r LEFT JOIN vendors v ON v.id = r.vendor_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 10", [$id]);
        $complaints = $db->rows("SELECT c.*, v.business_name FROM complaints c LEFT JOIN vendors v ON v.id = c.vendor_id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 10", [$id]);

        $this->renderAdmin('users/view', compact('user', 'reviews', 'complaints'));
    }

    // ── Reviews ───────────────────────────────────────────────
    public function reviews(): void {
        $db     = DB::getInstance();
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = ADMIN_ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($status) {
            $where[]  = 'r.status = ?';
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);
        $total    = $db->value("SELECT COUNT(*) FROM reviews r WHERE {$whereStr}", $params);
        $pag      = paginate($total, $limit);

      $reviews = $db->rows(
    "SELECT r.*, v.business_name, v.slug AS vendor_slug, u.full_name AS user_name, u.level AS user_level
       FROM reviews r
  LEFT JOIN vendors v ON v.id = r.vendor_id
  LEFT JOIN users   u ON u.id = r.user_id
      WHERE {$whereStr}
      ORDER BY r.created_at DESC
      LIMIT {$limit} OFFSET {$offset}",
    $params
);
        
        $this->renderAdmin('reviews/index', compact('reviews', 'pag', 'status', 'total'));
    }

    // ── Complaints ────────────────────────────────────────────
    public function complaints(): void {
        $db     = DB::getInstance();
        $status = $_GET['status'] ?? '';
        $search = clean($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = ADMIN_ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($status) {
            $where[]  = 'c.status = ?';
            $params[] = $status;
        }

        if ($search) {
            $where[]  = '(v.business_name LIKE ? OR c.ticket_id LIKE ? OR u.full_name LIKE ?)';
            $s        = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereStr = implode(' AND ', $where);
        $total    = $db->value("SELECT COUNT(*) FROM complaints c LEFT JOIN vendors v ON v.id = c.vendor_id LEFT JOIN users u ON u.id = c.user_id WHERE {$whereStr}", $params);
        $pag      = paginate($total, $limit);

      $complaints = $db->rows(
    "SELECT c.*, v.business_name AS vendor_name, v.slug AS vendor_slug,
            u.full_name AS user_name, u.level AS user_level
       FROM complaints c
  LEFT JOIN vendors v ON v.id = c.vendor_id
  LEFT JOIN users   u ON u.id = c.user_id
      WHERE {$whereStr}
      ORDER BY c.created_at DESC
      LIMIT {$limit} OFFSET {$offset}",
    $params
);

       $statusCounts = $db->rows(
    "SELECT status, COUNT(*) as cnt FROM complaints GROUP BY status"
);

$this->renderAdmin('complaints/index', compact(
    'complaints', 'pag', 'status', 'total', 'statusCounts', 'search'
));
    }

    public function complaintView(int $id): void {
        $db        = DB::getInstance();
     $complaint = $db->row(
    "SELECT c.*, v.business_name AS vendor_name,
            u.full_name AS user_name, u.email AS user_email
               FROM complaints c
          LEFT JOIN vendors v ON v.id = c.vendor_id
          LEFT JOIN users   u ON u.id = c.user_id
              WHERE c.id = ?",
            [$id]
        );

        if (!$complaint) { $this->notFound(); return; }

        $notes = $db->rows(
            "SELECT n.*, a.full_name AS admin_name
               FROM complaint_notes n
          LEFT JOIN admin_users a ON a.id = n.admin_id
              WHERE n.complaint_id = ?
              ORDER BY n.created_at ASC",
            [$id]
        );

        $this->renderAdmin('complaints/view', compact('complaint', 'notes'));
    }

    // ── Payments ──────────────────────────────────────────────
   public function payments(): void {
    $db     = DB::getInstance();
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    $search = clean($_GET['q'] ?? '');
    $status = clean($_GET['status'] ?? '');
    $from   = clean($_GET['from'] ?? '');
    $to     = clean($_GET['to']   ?? '');

    $where  = ['1=1'];
    $params = [];

    if ($search) {
        $where[]  = '(v.business_name LIKE ? OR p.reference LIKE ?)';
        $s        = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
    }
       
    if ($status) {
        $where[]  = 'p.status = ?';
        $params[] = $status;
    }

    if ($from) {
        $where[]  = 'DATE(p.created_at) >= ?';
        $params[] = $from;
    }

    if ($to) {
        $where[]  = 'DATE(p.created_at) <= ?';
        $params[] = $to;
    }

    $whereStr = implode(' AND ', $where);

    $total = $db->value(
        "SELECT COUNT(*) FROM payments p
         LEFT JOIN vendors v ON v.id = p.vendor_id
         WHERE {$whereStr}",
        $params
    );

    $pag = paginate($total, $limit);

    $payments = $db->rows(
        "SELECT p.*, v.business_name, v.vendor_type AS vendor_type,
                s.plan_type AS sub_plan_type,
                s.start_date AS subscription_start,
                s.expiry_date AS subscription_expiry
           FROM payments p
      LEFT JOIN vendors v ON v.id = p.vendor_id
      LEFT JOIN subscriptions s ON s.payment_id = p.id
          WHERE {$whereStr}
          ORDER BY p.created_at DESC
          LIMIT {$limit} OFFSET {$offset}",
        $params
    );

    // Aggregate totals (unfiltered by search/date to show global stats)
    $totals = $db->row(
        "SELECT
            COALESCE(SUM(CASE WHEN status='success' THEN amount ELSE 0 END),0) AS total_success,
            COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) AS total_pending,
            COUNT(CASE WHEN status='success' THEN 1 END) AS count_success
         FROM payments"
    ) ?? [];

    $this->renderAdmin('payments/index', compact(
        'payments', 'pag', 'total', 'search', 'status', 'from', 'to', 'totals'
    ));
}
    
    // ── Categories ────────────────────────────────────────────
    public function categories(): void {
        $db = DB::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $db->insert('categories', [
                    'name'       => clean($_POST['name'] ?? ''),
                    'slug'       => slugify($_POST['name'] ?? ''),
                    'icon'       => clean($_POST['icon'] ?? '🏪'),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                Session::setFlash('success', 'Category created.');
            }

            if ($action === 'delete') {
                $db->execute("DELETE FROM categories WHERE id = ?", [(int)$_POST['id']]);
                Session::setFlash('success', 'Category deleted.');
            }

            redirect('admin/categories');
        }

        $categories = $db->rows(
            "SELECT c.*, COUNT(v.id) AS vendor_count
               FROM categories c
          LEFT JOIN vendors v ON v.category_id = c.id
              GROUP BY c.id
              ORDER BY c.sort_order ASC"
        );

        $this->renderAdmin('categories/index', compact('categories'));
    }

    // ── Settings ──────────────────────────────────────────────
    public function settings(): void {
        $this->renderAdmin('settings/index', []);
    }

    // ── 404 ───────────────────────────────────────────────────
    public function notFound(): void {
        http_response_code(404);
        $this->renderAdmin('404', ['pageTitle' => 'Page Not Found']);
    }

    // ── Render helper for admin views ─────────────────────────
    private function renderAdmin(string $view, array $data = []): void {
        extract($data);
        $adminName = Session::get('admin_name', 'Admin');
        $adminRole = Session::get('admin_role', '');
        $viewFile  = __DIR__ . '/../views/admin/' . $view . '.php';
        $layout    = __DIR__ . '/../views/admin/layouts/admin.php';

        if (!file_exists($viewFile)) {
            echo "<div style='padding:2rem;color:red;font-family:monospace;'>
                    Admin view not found: <strong>views/admin/{$view}.php</strong>
                  </div>";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layout;
    }
}