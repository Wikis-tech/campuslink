<?php defined('CAMPUSLINK') or die(); ?>

<style>
/* User Sidebar Styles for Vendor Profile */
.ud-sidebar {
    position: fixed;
    top: 0; left: 0;
    width: 240px;
    height: 100vh;
    background: linear-gradient(180deg, #0b3d91 0%, #1a56db 100%);
    display: flex;
    flex-direction: column;
    z-index: 100;
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

@media (max-width: 768px) {
    .ud-sidebar {
        transform: translateX(-100%);
        visibility: hidden;
    }
}

/* Lucide icon baseline */
.nav-icon svg, .ud-logout-btn svg {
    width: 16px; height: 16px;
    stroke: currentColor; stroke-width: 2;
    fill: none; stroke-linecap: round; stroke-linejoin: round;
    vertical-align: middle; flex-shrink: 0;
}
.nav-icon { width: 20px; display: flex; align-items: center; justify-content: center; }
</style>

<!-- Sidebar -->
<aside class="ud-sidebar">
    <div class="ud-sidebar-logo">
        <a href="<?= SITE_URL ?>/user/dashboard">Campus<span>Link</span></a>
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
        <a href="<?= SITE_URL ?>/user/dashboard" class="ud-nav-link">
            <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/browse" class="ud-nav-link active">
            <span class="nav-icon"><i data-lucide="search"></i></span> Browse Vendors
        </a>

        <div class="ud-nav-section">My Activity</div>
        <a href="<?= SITE_URL ?>/user/saved-vendors" class="ud-nav-link">
            <span class="nav-icon"><i data-lucide="heart"></i></span> Saved Vendors
        </a>
        <a href="<?= SITE_URL ?>/user/my-reviews" class="ud-nav-link">
            <span class="nav-icon"><i data-lucide="star"></i></span> My Reviews
        </a>
        <a href="<?= SITE_URL ?>/user/my-complaints" class="ud-nav-link">
            <span class="nav-icon"><i data-lucide="alert-circle"></i></span> My Complaints
        </a>
        <a href="<?= SITE_URL ?>/user/notifications" class="ud-nav-link">
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
        <a href="<?= SITE_URL ?>/user/profile" class="ud-nav-link">
            <span class="nav-icon"><i data-lucide="user"></i></span> My Profile
        </a>
    </nav>

    <div class="ud-sidebar-footer">
        <a href="<?= SITE_URL ?>/logout" class="ud-logout-btn">
            <i data-lucide="log-out"></i> Sign Out
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>