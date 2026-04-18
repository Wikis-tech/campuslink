<?php defined('CAMPUSLINK') or die(); ?>

<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;
            padding:3rem 1rem;">
    <div style="max-width:480px;width:100%;text-align:center;">

        <div style="margin-bottom:1.5rem;display:flex;justify-content:center;">
            <span style="width:80px;height:80px;border-radius:50%;
                         background:#fef2f2;display:flex;align-items:center;
                         justify-content:center;color:#dc2626;">
                <i data-lucide="x-circle" style="width:44px;height:44px;"></i>
            </span>
        </div>

        <h1 style="font-size:2rem;font-weight:900;color:var(--text-primary);margin-bottom:0.75rem;">
            Payment Failed
        </h1>

        <p style="color:var(--text-secondary);margin-bottom:2rem;line-height:1.6;">
            Your payment could not be completed.
            <?php if (!empty($reason)): ?>
            <br><strong><?= e($reason) ?></strong>
            <?php endif; ?>
        </p>

        <div class="alert alert-info" style="text-align:left;margin-bottom:2rem;">
            <span class="alert-icon"><i data-lucide="info"></i></span>
            <div style="font-size:var(--font-size-sm);">
                <strong>Common reasons for failure:</strong>
                <ul style="margin-top:0.5rem;padding-left:1.25rem;color:var(--text-secondary);line-height:1.8;">
                    <li>Insufficient account balance</li>
                    <li>Card declined by your bank</li>
                    <li>Transaction timeout — try again</li>
                    <li>Incorrect card details</li>
                </ul>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:0.75rem;max-width:320px;margin:0 auto;">
            <a href="<?= SITE_URL ?>/vendor/payment"
               class="btn btn-primary btn-full"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i> Try Again
            </a>
            <a href="<?= SITE_URL ?>/vendor/subscription"
               class="btn btn-outline-primary btn-full"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                <i data-lucide="clipboard" style="width:16px;height:16px;"></i> View Subscription
            </a>
            <a href="<?= SITE_URL ?>/contact"
               class="btn btn-outline-primary btn-full"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                <i data-lucide="mail" style="width:16px;height:16px;"></i> Contact Support
            </a>
        </div>

        <?php if (!empty($reference)): ?>
        <p style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:1.5rem;">
            Reference: <span style="font-family:monospace;"><?= e($reference) ?></span>
        </p>
        <?php endif; ?>

    </div>
</div>