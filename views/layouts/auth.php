<?php
/**
 * CampusLink - Auth Layout
 * Used by: login, register, OTP, forgot/reset password
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

$pageTitle = $pageTitle ?? 'Account - ' . SITE_NAME;
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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/auth.css">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= e($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body class="auth-page">

    <!-- Minimal Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="header-logo">
                    <a href="<?= SITE_URL ?>">
                        <span class="logo-text-fallback">
                            <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
                        </span>
                    </a>
                </div>
                <nav class="header-nav" style="gap: 0.5rem;">
                    <a href="<?= SITE_URL ?>/browse" class="nav-link">Browse Vendors</a>
                    <a href="<?= SITE_URL ?>/login" class="nav-link">Login</a>
                    <a href="<?= SITE_URL ?>/register" class="nav-cta-filled nav-link">Register Free</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Toast Container -->
    <div class="toast-container" aria-live="polite"></div>

    <!-- Page Content -->
    <?= $content ?? '' ?>

    <!-- Scripts -->
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/auth.js" defer></script>
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