<?php defined('CAMPUSLINK') or die(); ?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Complete Payment</h1>
        <p class="dashboard-page-subtitle">
            Secure payment powered by Paystack
        </p>
    </div>
</div>

<div style="max-width:740px;margin:0 auto;">

    <!-- Plan Selection -->
    <div class="dash-card" style="margin-bottom:1.5rem;">
        <div class="dash-card-header">
            <div class="dash-card-title">
                <span class="dash-card-title-icon"><i data-lucide="clipboard"></i></span>
                Select a Plan
            </div>
        </div>
        <div class="dash-card-body">
            <div class="plan-select-grid">
                <?php foreach ($plans as $plan):
                    $isSelected = $plan['plan_type'] === ($selectedPlan ?? 'basic');
                ?>
                <div class="plan-select-card <?= $isSelected ? 'selected' : '' ?>
                            <?= $plan['plan_type'] === 'premium' ? 'popular-plan' : '' ?>"
                     data-plan="<?= e($plan['plan_type']) ?>"
                     data-amount="<?= (int)$plan['amount'] ?>">

                    <div class="plan-select-name"><?= ucfirst($plan['plan_type']) ?></div>
                    <div class="plan-select-price">
                        ₦<?= number_format($plan['amount_naira']) ?>
                    </div>
                    <div class="plan-select-period">per semester (180 days)</div>

                    <div class="plan-select-features">
                        <?php
                        $features = is_string($plan['features'])
                            ? json_decode($plan['features'], true)
                            : ($plan['features'] ?? []);
                        foreach ($features as $f):
                            $text = is_array($f) ? ($f['text'] ?? '') : $f;
                        ?>
                        <div class="plan-select-feature"><?= e($text) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="plan_type" id="selectedPlan"
                   value="<?= e($selectedPlan ?? 'basic') ?>">
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="dash-card" style="margin-bottom:1.5rem;">
        <div class="dash-card-header">
            <div class="dash-card-title">
                <span class="dash-card-title-icon"><i data-lucide="file-text"></i></span>
                Payment Summary
            </div>
        </div>
        <div class="dash-card-body">
            <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.25rem;">
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Business</span>
                    <strong><?= e($vendor['business_name']) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Plan</span>
                    <strong class="payment-summary-plan">
                        <?= ucfirst($selectedPlan ?? 'basic') ?>
                    </strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:var(--font-size-sm);">
                    <span style="color:var(--text-muted);">Duration</span>
                    <strong>180 days (1 semester)</strong>
                </div>
                <div style="border-top:1px solid var(--divider);padding-top:0.75rem;
                            display:flex;justify-content:space-between;">
                    <span style="font-weight:700;font-size:var(--font-size-base);">Total</span>
                    <span class="payment-summary-amount"
                          style="font-size:1.3rem;font-weight:900;color:var(--primary);">
                        <?php
                        $defaultPlan = array_filter($plans, fn($p) => $p['plan_type'] === ($selectedPlan ?? 'basic'));
                        $defaultPlan = reset($defaultPlan);
                        echo $defaultPlan ? '₦' . number_format($defaultPlan['amount_naira']) : '—';
                        ?>
                    </span>
                </div>
            </div>

            <!-- Payment Notice -->
            <div class="payment-notice" style="display:flex;align-items:flex-start;gap:0.5rem;">
                <i data-lucide="alert-triangle" style="width:16px;height:16px;flex-shrink:0;margin-top:0.1rem;"></i>
                <div>
                    <strong>Important — Read Before Paying</strong>
                    Payment is processed securely through Paystack.
                    Your subscription activates within minutes of successful payment.
                    <strong>Refunds are only issued in cases of duplicate payment or technical error</strong>
                    — see our <a href="<?= SITE_URL ?>/refund-policy" target="_blank">Refund Policy</a>.
                </div>
            </div>

            <!-- Pay Button -->
            <button class="btn btn-primary btn-full paystack-pay-btn"
                    style="font-size:1.1rem;padding:1rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;"
                    data-vendor-id="<?= (int)$vendor['id'] ?>"
                    data-vendor-type="<?= e($vendor['vendor_type']) ?>"
                    data-email="<?= e($vendor['school_email'] ?? $vendor['working_email'] ?? '') ?>"
                    data-paystack-key="<?= e(PAYSTACK_PUBLIC_KEY) ?>">
                <i data-lucide="lock" style="width:18px;height:18px;"></i> Pay Securely with Paystack
            </button>

            <p style="text-align:center;font-size:var(--font-size-xs);
                      color:var(--text-muted);margin-top:0.75rem;
                      display:flex;align-items:center;justify-content:center;gap:0.35rem;">
                <i data-lucide="shield" style="width:13px;height:13px;"></i>
                Secured by Paystack · Card, Bank Transfer, USSD supported
            </p>
        </div>
    </div>

    <div class="disclaimer-box">
        <span class="disclaimer-icon"><i data-lucide="info"></i></span>
        <div class="disclaimer-text">
            By completing this payment, you agree to the
            <a href="<?= SITE_URL ?>/vendor-terms" target="_blank">Vendor Terms of Service</a>
            and confirm that all information in your profile is accurate and truthful.
            Fraudulent listings will result in immediate suspension without refund.
        </div>
    </div>

</div>

<!-- Paystack SDK -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="<?= SITE_URL ?>/assets/js/paystack.js" defer></script>
<script>
    window.PAYSTACK_PUBLIC_KEY = '<?= e(PAYSTACK_PUBLIC_KEY) ?>';
</script>