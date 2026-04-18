<?php defined('CAMPUSLINK') or die(); ?>

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
                   style="flex:1;text-align:center;padding:0.6rem;border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:600;text-decoration:none;color:var(--text-muted);">
                    🎓 Student
                </a>
                <a href="<?= SITE_URL ?>/vendor/login"
                   style="flex:1;text-align:center;padding:0.6rem;border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:600;text-decoration:none;background:#fff;color:var(--primary);box-shadow:var(--shadow-sm);">
                    🏪 Vendor
                </a>
            </div>

            <form action="<?= SITE_URL ?>/vendor/login" method="POST" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        Email Address <span class="required">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           placeholder="your@email.com"
                           value="<?= e($_SESSION['form_old']['email'] ?? '') ?>"
                           required
                           autocomplete="email">
                    <span class="form-hint">Use your school or personal email registered with CampusLink</span>
                </div>

                <!-- Password -->
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
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                </div>

                <?php if (!empty($lockoutMinutes)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">🔒</span>
                    Too many failed attempts. Try again in <strong><?= (int)$lockoutMinutes ?> minute(s)</strong>.
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">
                    Sign In to Vendor Panel →
                </button>
            </form>

            <!-- Pending notice -->
            <div class="alert alert-info" style="margin-top:1rem;">
                <span class="alert-icon">ℹ️</span>
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
                <span>·</span>
                <a href="<?= SITE_URL ?>/login">Student Login</a>
            </div>
        </div>

    </div>
</div>