<?php defined('CAMPUSLINK') or die(); ?>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-card-header">
            <div class="auth-logo" style="font-size:2.5rem;">🔑</div>
            <div class="auth-title">Reset Password</div>
            <div class="auth-subtitle">Enter your email and we'll send a reset link</div>
        </div>

        <div class="auth-card-body">

            <?php if (!empty($sent)): ?>
            <!-- Success state -->
            <div style="text-align:center;padding:1rem 0;">
                <div style="font-size:3.5rem;margin-bottom:1rem;">📬</div>
                <h3 style="margin-bottom:0.75rem;">Check Your Email</h3>
                <p style="color:var(--text-secondary);font-size:var(--font-size-sm);line-height:1.7;">
                    If an account exists for that email, a password reset link has been sent.
                    The link expires in <strong>1 hour</strong>.
                </p>
                <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:1rem;">
                    Didn't get it? Check your spam folder or wait a minute.
                </p>
            </div>
            <?php else: ?>

            <form action="<?= SITE_URL ?>/forgot-password" method="POST" class="auth-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <?php $type = $_GET['type'] ?? 'user'; ?>
                <input type="hidden" name="type" value="<?= e($type) ?>">

                <!-- Account type selector -->
                <div style="display:flex;background:var(--bg);border-radius:var(--radius-lg);padding:4px;margin-bottom:var(--space-5);gap:4px;">
                    <a href="?type=user"
                       style="flex:1;text-align:center;padding:0.5rem;border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:600;text-decoration:none;
                       <?= $type === 'user' ? 'background:#fff;color:var(--primary);box-shadow:var(--shadow-sm);' : 'color:var(--text-muted);' ?>">
                        🎓 Student
                    </a>
                    <a href="?type=vendor"
                       style="flex:1;text-align:center;padding:0.5rem;border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:600;text-decoration:none;
                       <?= $type === 'vendor' ? 'background:#fff;color:var(--primary);box-shadow:var(--shadow-sm);' : 'color:var(--text-muted);' ?>">
                        🏪 Vendor
                    </a>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        <?= $type === 'vendor' ? 'Registered Email' : 'School or Personal Email' ?>
                        <span class="required">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           placeholder="<?= $type === 'vendor' ? 'vendor@email.com' : 'your@email.com' ?>"
                           required
                           autocomplete="email">
                    <span class="form-hint">
                        Enter the email address linked to your
                        <?= $type === 'vendor' ? 'vendor' : 'student' ?> account
                    </span>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    📤 Send Reset Link
                </button>

            </form>
            <?php endif; ?>

        </div>

        <div class="auth-card-footer">
            <div class="auth-footer-links">
                <a href="<?= SITE_URL ?>/login">← Back to Login</a>
                <span>·</span>
                <a href="<?= SITE_URL ?>/vendor/login">Vendor Login</a>
            </div>
        </div>

    </div>
</div>