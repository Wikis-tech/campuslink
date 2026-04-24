<?php
/**
 * CampusLink - Dashboard Layout
 * Used by: user dashboard, vendor dashboard
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

$pageTitle = $pageTitle ?? 'Dashboard - ' . SITE_NAME;

$isVendor     = Auth::isVendorLoggedIn();
$isUser       = Auth::isLoggedIn();
$currentPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$notifModel   = new NotificationModel();
$unreadNotifs = 0;
$vendorInfo   = null;
$userInfo     = null;

if ($isVendor) {
    $unreadNotifs = $notifModel->countUnread('vendor', Auth::vendorId());
    $vendorInfo   = (new VendorModel())->find(Auth::vendorId());
} elseif ($isUser) {
    $unreadNotifs = $notifModel->countUnread('user', Auth::userId());
    $userInfo     = (new UserModel())->find(Auth::userId());
}

$avatarInitials = '';
if ($isVendor && $vendorInfo) {
    $parts = explode(' ', $vendorInfo['full_name']);
    $avatarInitials = strtoupper(($parts[0][0] ?? '') . ($parts[1][0] ?? ''));
} elseif ($isUser && $userInfo) {
    $parts = explode(' ', $userInfo['full_name']);
    $avatarInitials = strtoupper(($parts[0][0] ?? '') . ($parts[1][0] ?? ''));
}

function isActiveLink(string $path, string $current): string {
    return str_contains($current, $path) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0b3d91">
    <meta name="csrf-token" content="<?= CSRF::token() ?>">

    <title><?= e($pageTitle) ?></title>

    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= e($css) ?>">
    <?php endforeach; endif; ?>

    <style>
    /* Lucide icon baseline reset for dashboard */
    .sidebar-nav-icon svg,
    .dash-icon svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
        vertical-align: middle;
        flex-shrink: 0;
    }
    .notif-bell-btn svg {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
        vertical-align: middle;
    }
    .dropdown-menu a svg,
    .sidebar-logout-link svg {
        width: 15px;
        height: 15px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
        vertical-align: middle;
        flex-shrink: 0;
    }
    </style>
