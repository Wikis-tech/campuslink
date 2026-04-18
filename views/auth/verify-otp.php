<?php defined('CAMPUSLINK') or die(); ?>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <div class="auth-logo" style="font-size:2.5rem;">📱</div>
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
                    <span class="alert-icon">⚠️</span>
                    <?= (int)$attemptsLeft ?> attempt(s) remaining. Too many wrong codes will lock you out.
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-full">
                    ✅ Verify Phone Number
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
                <span class="alert-icon">ℹ️</span>
                <div style="font-size:var(--font-size-xs);">
                    No SMS? We can also send the code to your
                    <strong>personal email</strong>.
                    <a href="<?= SITE_URL ?>/verify-otp/email" style="font-weight:700;">Send to email instead</a>
                </div>
            </div>

        </div>

        <div class="auth-card-footer">
            <div class="auth-footer-links">
                <a href="<?= SITE_URL ?>/login">← Back to Login</a>
                <span>·</span>
                <a href="<?= SITE_URL ?>/contact">Need Help?</a>
            </div>
        </div>

    </div>
</div>