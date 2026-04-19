<?php
/**
 * CampusLink — Front Controller + Landing Page
 *
 * HOW THIS FILE WORKS:
 * 1. Every URL on the site comes through here via .htaccess
 * 2. We check the URL first — if it's /login, /register, /vendor/... etc,
 *    we load bootstrap and hand off to the correct controller
 * 3. If the URL is just "/" (the home page), we fall through and
 *    render your beautiful landing page HTML below
 */

define('CAMPUSLINK', true);

// ─────────────────────────────────────────────────────────────────────
// STEP 1: DETECT THE CURRENT URL PATH
// Do this BEFORE loading anything heavy
// ─────────────────────────────────────────────────────────────────────

$rawUri     = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath   = dirname($scriptName);
$basePath   = $basePath === '\\' ? '/' : str_replace('\\', '/', $basePath);

$path = parse_url($rawUri, PHP_URL_PATH);
if ($basePath !== '/' && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}

$path = strtolower(trim($path, '/'));

// Split into segments
// e.g. "vendor/register" → ['vendor', 'register']
// e.g. ""                → []  (home page)
$segments = ($path !== '') ? explode('/', $path) : [];
$seg0     = $segments[0] ?? '';
$seg1     = $segments[1] ?? '';
$seg2     = $segments[2] ?? '';
$seg3     = $segments[3] ?? '';

// ─────────────────────────────────────────────────────────────────────
// STEP 2: IF THIS IS NOT THE HOME PAGE — ROUTE IT
// Any path other than "" means we need a controller
// ─────────────────────────────────────────────────────────────────────

