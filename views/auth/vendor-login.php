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
.toggle-tab{display:flex;align-items:center;justify-content:center;gap:0.4rem;}
.toggle-tab svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;}
.alert-icon svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0;}
.alert{display:flex;align-items:flex-start;gap:0.5rem;}
.password-toggle svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;}
</style>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <div class="auth-logo">
                <span style="color:#93c5fd;">Campus</span><span style="color:#34d399;">Link</span>
            </div>
            <div class="auth-title">Vendor Sign In</div>
            <div class="auth-subtitle">Access your vendor dashboard</div>
        </div>

        <div class="auth-card-body">

            <!-- Login Type Toggle -->
            <div style="display:flex;background:var(--bg);border-radius:var(--radius-lg);padding:4px;margin-bottom:var(--space-6);gap:4px;">
                <a href="<?= SITE_URL ?>/login"
                   style="flex:1;text-align:center;padding:0.6rem;border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:600;text-decoration:none;color:var(--text-muted);"
                   class="toggle-tab">
                    <i data-lucide="graduation-cap"></i> Student
                </a>
                <a href="<?= SITE_URL ?>/vendor/login"
                   style="flex:1;text-align:center;padding:0.6rem;border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:600;text-decoration:none;background:#fff;color:var(--primary);box-shadow:var(--shadow-sm);"
                   class="toggle-tab">
                    <i data-lucide="store"></i> Vendor
                </a>
            </div>

            <form action="<?= SITE_URL ?>/vendor/login" method="POST" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <div class="form-group">
                    <label class="form-label" for="email">
                        Email Address <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="your@email.com"
                           value="<?= e($_SESSION['form_old']['email'] ?? '') ?>"
                           required autocomplete="email">
                    <span class="form-hint">Use your school or personal email registered with CampusLink</span>
                </div>

                <div class="form-group">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label class="form-label" for="password">
                            Password <span class="required">*</span>
                        </label>
                        <a href="<?= SITE_URL ?>/forgot-password?type=vendor"
                           style="font-size:var(--font-size-xs);color:var(--primary);font-weight:600;">
                            Forgot password?
                        </a>
                    </div>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="vPwToggle" aria-label="Show password">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                <?php if (!empty($lockoutMinutes)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon"><i data-lucide="lock"></i></span>
                    Too many failed attempts. Try again in <strong><?= (int)$lockoutMinutes ?> minute(s)</strong>.
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">
                    Sign In to Vendor Panel &rarr;
                </button>
            </form>

            <div class="alert alert-info" style="margin-top:1rem;">
                <span class="alert-icon"><i data-lucide="info"></i></span>
                <div>
                    <strong>Account not approved yet?</strong><br>
                    <span style="font-size:var(--font-size-xs);">
                        New vendor accounts are reviewed within 24–48 hours.
                        You'll receive an email once your account is approved.
                    </span>
                </div>
            </div>

        </div>

        <div class="auth-card-footer">
            <div class="auth-footer-links">
                <span>Not a vendor yet?</span>
                <a href="<?= SITE_URL ?>/vendor/register">Register Your Business</a>
                <span>&middot;</span>
                <a href="<?= SITE_URL ?>/login">Student Login</a>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();

    var btn = document.getElementById('vPwToggle');
    var inp = document.getElementById('password');
    if (btn && inp) {
        btn.addEventListener('click', function() {
            var hidden = inp.type === 'password';
            inp.type = hidden ? 'text' : 'password';
            var icon = btn.querySelector('i[data-lucide]');
            if (icon) {
                icon.setAttribute('data-lucide', hidden ? 'eye-off' : 'eye');
                lucide.createIcons();
            }
        });
    }
});
</script>