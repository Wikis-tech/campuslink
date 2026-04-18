<?php defined('CAMPUSLINK') or die(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="<?= CSRF::token() ?>">
    <title><?= e($pageTitle ?? 'Admin Panel') ?> — <?= e(SITE_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <style>
        /* ── Admin Shell ───────────────────────────────── */
        :root {
            --admin-sidebar-w: 240px;
            --admin-header-h:  56px;
            --admin-bg:        #f1f5f9;
            --admin-sidebar:   #0f172a;
            --admin-sidebar-hover: rgba(255,255,255,0.07);
            --admin-sidebar-active: rgba(255,255,255,0.12);
            --admin-accent:    #3b82f6;
        }
        body { background: var(--admin-bg); margin:0; }

        /* Header */
        .admin-header {
            position: fixed; top:0; left:0; right:0; height: var(--admin-header-h);
            background: #fff; border-bottom:1px solid var(--divider);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem; z-index: 200; gap:1rem;
        }
        .admin-header-brand {
            font-size:1rem; font-weight:900; letter-spacing:-0.02em; white-space:nowrap;
        }
        .admin-header-brand .cl { color: var(--primary); }
        .admin-header-brand .admin-tag {
            font-size:0.6rem; font-weight:700; background:var(--danger);
            color:#fff; padding:1px 5px; border-radius:3px;
            margin-left:4px; vertical-align:middle; letter-spacing:0.04em;
        }
        .admin-header-right {
            display:flex; align-items:center; gap:1rem; font-size:0.8rem;
        }
        .admin-header-user {
            display:flex; align-items:center; gap:0.5rem;
            color:var(--text-secondary);
        }
        .admin-avatar {
            width:30px; height:30px; border-radius:50%;
            background:var(--hero-gradient); color:#fff;
            display:flex; align-items:center; justify-content:center;
            font-size:0.7rem; font-weight:700;
        }

        /* Sidebar */
        .admin-sidebar {
            position:fixed; top:var(--admin-header-h); left:0; bottom:0;
            width: var(--admin-sidebar-w);
            background: var(--admin-sidebar);
            overflow-y:auto; z-index:100; padding-bottom:2rem;
        }
        .admin-nav-section {
            padding: 1.25rem 0.75rem 0.5rem;
        }
        .admin-nav-label {
            font-size:0.6rem; font-weight:700; text-transform:uppercase;
            letter-spacing:0.1em; color:rgba(255,255,255,0.3);
            padding:0 0.5rem; margin-bottom:0.35rem;
        }
        .admin-nav-link {
            display:flex; align-items:center; gap:0.65rem;
            padding:0.55rem 0.75rem; border-radius:6px;
            font-size:0.8rem; font-weight:500; color:rgba(255,255,255,0.65);
            text-decoration:none; transition:all 0.15s;
            position:relative;
        }
        .admin-nav-link:hover { background:var(--admin-sidebar-hover); color:#fff; }
        .admin-nav-link.active {
            background:var(--admin-sidebar-active);
            color:#fff; font-weight:700;
            border-left:2px solid var(--admin-accent);
        }
        .admin-nav-badge {
            margin-left:auto; background:var(--danger); color:#fff;
            font-size:0.6rem; font-weight:700; padding:1px 5px;
            border-radius:9999px; min-width:16px; text-align:center;
        }
        .admin-nav-icon { font-size:1rem; flex-shrink:0; }

        /* Main */
        .admin-main {
            margin-left: var(--admin-sidebar-w);
            padding-top: var(--admin-header-h);
            min-height: 100vh;
        }
        .admin-content {
            padding: 1.5rem;
            max-width: 1400px;
        }

        /* Page header */
        .admin-page-header {
            display:flex; justify-content:space-between; align-items:flex-start;
            flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem;
        }
        .admin-page-title {
            font-size:1.4rem; font-weight:900; color:var(--text-primary);
            letter-spacing:-0.02em; margin:0;
        }
        .admin-page-sub {
            font-size:var(--font-size-sm); color:var(--text-muted); margin-top:0.2rem;
        }

        /* Stats cards */
        .admin-stats-grid {
            display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
            gap:1rem; margin-bottom:1.5rem;
        }
        .admin-stat-card {
            background:#fff; border-radius:var(--radius-xl);
            padding:1.1rem; border:1px solid var(--divider);
            border-top:3px solid var(--primary);
        }
        .admin-stat-card.green  { border-top-color:var(--accent-green); }
        .admin-stat-card.amber  { border-top-color:var(--warning-amber); }
        .admin-stat-card.red    { border-top-color:var(--danger); }
        .admin-stat-card.gray   { border-top-color:var(--text-muted); }
        .admin-stat-label {
            font-size:0.68rem; font-weight:700; text-transform:uppercase;
            letter-spacing:0.06em; color:var(--text-muted); margin-bottom:0.4rem;
        }
        .admin-stat-value {
            font-size:1.75rem; font-weight:900; color:var(--text-primary);
            letter-spacing:-0.03em; line-height:1;
        }

        /* Table */
        .admin-table { width:100%; border-collapse:collapse; font-size:var(--font-size-sm); }
        .admin-table thead th {
            background:var(--bg); padding:0.65rem 1rem; text-align:left;
            font-size:0.7rem; font-weight:700; text-transform:uppercase;
            letter-spacing:0.06em; color:var(--text-muted);
            border-bottom:1px solid var(--divider); white-space:nowrap;
        }
        .admin-table tbody tr { border-bottom:1px solid var(--divider); }
        .admin-table tbody tr:hover { background:var(--bg); }
        .admin-table tbody td { padding:0.75rem 1rem; color:var(--text-secondary); }

        /* Card */
        .admin-card {
            background:#fff; border-radius:var(--radius-xl);
            border:1px solid var(--divider); margin-bottom:1.25rem;
            overflow:hidden;
        }
        .admin-card-header {
            display:flex; justify-content:space-between; align-items:center;
            padding:0.9rem 1.25rem; border-bottom:1px solid var(--divider);
            background:#fff;
        }
        .admin-card-title {
            font-size:0.85rem; font-weight:800; color:var(--text-primary);
        }
        .admin-card-body { padding:1.25rem; }

        /* Filter bar */
        .admin-filter-bar {
            display:flex; gap:0.75rem; flex-wrap:wrap;
            margin-bottom:1.25rem; align-items:center;
        }
        .admin-filter-bar input,
        .admin-filter-bar select {
            padding:0.5rem 0.75rem; border:1px solid var(--divider);
            border-radius:var(--radius-md); font-size:0.8rem;
            color:var(--text-primary); background:#fff;
            min-width:160px;
        }
        .admin-filter-bar input:focus,
        .admin-filter-bar select:focus {
            outline:none; border-color:var(--primary);
            box-shadow:0 0 0 2px rgba(11,61,145,0.1);
        }

        /* Action buttons */
        .admin-action-row {
            display:flex; gap:0.5rem; flex-wrap:wrap;
        }

        /* Toast */
        .toast-container { z-index:9999; }

        /* Responsive */
        @media (max-width:768px) {
            .admin-sidebar { transform:translateX(-100%); transition:transform 0.25s; }
            .admin-sidebar.open { transform:translateX(0); }
            .admin-main { margin-left:0; }
            .admin-content { padding:1rem; }
            .admin-stats-grid { grid-template-columns:1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="admin-header">
    <div class="admin-header-brand">
        <span class="cl">CampusLink</span>
        <span class="admin-tag">ADMIN</span>
    </div>

    <div class="admin-header-right">
        <div class="admin-header-user">
            <div class="admin-avatar">
                <?= strtoupper(substr(AdminAuth::name(), 0, 2)) ?>
            </div>
            <span><?= e(AdminAuth::name()) ?></span>
            <span style="color:var(--divider);">·</span>
            <span style="color:var(--text-muted);"><?= ucfirst(AdminAuth::role()) ?></span>
        </div>
        <a href="<?= SITE_URL ?>/admin/logout"
           style="font-size:0.75rem;color:var(--danger);font-weight:600;text-decoration:none;">
            Logout
        </a>
    </div>
</header>

<!-- Sidebar -->
<?php
$cur = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function adminActive(string $path): string {
    global $cur;
    return str_contains($cur, $path) ? 'active' : '';
}
$pendingVendorCount  = DB::getInstance()->value("SELECT COUNT(*) FROM vendors WHERE status='pending'");
$openComplaintCount  = DB::getInstance()->value("SELECT COUNT(*) FROM complaints WHERE status IN('submitted','under_review')");
$pendingReviewCount  = DB::getInstance()->value("SELECT COUNT(*) FROM reviews WHERE status='pending'");
?>
<nav class="admin-sidebar" id="adminSidebar">

    <div class="admin-nav-section">
        <div class="admin-nav-label">Overview</div>
        <a href="<?= SITE_URL ?>/admin/dashboard"
           class="admin-nav-link <?= adminActive('dashboard') ?>">
            <span class="admin-nav-icon">🏠</span> Dashboard
        </a>
    </div>

    <div class="admin-nav-section">
        <div class="admin-nav-label">Vendors</div>
        <a href="<?= SITE_URL ?>/admin/vendors/pending"
           class="admin-nav-link <?= adminActive('vendors/pending') ?>">
            <span class="admin-nav-icon">⏳</span> Pending Approvals
            <?php if ($pendingVendorCount > 0): ?>
            <span class="admin-nav-badge"><?= $pendingVendorCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/vendors"
           class="admin-nav-link <?= str_contains($cur,'vendors') && !str_contains($cur,'pending') ? 'active' : '' ?>">
            <span class="admin-nav-icon">🏪</span> All Vendors
        </a>
    </div>

    <div class="admin-nav-section">
        <div class="admin-nav-label">Users</div>
        <a href="<?= SITE_URL ?>/admin/users"
           class="admin-nav-link <?= adminActive('/users') ?>">
            <span class="admin-nav-icon">🎓</span> Students
        </a>
    </div>

    <div class="admin-nav-section">
        <div class="admin-nav-label">Content</div>
        <a href="<?= SITE_URL ?>/admin/complaints"
           class="admin-nav-link <?= adminActive('complaints') ?>">
            <span class="admin-nav-icon">📋</span> Complaints
            <?php if ($openComplaintCount > 0): ?>
            <span class="admin-nav-badge"><?= $openComplaintCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/reviews"
           class="admin-nav-link <?= adminActive('reviews') ?>">
            <span class="admin-nav-icon">⭐</span> Review Moderation
            <?php if ($pendingReviewCount > 0): ?>
            <span class="admin-nav-badge"><?= $pendingReviewCount ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="admin-nav-section">
        <div class="admin-nav-label">Finance</div>
        <a href="<?= SITE_URL ?>/admin/payments"
           class="admin-nav-link <?= adminActive('payments') ?>">
            <span class="admin-nav-icon">💳</span> Payments
        </a>
    </div>

    <div class="admin-nav-section">
        <div class="admin-nav-label">Tools</div>
        <a href="<?= SITE_URL ?>/admin/notifications"
           class="admin-nav-link <?= adminActive('notifications') ?>">
            <span class="admin-nav-icon">📢</span> Send Notifications
        </a>
        <a href="<?= SITE_URL ?>" target="_blank"
           class="admin-nav-link">
            <span class="admin-nav-icon">🌐</span> View Site
        </a>
    </div>

</nav>

<!-- Main -->
<main class="admin-main">
    <div class="admin-content">
        <?php require VIEWS_PATH . '/partials/flash.php'; ?>
        <?= $content ?? '' ?>
    </div>
</main>

<div class="toast-container" aria-live="polite"></div>
<script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</body>
</html>