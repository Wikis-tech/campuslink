<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

$config = APP_CONFIG;
$csrf   = Security::generateCSRF();
$error  = '';

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireCSRF();

    $email    = strtolower(trim(Security::clean($_POST['email'] ?? '')));
    $password = $_POST['password'] ?? '';

    // Rate limit
    if (!Security::checkRateLimit('admin_login_' . md5($_SERVER['REMOTE_ADDR'] ?? ''), 5, 15)) {
        $error = 'Too many attempts. Please wait 15 minutes.';
    } elseif ($email === $config['admin']['email']) {
        // Verify password against stored BCrypt hash
        if (Security::verifyPassword($password, $config['admin']['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = 1;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_role'] = 'super_admin';
            $_SESSION['admin_name'] = 'Super Admin';
            $_SESSION['role']       = 'admin';

            // Log login
            try {
                $pdo = Database::getInstance();
                $pdo->prepare('INSERT INTO login_logs (entity_type, entity_id, email, ip_address, user_agent, status) VALUES (?,?,?,?,?,?)')
                    ->execute(['admin', 1, $email, $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), 'success']);
            } catch (Throwable $e) { error_log('[CL Admin Login Log] ' . $e->getMessage()); }

            header('Location: /admin/dashboard');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

// Redirect if already logged in
if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — Campuslink</title>
  <link rel="stylesheet" href="../assets/css/main.css" />
  <link rel="stylesheet" href="../assets/css/hero.css" />
  <link rel="stylesheet" href="../assets/css/auth-premium.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
  <style>
    .admin-login-page { min-height:100svh; display:grid; place-items:center; background:linear-gradient(135deg,#04122e 0%,#0b3d91 100%); position:relative; overflow:hidden; padding:20px; }
    #adminBgCanvas { position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:0; }
    .admin-login-card { position:relative;z-index:2; width:100%;max-width:440px; background:rgba(255,255,255,0.97); backdrop-filter:blur(20px); border-radius:24px; padding:48px 40px; box-shadow:0 32px 80px rgba(0,0,0,0.4); }
    .alc-header { text-align:center;margin-bottom:36px; }
    .alc-logo { display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;margin-bottom:20px; }
    .alc-logo .logo-text { color:var(--primary);font-family:var(--font-head);font-size:1.3rem;font-weight:400; }
    .alc-logo .logo-text strong { font-weight:900; }
    .alc-badge { display:inline-flex;align-items:center;gap:6px; background:rgba(11,61,145,0.08); color:var(--primary); border:1px solid rgba(11,61,145,0.15); border-radius:999px; padding:6px 14px; font-family:var(--font-head);font-size:0.72rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em; margin-bottom:16px; }
    .alc-badge svg { width:13px;height:13px; }
    .alc-title { font-family:var(--font-head);font-size:1.6rem;font-weight:900;color:var(--text);letter-spacing:-0.02em; }
    .alc-sub { font-size:0.85rem;color:var(--text-muted);margin-top:8px; }
    .admin-security-note { display:flex;align-items:center;gap:10px; background:rgba(245,158,11,0.07); border:1px solid rgba(245,158,11,0.2); border-radius:12px;padding:12px 14px;margin-bottom:24px;font-size:0.78rem;color:var(--text-secondary);line-height:1.5; }
    .admin-security-note svg { width:15px;height:15px;color:var(--amber);flex-shrink:0; }
  </style>
</head>
<body>
<div class="admin-login-page">
  <canvas id="adminBgCanvas"></canvas>

  <div class="admin-login-card">
    <div class="alc-header">
      <a href="/" class="alc-logo">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><rect width="36" height="36" rx="10" fill="#0b3d91"/><path d="M9 18C9 12.477 13.477 8 19 8s10 4.477 10 10" stroke="white" stroke-width="3" stroke-linecap="round"/><circle cx="18" cy="22" r="3.5" fill="#1ea952"/><path d="M14 28h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>
        <span class="logo-text">Campus<strong>link</strong></span>
      </a>
      <div class="alc-badge"><i data-lucide="shield"></i> Admin Access Only</div>
      <h1 class="alc-title">Admin Panel</h1>
      <p class="alc-sub">Authorised personnel only. All access attempts are logged.</p>
    </div>

    <?php if ($error): ?>
    <div class="auth-error-alert show" style="margin-bottom:20px">
      <i data-lucide="alert-circle"></i>
      <span><?= Security::escape($error) ?></span>
    </div>
    <?php endif; ?>

    <div class="admin-security-note">
      <i data-lucide="info"></i>
      <span>This panel is restricted. Unauthorised access is a violation of Campuslink's Terms and may result in legal action.</span>
    </div>

    <form method="POST" action="" novalidate>
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="prem-input-group">
        <label class="prem-label">Admin Email <span class="req">*</span></label>
        <div class="prem-input-wrap">
          <div class="prem-input-icon"><i data-lucide="mail"></i></div>
          <input type="email" name="email" class="prem-input" placeholder="admin@campuslink.ng" required autocomplete="username" value="<?= isset($_POST['email']) ? Security::escape($_POST['email']) : '' ?>" />
        </div>
      </div>

      <div class="prem-input-group">
        <label class="prem-label">Password <span class="req">*</span></label>
        <div class="prem-input-wrap" id="adminPwWrap">
          <div class="prem-input-icon"><i data-lucide="lock"></i></div>
          <input type="password" name="password" class="prem-input" id="adminPw" placeholder="Admin password" required autocomplete="current-password" />
          <div class="prem-input-suffix">
            <button type="button" class="prem-eye-btn" onclick="toggleAdminPw()"><i data-lucide="eye" id="adminEyeIcon"></i></button>
          </div>
        </div>
      </div>

      <button type="submit" class="auth-submit-btn" style="margin-top:8px">
        <i data-lucide="log-in"></i> Access Admin Panel
      </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.75rem;color:var(--text-muted)">
      <a href="/" style="color:var(--primary);text-decoration:none;font-weight:600">← Back to Campuslink</a>
    </p>
  </div>
</div>

<script src="../assets/js/auth-canvas.js"></script>
<script>
// Mini canvas for admin bg
(function(){
  const c = document.getElementById('adminBgCanvas');
  if (!c) return;
  const ctx = c.getContext('2d');
  function resize(){ c.width=c.offsetWidth; c.height=c.offsetHeight; }
  resize();
  window.addEventListener('resize', resize, {passive:true});
  let t = 0;
  function frame(){
    t += 0.005;
    ctx.clearRect(0,0,c.width,c.height);
    // Animated ring
    const cx = c.width/2; const cy = c.height/2;
    for (let i = 0; i < 3; i++) {
      const r = 200 + i*150 + Math.sin(t + i) * 30;
      ctx.beginPath();
      ctx.arc(cx, cy, r, 0, Math.PI*2);
      ctx.strokeStyle = `rgba(255,255,255,${0.04 - i*0.01})`;
      ctx.lineWidth = 1;
      ctx.stroke();
    }
    requestAnimationFrame(frame);
  }
  frame();
})();

function toggleAdminPw(){
  const inp = document.getElementById('adminPw');
  const ico = document.getElementById('adminEyeIcon');
  const isP = inp.type==='password';
  inp.type = isP?'text':'password';
  ico.setAttribute('data-lucide', isP?'eye-off':'eye');
  lucide.createIcons({nodes:[ico.parentElement]});
}

document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
</script>
</body>
</html>