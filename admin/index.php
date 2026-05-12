<?php
defined('CAMPUSLINK') or die('Direct access not permitted.');

// Load the full bootstrap (DB, helpers, models, session, etc)
require_once __DIR__ . '/../core/bootstrap.php';

// Parse admin sub-path
$adminUri  = $_SERVER['REQUEST_URI'] ?? '/admin';
$adminPath = parse_url($adminUri, PHP_URL_PATH);

// Normalize install directory and admin entrypoint
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($adminPath, $scriptDir)) {
    $adminPath = substr($adminPath, strlen($scriptDir));
}

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
require_once __DIR__ . '/AdminController.php';
$admin = new AdminController();

match(true) {
    // Login
    $aPage === 'login'                              => $admin->login(),

    // Dashboard
    $aPage === '' || $aPage === 'dashboard'         => $admin->dashboard(),

    // Vendors
    $aPage === 'vendors' && $aPage2 === 'view'      => $admin->vendorDetail((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'approve'   => $admin->vendorApprove((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'suspend'   => $admin->vendorSuspend((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'delete'    => $admin->vendorDelete((int)$aPage3),
    $aPage === 'vendors' && $aPage2 === 'pending'   => $admin->vendorsPending(),
    $aPage === 'vendors'                            => $admin->vendorsIndex(),

    // Users
    $aPage === 'users' && $aPage2 === 'view'        => $admin->userView((int)$aPage3),
    $aPage === 'users'                              => $admin->usersIndex(),

    // Reviews
    $aPage === 'reviews' && $aPage2 === 'approve'   => $admin->reviewApprove((int)$aPage3),
    $aPage === 'reviews' && $aPage2 === 'reject'    => $admin->reviewReject((int)$aPage3),
    $aPage === 'reviews'                            => $admin->reviewsIndex(),

    // Complaints
    $aPage === 'complaints' && ($aPage2 === 'view' || $aPage2 === 'detail') => $admin->complaintDetail((int)$aPage3),
    $aPage === 'complaints' && $aPage2 === 'verify'   => $admin->complaintVerify((int)$aPage3),
    $aPage === 'complaints' && $aPage2 === 'dismiss'  => $admin->complaintDismiss((int)$aPage3),
    $aPage === 'complaints' && $aPage2 === 'resolve'  => $admin->complaintResolve((int)$aPage3),
    $aPage === 'complaints'                         => $admin->complaintsIndex(),

    // Payments
    $aPage === 'payments'                           => $admin->paymentsIndex(),

    // Notifications
    $aPage === 'notifications' && $aPage2 === 'send'       => $admin->notificationSend(),
    $aPage === 'notifications'                             => $admin->notificationsIndex(),

    // Logout
    $aPage === 'logout'                             => $admin->logout(),

    // 404
    default                                         => $admin->notFound(),
};