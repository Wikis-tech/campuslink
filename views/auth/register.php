<?php defined('CAMPUSLINK') or die(); ?>

<script>
if (!window.lucide) {
    var s = document.createElement('script');
    s.src = 'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';
    s.onload = function(){ lucide.createIcons(); };
    document.head.appendChild(s);
}
</script>

<style>
.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;
background:linear-gradient(135deg,#0b3d91 0%,#1a56db 60%,#0e9f6e 100%);padding:1.5rem;}
.auth-box{background:#fff;border-radius:20px;width:100%;max-width:480px;
overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.auth-box-header{background:linear-gradient(135deg,#0b3d91,#1a56db);
padding:1.75rem;text-align:center;color:#fff;}
.auth-box-header .logo{font-size:1.5rem;font-weight:900;margin-bottom:0.2rem;}
.auth-box-header .logo span{color:#34d399;}
.auth-box-header p{font-size:0.82rem;opacity:0.85;margin:0;}
.auth-box-body{padding:1.5rem;}
.fg{margin-bottom:1rem;}
.fg label{display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.3rem;}
.fg input,.fg select{width:100%;padding:0.65rem 0.85rem;border:1.5px solid #e2e8f0;
border-radius:9px;font-size:0.875rem;outline:none;transition:border 0.2s;box-sizing:border-box;}
.fg input:focus,.fg select:focus{border-color:#1a56db;}
.fg .hint{font-size:0.7rem;color:#64748b;margin-top:0.25rem;line-height:1.4;
display:flex;align-items:flex-start;gap:0.3rem;}
.fg .hint svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0;margin-top:1px;}
.fg .hint strong{color:#1a56db;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;}
@media(max-width:400px){.row2{grid-template-columns:1fr;}}
.auth-btn{width:100%;padding:0.8rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);
color:#fff;border:none;border-radius:9px;font-size:0.9rem;font-weight:800;
cursor:pointer;transition:opacity 0.2s;margin-top:0.25rem;
display:flex;align-items:center;justify-content:center;gap:0.45rem;}
.auth-btn:hover{opacity:0.9;}
.auth-btn svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2;}
.check-row{display:flex;gap:0.5rem;align-items:flex-start;margin-bottom:0.6rem;}
.check-row input{width:auto;margin-top:3px;flex-shrink:0;}
.check-row label{font-size:0.78rem;color:#374151;font-weight:400;}
.check-row label a{color:#1a56db;font-weight:700;text-decoration:none;}
.auth-box-footer{padding:0.9rem 1.5rem;background:#f8fafc;
border-top:1px solid #e2e8f0;text-align:center;font-size:0.82rem;color:#64748b;}
.auth-box-footer a{color:#1a56db;font-weight:700;text-decoration:none;}
.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;
padding:0.65rem 0.85rem;border-radius:8px;font-size:0.8rem;margin-bottom:0.85rem;font-weight:600;}
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;
padding:0.65rem 0.85rem;border-radius:8px;font-size:0.8rem;margin-bottom:0.85rem;font-weight:600;}
.flash-warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e;
padding:0.65rem 0.85rem;border-radius:8px;font-size:0.8rem;margin-bottom:0.85rem;font-weight:600;}
.pw-wrap{position:relative;}
.pw-wrap input{padding-right:2.75rem;}
.pw-toggle{position:absolute;right:0.65rem;top:50%;transform:translateY(-50%);
background:none;border:none;cursor:pointer;color:#94a3b8;
display:flex;align-items:center;padding:0;}
.pw-toggle:hover{color:#374151;}
.pw-toggle svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;}
.section-label{font-size:0.7rem;font-weight:800;text-transform:uppercase;
letter-spacing:0.07em;color:#94a3b8;margin:1rem 0 0.6rem;
display:flex;align-items:center;gap:0.5rem;}
.section-label::after{content:'';flex:1;height:1px;background:#e2e8f0;}
</style>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-box-header">
      <div class="logo">Campus<span>Link</span></div>
      <p>Student Registration &middot; <?= e(SCHOOL_NAME) ?></p>
    </div>
    <div class="auth-box-body">
      <?php require VIEWS_PATH . '/partials/flash.php'; ?>
      <form method="POST" action="<?= SITE_URL ?>/register" novalidate>
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

        <div class="section-label">Personal Information</div>
        <div class="row2">
          <div class="fg">
            <label>First Name *</label>
            <input type="text" name="first_name" placeholder="John"
                   value="<?= e($_SESSION['form_old']['first_name'] ?? '') ?>" required>
          </div>
          <div class="fg">
            <label>Last Name *</label>
            <input type="text" name="last_name" placeholder="Doe"
                   value="<?= e($_SESSION['form_old']['last_name'] ?? '') ?>" required>
          </div>
        </div>

        <div class="fg">
          <label>Phone Number *</label>
          <input type="tel" name="phone" placeholder="08012345678"
                 value="<?= e($_SESSION['form_old']['phone'] ?? '') ?>" required>
        </div>

        <div class="section-label">Login &amp; Contact Email</div>
        <div class="fg">
          <label>Gmail Address *</label>
          <input type="email" name="personal_email" placeholder="yourname@gmail.com"
                 value="<?= e($_SESSION['form_old']['personal_email'] ?? '') ?>" required>
          <div class="hint">
            <i data-lucide="mail"></i>
            This will be your <strong>login email</strong> and where all
            notifications and the verification link will be sent. Use Gmail for best results.
          </div>
        </div>

        <div class="section-label">School Identity (for verification)</div>
        <div class="fg">
          <label>School Email *</label>
          <input type="email" name="school_email"
                 placeholder="you@<?= e(SCHOOL_EMAIL_DOMAIN) ?>"
                 value="<?= e($_SESSION['form_old']['school_email'] ?? '') ?>" required>
          <div class="hint">
            <i data-lucide="graduation-cap"></i>
            Must be your <strong>@<?= e(SCHOOL_EMAIL_DOMAIN) ?></strong>
            address. Used to confirm you are a <?= e(SCHOOL_NAME) ?> student.
            Emails will NOT be sent here.
          </div>
        </div>

        <div class="fg">
          <label>Matric Number *</label>
          <input type="text" name="matric_number" placeholder="UAT/2021/001"
                 value="<?= e($_SESSION['form_old']['matric_number'] ?? '') ?>" required>
        </div>

        <div class="section-label">Security</div>
        <div class="row2">
          <div class="fg">
            <label>Password *</label>
            <div class="pw-wrap">
              <input type="password" name="password" id="pw1" placeholder="Min 8 chars" required>
              <button type="button" class="pw-toggle" data-target="pw1" aria-label="Toggle password">
                  <i data-lucide="eye"></i>
              </button>
            </div>
          </div>
          <div class="fg">
            <label>Confirm Password *</label>
            <div class="pw-wrap">
              <input type="password" name="password_confirmation" id="pw2" placeholder="Repeat" required>
              <button type="button" class="pw-toggle" data-target="pw2" aria-label="Toggle password">
                  <i data-lucide="eye"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="check-row">
          <input type="checkbox" name="terms" id="terms" required>
          <label for="terms">I agree to the
            <a href="<?= SITE_URL ?>/user-terms" target="_blank">User Terms</a>
            and
            <a href="<?= SITE_URL ?>/privacy-policy" target="_blank">Privacy Policy</a> *
          </label>
        </div>

        <button type="submit" class="auth-btn">
            <i data-lucide="user-plus"></i>
            Create Account
        </button>
      </form>
    </div>
    <div class="auth-box-footer">
      Already have an account? <a href="<?= SITE_URL ?>/login">Sign in &rarr;</a>
      &nbsp;&middot;&nbsp;
      <a href="<?= SITE_URL ?>/vendor/register">Vendor? Register here</a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();

    document.querySelectorAll('.pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var inp = document.getElementById(btn.dataset.target);
            if (!inp) return;
            var hidden = inp.type === 'password';
            inp.type = hidden ? 'text' : 'password';
            var icon = btn.querySelector('i[data-lucide]');
            if (icon) {
                icon.setAttribute('data-lucide', hidden ? 'eye-off' : 'eye');
                lucide.createIcons();
            }
        });
    });
});
</script>