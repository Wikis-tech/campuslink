<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf   = Security::generateCSRF();
$config = APP_CONFIG;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account — Campuslink</title>
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
          Join <span class="highlight">5,000+</span><br/>UAT students.
        </h2>
        <p class="auth-visual-desc">Create your free account to browse verified campus vendors, leave reviews, and connect with service providers on campus.</p>
        <div class="auth-feat-list">
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="search"></i></div>
            <div class="afr-text"><strong>Browse Free</strong><span>Access 340+ verified campus vendors</span></div>
          </div>
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="star"></i></div>
            <div class="afr-text"><strong>Review Vendors</strong><span>Help fellow students make better choices</span></div>
          </div>
          <div class="auth-feat-row">
            <div class="afr-icon"><i data-lucide="flag"></i></div>
            <div class="afr-text"><strong>Report Issues</strong><span>Admin investigates every complaint</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Form -->
  <div class="auth-form-panel">
    <div class="auth-form-container">
      <div class="auth-form-top">
        <a href="/" class="back-to-home"><i data-lucide="arrow-left"></i> Back to home</a>
        <h1 class="auth-title">Create your<br/>free account</h1>
        <p class="auth-subtitle">Already registered? <a href="/login">Sign in here</a></p>
      </div>

      <!-- Step Indicators -->
      <div class="auth-steps" id="authSteps">
        <div class="auth-step active" data-step="1"><div class="step-num">1</div> Info</div>
        <div class="auth-step-line" id="line-1-2"></div>
        <div class="auth-step" data-step="2"><div class="step-num">2</div> Verify</div>
        <div class="auth-step-line" id="line-2-3"></div>
        <div class="auth-step" data-step="3"><div class="step-num">3</div> Secure</div>
      </div>

      <div class="auth-error-alert" id="regErrorAlert">
        <i data-lucide="alert-circle"></i>
        <span id="regErrorMsg">An error occurred. Please try again.</span>
      </div>
      <div class="auth-success-alert" id="regSuccessAlert">
        <i data-lucide="check-circle"></i>
        <span id="regSuccessMsg">Account created! Check your email to verify.</span>
      </div>

      <form id="registerForm" novalidate autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <!-- STEP 1 -->
        <div class="auth-step-panel active" id="reg-step1">
          <div class="prem-row">
            <div class="prem-input-group">
              <label class="prem-label">First Name <span class="req">*</span></label>
              <div class="prem-input-wrap" id="fn-wrap">
                <div class="prem-input-icon"><i data-lucide="user"></i></div>
                <input type="text" name="first_name" class="prem-input" placeholder="John" required minlength="2" />
              </div>
              <div class="prem-error-msg" id="fn-err"><i data-lucide="alert-circle"></i><span></span></div>
            </div>
            <div class="prem-input-group">
              <label class="prem-label">Last Name <span class="req">*</span></label>
              <div class="prem-input-wrap" id="ln-wrap">
                <div class="prem-input-icon"><i data-lucide="user"></i></div>
                <input type="text" name="last_name" class="prem-input" placeholder="Doe" required minlength="2" />
              </div>
              <div class="prem-error-msg" id="ln-err"><i data-lucide="alert-circle"></i><span></span></div>
            </div>
          </div>

          <div class="prem-input-group">
            <label class="prem-label">
              School Email <span class="req">*</span>
              <span class="label-hint">Format: surname.firstname@student.uat.edu.ng</span>
            </label>
            <div class="prem-input-wrap" id="se-wrap">
              <div class="prem-input-icon"><i data-lucide="mail"></i></div>
              <input type="email" name="school_email" class="prem-input" id="schoolEmailInput" placeholder="doe.john@student.uat.edu.ng" required />
            </div>
            <div class="prem-error-msg" id="se-err"><i data-lucide="alert-circle"></i><span></span></div>
          </div>

          <div class="prem-input-group">
            <label class="prem-label">
              Personal Email <span class="req">*</span>
              <span class="label-hint">Verification email sent here</span>
            </label>
            <div class="prem-input-wrap" id="pe-wrap">
              <div class="prem-input-icon"><i data-lucide="mail"></i></div>
              <input type="email" name="personal_email" class="prem-input" placeholder="johndoe@gmail.com" required />
            </div>
            <div class="prem-error-msg" id="pe-err"><i data-lucide="alert-circle"></i><span></span></div>
          </div>

          <div class="prem-row">
            <div class="prem-input-group">
              <label class="prem-label">Department <span class="req">*</span></label>
              <div class="prem-select-wrap" id="dept-wrap">
                <div class="prem-input-icon"><i data-lucide="building"></i></div>
                <select name="department" class="prem-select" required>
                  <option value="">Select Department</option>
                  <option>Computer Science</option><option>Information Technology</option>
                  <option>Electrical Engineering</option><option>Mechanical Engineering</option>
                  <option>Civil Engineering</option><option>Business Administration</option>
                  <option>Accounting</option><option>Mass Communication</option>
                  <option>Law</option><option>Medicine & Surgery</option>
                  <option>Nursing</option><option>Architecture</option>
                  <option>Mathematics</option><option>Physics</option><option>Chemistry</option>
                  <option>Other</option>
                </select>
                <div class="prem-select-arrow"><i data-lucide="chevron-down"></i></div>
              </div>
              <div class="prem-error-msg" id="dept-err"><i data-lucide="alert-circle"></i><span></span></div>
            </div>
            <div class="prem-input-group">
              <label class="prem-label">Level <span class="req">*</span></label>
              <div class="prem-select-wrap" id="lvl-wrap">
                <div class="prem-input-icon"><i data-lucide="graduation-cap"></i></div>
                <select name="level" class="prem-select" required>
                  <option value="">Select Level</option>
                  <option>100 Level</option><option>200 Level</option>
                  <option>300 Level</option><option>400 Level</option>
                  <option>500 Level</option><option>Postgraduate</option>
                </select>
                <div class="prem-select-arrow"><i data-lucide="chevron-down"></i></div>
              </div>
              <div class="prem-error-msg" id="lvl-err"><i data-lucide="alert-circle"></i><span></span></div>
            </div>
          </div>

          <div class="prem-input-group">
            <label class="prem-label">Phone Number <span class="req">*</span> <span class="label-hint">OTP sent to this number</span></label>
            <div class="prem-input-wrap" id="ph-wrap">
              <div class="prem-input-icon"><i data-lucide="phone"></i></div>
              <input type="tel" name="phone" class="prem-input" placeholder="+2348012345678" required />
              <div class="prem-input-suffix"><span class="prem-suffix-text">NGN</span></div>
            </div>
            <div class="prem-error-msg" id="ph-err"><i data-lucide="alert-circle"></i><span></span></div>
          </div>

          <button type="button" class="auth-submit-btn" onclick="regNextStep(2)">
            Continue <i data-lucide="arrow-right"></i>
          </button>
        </div>

        <!-- STEP 2: OTP -->
        <div class="auth-step-panel" id="reg-step2">
          <div class="otp-wrap">
            <div class="otp-header-icon"><i data-lucide="smartphone"></i></div>
            <h3>Verify your phone number</h3>
            <p>We've sent a 6-digit code to <strong id="phoneDisplay">your phone</strong>. Enter it below to continue.</p>
            <div class="otp-digits" id="otpDigits">
              <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" />
              <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" />
              <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" />
              <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" />
              <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" />
              <input type="text" class="otp-digit" maxlength="1" pattern="[0-9]" inputmode="numeric" />
            </div>
            <div class="otp-error-msg" id="otpErrMsg"></div>
            <button type="button" class="auth-submit-btn" onclick="verifyOTP()" style="margin-bottom:16px">
              <i data-lucide="check"></i> Verify Code
            </button>
            <div class="otp-resend">
              Didn't receive it?
              <button type="button" onclick="resendOTP()" id="resendBtn">Resend Code</button>
              <span class="otp-timer" id="otpTimer"></span>
            </div>
          </div>
          <button type="button" style="width:100%;margin-top:12px;background:none;border:2px solid var(--border);border-radius:14px;padding:13px;font-family:var(--font-head);font-size:0.875rem;font-weight:600;color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.22s ease" onclick="regGoStep(1)" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
            <i data-lucide="arrow-left"></i> Go Back
          </button>
        </div>

        <!-- STEP 3: Password & Terms -->
        <div class="auth-step-panel" id="reg-step3">
          <div class="prem-input-group">
            <label class="prem-label">Create Password <span class="req">*</span></label>
            <div class="prem-input-wrap" id="p1-wrap">
              <div class="prem-input-icon"><i data-lucide="lock"></i></div>
              <input type="password" name="password" class="prem-input" id="regPw" placeholder="Minimum 8 characters" required minlength="8" />
              <div class="prem-input-suffix">
                <button type="button" class="prem-eye-btn" onclick="toggleEye('regPw',this)"><i data-lucide="eye"></i></button>
              </div>
            </div>
            <div class="pw-strength-wrap">
              <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwFill"></div></div>
              <span class="pw-strength-label" id="pwLabel"></span>
            </div>
            <div class="prem-error-msg" id="p1-err"><i data-lucide="alert-circle"></i><span></span></div>
          </div>

          <div class="prem-input-group">
            <label class="prem-label">Confirm Password <span class="req">*</span></label>
            <div class="prem-input-wrap" id="p2-wrap">
              <div class="prem-input-icon"><i data-lucide="lock"></i></div>
              <input type="password" name="confirm_password" class="prem-input" id="regPw2" placeholder="Repeat your password" required />
              <div class="prem-input-suffix">
                <button type="button" class="prem-eye-btn" onclick="toggleEye('regPw2',this)"><i data-lucide="eye"></i></button>
              </div>
            </div>
            <div class="prem-error-msg" id="p2-err"><i data-lucide="alert-circle"></i><span></span></div>
          </div>

          <label class="terms-checkbox">
            <input type="checkbox" name="terms" id="termsCheck" />
            <span>I have read and agree to the <a href="/terms" target="_blank">Terms &amp; Conditions</a> and <a href="/privacy" target="_blank">Privacy Policy</a>. I confirm I am 18 years or older.</span>
          </label>

          <button type="submit" class="auth-submit-btn" id="regSubmitBtn">
            <i data-lucide="user-plus"></i> Create Account
          </button>

          <button type="button" style="width:100%;margin-top:12px;background:none;border:2px solid var(--border);border-radius:14px;padding:13px;font-family:var(--font-head);font-size:0.875rem;font-weight:600;color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.22s ease" onclick="regGoStep(2)" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
            <i data-lucide="arrow-left"></i> Go Back
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/main.js"></script>
<script src="../assets/js/auth-canvas.js"></script>
<script>
const SCHOOL_EMAIL_PATTERN = /^[a-z]+\.[a-z]+@student\.uat\.edu\.ng$/i;
let currentRegStep = 1;
let otpVerified    = false;
let otpTimerInt;