if ($seg0 !== '') {

    // Load the full bootstrap (DB, helpers, models, session, etc)
    require_once __DIR__ . '/core/bootstrap.php';

    // Load core utility classes
    require_once __DIR__ . '/core/Sanitizer.php';

    // Load all controllers
    require_once __DIR__ . '/controllers/HomeController.php';
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/controllers/VendorController.php';
    require_once __DIR__ . '/controllers/UserController.php';
    require_once __DIR__ . '/controllers/BrowseController.php';
    require_once __DIR__ . '/controllers/ReviewController.php';
    require_once __DIR__ . '/controllers/ComplaintController.php';
    require_once __DIR__ . '/controllers/NotificationController.php';
    require_once __DIR__ . '/controllers/PageController.php';

    // ── ADMIN ────────────────────────────────────────────────────
    if ($seg0 === 'admin') {
        require_once __DIR__ . '/admin/index.php';
        exit;
    }

    // ── STUDENT AUTH ─────────────────────────────────────────────
    if ($seg0 === 'login') {
        (new AuthController())->login();
        exit;
    }
    if ($seg0 === 'logout') {
        (new AuthController())->logout();
        exit;
    }
    if ($seg0 === 'register') {
        (new AuthController())->register();
        exit;
    }
    if ($seg0 === 'verify-email') {
        (new AuthController())->verifyEmail();
        exit;
    }
    if ($seg0 === 'verify-otp') {
        (new AuthController())->verifyOtp();
        exit;
    }
    if ($seg0 === 'forgot-password') {
        (new AuthController())->forgotPassword();
        exit;
    }
    if ($seg0 === 'reset-password') {
        (new AuthController())->resetPassword();
        exit;
    }

    // ── VENDOR ───────────────────────────────────────────────────
    if ($seg0 === 'vendor') {
        $ctrl = new VendorController();

        if ($seg1 === 'login') {
            $ctrl->login();
            exit;
        }
        if ($seg1 === 'logout') {
            $ctrl->logout();
            exit;
        }

        if ($seg1 === 'register') {
            if ($seg2 === 'student' || strtolower($_GET['type'] ?? '') === 'student') {
                $ctrl->registerStudent();
                exit;
            }
            if ($seg2 === 'community' || strtolower($_GET['type'] ?? '') === 'community') {
                $ctrl->registerCommunity();
                exit;
            }
            $ctrl->registerSelect();
            exit;
            exit;
        }

        if ($seg1 === 'dashboard') {
            $ctrl->dashboard();
            exit;
        }
        if ($seg1 === 'profile') {
            $ctrl->profile();
            exit;
        }
        if ($seg1 === 'reviews') {
            $ctrl->reviews();
            exit;
        }
        if ($seg1 === 'complaints') {
            $ctrl->complaints();
            exit;
        }
        if ($seg1 === 'subscription') {
            $ctrl->subscription();
            exit;
        }
        if ($seg1 === 'notifications') {
            $ctrl->notifications();
            exit;
        }

        if ($seg1 === 'payment') {
            if ($seg2 === 'initiate') { $ctrl->paymentInitiate();          exit; }
            if ($seg2 === 'verify')   { $ctrl->paymentVerify();            exit; }
            if ($seg2 === 'success')  { $ctrl->paymentSuccess();           exit; }
            if ($seg2 === 'failed')   { $ctrl->paymentFailed();            exit; }
            if ($seg2 === 'history')  { $ctrl->paymentHistory();           exit; }
            if ($seg2 === 'receipt')  { $ctrl->paymentReceipt((int)$seg3); exit; }
            $ctrl->payment();
            exit;
        }

        (new HomeController())->notFound();
        exit;
    }

    // ── USER DASHBOARD ───────────────────────────────────────────
    if ($seg0 === 'user') {
        $ctrl = new UserController();

        if ($seg1 === 'dashboard')     { $ctrl->dashboard();     exit; }
        if ($seg1 === 'profile')       { $ctrl->profile();       exit; }
        if ($seg1 === 'saved-vendors') { $ctrl->savedVendors();  exit; }
        if ($seg1 === 'my-reviews')    { $ctrl->myReviews();     exit; }
        if ($seg1 === 'my-complaints') { $ctrl->myComplaints();  exit; }
        if ($seg1 === 'notifications') { $ctrl->notifications(); exit; }

        (new HomeController())->notFound();
        exit;
    }

    // ── BROWSE ───────────────────────────────────────────────────
    if ($seg0 === 'browse') {
        $ctrl = new BrowseController();
        if ($seg1 === '')           { $ctrl->index();              exit; }
        if ($seg1 === 'categories') { $ctrl->categories();         exit; }
        $ctrl->vendorProfile($seg1);
        exit;
    }

    // ── CATEGORIES (shorthand) ────────────────────────────────────
    if ($seg0 === 'categories') {
        (new BrowseController())->categories();
        exit;
    }

    // ── REVIEWS ──────────────────────────────────────────────────
    if ($seg0 === 'reviews') {
        if ($seg1 === 'submit') {
            (new ReviewController())->submit();
            exit;
        }
        (new HomeController())->notFound();
        exit;
    }

    // ── COMPLAINTS ───────────────────────────────────────────────
    if ($seg0 === 'complaints') {
        $ctrl = new ComplaintController();
        if ($seg1 === 'submit') { $ctrl->submit(); exit; }
        if ($seg1 === 'track')  { $ctrl->track();  exit; }
        (new HomeController())->notFound();
        exit;
    }

    // ── NOTIFICATIONS (AJAX) ─────────────────────────────────────
    if ($seg0 === 'notifications') {
        $ctrl = new NotificationController();
        if ($seg1 === 'mark-read')     { $ctrl->markRead();    exit; }
        if ($seg1 === 'mark-all-read') { $ctrl->markAllRead(); exit; }
        if ($seg1 === 'unread-count')  { $ctrl->unreadCount(); exit; }
        (new HomeController())->notFound();
        exit;
    }

    // ── SAVED VENDORS (AJAX) ─────────────────────────────────────
    if ($seg0 === 'saved-vendors') {
        if ($seg1 === 'toggle') {
            (new UserController())->toggleSave();
            exit;
        }
        (new HomeController())->notFound();
        exit;
    }

    // ── STATIC PAGES ─────────────────────────────────────────────
    // Supports both /about AND /pages/about for backwards compat
    $pageSlug = ($seg0 === 'pages') ? $seg1 : $seg0;
    $pageCtrl = new PageController();

    $pageMap = [
        'about'                => 'about',
        'contact'              => 'contact',
        'how-it-works'         => 'howItWorks',
        'general-terms'        => 'generalTerms',
        'user-terms'           => 'userTerms',
        'vendor-terms'         => 'vendorTerms',
        'privacy-policy'       => 'privacyPolicy',
        'refund-policy'        => 'refundPolicy',
        'suspension-policy'    => 'suspensionPolicy',
        'complaint-resolution' => 'complaintResolution',
        'data-retention'       => 'dataRetention',
    ];

    if (isset($pageMap[$pageSlug])) {
        $method = $pageMap[$pageSlug];
        $pageCtrl->$method();
        exit;
    }

    // ── NOTHING MATCHED → 404 ────────────────────────────────────
 (new HomeController())->notFound();
    exit;
}

// ─────────────────────────────────────────────────────────────────────
// STEP 3: HOME PAGE ( path = "" )
// Your original beautiful landing page starts here
// ─────────────────────────────────────────────────────────────────────
require_once __DIR__ . '/core/bootstrap.php';

$db = Database::getInstance()->getConnection();

