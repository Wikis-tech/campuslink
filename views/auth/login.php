<?php defined('CAMPUSLINK') or die(); ?>
<style>
.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;
background:linear-gradient(135deg,#0b3d91 0%,#1a56db 60%,#0e9f6e 100%);
padding:1.5rem;}
.auth-box{background:#fff;border-radius:20px;width:100%;max-width:420px;
overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.auth-box-header{background:linear-gradient(135deg,#0b3d91,#1a56db);
padding:2rem;text-align:center;color:#fff;}
.auth-box-header .logo{font-size:1.6rem;font-weight:900;margin-bottom:0.25rem;}
.auth-box-header .logo span{color:#34d399;}
.auth-box-header p{font-size:0.85rem;opacity:0.85;margin:0;}
.auth-box-body{padding:1.75rem;}
.auth-box-body .form-group{margin-bottom:1.1rem;}
.auth-box-body label{display:block;font-size:0.8rem;font-weight:700;
color:#374151;margin-bottom:0.35rem;}
.auth-box-body input{width:100%;padding:0.7rem 0.9rem;border:1.5px solid #e2e8f0;
border-radius:9px;font-size:0.9rem;outline:none;transition:border 0.2s;
box-sizing:border-box;}
.auth-box-body input:focus{border-color:#1a56db;}
.auth-btn{width:100%;padding:0.8rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);
color:#fff;border:none;border-radius:9px;font-size:0.95rem;font-weight:800;
cursor:pointer;transition:opacity 0.2s;margin-top:0.5rem;}
.auth-btn:hover{opacity:0.9;}
.auth-box-footer{padding:1rem 1.75rem;background:#f8fafc;
border-top:1px solid #e2e8f0;text-align:center;font-size:0.82rem;color:#64748b;}
.auth-box-footer a{color:#1a56db;font-weight:700;text-decoration:none;}
.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;
padding:0.75rem 1rem;border-radius:8px;font-size:0.83rem;margin-bottom:1rem;}
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;
padding:0.75rem 1rem;border-radius:8px;font-size:0.83rem;margin-bottom:1rem;}
.pw-wrap{position:relative;}
.pw-wrap input{padding-right:2.5rem;}
.pw-toggle{position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);
background:none;border:none;cursor:pointer;font-size:1rem;color:#94a3b8;}
.divider{display:flex;align-items:center;gap:0.75rem;margin:1rem 0;color:#94a3b8;font-size:0.78rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e2e8f0;}
</style>

<div class="auth-page">
  <div class="auth-box">
    <div class="auth-box-header">
      <div class="logo">Campus<span>Link</span></div>
      <p>Student Login · <?= e(SCHOOL_NAME) ?></p>
    </div>
    <div class="auth-box-body">
      <?php require VIEWS_PATH . '/partials/flash.php'; ?>
      <form method="POST" action="<?= SITE_URL ?>/login">
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
        <div class="form-group">
          <label>School Email</label>
          <input type="email" name="email" placeholder="you@<?= e(SCHOOL_EMAIL_DOMAIN) ?>"
                 value="<?= e($_SESSION['form_old']['email'] ?? '') ?>" required autocomplete="email">
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="pw-wrap">
            <input type="password" name="password" id="pw" placeholder="Your password" required>
            <button type="button" class="pw-toggle" onclick="
              var i=document.getElementById('pw');
              i.type=i.type==='password'?'text':'password';
              this.textContent=i.type==='password'?'👁️':'🙈';">👁️</button>
          </div>
        </div>
        <div style="text-align:right;margin:-0.5rem 0 0.75rem;">
          <a href="<?= SITE_URL ?>/forgot-password"
             style="font-size:0.78rem;color:#1a56db;text-decoration:none;">Forgot password?</a>
        </div>
        <button type="submit" class="auth-btn">🔑 Sign In</button>
      </form>
      <div class="divider">or</div>
      <a href="<?= SITE_URL ?>/vendor/login"
         style="display:block;text-align:center;padding:0.65rem;border:1.5px solid #e2e8f0;
                border-radius:9px;font-size:0.85rem;font-weight:700;color:#374151;
                text-decoration:none;">🏪 Vendor Login</a>
    </div>
    <div class="auth-box-footer">
      No account? <a href="<?= SITE_URL ?>/register">Create one free →</a>
    </div>
  </div>
</div>