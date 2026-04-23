﻿<?php defined('CAMPUSLINK') or die(); ?>

<?php
if (!function_exists('planRank')) {
    function planRank(string $plan): int {
        return match($plan) { 'basic' => 1, 'premium' => 2, 'featured' => 3, default => 0 };
    }
}

/* safely parse features — handles string JSON, array of strings, array of arrays */
function parsePlanFeatures($raw): array {
    if (empty($raw)) return [];
    $arr = is_string($raw) ? json_decode($raw, true) : (array)$raw;
    if (!is_array($arr)) return [];
    $out = [];
    foreach ($arr as $f) {
        $text = is_array($f) ? ($f['text'] ?? $f['label'] ?? '') : (string)$f;
        $text = trim($text);
        if ($text !== '' && $text !== '1') $out[] = $text; // skip corrupt entries
    }
    return $out;
}

$planMeta = [
    'basic'    => ['icon' => 'zap',   'badge' => ''],
    'premium'  => ['icon' => 'star',  'badge' => 'Most Popular'],
    'featured' => ['icon' => 'crown', 'badge' => 'Best Value'],
];
$currentPlanType = $vendor['plan_type'] ?? 'basic';
?>

<style>
:root {
    --sub-blue:      #0b3d91;
    --sub-blue-dark: #082d6a;
    --sub-blue-lite: #1e5bb8;
    --sub-green:     #1ea952;
    --sub-green-dk:  #167a3d;
    --sub-amber:     #f59e0b;
    --sub-red:       #ef4444;
    --sub-text:      #1f2937;
    --sub-muted:     #6b7280;
    --sub-divider:   #e9ecef;
    --sub-bg:        #f4f6fb;
    --sub-white:     #ffffff;
    --sub-radius:    16px;
    --sub-radius-sm: 10px;
    --sub-shadow:    0 4px 24px rgba(11,61,145,0.10);
    --sub-shadow-lg: 0 16px 48px rgba(11,61,145,0.16);
    --ease-expo:     cubic-bezier(0.16,1,0.3,1);
    --ease-back:     cubic-bezier(0.34,1.56,0.64,1);
}

.sub-page { max-width: 900px; padding-bottom: 3rem; }

.sub-page-header {
    display: flex; align-items: flex-start; gap: 1.25rem;
    margin-bottom: 2rem;
    animation: subFadeUp .55s var(--ease-expo) both;
}
.sub-header-icon {
    width: 52px; height: 52px; flex-shrink: 0; border-radius: 14px;
    background: linear-gradient(135deg,var(--sub-blue-lite),var(--sub-blue));
    display: flex; align-items: center; justify-content: center;
    color: #fff; box-shadow: 0 6px 20px rgba(11,61,145,0.28);
}
.sub-page-title  { font-size:1.65rem; font-weight:800; color:var(--sub-text); letter-spacing:-.03em; line-height:1.1; }
.sub-page-sub    { font-size:.875rem; color:var(--sub-muted); margin-top:.25rem; }

/* ── banner ── */
.sub-status-banner {
    border-radius: var(--sub-radius); padding: 1.5rem 1.75rem;
    margin-bottom: 1.75rem;
    display: flex; align-items: center; gap: 1.25rem;
    position: relative; overflow: hidden;
    animation: subFadeUp .55s .08s var(--ease-expo) both;
}
.sub-status-banner.active-banner {
    background: linear-gradient(135deg,var(--sub-blue) 0%,var(--sub-blue-lite) 60%,#1a4fa8 100%);
    color: #fff; box-shadow: var(--sub-shadow-lg);
}
.sub-status-banner.inactive-banner {
    background: #fff3f3; border: 1px solid #fecaca; color: var(--sub-red);
}
.sub-status-banner::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 80% 50%, rgba(255,255,255,0.07) 0%, transparent 60%);
    pointer-events: none;
}
.sub-banner-icon {
    width: 48px; height: 48px; flex-shrink: 0; border-radius: 12px;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(8px);
}
.inactive-banner .sub-banner-icon { background: rgba(239,68,68,0.12); }
.sub-banner-body  { flex: 1; min-width: 0; }
.sub-banner-label { font-size:.7rem; text-transform:uppercase; letter-spacing:.1em; opacity:.7; margin-bottom:.2rem; }
.sub-banner-plan  { font-size:1.35rem; font-weight:800; letter-spacing:-.02em; line-height:1.1; }
.sub-banner-amount{ font-size:.875rem; opacity:.75; margin-top:.15rem; }
.inactive-banner .sub-banner-plan,
.inactive-banner .sub-banner-label,
.inactive-banner .sub-banner-amount { color: var(--sub-red); }
.inactive-banner .sub-banner-label,
.inactive-banner .sub-banner-amount { opacity:.7; }