function showErr(wrapId, errId, msg) {
  document.getElementById(wrapId)?.classList.add('error');
  const el = document.getElementById(errId);
  if (el) { el.querySelector('span').textContent = msg; el.classList.add('show'); }
}
function clearErr(wrapId, errId) {
  document.getElementById(wrapId)?.classList.remove('error');
  document.getElementById(errId)?.classList.remove('show');
}

function regGoStep(step) {
  document.querySelectorAll('.auth-step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`reg-step${step}`)?.classList.add('active');
  document.querySelectorAll('.auth-step').forEach(s => {
    const n = parseInt(s.dataset.step);
    s.classList.toggle('active', n === step);
    s.classList.toggle('done', n < step);
  });
  document.getElementById('line-1-2')?.classList.toggle('done', step > 1);
  document.getElementById('line-2-3')?.classList.toggle('done', step > 2);
  currentRegStep = step;
  lucide.createIcons();
}

function regNextStep(step) {
  // Validate step 1
  if (step === 2) {
    let valid = true;
    const form = document.getElementById('registerForm');
    const fn   = form.first_name.value.trim();
    const ln   = form.last_name.value.trim();
    const se   = form.school_email.value.trim();
    const pe   = form.personal_email.value.trim();
    const dept = form.department.value;
    const lvl  = form.level.value;
    const ph   = form.phone.value.trim();

    clearErr('fn-wrap','fn-err'); clearErr('ln-wrap','ln-err');
    clearErr('se-wrap','se-err'); clearErr('pe-wrap','pe-err');
    clearErr('dept-wrap','dept-err'); clearErr('lvl-wrap','lvl-err');
    clearErr('ph-wrap','ph-err');

    if (fn.length < 2) { showErr('fn-wrap','fn-err','Enter your first name (min 2 chars).'); valid = false; }
    if (ln.length < 2) { showErr('ln-wrap','ln-err','Enter your last name (min 2 chars).'); valid = false; }
    if (!SCHOOL_EMAIL_PATTERN.test(se)) { showErr('se-wrap','se-err','Format must be: surname.firstname@student.uat.edu.ng'); valid = false; }
    if (!pe || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(pe)) { showErr('pe-wrap','pe-err','Enter a valid personal email.'); valid = false; }
    if (se.toLowerCase() === pe.toLowerCase()) { showErr('pe-wrap','pe-err','School and personal email must be different.'); valid = false; }
    if (!dept) { showErr('dept-wrap','dept-err','Please select your department.'); valid = false; }
    if (!lvl) { showErr('lvl-wrap','lvl-err','Please select your level.'); valid = false; }
    if (!ph || !/^\+?[0-9]{10,15}$/.test(ph)) { showErr('ph-wrap','ph-err','Enter a valid Nigerian phone number.'); valid = false; }
    if (!valid) return;

    document.getElementById('phoneDisplay').textContent = ph;
    startOTPTimer();
  }
  regGoStep(step);
}

function startOTPTimer() {
  let sec = 60;
  const el = document.getElementById('otpTimer');
  const btn = document.getElementById('resendBtn');
  btn.disabled = true; btn.style.opacity = '0.5';
  clearInterval(otpTimerInt);
  otpTimerInt = setInterval(() => {
    el.textContent = `(${sec}s)`;
    sec--;
    if (sec < 0) {
      clearInterval(otpTimerInt);
      el.textContent = '';
      btn.disabled = false; btn.style.opacity = '1';
    }
  }, 1000);
}

function resendOTP() { startOTPTimer(); showToast('OTP resent to your phone.','success'); }

function verifyOTP() {
  const digits = Array.from(document.querySelectorAll('.otp-digit')).map(d => d.value).join('');
  const errEl  = document.getElementById('otpErrMsg');
  if (digits.length < 6) { errEl.textContent = 'Please enter all 6 digits.'; return; }
  if (!/^\d{6}$/.test(digits)) { errEl.textContent = 'Only numbers allowed.'; return; }
  // Demo: any 6-digit code passes. In prod: call /api/verify-otp
  errEl.textContent = '';
  otpVerified = true;
  document.querySelectorAll('.otp-digit').forEach(d => d.classList.add('filled'));
  setTimeout(() => regGoStep(3), 400);
}

// OTP digit auto-advance
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();
  const digits = document.querySelectorAll('.otp-digit');
  digits.forEach((d, i) => {
    d.addEventListener('input', () => {
      if (d.value && i < digits.length - 1) digits[i+1].focus();
    });
    d.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !d.value && i > 0) digits[i-1].focus();
    });
    d.addEventListener('input', () => {
      if (d.value) d.classList.add('filled'); else d.classList.remove('filled');
    });
  });

  // Password strength
  document.getElementById('regPw')?.addEventListener('input', function() {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const fills = ['0%','25%','50%','75%','100%'];
    const labels = ['','Weak','Fair','Good','Strong'];
    const colors = ['','#ef4444','#f59e0b','#3b82f6','#1ea952'];
    const fill = document.getElementById('pwFill');
    const lbl  = document.getElementById('pwLabel');
    fill.style.width = fills[score];
    fill.style.background = colors[score];
    lbl.textContent = labels[score];
    lbl.style.color = colors[score];
  });

  // Form submit
  document.getElementById('registerForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    document.getElementById('regErrorAlert').classList.remove('show');

    if (!otpVerified) { showToast('Please verify your phone first.','error'); regGoStep(2); return; }

    const form = e.target;
    const pw   = document.getElementById('regPw').value;
    const pw2  = document.getElementById('regPw2').value;

    clearErr('p1-wrap','p1-err'); clearErr('p2-wrap','p2-err');

    let valid = true;
    if (pw.length < 8) { showErr('p1-wrap','p1-err','Password must be at least 8 characters.'); valid = false; }
    if (pw !== pw2) { showErr('p2-wrap','p2-err','Passwords do not match.'); valid = false; }
    if (!document.getElementById('termsCheck').checked) {
      showToast('Please accept the Terms & Conditions.','error'); return;
    }
    if (!valid) return;

    const btn = document.getElementById('regSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Creating Account…';

    try {
      const res  = await fetch('/api/register', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify({
          first_name:     form.first_name.value.trim(),
          last_name:      form.last_name.value.trim(),
          school_email:   form.school_email.value.trim(),
          personal_email: form.personal_email.value.trim(),
          phone:          form.phone.value.trim(),
          department:     form.department.value,
          level:          form.level.value,
          password:       pw,
          terms_accepted: true,
          otp_verified:   true,
          csrf_token:     form.csrf_token.value,
        }),
      });
      const data = await res.json();
      if (data.success) {
        const sa = document.getElementById('regSuccessAlert');
        document.getElementById('regSuccessMsg').textContent = data.message || 'Account created! Please check your personal email to verify your account.';
        sa.classList.add('show');
        lucide.createIcons({ nodes: [sa] });
        btn.innerHTML = '<i data-lucide="check"></i> Account Created!';
        btn.style.background = 'linear-gradient(135deg,#1ea952,#167a3d)';
        lucide.createIcons({ nodes: [btn] });
        setTimeout(() => window.location.href = '/login', 3000);
      } else {
        const ea = document.getElementById('regErrorAlert');
        document.getElementById('regErrorMsg').textContent = data.error || 'Registration failed.';
        ea.classList.add('show');
        lucide.createIcons({ nodes: [ea] });
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="user-plus"></i> Create Account';
        lucide.createIcons({ nodes: [btn] });
      }
    } catch {
      document.getElementById('regErrorMsg').textContent = 'Network error. Please try again.';
      document.getElementById('regErrorAlert').classList.add('show');
      btn.disabled = false;
      btn.innerHTML = '<i data-lucide="user-plus"></i> Create Account';
      lucide.createIcons({ nodes: [btn] });
    }
  });
});

function toggleEye(id, btn) {
  const inp = document.getElementById(id);
  const isT = inp.type === 'text';
  inp.type = isT ? 'password' : 'text';
  btn.innerHTML = isT ? '<i data-lucide="eye"></i>' : '<i data-lucide="eye-off"></i>';
  lucide.createIcons({ nodes: [btn] });
}
function showErr(wrapId, errId, msg) {
  document.getElementById(wrapId)?.classList.add('error');
  const el = document.getElementById(errId);
  if (el) { el.querySelector('span').textContent = msg; el.classList.add('show'); }
}
function clearErr(wrapId, errId) {
  document.getElementById(wrapId)?.classList.remove('error');
  document.getElementById(errId)?.classList.remove('show');
}
</script>
</body>
</html>