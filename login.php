<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/hero.css" />
  <link rel="stylesheet" href="assets/css/auth-premium.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<meta name="csrf-token" content="<?= $csrf ?>">

<div class="auth-page">
  <!-- Left Visual -->
  <div class="auth-visual">
    <canvas id="authCanvas"></canvas>
    <div class="auth-visual-content">
      <a href="/" class="auth-visual-logo">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><rect width="36" height="36" rx="10" fill="rgba(255,255,255,0.12)"/><path d="M9 18C9 12.477 13.477 8 19 8s10 4.477 10 10" stroke="white" stroke-width="3" stroke-linecap="round"/><circle cx="18" cy="22" r="3.5" fill="#1ea952"/><path d="M14 28h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/></svg>
        <span class="logo-text">Campus<strong>link</strong></span>
      </a>
      <div class="auth-visual-main">
        <h2 class="auth-visual-headline">
          Welcome back to<br/><span class="highlight">Campuslink.</span>
        </h2>
        <p class="auth-visual-desc">Access your dashboard, manage reviews, track complaints, and stay updated on your campus vendor network.</p>
        <div class="auth-feat-list">
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="layout-dashboard"></i></div>
            <div class="afr-text"><strong>Full Dashboard Access</strong><span>Manage everything in one place</span></div>
          </div>
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="bell"></i></div>
            <div class="afr-text"><strong>Real-Time Notifications</strong><span>Subscription alerts, review updates</span></div>
          </div>
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="shield-check"></i></div>
            <div class="afr-text"><strong>Secure Session</strong><span>Protected with CSRF &amp; session binding</span></div>
          </div>
        </div>
      </div>
      <div class="auth-float-cards">
        <div class="afc-card">
          <div class="afc-label">Active Vendors</div>
          <div class="afc-value"><div class="afc-dot"></div> 340 Online</div>
        </div>
        <div class="afc-card">
          <div class="afc-label">Avg. Rating</div>
          <div class="afc-value"><span class="afc-stars">★★★★★</span> 4.9</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Form -->
  <div class="auth-form-panel">
    <div class="auth-form-container">
      <div class="auth-form-top">
        <a href="<?= BASE_PATH ?: '/' ?>" class="back-to-home"><i data-lucide="arrow-left"></i> Back to home</a>
        <h1 class="auth-title">Sign in to<br/>your account</h1>
        <p class="auth-subtitle">Don't have an account? <a href="<?= BASE_PATH ?>/register">Create one free</a></p>
      </div>

      <!-- Login Type Tabs -->
      <div class="login-type-tabs" id="loginTypeTabs">
        <button class="ltt-tab active" data-type="user" onclick="setLoginType('user')">
          <i data-lucide="user"></i> Student
        </button>
        <button class="ltt-tab" data-type="vendor" onclick="setLoginType('vendor')">
          <i data-lucide="store"></i> Vendor
        </button>
      </div>

      <form id="loginForm" novalidate autocomplete="off">
        <input type="hidden" name="csrf_token" id="csrfToken" value="<?= $csrf ?>">
        <input type="hidden" name="login_type" id="loginTypeInput" value="user">

        <div class="auth-error-alert" id="loginErrorAlert">
          <i data-lucide="alert-circle"></i>
          <span id="loginErrorMsg">Invalid credentials. Please try again.</span>
        </div>

        <div class="prem-input-group">
          <label class="prem-label">Email Address <span class="req">*</span></label>
          <div class="prem-input-wrap" id="emailWrap">
            <div class="prem-input-icon"><i data-lucide="mail"></i></div>
            <input type="email" name="email" class="prem-input" id="loginEmail" placeholder="your@email.com" required autocomplete="email" />
          </div>
          <div class="prem-error-msg" id="emailErr"><i data-lucide="alert-circle"></i> <span></span></div>
        </div>

        <div class="prem-input-group">
          <label class="prem-label">
            Password <span class="req">*</span>
            <a href="<?= BASE_PATH ?>/forgot-password">Forgot password?</a>
          </label>
          <div class="prem-input-wrap" id="pwWrap">
            <div class="prem-input-icon"><i data-lucide="lock"></i></div>
            <input type="password" name="password" class="prem-input" id="loginPw" placeholder="Your password" required autocomplete="current-password" />
            <div class="prem-input-suffix">
              <button type="button" class="prem-eye-btn" onclick="toggleEye('loginPw',this)"><i data-lucide="eye"></i></button>
            </div>
          </div>
          <div class="prem-error-msg" id="pwErr"><i data-lucide="alert-circle"></i> <span></span></div>
        </div>

        <label style="display:flex;align-items:center;gap:10px;margin-bottom:24px;cursor:pointer;font-size:0.85rem;color:var(--text-secondary)">
          <input type="checkbox" name="remember" style="accent-color:var(--primary)" />
          Keep me signed in for 30 days
        </label>

        <button type="submit" class="auth-submit-btn" id="loginSubmitBtn">
          <i data-lucide="log-in"></i> Sign In
        </button>
      </form>

      <div class="auth-or"><span>or</span></div>
      <div class="auth-alt-btns">
        <a href="/register" class="auth-alt-btn"><i data-lucide="user-plus"></i> Create Student Account</a>
        <a href="/vendor-register" class="auth-alt-btn"><i data-lucide="store"></i> Register as Vendor</a>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/main.js"></script>