.sub-stats-row { display:flex; gap:1.5rem; flex-wrap:wrap; margin-top:1.1rem; }
.sub-stat       { display:flex; flex-direction:column; }
.sub-stat-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.08em; opacity:.65; }
.sub-stat-value { font-size:.95rem; font-weight:700; margin-top:.1rem; }
.sub-stat-divider{ width:1px; background:rgba(255,255,255,0.2); align-self:stretch; }

/* renew strip */
.sub-renew-strip {
    background: linear-gradient(90deg,rgba(30,169,82,0.08),rgba(30,169,82,0.04));
    border: 1px solid rgba(30,169,82,0.22); border-radius: var(--sub-radius-sm);
    padding: .875rem 1.25rem;
    display: flex; align-items: center; gap: .875rem;
    margin-bottom: 1.5rem;
    animation: subFadeUp .55s .12s var(--ease-expo) both;
}
.sub-renew-strip-text { flex:1; font-size:.875rem; color:var(--sub-text); }
.sub-renew-strip-text strong { color:var(--sub-green-dk); }

/* section title */
.sub-section-title {
    font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.12em;
    color:var(--sub-muted); display:flex; align-items:center; gap:.5rem;
    margin-bottom:1.1rem;
}
.sub-section-title::after { content:''; flex:1; height:1px; background:var(--sub-divider); }

