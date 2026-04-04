<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
$msg  = '';
$err  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireCSRF();
    $email = strtolower(trim(Security::clean($_POST['email'] ?? '')));

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Please enter a valid email address.';
    } elseif (!Security::checkRateLimit('forgot_' . md5($email), 3, 60)) {
        $err = 'Too many requests. Please wait before trying again.';
    } else {
        try {
            $pdo  = Database::getInstance();
            $stmt = $pdo->prepare(
                'SELECT id, first_name, personal_email FROM users WHERE personal_email = ? OR school_email = ? LIMIT 1'
            );
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();

            if ($user) {
                $token     = Security::generateToken(32);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $pdo->prepare(
                    'UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?'
                )->execute([$token, $expiresAt, $user['id']]);

                Mailer::sendPasswordReset(
                    $user['personal_email'],
                    $user['first_name'],
                    $token
                );
            }
            // Always show success to prevent email enumeration
            $msg = 'If an account exists with that email, a reset link has been sent to your personal email.';
        } catch (\Throwable $e) {
            error_log('[CL ForgotPW] ' . $e->getMessage());
            $err = 'A server error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/hero.css" />
  <link rel="stylesheet" href="assets/css/auth-premium.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<div class="auth-page">
  <div class="auth-visual">
    <canvas id="authCanvas"></canvas>
    <div class="auth-visual-content">
      <a href="/" class="auth-visual-logo">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
          <rect width="36" height="36" rx="10" fill="rgba(255,255,255,0.12)"/>
          <path d="M9 18C9 12.477 13.477 8 19 8s10 4.477 10 10" stroke="white" stroke-width="3" stroke-linecap="round"/>
          <circle cx="18" cy="22" r="3.5" fill="#1ea952"/>
          <path d="M14 28h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
        <span class="logo-text">Campus<strong>link</strong></span>
      </a>
      <div class="auth-visual-main">
        <h2 class="auth-visual-headline">Forgot your<br/><span class="highlight">password?</span></h2>
        <p class="auth-visual-desc">No worries. Enter your registered email and we'll send a reset link to your personal email address.</p>
        <div class="auth-feat-list">
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="mail"></i></div>
            <div class="afr-text"><strong>Reset via Personal Email</strong><span>Link sent within seconds</span></div>
          </div>
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="clock"></i></div>
            <div class="afr-text"><strong>Link Expires in 1 Hour</strong><span>Request a new one if needed</span></div>
          </div>
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="shield-check"></i></div>
            <div class="afr-text"><strong>Secure Process</strong><span>Encrypted, rate-limited reset</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-form-panel">
    <div class="auth-form-container">
      <div class="auth-form-top">
        <a href="/login" class="back-to-home"><i data-lucide="arrow-left"></i> Back to login</a>
        <h1 class="auth-title">Reset your<br/>password</h1>
        <p class="auth-subtitle">Enter your school or personal email address and we'll send you a link.</p>
      </div>

      <?php if ($msg): ?>
      <div class="auth-success-alert show" style="margin-bottom:20px">
        <i data-lucide="check-circle"></i>
        <span><?= e($msg) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($err): ?>
      <div class="auth-error-alert show" style="margin-bottom:20px">
        <i data-lucide="alert-circle"></i>
        <span><?= e($err) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!$msg): ?>
      <form method="POST" action="" novalidate>
        <?= csrfField() ?>
        <div class="prem-input-group">
          <label class="prem-label">Email Address <span class="req">*</span></label>
          <div class="prem-input-wrap">
            <div class="prem-input-icon"><i data-lucide="mail"></i></div>
            <input type="email" name="email" class="prem-input"
                   placeholder="your@email.com" required
                   value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" />
          </div>
          <p class="prem-hint">Enter your school email or personal email address.</p>
        </div>
        <button type="submit" class="auth-submit-btn">
          <i data-lucide="send"></i> Send Reset Link
        </button>
      </form>
      <?php endif; ?>

      <div style="text-align:center;margin-top:20px;font-size:0.85rem;color:var(--text-muted)">
        Remembered it? <a href="/login" style="color:var(--primary);font-weight:700;text-decoration:none">Back to Sign In</a>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/auth-canvas.js"></script>
<script>document.addEventListener('DOMContentLoaded', () => lucide.createIcons());</script>
</body>
</html>