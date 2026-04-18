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
.alert{display:flex;align-items:flex-start;gap:0.5rem;}
.alert-icon svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0;margin-top:1px;}
.otp-header-icon{display:flex;justify-content:center;padding:0.5rem 0;}
.otp-header-icon svg{width:40px;height:40px;stroke:#93c5fd;stroke-width:1.5;fill:none;}
.verify-btn{display:flex;align-items:center;justify-content:center;gap:0.45rem;}
.verify-btn svg{width:15px;height:15px;stroke:#fff;fill:none;stroke-width:2;}
</style>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <div class="auth-logo otp-header-icon">
                <i data-lucide="smartphone"></i>
            </div>
            <div class="auth-title">Phone Verification</div>
            <div class="auth-subtitle">Enter the 6-digit code sent to your phone</div>
        </div>

        <div class="auth-card-body">

            <?php
            $phone = $_SESSION['otp_phone'] ?? '';
            $maskedPhone = $phone ? substr($phone, 0, 4) . '****' . substr($phone, -3) : '';
            ?>

            <p style="text-align:center;font-size:var(--font-size-sm);color:var(--text-secondary);margin-bottom:0.5rem;">
                Code sent to <strong><?= e($maskedPhone) ?></strong>
            </p>

            <form action="<?= SITE_URL ?>/verify-otp" method="POST" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                <input type="hidden" name="otp" id="otpHidden">

                <!-- OTP Digit Inputs -->
                <div class="otp-inputs" role="group" aria-label="OTP input">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text"
                           class="otp-input"
                           maxlength="1"
                           inputmode="numeric"
                           pattern="[0-9]"
                           autocomplete="<?= $i === 0 ? 'one-time-code' : 'off' ?>"
                           aria-label="Digit <?= $i + 1 ?>">
                    <?php endfor; ?>
                </div>

                <!-- Attempts warning -->
                <?php if (!empty($attemptsLeft) && $attemptsLeft < 3): ?>
                <div class="alert alert-warning" style="margin-bottom:1rem;">
                    <span class="alert-icon"><i data-lucide="alert-triangle"></i></span>
                    <?= (int)$attemptsLeft ?> attempt(s) remaining. Too many wrong codes will lock you out.
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-full verify-btn">
                    <i data-lucide="shield-check"></i> Verify Phone Number
                </button>
            </form>

            <div class="otp-resend" style="margin-top:1.25rem;">
                <span style="color:var(--text-muted);">Didn't receive the code? </span>
                <button type="button" class="otp-resend-btn" disabled>
                    Resend Code
                </button>
                <span class="otp-timer" data-seconds="60">60s</span>
            </div>

            <div class="alert alert-info" style="margin-top:1.25rem;">
                <span class="alert-icon"><i data-lucide="info"></i></span>
                <div style="font-size:var(--font-size-xs);">
                    No SMS? We can also send the code to your
                    <strong>personal email</strong>.
                    <a href="<?= SITE_URL ?>/verify-otp/email" style="font-weight:700;">Send to email instead</a>
                </div>
            </div>

        </div>

        <div class="auth-card-footer">
            <div class="auth-footer-links">
                <a href="<?= SITE_URL ?>/login">&larr; Back to Login</a>
                <span>&middot;</span>
                <a href="<?= SITE_URL ?>/contact">Need Help?</a>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
});
</script>