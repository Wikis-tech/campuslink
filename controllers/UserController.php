<?php
defined('CAMPUSLINK') or die('Direct access not permitted.');

class UserController extends BaseController {

    private function requireUserLogin(): void {
        if (!Auth::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to continue.');
            $this->redirect('login');
        }
    }

    private function currentUser(): array {
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        return $db->row("SELECT * FROM users WHERE id = ?", [$userId]) ?? [];
    }

    public function dashboard(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        $user   = $this->currentUser();

        $savedCount     = (int)$db->value("SELECT COUNT(*) FROM saved_vendors WHERE user_id = ?", [$userId]);
        $reviewCount    = (int)$db->value("SELECT COUNT(*) FROM reviews WHERE user_id = ?", [$userId]);
        $complaintCount = (int)$db->value("SELECT COUNT(*) FROM complaints WHERE user_id = ?", [$userId]);
        $unreadNotifs   = (int)$db->value("SELECT COUNT(*) FROM notifications WHERE recipient_type = 'user' AND recipient_id = ? AND is_read = 0", [$userId]);

        $savedVendors = $db->rows(
            "SELECT v.*, c.name AS category_name
               FROM saved_vendors sv
               JOIN vendors v ON v.id = sv.vendor_id
          LEFT JOIN categories c ON c.id = v.category_id
              WHERE sv.user_id = ?
              ORDER BY sv.created_at DESC
              LIMIT 5",
            [$userId]
        );

        $recentReviews = $db->rows(
            "SELECT r.*, v.business_name
               FROM reviews r
               JOIN vendors v ON v.id = r.vendor_id
              WHERE r.user_id = ?
              ORDER BY r.created_at DESC
              LIMIT 3",
            [$userId]
        );

        $this->render('user/dashboard', compact(
            'user', 'savedCount', 'reviewCount',
            'complaintCount', 'unreadNotifs',
            'savedVendors', 'recentReviews'
        ), 'user');
    }

    public function profile(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        $user   = $this->currentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $firstName = clean($_POST['first_name'] ?? '');
            $lastName  = clean($_POST['last_name']  ?? '');
            $phone     = clean($_POST['phone']       ?? '');

            if (!$firstName || !$lastName || !$phone) {
                Session::setFlash('error', 'Please fill in all required fields.');
                $this->redirect('user/profile');
            }

            // Check phone not taken by another user
            $phoneExists = (int)$db->value(
                "SELECT COUNT(*) FROM users WHERE phone = ? AND id != ?",
                [$phone, $userId]
            );
            if ($phoneExists > 0) {
                Session::setFlash('error', 'This phone number is already used by another account.');
                $this->redirect('user/profile');
            }

            $db->execute(
                "UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?",
                [trim($firstName . ' ' . $lastName), $phone, $userId]
            );

            Session::set('user_name', trim($firstName . ' ' . $lastName));
            Session::setFlash('success', 'Profile updated successfully.');
            $this->redirect('user/profile');
        }

        $this->render('user/profile', compact('user'), 'user');
    }

    public function savedVendors(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        $user   = $this->currentUser();

        $vendors = $db->rows(
            "SELECT v.*, c.name AS category_name,
                    COALESCE(AVG(r.rating),0) AS avg_rating,
                    COUNT(r.id) AS review_count
               FROM saved_vendors sv
               JOIN vendors v ON v.id = sv.vendor_id
          LEFT JOIN categories c ON c.id = v.category_id
          LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
              WHERE sv.user_id = ?
              GROUP BY v.id
              ORDER BY sv.created_at DESC",
            [$userId]
        );

        $this->render('user/saved-vendors', compact('user', 'vendors'), 'user');
    }

    public function myReviews(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        $user   = $this->currentUser();

        $reviews = $db->rows(
            "SELECT r.*, v.business_name, v.slug
               FROM reviews r
               JOIN vendors v ON v.id = r.vendor_id
              WHERE r.user_id = ?
              ORDER BY r.created_at DESC",
            [$userId]
        );

        $this->render('user/my-reviews', compact('user', 'reviews'), 'user');
    }

    public function myComplaints(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        $user   = $this->currentUser();

        $complaints = $db->rows(
            "SELECT c.*, v.business_name
               FROM complaints c
               JOIN vendors v ON v.id = c.vendor_id
              WHERE c.user_id = ?
              ORDER BY c.created_at DESC",
            [$userId]
        );

        $this->render('user/my-complaints', compact('user', 'complaints'), 'user');
    }

    public function notifications(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');
        $user   = $this->currentUser();

        // Mark all as read
        $db->execute(
            "UPDATE notifications SET is_read = 1
             WHERE recipient_type = 'user' AND recipient_id = ?",
            [$userId]
        );

        $notifications = $db->rows(
            "SELECT * FROM notifications
             WHERE recipient_type = 'user' AND recipient_id = ?
             ORDER BY created_at DESC
             LIMIT 50",
            [$userId]
        );

        $this->render('user/notifications', compact('user', 'notifications'), 'user');
    }

    public function toggleSave(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $raw      = file_get_contents('php://input');
        $body     = json_decode($raw, true) ?: [];
        $vendorId = (int)($body['vendor_id'] ?? $_POST['vendor_id'] ?? 0);
        $csrf     = $body['csrf_token'] ?? $_POST['csrf_token'] ?? '';

        if (!CSRF::validate($csrf)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Invalid security token.']);
            return;
        }

        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Not logged in']);
            return;
        }

        $db = DB::getInstance();
        $userId = (int)Session::get('user_id');

        if (!$vendorId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Invalid vendor']);
            return;
        }

        $exists = (int)$db->value(
            "SELECT COUNT(*) FROM saved_vendors WHERE user_id = ? AND vendor_id = ?",
            [$userId, $vendorId]
        );

        if ($exists) {
            $db->execute(
                "DELETE FROM saved_vendors WHERE user_id = ? AND vendor_id = ?",
                [$userId, $vendorId]
            );
            $this->json(['status' => 'success', 'success' => true, 'saved' => false, 'message' => 'Vendor removed from saved list.']);
        } else {
            $db->insert('saved_vendors', [
                'user_id'    => $userId,
                'vendor_id'  => $vendorId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->json(['status' => 'success', 'success' => true, 'saved' => true, 'message' => 'Vendor added to saved list.']);
        }
    }

    public function deleteAccount(): void {
        $this->requireUserLogin();
        $db     = DB::getInstance();
        $userId = (int)Session::get('user_id');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();
            $confirm = $_POST['confirm_delete'] ?? '';

            if ($confirm !== 'DELETE') {
                Session::setFlash('error', 'Please type "DELETE" to confirm.');
                $this->redirect('user/profile');
            }

            // Delete related data
            $db->execute("DELETE FROM saved_vendors WHERE user_id = ?", [$userId]);
            $db->execute("DELETE FROM reviews WHERE user_id = ?", [$userId]);
            $db->execute("DELETE FROM complaints WHERE user_id = ?", [$userId]);
            $db->execute("DELETE FROM notifications WHERE recipient_type = 'user' AND recipient_id = ?", [$userId]);

            // Delete user
            $db->execute("DELETE FROM users WHERE id = ?", [$userId]);

            // Logout
            Session::destroy();
            Session::setFlash('success', 'Your account has been deleted.');
            $this->redirect('/');
        }

        $this->redirect('user/profile');
    }
}