<script src="assets/js/auth-canvas.js"></script>
<script>
const REDIRECTS = { user: '/user/dashboard', vendor: '/vendor/dashboard', admin: '/admin/dashboard' };

function setLoginType(type) {
  document.getElementById('loginTypeInput').value = type;
  document.querySelectorAll('.ltt-tab').forEach(t => t.classList.toggle('active', t.dataset.type === type));
  lucide.createIcons();
}

function toggleEye(inputId, btn) {
  const inp = document.getElementById(inputId);
  const isText = inp.type === 'text';
  inp.type = isText ? 'password' : 'text';
  btn.innerHTML = isText ? '<i data-lucide="eye"></i>' : '<i data-lucide="eye-off"></i>';
  lucide.createIcons({ nodes: [btn] });
}

function showInputError(wrapId, errId, msg) {
  document.getElementById(wrapId)?.classList.add('error');
  const err = document.getElementById(errId);
  if (err) { err.querySelector('span').textContent = msg; err.classList.add('show'); }
}
function clearInputError(wrapId, errId) {
  document.getElementById(wrapId)?.classList.remove('error');
  document.getElementById(errId)?.classList.remove('show');
}

document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  clearInputError('emailWrap','emailErr');
  clearInputError('pwWrap','pwErr');
  document.getElementById('loginErrorAlert').classList.remove('show');

  const email  = document.getElementById('loginEmail').value.trim();
  const pw     = document.getElementById('loginPw').value;
  const type   = document.getElementById('loginTypeInput').value;
  const remember = e.target.remember.checked;
  let valid    = true;

  if (!email) { showInputError('emailWrap','emailErr','Email is required.'); valid = false; }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showInputError('emailWrap','emailErr','Enter a valid email address.'); valid = false; }
  if (!pw) { showInputError('pwWrap','pwErr','Password is required.'); valid = false; }
  if (!valid) return;

  const btn = document.getElementById('loginSubmitBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div> Signing in…';

  try {
    const res  = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ email, password: pw, login_type: type, remember, csrf_token: document.getElementById('csrfToken').value }),
    });
    const data = await res.json();
    if (data.success) {
      btn.innerHTML = '<i data-lucide="check"></i> Redirecting…';
      btn.style.background = 'linear-gradient(135deg,#1ea952,#167a3d)';
      lucide.createIcons({ nodes: [btn] });
      setTimeout(() => window.location.href = data.redirect || REDIRECTS[type] || '/', 800);
    } else {
      const alert = document.getElementById('loginErrorAlert');
      document.getElementById('loginErrorMsg').textContent = data.error || 'Invalid email or password.';
      alert.classList.add('show');
      lucide.createIcons({ nodes: [alert] });
      btn.disabled = false;
      btn.innerHTML = '<i data-lucide="log-in"></i> Sign In';
      lucide.createIcons({ nodes: [btn] });
    }
  } catch {
    document.getElementById('loginErrorMsg').textContent = 'Network error. Please check your connection.';
    document.getElementById('loginErrorAlert').classList.add('show');
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="log-in"></i> Sign In';
    lucide.createIcons({ nodes: [btn] });
  }
});
</script>
</body>
</html>