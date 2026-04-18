<?php defined('CAMPUSLINK') or die(); ?>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <div class="auth-logo" style="font-size:2.5rem;">🔐</div>
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
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control"
                               placeholder="Min 8 chars, include uppercase & number"
                               required
                               data-min="8"
                               autocomplete="new-password">
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                    <div class="password-strength" role="progressbar"></div>
                    <div class="password-strength-text"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">
                        Confirm New Password <span class="required">*</span>
                    </label>
                    <div class="password-field">
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Repeat your new password"
                               required
                               autocomplete="new-password">
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                </div>

                <div class="alert alert-info">
                    <span class="alert-icon">🔒</span>
                    <div style="font-size:var(--font-size-xs);">
                        Password must be at least <strong>8 characters</strong> and include
                        an <strong>uppercase letter</strong>, a <strong>number</strong>,
                        and a <strong>special character</strong>.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">
                    🔐 Reset Password
                </button>

            </form>

        </div>

        <div class="auth-card-footer">
            <div class="auth-footer-links">
                <a href="<?= SITE_URL ?>/login">← Back to Login</a>
            </div>
        </div>

    </div>
</div>