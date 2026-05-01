<?php defined('CAMPUSLINK') or die(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — CampusLink</title>
    <link rel="icon" type="image/webp" href="<?= SITE_URL ?>/assets/images/logo.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.568.0/dist/lucide.min.js"></script>
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', sans-serif;
        background: #f1f5f9;
        color: #1e293b;
        min-height: 100vh;
    }

    /* Lucide icon baseline */
    .nav-icon svg, .ud-logout-btn svg, .ud-menu-btn svg,
    .ud-notif-btn svg, .ud-browse-btn svg {
        width: 16px; height: 16px;
        stroke: currentColor; stroke-width: 2;
        fill: none; stroke-linecap: round; stroke-linejoin: round;
        vertical-align: middle; flex-shrink: 0;
    }
    .nav-icon { width: 20px; display: flex; align-items: center; justify-content: center; }

    /* ── Sidebar ─────────────────────────────────────── */
    .ud-sidebar {
        position: fixed;
        top: 0; left: 0;
        width: 240px;
        height: 100vh;
        background: linear-gradient(180deg, #0b3d91 0%, #1a56db 100%);
        display: flex;
        flex-direction: column;
        z-index: 100;
        transition: transform 0.3s ease;
        overflow-y: auto;
    }
    .ud-sidebar-logo {
        padding: 1.5rem 1.25rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .ud-sidebar-logo a {
        text-decoration: none;
        font-size: 1.3rem;
        font-weight: 900;
        color: #fff;
        letter-spacing: -0.02em;
    }
    .ud-sidebar-logo a span { color: #34d399; }
    .ud-sidebar-logo .ud-user-info {
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .ud-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: #fff;
        flex-shrink: 0;
    }
    .ud-user-name {
        font-size: 0.8rem;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ud-user-role {
        font-size: 0.68rem;
        color: rgba(255,255,255,0.6);
    }
    .ud-nav {
        padding: 0.75rem 0;
        flex: 1;
    }
    .ud-nav-section {
        padding: 0.5rem 1rem 0.25rem;
        font-size: 0.62rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255,255,255,0.4);
    }
    .ud-nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 1.25rem;
        color: rgba(255,255,255,0.75);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.15s;
        position: relative;
    }
    .ud-nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    .ud-nav-link.active {
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-weight: 700;
    }
    .ud-nav-link.active::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: #34d399;
        border-radius: 0 2px 2px 0;
    }
    .ud-nav-badge {
        margin-left: auto;
        background: #dc2626;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 1px 6px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }
    .ud-sidebar-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    .ud-logout-btn {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: rgba(255,255,255,0.6);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 600;
        transition: color 0.15s;
        padding: 0.5rem 0;
    }
    .ud-logout-btn:hover { color: #f87171; }

    /* ── Main content ────────────────────────────────── */
    .ud-main {
        margin-left: 240px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* ── Top bar ─────────────────────────────────────── */
    .ud-topbar {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 50;
    }
    .ud-topbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .ud-menu-btn {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        color: #64748b;
        padding: 0.25rem;
        align-items: center;
        justify-content: center;
    }
    .ud-page-title {
        font-size: 1rem;
        font-weight: 800;
        color: #1e293b;
    }
    .ud-topbar-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .ud-browse-btn {
        padding: 0.45rem 1rem;
        background: linear-gradient(135deg, #1a56db, #0e9f6e);
        color: #fff;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        transition: opacity 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .ud-browse-btn:hover { opacity: 0.88; }
    .ud-notif-btn {
        position: relative;
        background: #f1f5f9;
        border: none;
        border-radius: 8px;
        width: 36px; height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        color: #64748b;
        transition: background 0.15s;
    }
    .ud-notif-btn:hover { background: #e2e8f0; }
    .ud-notif-dot {
        position: absolute;
        top: 4px; right: 4px;
        width: 8px; height: 8px;
        background: #dc2626;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    /* ── Content area ────────────────────────────────── */
    .ud-content {
        padding: 1.5rem;
        flex: 1;
    }

    /* ── Flash messages ──────────────────────────────── */
    .ud-flash {
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-size: 0.83rem;
        font-weight: 600;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ud-flash svg { width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0; }
    .ud-flash-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .ud-flash-error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
    .ud-flash-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

    /* ── Overlay for mobile ──────────────────────────── */
    .ud-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 99;
    }

    /* ── Mobile ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .ud-sidebar { transform: translateX(-100%); }
        .ud-sidebar.open { transform: translateX(0); }
        .ud-main { margin-left: 0; }
        .ud-menu-btn { display: flex; }
        .ud-overlay.open { display: block; }
        .ud-content { padding: 1rem; }
    }
    </style>
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= e($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body>

<!-- Sidebar overlay for mobile -->
<div class="ud-overlay" id="udOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="ud-sidebar" id="udSidebar">
    <div class="ud-sidebar-logo">
        <a href="<?= SITE_URL ?>">Campus<span>Link</span></a>
        <div class="ud-user-info">
            <div class="ud-avatar">
                <?= strtoupper(substr(Session::get('user_name', 'U'), 0, 1)) ?>
            </div>
            <div>
                <div class="ud-user-name"><?= e(Session::get('user_name', 'Student')) ?></div>
                <div class="ud-user-role">Student Account</div>
            </div>
        </div>
    </div>

    <nav class="ud-nav">
        <div class="ud-nav-section">Main</div>
        <a href="<?= SITE_URL ?>/user/dashboard"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/user/dashboard') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/browse"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/browse') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="search"></i></span> Browse Vendors
        </a>

        <div class="ud-nav-section">My Activity</div>
        <a href="<?= SITE_URL ?>/user/saved-vendors"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'saved-vendors') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="heart"></i></span> Saved Vendors
        </a>
        <a href="<?= SITE_URL ?>/user/my-reviews"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'my-reviews') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="star"></i></span> My Reviews
        </a>
        <a href="<?= SITE_URL ?>/user/my-complaints"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'my-complaints') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="alert-circle"></i></span> My Complaints
        </a>
        <a href="<?= SITE_URL ?>/user/notifications"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'notifications') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="bell"></i></span> Notifications
            <?php
            $unreadCount = 0;
            if (Auth::isLoggedIn()) {
                $db = DB::getInstance();
                $unreadCount = (int)$db->value(
                    "SELECT COUNT(*) FROM notifications
                     WHERE recipient_type = 'user'
                     AND recipient_id = ?
                     AND is_read = 0",
                    [(int)Session::get('user_id')]
                );
            }
            if ($unreadCount > 0): ?>
            <span class="ud-nav-badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>

        <div class="ud-nav-section">Account</div>
        <a href="<?= SITE_URL ?>/user/profile"
           class="ud-nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'profile') ? 'active' : '' ?>">
            <span class="nav-icon"><i data-lucide="user"></i></span> My Profile
        </a>
    </nav>

    <div class="ud-sidebar-footer">
        <a href="<?= SITE_URL ?>/logout" class="ud-logout-btn">
            <i data-lucide="log-out"></i> Sign Out
        </a>
    </div>
</aside>

<!-- Main -->
<div class="ud-main">

    <!-- Top bar -->
    <header class="ud-topbar">
        <div class="ud-topbar-left">
            <button class="ud-menu-btn" onclick="openSidebar()">
                <i data-lucide="menu"></i>
            </button>
            <div class="ud-page-title"><?= e($pageTitle ?? 'Dashboard') ?></div>
        </div>
        <div class="ud-topbar-right">
            <a href="<?= SITE_URL ?>/browse" class="ud-browse-btn">
                <i data-lucide="search"></i> Browse
            </a>
            <a href="<?= SITE_URL ?>/user/notifications" class="ud-notif-btn" aria-label="Notifications">
                <i data-lucide="bell"></i>
                <?php if ($unreadCount > 0): ?>
                <span class="ud-notif-dot"></span>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <!-- Flash messages -->
    <?php
    $flashes = Session::getAllFlash();
    foreach ($flashes as $type => $messages):
        if (!is_array($messages)) $messages = [$messages];
        foreach ($messages as $message):
            if (empty($message)) continue;
            $cls = match($type) {
                'success' => 'ud-flash-success',
                'error'   => 'ud-flash-error',
                default   => 'ud-flash-warning',
            };
            $iconName = match($type) {
                'success' => 'check-circle',
                'error'   => 'alert-triangle',
                default   => 'info',
            };
    ?>
    <div class="ud-flash <?= $cls ?>" style="margin:0.75rem 1.5rem 0;">
        <i data-lucide="<?= $iconName ?>"></i>
        <?= e($message) ?>
    </div>
    <?php endforeach; endforeach; ?>

    <!-- Page content -->
    <main class="ud-content">
        <?= $content ?>
    </main>

</div>

<script>
function openSidebar() {
    document.getElementById('udSidebar').classList.add('open');
    document.getElementById('udOverlay').classList.add('open');
}
function closeSidebar() {
    document.getElementById('udSidebar').classList.remove('open');
    document.getElementById('udOverlay').classList.remove('open');
}
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
});
</script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= SITE_URL ?>/assets/js/<?= e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>