<?php defined('CAMPUSLINK') or die(); ?>

<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">

            <!-- Logo -->
            <div class="header-logo">
                <a href="<?= SITE_URL ?>" aria-label="<?= SITE_NAME ?> Home">
                    <?php if (file_exists(PUBLIC_PATH . '/assets/img/logo.png')): ?>
                        <img src="<?= SITE_URL ?>/assets/img/logo.png"
                             alt="<?= SITE_NAME ?>" height="40">
                    <?php else: ?>
                        <span class="logo-text-fallback">
                            <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="header-nav" role="navigation" aria-label="Main navigation">
                <a href="<?= SITE_URL ?>/browse"     class="nav-link">Browse Vendors</a>
                <a href="<?= SITE_URL ?>/categories"  class="nav-link">Categories</a>
                <a href="<?= SITE_URL ?>/how-it-works" class="nav-link">How It Works</a>

                <?php if ($isLoggedIn ?? false): ?>
                    <a href="<?= SITE_URL ?>/user/dashboard"    class="nav-link">My Account</a>
                    <a href="<?= SITE_URL ?>/logout"            class="nav-link">Logout</a>
                <?php elseif ($isVendorLoggedIn ?? false): ?>
                    <a href="<?= SITE_URL ?>/vendor/dashboard"  class="nav-link">Vendor Panel</a>
                    <a href="<?= SITE_URL ?>/logout"            class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login"             class="nav-link">Login</a>
                    <a href="<?= SITE_URL ?>/vendor/register"   class="nav-cta-outline nav-link">List Your Business</a>
                    <a href="<?= SITE_URL ?>/register"          class="nav-cta-filled nav-link">Join Free</a>
                <?php endif; ?>
            </nav>

            <!-- School Logo -->
            <?php if (defined('SCHOOL_LOGO') && SCHOOL_LOGO): ?>
            <div class="header-school-logo">
                <img src="<?= SITE_URL ?>/assets/img/<?= e(SCHOOL_LOGO) ?>"
                     alt="<?= e(SCHOOL_NAME) ?>" title="<?= e(SCHOOL_NAME) ?>">
            </div>
            <?php endif; ?>

            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" aria-label="Open navigation menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav-overlay" role="presentation"></div>

<!-- Mobile Nav Drawer -->
<nav class="mobile-nav" aria-label="Mobile navigation">
    <button class="mobile-nav-close" aria-label="Close menu">&times;</button>

    <div class="mobile-nav-logo">
        <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
    </div>

    <a href="<?= SITE_URL ?>"              class="mobile-nav-link"><i data-lucide="home" class="nav-icon" aria-hidden="true"></i> Home</a>
    <a href="<?= SITE_URL ?>/browse"       class="mobile-nav-link"><i data-lucide="search" class="nav-icon" aria-hidden="true"></i> Browse Vendors</a>
    <a href="<?= SITE_URL ?>/categories"   class="mobile-nav-link"><i data-lucide="folder" class="nav-icon" aria-hidden="true"></i> Categories</a>
    <a href="<?= SITE_URL ?>/how-it-works" class="mobile-nav-link"><i data-lucide="help-circle" class="nav-icon" aria-hidden="true"></i> How It Works</a>
    <a href="<?= SITE_URL ?>/about"        class="mobile-nav-link"><i data-lucide="info" class="nav-icon" aria-hidden="true"></i> About</a>
    <a href="<?= SITE_URL ?>/contact"      class="mobile-nav-link"><i data-lucide="mail" class="nav-icon" aria-hidden="true"></i> Contact</a>

    <div style="border-top:1px solid var(--divider);padding-top:1rem;margin-top:1rem;">
        <?php if ($isLoggedIn ?? false): ?>
            <a href="<?= SITE_URL ?>/user/dashboard" class="mobile-nav-link">ðŸ‘¤ My Account</a>
            <a href="<?= SITE_URL ?>/logout"         class="mobile-nav-link" style="color:var(--danger);">ðŸšª Logout</a>
        <?php elseif ($isVendorLoggedIn ?? false): ?>
            <a href="<?= SITE_URL ?>/vendor/dashboard" class="mobile-nav-link">ðŸª Vendor Panel</a>
            <a href="<?= SITE_URL ?>/logout"           class="mobile-nav-link" style="color:var(--danger);">ðŸšª Logout</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login"          class="mobile-nav-link">ðŸ”‘ Login</a>
            <a href="<?= SITE_URL ?>/register"       class="mobile-nav-link mobile-nav-cta">âœ¨ Join Free</a>
            <a href="<?= SITE_URL ?>/vendor/register" class="mobile-nav-link" style="color:var(--accent-green);font-weight:700;">ðŸª List Your Business</a>
        <?php endif; ?>
    </div>
</nav>
