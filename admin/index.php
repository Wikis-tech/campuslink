<?php
defined('CAMPUSLINK') or die('Direct access not permitted.');

// Parse admin sub-path
$adminUri  = $_SERVER['REQUEST_URI'] ?? '/admin';
$adminPath = parse_url($adminUri, PHP_URL_PATH);
$adminPath = strtolower(trim($adminPath, '/'));

// Remove 'admin' prefix to get sub-route
// e.g. 'admin/login' → 'login'
//      'admin/dashboard' → 'dashboard'
$adminSeg = '';
if (str_starts_with($adminPath, 'admin/')) {
    $adminSeg = substr($adminPath, strlen('admin/'));
} elseif ($adminPath === 'admin') {
    $adminSeg = '';
}

$adminParts = $adminSeg !== '' ? explode('/', $adminSeg) : [];
$aPage  = $adminParts[0] ?? '';
$aPage2 = $adminParts[1] ?? '';
$aPage3 = $adminParts[2] ?? '';

// ── Public admin routes (no login required) ──────────────
$publicAdminRoutes = ['login', 'login-submit'];

if (!in_array($aPage, $publicAdminRoutes) && !AdminAuth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/login');
    exit;
}

// ── If logged in and hitting /admin/login → redirect to dashboard
if (in_array($aPage, $publicAdminRoutes) && AdminAuth::isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/dashboard');
    exit;
}

// ── Route admin pages ─────────────────────────────────────
require_once __DIR__ . '/../controllers/AdminController.php';
$admin = new AdminController();

match(true) {
    // Login
    $aPage === 'login'                              => $admin->login(),

    // Dashboard
    $aPage === '' || $aPage === 'dashboard'         => $admin->dashboard(),

    // Vendors
    $aPage === 'vendors' && $aPage2 === 'view'      => $admin->vendorView((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'approve'   => $admin->vendorApprove((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'suspend'   => $admin->vendorSuspend((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'delete'    => $admin->vendorDelete((int)$aPage3),
    $aPage === 'vendors'                            => $admin->vendors(),

    // Users
    $aPage === 'users' && $aPage2 === 'view'        => $admin->userView((int)$aPage3),
    $aPage === 'users'                              => $admin->users(),

    // Reviews
    $aPage === 'reviews'                            => $admin->reviews(),

    // Complaints
    $aPage === 'complaints' && $aPage2 === 'view'   => $admin->complaintView((int)$aPage3),
    $aPage === 'complaints'                         => $admin->complaints(),

    // Payments
    $aPage === 'payments'                           => $admin->payments(),

    // Categories
    $aPage === 'categories'                         => $admin->categories(),

    // Notifications
$aPage === 'notifications' && $aPage2 === 'send'       => $admin->notificationSend(),
$aPage === 'notifications' && $aPage2 === 'recipients' => $admin->notificationRecipients(),
$aPage === 'notifications'                             => $admin->notifications(),
    
    // Settings
    $aPage === 'settings'                           => $admin->settings(),

    // Logout
    $aPage === 'logout'                             => $admin->logout(),

    // 404
    default                                         => $admin->notFound(),
};