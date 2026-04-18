<?php defined('CAMPUSLINK') or die(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Admin Login — <?= e(SITE_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/auth.css">
</head>
<body class="auth-page" style="background:#0f172a;">

<?php
// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::verify($_POST['csrf_token'] ?? '');

    $ip       = $_SERVER['REMOTE_ADDR'];
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!AdminAuth::checkLoginRateLimit($ip)) {
        Session::setFlash('error', 'Too many failed attempts. Try again later.');
    } else {
        $db    = DB::getInstance();
        $admin = $db->row(
            "SELECT * FROM admins WHERE email=? AND status='active' LIMIT 1",
            [$email]
        );

        if ($admin && password_verify($password, $admin['password_hash'])) {
            AdminAuth::clearLoginAttempts($ip);
            AdminAuth::login($admin);
            header('Location: ' . SITE_URL . '/admin/dashboard');
            exit;
        }

        AdminAuth::recordFailedLogin($ip);
        Session::setFlash('error', 'Invalid email or password.');
    }
}
?>

<div class="auth-container">
    <div class="auth-card" style="max-width:420px;">

        <div class="auth-card-header" style="background:linear-gradient(135deg,#0f172a,#1e293b);">
            <div style="font-size:2rem;margin-bottom:0.5rem;">🔐</div>
            <div class="auth-title">Admin Panel</div>
            <div class="auth-subtitle"><?= e(SITE_NAME) ?> Administration</div>
        </div>

        <div class="auth-card-body">
            <?php require VIEWS_PATH . '/partials/flash.php'; ?>

            <form action="<?= SITE_URL ?>/admin/login" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <div class="form-group">
                    <label class="form-label" for="email">
                        Admin Email <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           class="form-control"
                           placeholder="admin@campuslink.com"
                           required autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        Password <span class="required">*</span>
                    </label>
                    <div class="password-field">
                        <input type="password" id="password" name="password"
                               class="form-control"
                               placeholder="Admin password"
                               required autocomplete="current-password">
                        <button type="button" class="password-toggle"
                                aria-label="Show password">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full"
                        style="background:#1e40af;margin-top:0.5rem;">
                    🔐 Sign In to Admin Panel
                </button>
            </form>
        </div>

        <div style="padding:1rem;text-align:center;border-top:1px solid var(--divider);">
            <a href="<?= SITE_URL ?>"
               style="font-size:var(--font-size-xs);color:var(--text-muted);">
                ← Back to <?= e(SITE_NAME) ?>
            </a>
        </div>

    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
<script src="<?= SITE_URL ?>/assets/js/auth.js" defer></script>
</body>
</html>