</head>
<body class="dashboard-page">

    <!-- Dashboard Header -->
    <?php if (!$isVendor): ?>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <!-- Logo -->
                <div class="header-logo">
                    <a href="<?= SITE_URL ?>" aria-label="<?= SITE_NAME ?> Home">
                        <?php if (file_exists(PUBLIC_PATH . '/assets/img/favicon.png')): ?>
                            <img src="<?= SITE_URL ?>/assets/img/favicon.png"
                                 alt="<?= SITE_NAME ?>" height="40">
                        <?php else: ?>
                            <span class="logo-text-fallback">
                                <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>

                <nav class="header-nav">
                    <a href="<?= SITE_URL ?>/browse" class="nav-link">Browse</a>

                    <!-- Notification Bell -->
                    <div style="position:relative;">
                        <button class="notif-bell-btn nav-link" aria-label="Notifications" aria-expanded="false"
                                style="background:none;border:none;cursor:pointer;padding:0.4rem;display:flex;align-items:center;">
                            <i data-lucide="bell"></i>
                            <?php if ($unreadNotifs > 0): ?>
                            <span class="notif-count-badge" style="
                                position:absolute;top:-4px;right:-4px;
                                background:var(--danger);color:#fff;
                                font-size:10px;font-weight:700;
                                width:18px;height:18px;border-radius:50%;
                                display:flex;align-items:center;justify-content:center;">
                                <?= $unreadNotifs > 99 ? '99+' : $unreadNotifs ?>
                            </span>
                            <?php else: ?>
                            <span class="notif-count-badge" style="display:none;position:absolute;top:-4px;right:-4px;
                                background:var(--danger);color:#fff;font-size:10px;font-weight:700;
                                width:18px;height:18px;border-radius:50%;
                                align-items:center;justify-content:center;"></span>
                            <?php endif; ?>
                        </button>
                    </div>

                    <!-- User Menu -->
                    <div style="position:relative;">
                        <button data-dropdown-trigger="user-menu"
                                style="display:flex;align-items:center;gap:0.5rem;background:none;border:none;cursor:pointer;padding:0.4rem 0.75rem;border-radius:9999px;border:1px solid var(--divider);"
                                aria-expanded="false">
                            <span style="width:28px;height:28px;background:var(--hero-gradient);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;">
                                <?= e($avatarInitials) ?>
                            </span>
                            <span style="font-size:0.875rem;font-weight:600;color:var(--text-primary);">
                                <?= e($isVendor ? ($vendorInfo['full_name'] ?? 'Vendor') : ($userInfo['full_name'] ?? 'User')) ?>
                            </span>
                            <i data-lucide="chevron-down" style="width:14px;height:14px;color:var(--text-muted);"></i>
                        </button>
                        <div id="user-menu" class="dropdown-menu" style="
                            display:none;position:absolute;top:calc(100% + 0.5rem);right:0;
                            background:var(--card-bg);border:1px solid var(--divider);
                            border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);
                            min-width:180px;z-index:500;padding:0.5rem;">
                            <div style="padding:0.5rem 1rem;font-size:0.75rem;color:var(--text-muted);border-bottom:1px solid var(--divider);margin-bottom:0.5rem;">
                                <?= $isVendor ? 'Vendor Account' : 'Student Account' ?>
                            </div>
                            <?php if ($isVendor): ?>
                            <a href="<?= SITE_URL ?>/vendor/dashboard" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;font-size:0.875rem;color:var(--text-primary);border-radius:var(--radius-md);">
                                <i data-lucide="layout-dashboard"></i> Dashboard
                            </a>
                            <a href="<?= SITE_URL ?>/vendor/profile" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;font-size:0.875rem;color:var(--text-primary);border-radius:var(--radius-md);">
                                <i data-lucide="user"></i> My Profile
                            </a>
                            <?php else: ?>
                            <a href="<?= SITE_URL ?>/user/dashboard" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;font-size:0.875rem;color:var(--text-primary);border-radius:var(--radius-md);">
                                <i data-lucide="layout-dashboard"></i> Dashboard
                            </a>
                            <a href="<?= SITE_URL ?>/user/profile" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;font-size:0.875rem;color:var(--text-primary);border-radius:var(--radius-md);">
                                <i data-lucide="user"></i> My Profile
                            </a>
                            <?php endif; ?>
                            <div style="border-top:1px solid var(--divider);margin-top:0.5rem;padding-top:0.5rem;">
                                <a href="<?= SITE_URL ?>/logout" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 1rem;font-size:0.875rem;color:var(--danger);border-radius:var(--radius-md);">
                                    <i data-lucide="log-out"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- School Logo -->
                <?php if (defined('SCHOOL_LOGO') && SCHOOL_LOGO): ?>
                <div class="header-school-logo">
                    <img src="<?= SITE_URL ?>/assets/img/<?= e(SCHOOL_LOGO) ?>"
                         alt="<?= e(SCHOOL_NAME) ?>" title="<?= e(SCHOOL_NAME) ?>" height="40">
                </div>
                <?php endif; ?>

                <button class="mobile-menu-btn" aria-label="Open menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <div class="dashboard-layout container" style="max-width:1400px;">

        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-user-info">
                <div class="sidebar-avatar"><?= e($avatarInitials) ?></div>
                <div class="sidebar-user-name">
                    <?= e($isVendor ? ($vendorInfo['full_name'] ?? '') : ($userInfo['full_name'] ?? '')) ?>
                </div>
                <span class="sidebar-user-type">
                    <?php if ($isVendor): ?>
                        <?= ucfirst($vendorInfo['vendor_type'] ?? 'vendor') ?> Vendor
                        &middot; <?= ucfirst($vendorInfo['plan_type'] ?? 'basic') ?> Plan
                    <?php else: ?>
                        Student &middot; <?= e($userInfo['level'] ?? '') ?>
                    <?php endif; ?>
                </span>
            </div>

            <nav class="sidebar-nav">
                <?php if ($isVendor): ?>
                <!-- Vendor Sidebar -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Main</div>
                    <a href="<?= SITE_URL ?>/vendor/dashboard" class="sidebar-nav-link <?= isActiveLink('/vendor/dashboard', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="layout-dashboard"></i></span> Dashboard
                    </a>
                    <a href="<?= SITE_URL ?>/vendor/profile" class="sidebar-nav-link <?= isActiveLink('/vendor/profile', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="user"></i></span> My Profile
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Business</div>
                    <a href="<?= SITE_URL ?>/vendor/reviews" class="sidebar-nav-link <?= isActiveLink('/vendor/reviews', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="star"></i></span> Reviews
                    </a>
                    <a href="<?= SITE_URL ?>/vendor/complaints" class="sidebar-nav-link <?= isActiveLink('/vendor/complaints', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="file-text"></i></span> Complaints
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Billing</div>
                    <a href="<?= SITE_URL ?>/vendor/subscription" class="sidebar-nav-link <?= isActiveLink('/vendor/subscription', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="credit-card"></i></span> Subscription
                    </a>
                    <a href="<?= SITE_URL ?>/vendor/payment-history" class="sidebar-nav-link <?= isActiveLink('/vendor/payment-history', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="receipt"></i></span> Payment History
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Other</div>
                    <a href="<?= SITE_URL ?>/vendor/notifications" class="sidebar-nav-link <?= isActiveLink('/vendor/notifications', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="bell"></i></span> Notifications
                        <?php if ($unreadNotifs > 0): ?>
                        <span class="sidebar-nav-badge"><?= $unreadNotifs ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= SITE_URL ?>/browse" class="sidebar-nav-link">
                        <span class="sidebar-nav-icon"><i data-lucide="search"></i></span> Browse Directory
                    </a>
                    <a href="<?= SITE_URL ?>/logout" class="sidebar-nav-link sidebar-logout-link">
                        <span class="sidebar-nav-icon"><i data-lucide="log-out"></i></span> Logout
                    </a>
                </div>

                <?php else: ?>
                <!-- User Sidebar -->
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Main</div>
                    <a href="<?= SITE_URL ?>/user/dashboard" class="sidebar-nav-link <?= isActiveLink('/user/dashboard', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="layout-dashboard"></i></span> Dashboard
                    </a>
                    <a href="<?= SITE_URL ?>/user/profile" class="sidebar-nav-link <?= isActiveLink('/user/profile', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="user"></i></span> My Profile
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Activity</div>
                    <a href="<?= SITE_URL ?>/user/saved-vendors" class="sidebar-nav-link <?= isActiveLink('/user/saved-vendors', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="heart"></i></span> Saved Vendors
                    </a>
                    <a href="<?= SITE_URL ?>/user/my-reviews" class="sidebar-nav-link <?= isActiveLink('/user/my-reviews', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="star"></i></span> My Reviews
                    </a>
                    <a href="<?= SITE_URL ?>/user/my-complaints" class="sidebar-nav-link <?= isActiveLink('/user/my-complaints', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="file-text"></i></span> My Complaints
                    </a>
                </div>
                <div class="sidebar-nav-section">
                    <div class="sidebar-nav-label">Other</div>
                    <a href="<?= SITE_URL ?>/user/notifications" class="sidebar-nav-link <?= isActiveLink('/user/notifications', $currentPath) ?>">
                        <span class="sidebar-nav-icon"><i data-lucide="bell"></i></span> Notifications
                        <?php if ($unreadNotifs > 0): ?>
                        <span class="sidebar-nav-badge"><?= $unreadNotifs ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= SITE_URL ?>/browse" class="sidebar-nav-link">
                        <span class="sidebar-nav-icon"><i data-lucide="search"></i></span> Browse Directory
                    </a>
                    <a href="<?= SITE_URL ?>/vendor/register" class="sidebar-nav-link" style="color:var(--accent-green);">
                        <span class="sidebar-nav-icon"><i data-lucide="store"></i></span> Become a Vendor
                    </a>
                    <a href="<?= SITE_URL ?>/logout" class="sidebar-nav-link sidebar-logout-link">
                        <span class="sidebar-nav-icon"><i data-lucide="log-out"></i></span> Logout
                    </a>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Mobile sidebar toggle -->
            <button class="mobile-sidebar-btn">
                <i data-lucide="menu" style="width:16px;height:16px;vertical-align:middle;"></i> Menu
            </button>

            <!-- Flash Messages -->
            <?php require __DIR__ . '/../partials/flash.php'; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
        </main>

    </div><!-- /dashboard-layout -->

    <div class="toast-container" aria-live="polite"></div>

    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/vendor.js" defer></script>
    <?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
    <script src="<?= SITE_URL ?>/assets/js/<?= e($js) ?>" defer></script>
    <?php endforeach; endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/lucide@0.568.0/dist/lucide.min.js" defer></script>
    <script defer>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>

</body>
</html>