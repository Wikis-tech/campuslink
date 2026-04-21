﻿<?php defined('CAMPUSLINK') or die(); ?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Subscription</h1>
        <p class="dashboard-page-subtitle">Manage your listing plan and billing</p>
    </div>
</div>

<!-- Current Subscription Card -->
<?php if (isset($subscription) && $subscription): ?>
<div class="subscription-details-card">
    <div style="position:relative;z-index:2;">
        <div style="font-size:var(--font-size-xs);text-transform:uppercase;letter-spacing:0.08em;
                    color:rgba(255,255,255,0.7);margin-bottom:0.5rem;">
            Current Plan
        </div>
        <div class="sub-plan-name">
            <?= ucfirst($vendor['plan_type']) ?> Plan
        </div>
        <div class="sub-plan-amount">
            <?= CampusLink::formatCurrency($subscription['amount']) ?> / semester
        </div>
        <div class="sub-detail-row">
            <div class="sub-detail-item">
                <div class="sub-detail-label">Status</div>
                <div class="sub-detail-value">
                    <?= $subscription['days_left'] > 0 ? '<i data-lucide="check-circle" class="status-icon" aria-hidden="true"></i> Active' : '<i data-lucide="x-circle" class="status-icon" aria-hidden="true"></i> Expired' ?>
                </div>
            </div>
            <div class="sub-detail-item">
                <div class="sub-detail-label">Expires</div>
                <div class="sub-detail-value">
                    <?= date('d M Y', strtotime($subscription['expiry_date'])) ?>
                </div>
            </div>
            <div class="sub-detail-item">
                <div class="sub-detail-label">Days Remaining</div>
                <div class="sub-detail-value">
                    <?= max(0, (int)$subscription['days_left']) ?> days
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Renew Button -->
<?php if ($subscription['days_left'] <= 30): ?>
<div style="margin-bottom:1.5rem;">
    <a href="<?= SITE_URL ?>/vendor/payment?plan=<?= e($vendor['plan_type']) ?>"
       class="btn btn-primary">
        ðŸ”„ Renew <?= ucfirst($vendor['plan_type']) ?> Plan â†’
    </a>
</div>
<?php endif; ?>

<?php else: ?>
<div class="alert alert-error" style="margin-bottom:1.5rem;">
    <span class="alert-icon"><i data-lucide="x-circle" class="alert-icon" aria-hidden="true"></i></span>
    <div>
        <strong>No active subscription.</strong>
        Your profile is not visible to students.
        Subscribe to get listed in the directory.
    </div>
</div>
<?php endif; ?>

<!-- Change Plan -->
<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-header">
        <div class="dash-card-title">
            <span class="dash-card-title-icon">ðŸ“‹</span>
            Change Your Plan
        </div>
    </div>
    <div class="dash-card-body">

        <div class="plan-change-grid">
            <?php foreach (($plans ?? []) as $plan):
                $isCurrent = $plan['plan_type'] === $vendor['plan_type'];
                $isUpgrade = planRank($plan['plan_type']) > planRank($vendor['plan_type']);
                $action    = $isUpgrade ? 'upgrade' : 'downgrade';
            ?>
            <div class="plan-change-card <?= $isCurrent ? 'current-plan' : '' ?>"
                 data-plan="<?= e($plan['plan_type']) ?>"
                 data-action="<?= $isCurrent ? '' : $action ?>"
                 style="<?= $isCurrent ? 'pointer-events:none;' : 'cursor:pointer;' ?>">

                <div class="plan-name" style="font-size:var(--font-size-sm);font-weight:700;
                            color:var(--text-secondary);text-transform:uppercase;
                            letter-spacing:0.06em;margin-bottom:0.75rem;">
                    <?= ucfirst($plan['plan_type']) ?>
                </div>
                <div class="plan-select-price">
                    â‚¦<?= number_format($plan['amount_naira']) ?>
                    <small>/semester</small>
                </div>
                <div class="plan-select-features" style="margin-top:0.75rem;">
                    <?php
                    $features = is_string($plan['features'])
                        ? json_decode($plan['features'], true)
                        : ($plan['features'] ?? []);
                    foreach (array_slice($features, 0, 3) as $f):
                    ?>
                    <div class="plan-select-feature">
                        <?= e(is_array($f) ? ($f['text'] ?? '') : $f) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Plan Change Confirmation -->
        <div class="plan-change-confirm-text" style="display:none;font-size:var(--font-size-sm);
             color:var(--text-secondary);background:var(--bg);border-radius:var(--radius-lg);
             padding:1rem;border:1px solid var(--divider);margin-bottom:1rem;">
        </div>

        <form action="<?= SITE_URL ?>/vendor/subscription" method="POST" id="plan-form">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <input type="hidden" name="action" value="<?= !empty($subscription) ? 'upgrade' : 'subscribe' ?>">
            <input type="hidden" name="plan"   value="">
            <button type="submit"
                    class="btn btn-primary plan-change-confirm-btn"
                    disabled
                    style="width:100%;">
                Select a plan above
            </button>
        </form>

    </div>
</div>

<!-- Subscription History -->
<?php if (!empty($history)): ?>
<div class="dash-card">
    <div class="dash-card-header">
        <div class="dash-card-title">
            <span class="dash-card-title-icon">ðŸ“œ</span>
            Subscription History
        </div>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Start Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><strong><?= ucfirst(e($h['plan_type'])) ?></strong></td>
                    <td class="payment-amount">
                        â‚¦<?= number_format($h['amount'] / 100, 2) ?>
                    </td>
                    <td><?= date('d M Y', strtotime($h['start_date'])) ?></td>
                    <td><?= date('d M Y', strtotime($h['expiry_date'])) ?></td>
                    <td>
                        <span class="badge <?= $h['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                            <?= ucfirst($h['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
function planRank(string $plan): int {
    return match($plan) { 'basic' => 1, 'premium' => 2, 'featured' => 3, default => 0 };
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const planCards = document.querySelectorAll('.plan-change-card');
    const confirmText = document.querySelector('.plan-change-confirm-text');
    const confirmBtn = document.querySelector('.plan-change-confirm-btn');
    const planForm = document.getElementById('plan-form');
    const actionInput = planForm.querySelector('input[name="action"]');
    const planInput = planForm.querySelector('input[name="plan"]');

    planCards.forEach(card => {
        if (card.dataset.action) {
            card.addEventListener('click', function() {
                // Remove previous selection
                planCards.forEach(c => c.style.border = '');
                
                // Highlight selected
                this.style.border = '2px solid var(--accent-blue)';
                
                const planType = this.dataset.plan;
                const action = this.dataset.action;
                const planName = this.querySelector('.plan-name')?.textContent?.trim() || 'plan';
                
                // Update form values
                planInput.value = planType;
                
                // Show confirmation
                confirmText.textContent = `You are selecting the ${planName}. Click below to proceed.`;
                confirmText.style.display = 'block';
                
                // Enable button
                confirmBtn.disabled = false;
                confirmBtn.textContent = action === 'upgrade' ? `Upgrade to ${planName} Plan` : 
                                        action === 'downgrade' ? `Downgrade to ${planName} Plan` :
                                        `Subscribe to ${planName} Plan`;
            });
        }
    });
});
</script>
