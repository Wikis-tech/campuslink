<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';

$token = Security::clean($_GET['token'] ?? '');
$csrf  = Security::generateCSRF();
$err   = '';
$msg   = '';
$user  = null;

if (!$token) {
    header('Location: /forgot-password?msg=invalid');
    exit;
}

try {
    $pdo  = Database::getInstance();
    $stmt = $pdo->prepare(
        'SELECT id, first_name, personal_email FROM users
         WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();
} catch (\Throwable $e) {
    error_log('[CL ResetPW] ' . $e->getMessage());
    $err = 'A server error occurred.';
}

if (!$user && !$err) {
    header('Location: /forgot-password?msg=expired');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    Security::requireCSRF();
    $pw1 = $_POST['password']  ?? '';
    $pw2 = $_POST['confirm_password'] ?? '';

    if (strlen($pw1) < 8) {
        $err = 'Password must be at least 8 characters.';
    } elseif ($pw1 !== $pw2) {
        $err = 'Passwords do not match.';
    } else {
        try {
            $hash = Security::hashPassword($pw1);
            $pdo->prepare(
                'UPDATE users SET password_hash=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?'
            )->execute([$hash, $user['id']]);
            $msg = 'Your password has been reset. You can now log in.';
        } catch (\Throwable $e) {
            error_log('[CL ResetPW] ' . $e->getMessage());
            $err = 'A server error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password — Campuslink</title>
  <link rel="stylesheet" href="../assets/css/main.css" />
  <link rel="stylesheet" href="../assets/css/hero.css" />
  <link rel="stylesheet" href="../assets/css/auth-premium.css" />
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
        <h2 class="auth-visual-headline">Create a<br/><span class="highlight">new password.</span></h2>
        <p class="auth-visual-desc">Choose a strong password that you haven't used before.</p>
        <div class="auth-feat-list">
          <div class="auth-feat-row"><div class="afr-icon"><i data-lucide="lock"></i></div><div class="afr-text"><strong>Minimum 8 characters</strong><span>Use a mix of letters, numbers, symbols</span></div></div>
          <div class="auth-feat-row"><div class="afr-icon"><i data-lucide="shield-check"></i></div><div class="afr-text"><strong>BCrypt Encrypted</strong><span>Your password is never stored in plain text</span></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-form-panel">
    <div class="auth-form-container">
      <div class="auth-form-top">
        <a href="/login" class="back-to-home"><i data-lucide="arrow-left"></i> Back to login</a>
        <h1 class="auth-title">Create new<br/>password</h1>
        <?php if ($user): ?>
        <p class="auth-subtitle">For account: <strong><?= e($user['personal_email']) ?></strong></p>
        <?php endif; ?>
      </div>

      <?php if ($msg): ?>
      <div class="auth-success-alert show" style="margin-bottom:20px">
        <i data-lucide="check-circle"></i>
        <span><?= e($msg) ?></span>
      </div>
      <a href="/login" class="auth-submit-btn" style="text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px">
        <i data-lucide="log-in"></i> Go to Login
      </a>
      <?php elseif ($err && !$user): ?>
      <div class="auth-error-alert show" style="margin-bottom:20px">
        <i data-lucide="alert-circle"></i>
        <span>This reset link is invalid or has expired.</span>
      </div>
      <a href="/forgot-password" class="auth-submit-btn" style="text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px">
        Request New Link
      </a>
      <?php else: ?>
      <?php if ($err): ?>
      <div class="auth-error-alert show" style="margin-bottom:20px">
        <i data-lucide="alert-circle"></i><span><?= e($err) ?></span>
      </div>
      <?php endif; ?>
      <form method="POST" action="" novalidate>
        <?= csrfField() ?>
        <div class="prem-input-group">
          <label class="prem-label">New Password <span class="req">*</span></label>
          <div class="prem-input-wrap" id="p1wrap">
            <div class="prem-input-icon"><i data-lucide="lock"></i></div>
            <input type="password" name="password" id="newPw" class="prem-input" placeholder="Minimum 8 characters" required minlength="8" />
            <div class="prem-input-suffix">
              <button type="button" class="prem-eye-btn" onclick="toggleEye('newPw',this)"><i data-lucide="eye"></i></button>
            </div>
          </div>
          <div class="pw-strength-wrap">
            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwFill"></div></div>
            <span class="pw-strength-label" id="pwLabel"></span>
          </div>
        </div>
        <div class="prem-input-group">
          <label class="prem-label">Confirm Password <span class="req">*</span></label>
          <div class="prem-input-wrap">
            <div class="prem-input-icon"><i data-lucide="lock"></i></div>
            <input type="password" name="confirm_password" id="newPw2" class="prem-input" placeholder="Repeat new password" required />
            <div class="prem-input-suffix">
              <button type="button" class="prem-eye-btn" onclick="toggleEye('newPw2',this)"><i data-lucide="eye"></i></button>
            </div>
          </div>
          <div class="prem-error-msg" id="matchErr" style="display:none"><i data-lucide="alert-circle"></i><span>Passwords do not match.</span></div>
        </div>
        <button type="submit" class="auth-submit-btn"><i data-lucide="save"></i> Save New Password</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="assets/js/auth-canvas.js"></script>
<script>
function toggleEye(id, btn) {
  const inp = document.getElementById(id);
  const isP = inp.type === 'password';
  inp.type = isP ? 'text' : 'password';
  btn.innerHTML = isP ? '<i data-lucide="eye-off"></i>' : '<i data-lucide="eye"></i>';
  lucide.createIcons({ nodes: [btn] });
}
document.getElementById('newPw')?.addEventListener('input', function() {
  const v = this.value, fill = document.getElementById('pwFill'), lbl = document.getElementById('pwLabel');
  let s = 0;
  if (v.length >= 8) s++;
  if (/[A-Z]/.test(v)) s++;
  if (/[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  const w=['0%','25%','50%','75%','100%'], l=['','Weak','Fair','Good','Strong'], c=['','#ef4444','#f59e0b','#3b82f6','#1ea952'];
  fill.style.width = w[s]; fill.style.background = c[s];
  lbl.textContent = l[s]; lbl.style.color = c[s];
});
document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
</body>
</html>