// Stats
try {
    $totalVendors = $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'active'")->fetchColumn();
    $totalUsers   = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $activeSubs   = $db->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active' AND expiry_date >= NOW()")->fetchColumn();
    $avgRating    = $db->query("SELECT ROUND(AVG(rating),1) FROM reviews WHERE status = 'approved'")->fetchColumn();
    $avgRating    = $avgRating ?: '4.8';
} catch (Exception $e) {
    $totalVendors = 0; $totalUsers = 0; $activeSubs = 0; $avgRating = '4.8';
}

// Featured vendors
try {
    $featuredStmt = $db->query("
        SELECT v.*, c.name as category_name,
               COALESCE(AVG(r.rating),0) as avg_rating,
               COUNT(r.id) as review_count
        FROM vendors v
        LEFT JOIN categories c ON v.category_id = c.id
        LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
        WHERE v.status = 'active' AND v.plan_type IN ('featured','premium')
        GROUP BY v.id
        ORDER BY v.plan_type = 'featured' DESC, avg_rating DESC
        LIMIT 6
    ");
    $featuredVendors = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredVendors = [];
}

// Categories with vendor counts
try {
    $catStmt = $db->query("
        SELECT c.*, COUNT(v.id) as vendor_count
        FROM categories c
        LEFT JOIN vendors v ON v.category_id = c.id AND v.status = 'active'
        GROUP BY c.id
        ORDER BY c.sort_order ASC
        LIMIT 10
    ");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

$pageTitle = 'CampusLink - Find Trusted Campus Services Instantly';
$pageDesc  = 'CampusLink connects students with verified vendors within the university environment. Browse campus services, contact vendors directly via phone or WhatsApp.';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="keywords" content="campus services, university vendors, student services, campus directory">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= SITE_URL ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/webp" href="assets/images/logo.webp">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Lucide icon sizing defaults */
        i[data-lucide] {
            display: inline-flex;
            width: 20px;
            height: 20px;
            stroke-width: 1.75;
            vertical-align: middle;
        }
        .nav-icon[data-lucide]       { width: 18px; height: 18px; }
        .trust-icon i[data-lucide]   { width: 28px; height: 28px; }
        .step-icon i[data-lucide]    { width: 32px; height: 32px; }
        .category-icon i[data-lucide]{ width: 28px; height: 28px; }
        .stat-icon i[data-lucide]    { width: 32px; height: 32px; }
        .vendor-badge-icon[data-lucide]{ width: 12px; height: 12px; stroke-width: 2.5; }
        .benefit-icon[data-lucide]   { width: 16px; height: 16px; stroke-width: 2.5; color: #16a34a; }
        .plan-star[data-lucide]      { width: 14px; height: 14px; stroke-width: 2; color: #f59e0b; }
        .star-icon[data-lucide]      { width: 14px; height: 14px; }
        .footer-mail-icon[data-lucide]{ width: 16px; height: 16px; }
        .price-icon[data-lucide]     { width: 14px; height: 14px; }
        .empty-state-icon[data-lucide]{ width: 56px; height: 56px; stroke-width: 1.25; opacity: 0.45; }
        .disclaimer-icon i[data-lucide]{ width: 20px; height: 20px; }
    </style>
</head>
<body>

<!-- ============================================================
     HEADER / NAVIGATION
============================================================ -->
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">

            <!-- Logo Left -->
            <div class="header-logo">
                <a href="<?= SITE_URL ?>">
                    <img src="assets/images/logo.webp" alt="CampusLink Logo" width="150" height="40"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <span class="logo-text-fallback" style="display:none">
                        <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
                    </span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="header-nav" id="headerNav">
                <a href="#home" class="nav-link active">Home</a>
                <a href="#browse" class="nav-link">Browse Services</a>
                <a href="#categories" class="nav-link">Categories</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <a href="vendor/register" class="nav-link nav-cta-outline"><i data-lucide="store" class="nav-icon"></i> List Your Business</a>
                <?php if (Auth::isLoggedIn()): ?>
                    <a href="<?= Auth::isVendor() ? 'vendor/dashboard' : 'user/dashboard' ?>" class="nav-link nav-cta-filled">Dashboard</a>
                <?php else: ?>
                    <a href="login" class="nav-link nav-cta-filled">Login</a>
                <?php endif; ?>
            </nav>

            <!-- School Logo Right -->
            <div class="header-school-logo">
                <img src="uploads/school-logo/school-logo.webp" alt="University Logo" width="50" height="50"
                     onerror="this.style.display='none'">
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Nav Drawer -->
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
<nav class="mobile-nav" id="mobileNav">
    <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close menu">&times;</button>
    <div class="mobile-nav-logo">
        <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
    </div>
    <a href="#home" class="mobile-nav-link"><i data-lucide="home" class="nav-icon" aria-hidden="true"></i> Home</a>
    <a href="browse" class="mobile-nav-link"><i data-lucide="search" class="nav-icon" aria-hidden="true"></i> Browse Services</a>
    <a href="categories" class="mobile-nav-link"><i data-lucide="folder" class="nav-icon" aria-hidden="true"></i> Categories</a>
    <a href="#how-it-works" class="mobile-nav-link"><i data-lucide="help-circle" class="nav-icon" aria-hidden="true"></i> How It Works</a>
    <a href="vendor/register" class="mobile-nav-link"><i data-lucide="store" class="nav-icon" aria-hidden="true"></i> List Your Business</a>
    <?php if (Auth::isLoggedIn()): ?>
        <a href="<?= Auth::isVendor() ? 'vendor/dashboard' : 'user/dashboard' ?>" class="mobile-nav-link mobile-nav-cta">Dashboard</a>
    <?php else: ?>
        <a href="login" class="mobile-nav-link mobile-nav-cta">Login / Sign Up</a>
    <?php endif; ?>
</nav>


<!-- ============================================================
     HERO SECTION
============================================================ -->
<section class="hero-section" id="home">
    <div class="hero-bg-overlay"></div>
    <div class="container">
        <div class="hero-content">

            <div class="hero-badge fade-in">
                <span class="badge-dot"></span>
                Verified Campus Vendors
            </div>

            <h1 class="hero-headline fade-in">
                Find Trusted Campus<br>
                <span class="hero-headline-accent">Services Instantly</span>
            </h1>

            <p class="hero-subtext fade-in">
                CampusLink connects students and campus community members with
                verified vendors within the university environment. Browse, contact,
                and get things done — safely and conveniently.
            </p>

            <!-- Search Bar -->
            <div class="hero-search-wrapper fade-in" id="browse">
                <form class="hero-search-form" action="browse" method="GET" id="heroSearchForm">
                    <div class="search-input-group">
                        <span class="search-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" name="q" placeholder="Search for a vendor or service..." class="search-input" id="heroSearchInput" autocomplete="off">
                        <select name="category" class="search-category-select" id="heroCategorySelect">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </form>
            </div>

            <!-- Hero CTAs -->
            <div class="hero-cta-group fade-in">
                <a href="browse" class="btn btn-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Browse Services
                </a>
                <a href="vendor/register" class="btn btn-outline-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <i data-lucide="store" class="btn-icon"></i> List Your Business
                </a>
            </div>

            <!-- Hero Stats Strip -->
            <div class="hero-stats fade-in">
                <div class="hero-stat">
                    <span class="hero-stat-number" id="statVendors"><?= number_format($totalVendors) ?>+</span>
                    <span class="hero-stat-label">Verified Vendors</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number" id="statUsers"><?= number_format($totalUsers) ?>+</span>
                    <span class="hero-stat-label">Active Students</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number" id="statRating">
                        <?= $avgRating ?> <i data-lucide="star" style="width:16px;height:16px;fill:#f59e0b;color:#f59e0b;vertical-align:middle;"></i>
                    </span>
                    <span class="hero-stat-label">Average Rating</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Decorative wave -->
    <div class="hero-wave">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#f8f9fa"/>
        </svg>
    </div>
</section>


<!-- ============================================================
     CATEGORIES GRID SECTION
============================================================ -->
<section class="categories-section" id="categories">
    <div class="container">
        <div class="section-header">
            <span class="section-label">What do you need?</span>
            <h2 class="section-title">Browse by Category</h2>
            <p class="section-subtitle">Discover verified vendors across all major campus service categories</p>
        </div>

        <div class="categories-grid">
            <?php
            $catIcons = [
                'beauty'      => '<i data-lucide="scissors"></i>',
                'tech'        => '<i data-lucide="cpu"></i>',
                'repairs'     => '<i data-lucide="wrench"></i>',
                'academic'    => '<i data-lucide="book-open"></i>',
                'fashion'     => '<i data-lucide="shopping-bag"></i>',
                'printing'    => '<i data-lucide="printer"></i>',
                'food'        => '<i data-lucide="coffee"></i>',
                'photography' => '<i data-lucide="camera"></i>',
                'tutoring'    => '<i data-lucide="graduation-cap"></i>',
                'laundry'     => '<i data-lucide="wind"></i>',
                'logistics'   => '<i data-lucide="truck"></i>',
                'health'      => '<i data-lucide="heart"></i>',
            ];

            if (!empty($categories)):
                foreach ($categories as $cat):
                    $icon  = $catIcons[strtolower($cat['slug'] ?? $cat['name'])] ?? '<i data-lucide="store"></i>';
                    $count = $cat['vendor_count'] ?? 0;
            ?>
            <a href="browse?category=<?= urlencode($cat['slug']) ?>" class="category-card">
                <div class="category-icon"><?= $icon ?></div>
                <div class="category-name"><?= htmlspecialchars($cat['name']) ?></div>
                <div class="category-count"><?= $count ?> vendor<?= $count !== 1 ? 's' : '' ?></div>
            </a>
            <?php endforeach;
            else:
                $staticCats = [
                    ['slug'=>'beauty',      'icon'=>'<i data-lucide="scissors"></i>',      'name'=>'Beauty & Grooming'],
                    ['slug'=>'tech',        'icon'=>'<i data-lucide="cpu"></i>',            'name'=>'Tech & Gadgets'],
                    ['slug'=>'repairs',     'icon'=>'<i data-lucide="wrench"></i>',         'name'=>'Repairs'],
                    ['slug'=>'academic',    'icon'=>'<i data-lucide="book-open"></i>',      'name'=>'Academic Help'],
                    ['slug'=>'fashion',     'icon'=>'<i data-lucide="shopping-bag"></i>',   'name'=>'Fashion'],
                    ['slug'=>'printing',    'icon'=>'<i data-lucide="printer"></i>',        'name'=>'Printing'],
                    ['slug'=>'food',        'icon'=>'<i data-lucide="coffee"></i>',         'name'=>'Food & Snacks'],
                    ['slug'=>'photography', 'icon'=>'<i data-lucide="camera"></i>',         'name'=>'Photography'],
                    ['slug'=>'tutoring',    'icon'=>'<i data-lucide="graduation-cap"></i>', 'name'=>'Tutoring'],
                    ['slug'=>'laundry',     'icon'=>'<i data-lucide="wind"></i>',           'name'=>'Laundry'],
                ];
                foreach ($staticCats as $cat): ?>
            <a href="browse?category=<?= $cat['slug'] ?>" class="category-card">
                <div class="category-icon"><?= $cat['icon'] ?></div>
                <div class="category-name"><?= $cat['name'] ?></div>
                <div class="category-count">Coming soon</div>
            </a>
            <?php endforeach;
            endif; ?>
        </div>

        <div class="section-cta">
            <a href="categories" class="btn btn-outline-primary">View All Categories</a>
        </div>
    </div>
</section>


<!-- ============================================================
     FEATURED VENDORS SECTION
============================================================ -->
<section class="featured-section" id="featured-vendors">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Top Picks</span>
            <h2 class="section-title">Featured Vendors</h2>
            <p class="section-subtitle">Handpicked, verified vendors offering top-rated campus services</p>
        </div>

        <div class="vendors-grid">
            <?php if (!empty($featuredVendors)): ?>
                <?php foreach ($featuredVendors as $vendor): ?>
                <div class="vendor-card">

                    <!-- Badges -->
                    <div class="vendor-card-badges">
                        <span class="badge-verified">
                            <i data-lucide="badge-check" class="vendor-badge-icon" aria-hidden="true"></i> Verified
                        </span>
                        <?php if ($vendor['plan_type'] === 'featured'): ?>
                            <span class="badge-featured">
                                <i data-lucide="star" class="vendor-badge-icon" aria-hidden="true"></i> Featured
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Logo -->
                    <div class="vendor-logo-wrap">
                        <?php if (!empty($vendor['logo'])): ?>
                            <img src="uploads/logos/<?= htmlspecialchars($vendor['logo']) ?>"
                                 alt="<?= htmlspecialchars($vendor['business_name']) ?> logo"
                                 class="vendor-logo" width="80" height="80"
                                 onerror="this.src='assets/images/default/vendor-placeholder.webp'">
                        <?php else: ?>
                            <div class="vendor-logo-placeholder">
                                <?= strtoupper(substr($vendor['business_name'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="vendor-info">
                        <h3 class="vendor-name"><?= htmlspecialchars($vendor['business_name']) ?></h3>
                        <span class="vendor-category"><?= htmlspecialchars($vendor['category_name'] ?? '') ?></span>

                        <!-- Stars -->
                        <div class="vendor-rating">
                            <?php
                            $rating = round($vendor['avg_rating'] * 2) / 2;
                            for ($s = 1; $s <= 5; $s++):
                                if ($s <= $rating)
                                    echo '<span class="star full"><i data-lucide="star" class="star-icon" aria-hidden="true" style="fill:#f59e0b;color:#f59e0b;"></i></span>';
                                elseif ($s - 0.5 <= $rating)
                                    echo '<span class="star half"><i data-lucide="star" class="star-icon" aria-hidden="true" style="fill:#fcd34d;color:#fcd34d;"></i></span>';
                                else
                                    echo '<span class="star empty"><i data-lucide="star" class="star-icon" aria-hidden="true" style="color:#d1d5db;"></i></span>';
                            endfor;
                            ?>
                            <span class="rating-number"><?= number_format($vendor['avg_rating'], 1) ?></span>
                            <span class="rating-count">(<?= $vendor['review_count'] ?>)</span>
                        </div>

                        <p class="vendor-description">
                            <?= htmlspecialchars(substr($vendor['description'] ?? '', 0, 100)) ?>
                            <?= strlen($vendor['description'] ?? '') > 100 ? '...' : '' ?>
                        </p>

                        <?php if (!empty($vendor['price_range'])): ?>
                            <div class="vendor-price">
                                <i data-lucide="tag" class="price-icon" aria-hidden="true"></i>
                                <?= htmlspecialchars($vendor['price_range']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="vendor-actions">
                        <?php if (!empty($vendor['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>?text=Hi%2C%20I%20found%20your%20listing%20on%20CampusLink"
                           target="_blank" rel="noopener" class="btn-whatsapp">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            WhatsApp
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($vendor['phone'])): ?>
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $vendor['phone']) ?>" class="btn-call">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 .82h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            Call
                        </a>
                        <?php endif; ?>

                        <a href="vendor/<?= htmlspecialchars($vendor['slug'] ?? $vendor['id']) ?>" class="btn-profile">
                            View Profile <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php else: ?>
                <!-- Empty state -->
                <div class="vendors-empty-state">
                    <div class="empty-icon">
                        <i data-lucide="store" class="empty-state-icon" aria-hidden="true"></i>
                    </div>
                    <h3>Vendors Coming Soon</h3>
                    <p>Be among the first verified vendors on CampusLink.</p>
                    <a href="vendor/register" class="btn btn-primary"><i data-lucide="store" class="btn-icon"></i> List Your Business</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($featuredVendors)): ?>
        <div class="section-cta">
            <a href="browse" class="btn btn-primary">View All Vendors</a>
        </div>
        <?php endif; ?>
    </div>
</section>


<!-- ============================================================
     TRUST & SAFETY SECTION
============================================================ -->
<section class="trust-section">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Your Safety Matters</span>
            <h2 class="section-title">Why Trust CampusLink?</h2>
            <p class="section-subtitle">We enforce strict standards to keep the campus community safe</p>
        </div>

        <div class="trust-grid">
            <div class="trust-card">
                <div class="trust-icon"><i data-lucide="search" aria-hidden="true"></i></div>
                <h3>Vendor Verification</h3>
                <p>Every vendor undergoes manual identity and document verification before being listed. Student vendors must submit valid ID cards and proof of service.</p>
            </div>
            <div class="trust-card">
                <div class="trust-icon"><i data-lucide="lock" aria-hidden="true"></i></div>
                <h3>Secure Payments</h3>
                <p>All subscription payments are processed via Paystack with full server-side verification. No payment data is stored on our servers.</p>
            </div>
            <div class="trust-card">
                <div class="trust-icon"><i data-lucide="star" aria-hidden="true"></i></div>
                <h3>Moderated Reviews</h3>
                <p>Every review is manually moderated by our admin team before publication. Abusive, fake, or misleading reviews are removed.</p>
            </div>
            <div class="trust-card">
                <div class="trust-icon"><i data-lucide="shield" aria-hidden="true"></i></div>
                <h3>Complaints Investigated</h3>
                <p>Users can submit verified complaints with evidence. Three valid complaints trigger a suspension review. Vendors can be permanently banned.</p>
            </div>
            <div class="trust-card">
                <div class="trust-icon"><i data-lucide="check-circle" aria-hidden="true"></i></div>
                <h3>Admin Approval Required</h3>
                <p>No vendor goes live without explicit admin approval. Registrations are reviewed within 48 hours. All upgrades require admin confirmation.</p>
            </div>
            <div class="trust-card">
                <div class="trust-icon"><i data-lucide="file-text" aria-hidden="true"></i></div>
                <h3>Legal Compliance</h3>
                <p>CampusLink operates under comprehensive legal policies including privacy policy, refund policy, and suspension policy aligned with Nigerian law.</p>
            </div>
        </div>

        <!-- Disclaimer Box -->
        <div class="disclaimer-box">
            <div class="disclaimer-icon"><i data-lucide="alert-triangle" aria-hidden="true"></i></div>
            <div class="disclaimer-text">
                <strong>Important Disclaimer:</strong> CampusLink is a <em>directory platform only</em>.
                We do not provide services directly, do not facilitate chat between users and vendors,
                and do not participate in transactions between users and vendors. All business
                communication must happen externally through phone calls or WhatsApp using the
                provided contact buttons. CampusLink bears no liability for the outcome of
                transactions conducted outside this platform.
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     HOW IT WORKS SECTION
============================================================ -->
<section class="how-it-works-section" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Simple Process</span>
            <h2 class="section-title">How CampusLink Works</h2>
            <p class="section-subtitle">Getting connected with campus vendors is easy and straightforward</p>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-icon"><i data-lucide="search" aria-hidden="true"></i></div>
                <h3>Browse Vendors</h3>
                <p>Search and filter verified vendors by category, name, or service. View full profiles, ratings, and reviews.</p>
            </div>

            <div class="step-arrow"><i data-lucide="arrow-right" style="width:24px;height:24px;"></i></div>

            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-icon"><i data-lucide="smartphone" aria-hidden="true"></i></div>
                <h3>Contact via Phone or WhatsApp</h3>
                <p>Use the Call or WhatsApp button on the vendor's profile to reach them directly. No in-app chat is provided.</p>
            </div>

            <div class="step-arrow"><i data-lucide="arrow-right" style="width:24px;height:24px;"></i></div>

            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-icon"><i data-lucide="handshake" aria-hidden="true"></i></div>
                <h3>Complete Business Offline</h3>
                <p>Negotiate, agree on terms, and complete your transaction directly with the vendor offline or via external channels.</p>
            </div>

            <div class="step-arrow"><i data-lucide="arrow-right" style="width:24px;height:24px;"></i></div>

            <div class="step-card">
                <div class="step-number">04</div>
                <div class="step-icon"><i data-lucide="star" aria-hidden="true"></i></div>
                <h3>Leave a Review</h3>
                <p>After your experience, leave an honest rating and review to help other students make informed decisions.</p>
            </div>

            <div class="step-arrow"><i data-lucide="arrow-right" style="width:24px;height:24px;"></i></div>

            <div class="step-card">
                <div class="step-number">05</div>
                <div class="step-icon"><i data-lucide="alert-triangle" aria-hidden="true"></i></div>
                <h3>Report Issues</h3>
                <p>If you experience problems, submit a formal complaint with evidence. Our team investigates all reported issues.</p>
            </div>
        </div>

        <div class="how-disclaimer">
            <p>
                <strong>Note:</strong> CampusLink operates strictly as a digital directory.
                We connect you with vendors but do not participate in, mediate, or guarantee
                any transaction or service rendered between users and vendors.
            </p>
        </div>
    </div>
</section>


<!-- ============================================================
     VENDOR CTA SECTION
============================================================ -->
<section class="vendor-cta-section">
    <div class="container">
        <div class="vendor-cta-inner">
            <div class="vendor-cta-text">
                <h2>Are You a Campus Vendor?</h2>
                <p>
                    Join hundreds of verified vendors on CampusLink. Get discovered by thousands
                    of students and campus community members. Register and subscribe per semester
                    to keep your listing active.
                </p>
                <ul class="vendor-cta-benefits">
                    <li><i data-lucide="check-circle" class="benefit-icon" aria-hidden="true"></i> Reach thousands of students daily</li>
                    <li><i data-lucide="check-circle" class="benefit-icon" aria-hidden="true"></i> Build your reputation with verified reviews</li>
                    <li><i data-lucide="check-circle" class="benefit-icon" aria-hidden="true"></i> Student vendor plans from &#8358;2,000/semester</li>
                    <li><i data-lucide="check-circle" class="benefit-icon" aria-hidden="true"></i> Community vendor plans from &#8358;4,000/semester</li>
                    <li><i data-lucide="check-circle" class="benefit-icon" aria-hidden="true"></i> Featured listings for maximum visibility</li>
                    <li><i data-lucide="check-circle" class="benefit-icon" aria-hidden="true"></i> Dashboard to manage your profile and reviews</li>
                </ul>
            </div>
            <div class="vendor-cta-cards">
                <div class="plan-preview-card">
                    <div class="plan-preview-type">Student Vendor</div>
                    <div class="plan-preview-plans">
                        <div class="plan-preview-item">
                            <span class="plan-name">Basic</span>
                            <span class="plan-price">&#8358;2,000<small>/sem</small></span>
                        </div>
                        <div class="plan-preview-item popular">
                            <span class="plan-name">Premium <i data-lucide="star" class="plan-star" aria-hidden="true"></i></span>
                            <span class="plan-price">&#8358;5,000<small>/sem</small></span>
                        </div>
                        <div class="plan-preview-item">
                            <span class="plan-name">Featured</span>
                            <span class="plan-price">&#8358;10,000<small>/sem</small></span>
                        </div>
                    </div>
                    <a href="vendor/register?type=student" class="btn btn-primary btn-full">Register as Student Vendor</a>
                </div>
                <div class="plan-preview-card">
                    <div class="plan-preview-type">Community Vendor</div>
                    <div class="plan-preview-plans">
                        <div class="plan-preview-item">
                            <span class="plan-name">Basic</span>
                            <span class="plan-price">&#8358;4,000<small>/sem</small></span>
                        </div>
                        <div class="plan-preview-item popular">
                            <span class="plan-name">Premium <i data-lucide="star" class="plan-star" aria-hidden="true"></i></span>
                            <span class="plan-price">&#8358;7,000<small>/sem</small></span>
                        </div>
                        <div class="plan-preview-item">
                            <span class="plan-name">Featured</span>
                            <span class="plan-price">&#8358;12,000<small>/sem</small></span>
                        </div>
                    </div>
                    <a href="vendor/register?type=community" class="btn btn-primary btn-full">Register as Community Vendor</a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     DYNAMIC STATISTICS SECTION
============================================================ -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number counter" data-target="<?= $totalVendors ?>"><?= $totalVendors ?></div>
                <div class="stat-label">Verified Vendors</div>
                <div class="stat-icon"><i data-lucide="store" aria-hidden="true"></i></div>
            </div>
            <div class="stat-item">
                <div class="stat-number counter" data-target="<?= $totalUsers ?>"><?= $totalUsers ?></div>
                <div class="stat-label">Registered Students</div>
                <div class="stat-icon"><i data-lucide="graduation-cap" aria-hidden="true"></i></div>
            </div>
            <div class="stat-item">
                <div class="stat-number counter" data-target="<?= $activeSubs ?>"><?= $activeSubs ?></div>
                <div class="stat-label">Active Subscriptions</div>
                <div class="stat-icon"><i data-lucide="clipboard-list" aria-hidden="true"></i></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $avgRating ?></div>
                <div class="stat-label">Average Rating</div>
                <div class="stat-icon"><i data-lucide="star" aria-hidden="true"></i></div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     FOOTER
============================================================ -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand Column -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
                </div>
                <p class="footer-tagline">
                    A secure, lightweight digital campus service directory connecting students
                    with verified vendors within the university environment.
                </p>
                <div class="footer-disclaimer-small">
                    CampusLink is a directory platform only. We do not provide services,
                    process transactions, or mediate between users and vendors.
                </div>
                <div class="footer-contact-info">
                    <a href="mailto:<?= CONTACT_EMAIL ?>">
                        <i data-lucide="mail" class="footer-mail-icon" aria-hidden="true"></i>
                        <?= CONTACT_EMAIL ?>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h4 class="footer-col-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>">Home</a></li>
                    <li><a href="browse">Browse Services</a></li>
                    <li><a href="categories">All Categories</a></li>
                    <li><a href="how-it-works">How It Works</a></li>
                    <li><a href="about">About CampusLink</a></li>
                    <li><a href="contact">Contact Us</a></li>
                </ul>
            </div>

            <!-- For Vendors -->
            <div class="footer-col">
                <h4 class="footer-col-title">For Vendors</h4>
                <ul class="footer-links">
                    <li><a href="vendor/register?type=student">Register as Student Vendor</a></li>
                    <li><a href="vendor/register?type=community">Register as Community Vendor</a></li>
                    <li><a href="vendor/login">Vendor Login</a></li>
                    <li><a href="pages/vendor-terms">Vendor Terms &amp; Conditions</a></li>
                    <li><a href="pages/suspension-policy">Suspension Policy</a></li>
                    <li><a href="pages/complaint-resolution">Complaint Resolution</a></li>
           </div>

            <!-- Legal -->
            <div class="footer-col">
                <h4 class="footer-col-title">Legal &amp; Policies</h4>
                <ul class="footer-links">
                    <li><a href="pages/general-terms">General Terms &amp; Conditions</a></li>
                    <li><a href="pages/user-terms">User Terms &amp; Conditions</a></li>
                    <li><a href="pages/vendor-terms">Vendor Terms &amp; Conditions</a></li>
                    <li><a href="pages/privacy-policy">Privacy Policy</a></li>
                    <li><a href="pages/refund-policy">Refund Policy</a></li>
                    <li><a href="pages/data-retention">Data Retention Policy</a></li>
                </ul>
            </div>

        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <div class="footer-bottom-left">
                <p>&copy; <?= date('Y') ?> CampusLink. All rights reserved. | Governed by Nigerian Law</p>
            </div>
            <div class="footer-bottom-right">
                <span>Built for the campus community <i data-lucide="graduation-cap" style="width:16px;height:16px;vertical-align:middle;"></i></span>
            </div>
        </div>
    </div>
</footer>


<!-- Scripts -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
    lucide.createIcons();
</script>
<script src="assets/js/main.js"></script>
<script src="assets/js/landing.js"></script>

</body>
</html>