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
.password-toggle svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;}
.alert{display:flex;align-items:flex-start;gap:0.5rem;}
.alert-icon svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0;margin-top:1px;}
</style>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <div class="auth-logo" style="display:flex;justify-content:center;padding:0.5rem 0;">
                <i data-lucide="lock-keyhole" style="width:40px;height:40px;stroke:#93c5fd;stroke-width:1.5;fill:none;"></i>
            </div>
            <div class="auth-title">Set New Password</div>
            <div class="auth-subtitle">Choose a strong password for your account</div>
        </div>

        <div class="auth-card-body">

            <form action="<?= SITE_URL ?>/reset-password" method="POST" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <input type="hidden" name="token" value="<?= e($_GET['token'] ?? '') ?>">
                <input type="hidden" name="type"  value="<?= e($_GET['type']  ?? 'user') ?>">

                <div class="form-group">
                    <label class="form-label" for="password">
                        New Password <span class="required">*</span>
                    </label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Min 8 chars, include uppercase &amp; number"
                               required data-min="8" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" role="progressbar"></div>
                    <div class="password-strength-text"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">
                        Confirm New Password <span class="required">*</span>
                    </label>
                    <div class="password-field">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" placeholder="Repeat your new password"
                               required autocomplete="new-password">
                        <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Show password">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                <div class="alert alert-info">
                    <span class="alert-icon"><i data-lucide="shield-check"></i></span>
                    <div style="font-size:var(--font-size-xs);">
                        Password must be at least <strong>8 characters</strong> and include
                        an <strong>uppercase letter</strong>, a <strong>number</strong>,
                        and a <strong>special character</strong>.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full"
                        style="margin-top:0.5rem;display:flex;align-items:center;justify-content:center;gap:0.45rem;">
                    <i data-lucide="check-circle" style="width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2;"></i>
                    Reset Password
                </button>

            </form>

        </div>

        <div class="auth-card-footer">
            <div class="auth-footer-links">
                <a href="<?= SITE_URL ?>/login">&larr; Back to Login</a>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();

    document.querySelectorAll('.password-toggle').forEach(function(btn) {
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