<?php defined('CAMPUSLINK') or die(); ?>

<div class="payment-success-page"
     style="min-height:80vh;display:flex;align-items:center;justify-content:center;
            padding:3rem 1rem;">
    <div style="max-width:520px;width:100%;text-align:center;">

        <div style="margin-bottom:1.5rem;display:flex;justify-content:center;
                    animation:fadeIn 0.6s ease;">
            <span style="width:80px;height:80px;border-radius:50%;
                         background:#f0fdf4;display:flex;align-items:center;
                         justify-content:center;color:#16a34a;">
                <i data-lucide="check-circle" style="width:44px;height:44px;"></i>
            </span>
        </div>

        <h1 style="font-size:2rem;font-weight:900;color:var(--text-primary);
                   margin-bottom:0.75rem;letter-spacing:-0.02em;">
            Payment Successful!
        </h1>

        <p style="font-size:var(--font-size-lg);color:var(--text-secondary);
                  margin-bottom:2rem;line-height:1.6;">
            Your <strong><?= ucfirst(e($payment['plan_type'] ?? '')) ?> Plan</strong>
            subscription is now active.
            Your business is live in the CampusLink directory!
        </p>

        <!-- Details Card -->
        <div style="background:var(--card-bg);border-radius:var(--radius-xl);
                    border:1px solid var(--divider);padding:1.5rem;
                    margin-bottom:2rem;text-align:left;box-shadow:var(--shadow-card);">

            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Reference</span>
                    <span style="font-family:monospace;font-weight:600;font-size:var(--font-size-xs);"
                          data-copy="<?= e($payment['reference'] ?? '') ?>">
                        <?= e($payment['reference'] ?? '') ?>
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Amount Paid</span>
                    <strong style="color:var(--accent-green);">
                        ₦<?= number_format(($payment['amount'] ?? 0) / 100, 2) ?>
                    </strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Plan</span>
                    <strong><?= ucfirst(e($payment['plan_type'] ?? '')) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Expires</span>
                    <strong>
                        <?= isset($subscription['expiry_date'])
                            ? date('d M Y', strtotime($subscription['expiry_date']))
                            : '—' ?>
                    </strong>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:0.75rem;max-width:320px;margin:0 auto;">
            <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug'] ?? '') ?>"
               target="_blank"
               class="btn btn-primary btn-full"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                <i data-lucide="eye" style="width:16px;height:16px;"></i> View My Public Profile
            </a>
            <a href="<?= SITE_URL ?>/vendor/dashboard"
               class="btn btn-outline-primary btn-full"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                <i data-lucide="home" style="width:16px;height:16px;"></i> Go to Dashboard
            </a>
        </div>

        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:1.5rem;">
            A payment receipt has been sent to your registered email.
        </p>

    </div>
</div>