/* ── plan cards ── */
.sub-plans-grid {
    display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap: 1rem; margin-bottom: 1.5rem;
    animation: subFadeUp .55s .18s var(--ease-expo) both;
}
.sub-plan-card {
    position:relative; border-radius:var(--sub-radius);
    background:var(--sub-white); border:2px solid var(--sub-divider);
    padding:1.5rem; cursor:pointer;
    transition: transform .35s var(--ease-expo), box-shadow .35s var(--ease-expo), border-color .25s;
    overflow:hidden; outline:none;
}
.sub-plan-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    opacity:0; transition:opacity .25s;
}
.sub-plan-card.basic-card::before    { background:linear-gradient(90deg,var(--sub-blue-lite),var(--sub-blue)); }
.sub-plan-card.premium-card::before  { background:linear-gradient(90deg,var(--sub-green),#0b7a3e); }
.sub-plan-card.featured-card::before { background:linear-gradient(90deg,var(--sub-amber),#b45309); }

.sub-plan-card:hover,
.sub-plan-card:focus-visible {
    transform: translateY(-6px) scale(1.012);
    box-shadow: var(--sub-shadow-lg);
}
.sub-plan-card.plan-selected {
    border-color: var(--sub-blue) !important;
    box-shadow: 0 0 0 3px rgba(11,61,145,0.13), var(--sub-shadow-lg);
    transform: translateY(-6px) scale(1.012);
}
.sub-plan-card.plan-selected::before { opacity:1; }

/* current plan — still clickable but visually marked */
.sub-plan-card.current-plan {
    background: linear-gradient(135deg,rgba(11,61,145,0.04),rgba(30,91,184,0.06));
    border-color: rgba(11,61,145,0.25);
}
.sub-plan-card.current-plan::before { opacity:1; }

/* icon */
.sub-plan-icon-wrap {
    width:44px; height:44px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    margin-bottom:1rem; transition:transform .35s var(--ease-back);
}
.basic-card    .sub-plan-icon-wrap { background:rgba(11,61,145,0.10); color:var(--sub-blue); }
.premium-card  .sub-plan-icon-wrap { background:rgba(30,169,82,0.10); color:var(--sub-green); }
.featured-card .sub-plan-icon-wrap { background:rgba(245,158,11,0.12); color:var(--sub-amber); }
.sub-plan-card:hover .sub-plan-icon-wrap,
.sub-plan-card.plan-selected .sub-plan-icon-wrap { transform:scale(1.15) rotate(-5deg); }

.sub-plan-badge {
    position:absolute; top:1rem; right:1rem;
    font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em;
    padding:.2rem .55rem; border-radius:20px; color:#fff;
}
.premium-card  .sub-plan-badge { background:var(--sub-green); }
.featured-card .sub-plan-badge { background:var(--sub-amber); color:#7c2d12; }

.sub-plan-type-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.1em; font-weight:700; color:var(--sub-muted); margin-bottom:.25rem; }
.sub-plan-price      { font-size:1.6rem; font-weight:800; color:var(--sub-text); letter-spacing:-.03em; line-height:1; }
.sub-plan-price small{ font-size:.75rem; font-weight:500; color:var(--sub-muted); letter-spacing:0; }

.sub-plan-features { margin-top:1rem; display:flex; flex-direction:column; gap:.5rem; }
.sub-plan-feature  { display:flex; align-items:flex-start; gap:.5rem; font-size:.8rem; color:var(--sub-muted); line-height:1.4; }
.sub-plan-feature-icon { flex-shrink:0; margin-top:1px; color:var(--sub-green); }

.current-plan-tag {
    display:inline-flex; align-items:center; gap:.3rem;
    font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em;
    color:var(--sub-blue); background:rgba(11,61,145,0.08);
    padding:.25rem .6rem; border-radius:20px; margin-top:.75rem;
}

/* confirm panel */
.sub-confirm-panel {
    display:none; background:#fff; border:1px solid var(--sub-divider);
    border-radius:var(--sub-radius); padding:1.25rem 1.5rem;
    margin-bottom:1.25rem; box-shadow:var(--sub-shadow);
    animation: subConfirmIn .4s var(--ease-expo) both;
}
@keyframes subConfirmIn {
    from { opacity:0; transform:translateY(8px) scale(.98); }
    to   { opacity:1; transform:none; }
}
.sub-confirm-inner { display:flex; align-items:center; gap:1rem; }
.sub-confirm-dot {
    width:10px; height:10px; border-radius:50%;
    background:var(--sub-blue); flex-shrink:0;
    animation: confirmDotPulse 1.8s ease-in-out infinite;
}
@keyframes confirmDotPulse {
    0%,100% { box-shadow:0 0 0 0 rgba(11,61,145,.5); }
    50%      { box-shadow:0 0 0 7px rgba(11,61,145,0); }
}
.sub-confirm-text { font-size:.875rem; color:var(--sub-text); line-height:1.5; }
.sub-confirm-text strong { color:var(--sub-blue); }

/* submit button */
.sub-submit-btn {
    width:100%; padding:.9rem 1.5rem; border-radius:var(--sub-radius-sm);
    border:none; font-size:.95rem; font-weight:700; letter-spacing:.01em;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.6rem;
    transition: transform .3s var(--ease-back), box-shadow .3s var(--ease-expo), filter .2s;
    position:relative; overflow:hidden;
    background: linear-gradient(135deg,var(--sub-blue-lite),var(--sub-blue));
    color:#fff; box-shadow:0 4px 16px rgba(11,61,145,.3);
    animation: subFadeUp .55s .28s var(--ease-expo) both;
}
.sub-submit-btn:disabled {
    background:var(--sub-divider) !important; color:var(--sub-muted) !important;
    cursor:not-allowed !important; transform:none !important; box-shadow:none !important;
}
.sub-submit-btn:not(:disabled):hover {
    transform:translateY(-3px) scale(1.01);
    box-shadow:0 10px 30px rgba(11,61,145,.38); filter:brightness(1.06);
}
.sub-submit-btn:not(:disabled):active { transform:scale(.98); }
.sub-submit-btn::after {
    content:''; position:absolute; top:0; left:-75%; width:50%; height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);
    transform:skewX(-20deg); animation:btnShimmer 3s ease-in-out infinite;
}
.sub-submit-btn:disabled::after { display:none; }
@keyframes btnShimmer {
    0%   { left:-75%; } 60%  { left:125%; } 100% { left:125%; }
}

/* history */
.sub-history-card {
    background:var(--sub-white); border-radius:var(--sub-radius);
    border:1px solid var(--sub-divider); overflow:hidden;
    box-shadow:var(--sub-shadow);
    animation: subFadeUp .55s .32s var(--ease-expo) both;
}
.sub-history-header {
    padding:1.1rem 1.5rem; border-bottom:1px solid var(--sub-divider);
    display:flex; align-items:center; gap:.65rem;
    font-size:.9rem; font-weight:700; color:var(--sub-text);
}
.sub-history-icon {
    width:32px; height:32px; border-radius:8px;
    background:rgba(11,61,145,0.08);
    display:flex; align-items:center; justify-content:center; color:var(--sub-blue);
}
.sub-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.sub-table thead tr { background:rgba(11,61,145,0.03); }
.sub-table th {
    text-align:left; padding:.75rem 1.25rem;
    font-size:.65rem; text-transform:uppercase; letter-spacing:.1em;
    color:var(--sub-muted); font-weight:700; white-space:nowrap;
}
.sub-table td { padding:.875rem 1.25rem; border-top:1px solid var(--sub-divider); color:var(--sub-text); }
.sub-table tbody tr:hover td { background:rgba(11,61,145,0.025); }
.sub-table .plan-cell   { font-weight:700; }
.sub-table .amount-cell { font-weight:700; color:var(--sub-blue); }
.hist-badge {
    display:inline-flex; align-items:center; gap:.3rem;
    padding:.22rem .65rem; border-radius:20px;
    font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
}
.hist-badge.active    { background:rgba(30,169,82,0.10); color:var(--sub-green-dk); }
.hist-badge.expired   { background:rgba(107,114,128,0.10); color:var(--sub-muted); }
.hist-badge.cancelled { background:rgba(239,68,68,0.10); color:var(--sub-red); }

@keyframes subFadeUp {
    from { opacity:0; transform:translateY(22px); }
    to   { opacity:1; transform:none; }
}

@media (max-width:600px) {
    .sub-plans-grid { grid-template-columns:1fr; }
    .sub-stats-row  { gap:1rem; }
    .sub-stat-divider { display:none; }
    .sub-table th:nth-child(3),
    .sub-table td:nth-child(3) { display:none; }
}
</style>

<div class="sub-page">

    <div class="sub-page-header">
        <div class="sub-header-icon">
            <i data-lucide="credit-card" style="width:24px;height:24px;" aria-hidden="true"></i>
        </div>
        <div>
            <div class="sub-page-title">Subscription</div>
            <div class="sub-page-sub">Manage your listing plan and billing</div>
        </div>
    </div>

    <?php if (!empty($subscription) && $subscription): ?>
    <?php
        $daysLeft  = max(0, (int)($subscription['days_left'] ?? 0));
        $isActive  = $daysLeft > 0;
        $isWarning = $isActive && $daysLeft <= 30;
        $planAmt   = CampusLink::formatCurrency($subscription['amount']);
        $expDate   = date('d M Y', strtotime($subscription['expiry_date']));
        $startDate = !empty($subscription['start_date']) ? date('d M Y', strtotime($subscription['start_date'])) : '—';
    ?>
    <div class="sub-status-banner <?= $isActive ? 'active-banner' : 'inactive-banner' ?>">
        <div class="sub-banner-icon">
            <i data-lucide="<?= $isActive ? 'shield-check' : 'shield-x' ?>" style="width:22px;height:22px;" aria-hidden="true"></i>
        </div>
        <div class="sub-banner-body">
            <div class="sub-banner-label">Current Plan</div>
            <div class="sub-banner-plan">
                <?= htmlspecialchars(ucfirst($currentPlanType)) ?> Plan
                <?php if (!$isActive): ?> &mdash; <span style="font-size:1rem;font-weight:600;opacity:.8;">Expired</span><?php endif; ?>
            </div>
            <div class="sub-banner-amount"><?= $planAmt ?> / semester</div>
            <?php if ($isActive): ?>
            <div class="sub-stats-row">
                <div class="sub-stat">
                    <span class="sub-stat-label">Status</span>
                    <span class="sub-stat-value">Active</span>
                </div>
                <div class="sub-stat-divider"></div>
                <div class="sub-stat">
                    <span class="sub-stat-label">Expires</span>
                    <span class="sub-stat-value"><?= $expDate ?></span>
                </div>
                <div class="sub-stat-divider"></div>
                <div class="sub-stat">
                    <span class="sub-stat-label">Days Left</span>
                    <span class="sub-stat-value"><?= $daysLeft ?> days</span>
                </div>
                <?php if ($startDate !== '—'): ?>
                <div class="sub-stat-divider"></div>
                <div class="sub-stat">
                    <span class="sub-stat-label">Started</span>
                    <span class="sub-stat-value"><?= $startDate ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isWarning): ?>
    <div class="sub-renew-strip">
        <i data-lucide="alert-triangle" style="width:18px;height:18px;color:#b45309;flex-shrink:0;" aria-hidden="true"></i>
        <div class="sub-renew-strip-text">
            <strong>Your plan expires in <?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?>.</strong>
            Renew now to keep your profile visible.
        </div>
        <a href="<?= SITE_URL ?>/vendor/payment?plan=<?= htmlspecialchars($currentPlanType) ?>"
           class="btn btn-primary" style="white-space:nowrap;flex-shrink:0;font-size:.85rem;padding:.55rem 1rem;">
            <i data-lucide="refresh-cw" style="width:14px;height:14px;" aria-hidden="true"></i>
            Renew Now
        </a>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="sub-status-banner inactive-banner">
        <div class="sub-banner-icon">
            <i data-lucide="shield-x" style="width:22px;height:22px;" aria-hidden="true"></i>
        </div>
        <div class="sub-banner-body">
            <div class="sub-banner-label">Subscription Status</div>
            <div class="sub-banner-plan">No Active Plan</div>
            <div class="sub-banner-amount">Your profile is not visible to students right now.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- PLAN SELECTION -->
    <div class="sub-section-title">
        <i data-lucide="layers" style="width:14px;height:14px;" aria-hidden="true"></i>
        <?= !empty($subscription) ? 'Change Your Plan' : 'Choose a Plan' ?>
    </div>

    <div class="sub-plans-grid" id="plansGrid" role="radiogroup" aria-label="Select a plan">
        <?php foreach (($plans ?? []) as $plan):
            $pType     = $plan['plan_type'] ?? '';
            $pAmount   = $plan['amount_naira'] ?? 0;
            $features  = parsePlanFeatures($plan['features'] ?? []);
            $isCurrent = $pType === $currentPlanType;
            $rank      = planRank($pType);
            $curRank   = planRank($currentPlanType);
            $action    = $rank > $curRank ? 'upgrade' : ($rank < $curRank ? 'downgrade' : 'renew');
            $meta      = $planMeta[$pType] ?? ['icon' => 'zap', 'badge' => ''];
        ?>
        <div class="sub-plan-card <?= $pType ?>-card <?= $isCurrent ? 'current-plan' : '' ?>"
             data-plan="<?= htmlspecialchars($pType) ?>"
             data-action="<?= htmlspecialchars($action) ?>"
             data-price="<?= number_format($pAmount) ?>"
             data-is-current="<?= $isCurrent ? '1' : '0' ?>"
             tabindex="0"
             role="radio"
             aria-checked="false">

            <?php if (!empty($meta['badge'])): ?>
                <div class="sub-plan-badge"><?= htmlspecialchars($meta['badge']) ?></div>
            <?php endif; ?>

            <div class="sub-plan-icon-wrap">
                <i data-lucide="<?= $meta['icon'] ?>" style="width:20px;height:20px;" aria-hidden="true"></i>
            </div>

            <div class="sub-plan-type-label"><?= htmlspecialchars(ucfirst($pType)) ?></div>
            <div class="sub-plan-price">
                &#8358;<?= number_format($pAmount) ?><small>/semester</small>
            </div>

            <?php if (!empty($features)): ?>
            <div class="sub-plan-features">
                <?php foreach (array_slice($features, 0, 4) as $feat): ?>
                <div class="sub-plan-feature">
                    <i data-lucide="check" class="sub-plan-feature-icon" style="width:13px;height:13px;" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($feat) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($isCurrent): ?>
                <div class="current-plan-tag">
                    <i data-lucide="check-circle" style="width:11px;height:11px;" aria-hidden="true"></i>
                    Current Plan
                </div>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    </div>

    <!-- CONFIRM PANEL -->
    <div class="sub-confirm-panel" id="subConfirmPanel" role="status" aria-live="polite">
        <div class="sub-confirm-inner">
            <div class="sub-confirm-dot"></div>
            <div class="sub-confirm-text" id="subConfirmText">
                You selected the <strong id="confirmPlanName"></strong> &mdash;
                <strong id="confirmPlanPrice"></strong> / semester.
                Click below to proceed to payment.
            </div>
        </div>
    </div>

    <!-- FORM — posts to subscription, then redirects to payment -->
    <form action="<?= SITE_URL ?>/vendor/subscription" method="POST" id="subPlanForm">
        <input type="hidden" name="csrf_token"  id="subCsrfToken"  value="<?= CSRF::token() ?>">
        <input type="hidden" name="action"      id="subActionInput" value="subscribe">
        <input type="hidden" name="plan"        id="subPlanInput"   value="">

        <button type="submit" class="sub-submit-btn" id="subSubmitBtn"
                disabled aria-disabled="true">
            <i data-lucide="arrow-right" style="width:18px;height:18px;" aria-hidden="true"></i>
            <span id="subSubmitLabel">Select a plan above to continue</span>
        </button>
    </form>

    <?php if (!empty($history)): ?>
    <div style="margin-top:2rem;">
        <div class="sub-section-title">
            <i data-lucide="clock" style="width:14px;height:14px;" aria-hidden="true"></i>
            Billing History
        </div>
        <div class="sub-history-card">
            <div class="sub-history-header">
                <div class="sub-history-icon">
                    <i data-lucide="receipt" style="width:15px;height:15px;" aria-hidden="true"></i>
                </div>
                Past Subscriptions
            </div>
            <div style="overflow-x:auto;">
                <table class="sub-table" aria-label="Subscription history">
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
                        <?php foreach ($history as $h):
                            $hStatus = strtolower($h['status'] ?? 'expired');
                        ?>
                        <tr>
                            <td class="plan-cell"><?= htmlspecialchars(ucfirst($h['plan_type'] ?? '')) ?></td>
                            <td class="amount-cell">&#8358;<?= number_format(($h['amount'] ?? 0) / 100, 2) ?></td>
                            <td><?= date('d M Y', strtotime($h['start_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($h['expiry_date'])) ?></td>
                            <td>
                                <span class="hist-badge <?= htmlspecialchars($hStatus) ?>">
                                    <i data-lucide="<?= $hStatus === 'active' ? 'check-circle' : ($hStatus === 'cancelled' ? 'x-circle' : 'clock') ?>"
                                       style="width:10px;height:10px;" aria-hidden="true"></i>
                                    <?= htmlspecialchars(ucfirst($hStatus)) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
(function () {
    'use strict';

    if (window.lucide) window.lucide.createIcons();

    const grid         = document.getElementById('plansGrid');
    const confirmPanel = document.getElementById('subConfirmPanel');
    const confirmName  = document.getElementById('confirmPlanName');
    const confirmPrice = document.getElementById('confirmPlanPrice');
    const actionInput  = document.getElementById('subActionInput');
    const planInput    = document.getElementById('subPlanInput');
    const submitBtn    = document.getElementById('subSubmitBtn');
    const submitLabel  = document.getElementById('subSubmitLabel');
    const csrfInput    = document.getElementById('subCsrfToken');
    if (!grid) return;

    const cards = [...grid.querySelectorAll('.sub-plan-card')];

    /* ── CSRF auto-refresh every 10 min to prevent session timeout redirect ── */
    function refreshCsrf() {
        fetch('<?= SITE_URL ?>/vendor/subscription?csrf_refresh=1', {
            method: 'GET', credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => { if (data.token && csrfInput) csrfInput.value = data.token; })
        .catch(() => {}); // silent fail
    }
    setInterval(refreshCsrf, 10 * 60 * 1000); // every 10 minutes

    /* ── keyboard ── */
    cards.forEach(card => {
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectCard(card);
            }
        });
        card.addEventListener('click', () => selectCard(card));
    });

    function selectCard(card) {
        cards.forEach(c => {
            c.classList.remove('plan-selected');
            c.setAttribute('aria-checked', 'false');
        });

        card.classList.add('plan-selected');
        card.setAttribute('aria-checked', 'true');

        const pType     = card.dataset.plan;
        const pAction   = card.dataset.action;   // upgrade / downgrade / renew
        const pPrice    = card.dataset.price;
        const isCurrent = card.dataset.isCurrent === '1';
        const pLabel    = card.querySelector('.sub-plan-type-label')?.textContent?.trim() || pType;

        planInput.value   = pType;
        actionInput.value = pAction || 'subscribe';

        /* confirm panel */
        if (confirmName)  confirmName.textContent  = pLabel + ' Plan';
        if (confirmPrice) confirmPrice.textContent = '₦' + pPrice;

        let confirmMsg;
        if (isCurrent && pAction === 'renew') {
            confirmMsg = `You are renewing your <strong>${pLabel} Plan</strong> &mdash; <strong>&#8358;${pPrice}</strong> / semester.`;
        } else if (pAction === 'upgrade') {
            confirmMsg = `You are upgrading to the <strong>${pLabel} Plan</strong> &mdash; <strong>&#8358;${pPrice}</strong> / semester.`;
        } else if (pAction === 'downgrade') {
            confirmMsg = `You are downgrading to the <strong>${pLabel} Plan</strong> &mdash; <strong>&#8358;${pPrice}</strong> / semester.`;
        } else {
            confirmMsg = `You selected the <strong>${pLabel} Plan</strong> &mdash; <strong>&#8358;${pPrice}</strong> / semester.`;
        }
        const confirmTextEl = document.getElementById('subConfirmText');
        if (confirmTextEl) confirmTextEl.innerHTML = confirmMsg + ' Click below to proceed.';
        confirmPanel.style.display = 'block';

        /* button label */
        const verb = pAction === 'upgrade'   ? 'Upgrade to'   :
                     pAction === 'downgrade'  ? 'Downgrade to' :
                     pAction === 'renew'      ? 'Renew'        : 'Subscribe to';
        const suffix = pAction === 'renew' ? pLabel + ' Plan' : pLabel + ' Plan';
        submitLabel.textContent = verb + ' ' + suffix;
        submitBtn.disabled      = false;
        submitBtn.setAttribute('aria-disabled', 'false');

        if (window.innerWidth < 640) {
            setTimeout(() => submitBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 80);
        }
    }

    if (window.lucide) window.lucide.createIcons();
})();
</script>