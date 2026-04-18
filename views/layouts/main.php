<?php
/**
 * CampusLink - Main Public Layout
 * Used by: home, browse, vendor profile, static pages
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

$pageTitle = $pageTitle ?? SITE_NAME;
$pageDesc  = $pageDesc  ?? SITE_DESCRIPTION;
$canonical = $canonical ?? SITE_URL . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');

// Auth state for nav
$isLoggedIn       = Auth::isLoggedIn();
$isVendorLoggedIn = Auth::isVendorLoggedIn();
$userName         = Auth::userName();
$unreadNotifs     = 0;

if ($isLoggedIn) {
    $notifModel   = new NotificationModel();
    $unreadNotifs = $notifModel->countUnread('user', Auth::userId());
} elseif ($isVendorLoggedIn) {
    $notifModel   = new NotificationModel();
    $unreadNotifs = $notifModel->countUnread('vendor', Auth::vendorId());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDesc) ?>">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0b3d91">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= e($canonical) ?>">
    <meta property="og:site_name"   content="<?= e(SITE_NAME) ?>">
    <meta property="og:image"       content="<?= SITE_URL ?>/assets/img/og-image.png">

    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/favicon.png">
    <link rel="apple-touch-icon"     href="<?= SITE_URL ?>/assets/img/apple-touch-icon.png">

    <title><?= e($pageTitle) ?></title>

    <!-- CSRF Meta -->
    <meta name="csrf-token" content="<?= CSRF::token() ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= e($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">

    <!-- Header -->
    <?php require __DIR__ . '/../partials/header.php'; ?>

    <!-- Flash Messages -->
    <div class="page-content">
        <div class="container" style="padding-top: 1rem;">
            <?php require __DIR__ . '/../partials/flash.php'; ?>
        </div>

        <!-- Page Content -->
        <?= $content ?? '' ?>
    </div>

    <!-- Footer -->
    <?php require __DIR__ . '/../partials/footer.php'; ?>

    <!-- Toast Container -->
    <div class="toast-container" aria-live="polite"></div>

    <!-- Scripts -->
    <script>window.CAMPUSLINK_ROOT = '<?= rtrim(SITE_URL, '/') ?>';</script>
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
    <?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
    <script src="<?= SITE_URL ?>/assets/js/<?= e($js) ?>" defer></script>
    <?php endforeach; endif; ?>

    <?php if (!empty($inlineJs)): ?>
    <script><?= $inlineJs ?></script>
    <?php endif; ?>

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