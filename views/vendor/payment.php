<?php defined('CAMPUSLINK') or die(); ?>

<?php
if (!function_exists('parsePlanFeaturesPayment')) {
    function parsePlanFeaturesPayment($raw): array {
        if (empty($raw)) return [];
        $arr = is_string($raw) ? json_decode($raw, true) : (array)$raw;
        if (!is_array($arr)) return [];
        $out = [];
        foreach ($arr as $f) {
            $text = is_array($f) ? ($f['text'] ?? $f['label'] ?? '') : (string)$f;
            $text = trim($text);
            if ($text !== '' && $text !== '1' && $text !== '0' && !ctype_digit($text)) {
                $out[] = $text;
            }
        }
        return $out;
    }
}

$planMeta = [
    'basic'    => ['icon' => 'zap',   'color' => '#0b3d91', 'bg' => 'rgba(11,61,145,0.08)',  'badge' => '',             'accent' => '#1e5bb8'],
    'premium'  => ['icon' => 'star',  'color' => '#1ea952', 'bg' => 'rgba(30,169,82,0.09)',  'badge' => 'Most Popular', 'accent' => '#167a3d'],
    'featured' => ['icon' => 'crown', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.10)', 'badge' => 'Best Value',   'accent' => '#b45309'],
];

$selectedPlan = $_GET['plan'] ?? $selectedPlan ?? 'basic';

$activePlan = null;
foreach ($plans as $p) {
    if ($p['plan_type'] === $selectedPlan) { $activePlan = $p; break; }
}
if (!$activePlan && !empty($plans)) { $activePlan = $plans[0]; $selectedPlan = $activePlan['plan_type']; }
?>

<style>
:root {
    --pay-blue:       #0b3d91;
    --pay-blue-dk:    #082d6a;
    --pay-blue-lt:    #1e5bb8;
    --pay-green:      #1ea952;
    --pay-green-dk:   #167a3d;
    --pay-amber:      #f59e0b;
    --pay-red:        #ef4444;
    --pay-text:       #1f2937;
    --pay-muted:      #6b7280;
    --pay-divider:    #e9ecef;
    --pay-bg:         #f4f6fb;
    --pay-white:      #ffffff;
    --pay-radius:     18px;
    --pay-radius-sm:  11px;
    --pay-shadow:     0 4px 24px rgba(11,61,145,0.09);
    --pay-shadow-lg:  0 20px 60px rgba(11,61,145,0.16);
    --ease-expo:      cubic-bezier(0.16,1,0.3,1);
    --ease-back:      cubic-bezier(0.34,1.56,0.64,1);
}
.pay-page { max-width: 960px; margin: 0 auto; padding-bottom: 4rem; }
.pay-page-hero {
    background: linear-gradient(135deg, var(--pay-blue) 0%, var(--pay-blue-lt) 55%, #1a4fa8 100%);
    border-radius: var(--pay-radius); padding: 2.25rem 2rem; margin-bottom: 2rem;
    position: relative; overflow: hidden; animation: payFadeUp .5s var(--ease-expo) both;
}
.pay-page-hero::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse at 75% 0%, rgba(255,255,255,0.08) 0%, transparent 60%),
                radial-gradient(circle at 10% 90%, rgba(30,169,82,0.12) 0%, transparent 50%);
    pointer-events: none;
}
.pay-page-hero-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 1.25rem; }
.pay-hero-icon {
    width: 56px; height: 56px; border-radius: 15px; flex-shrink: 0;
    background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
    display: flex; align-items: center; justify-content: center; color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}
.pay-hero-title { font-size: 1.75rem; font-weight: 900; color: #fff; letter-spacing: -.035em; line-height: 1.05; }
.pay-hero-sub { font-size: .875rem; color: rgba(255,255,255,0.72); margin-top: .3rem; display: flex; align-items: center; gap: .4rem; }
.pay-hero-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.22);
    border-radius: 20px; padding: .25rem .7rem; font-size: .7rem; font-weight: 700;
    color: rgba(255,255,255,0.9); backdrop-filter: blur(6px); margin-top: .5rem;
}
.pay-layout { display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem; align-items: start; }
@media (max-width: 820px) {
    .pay-layout { grid-template-columns: 1fr; }
    .pay-sticky-sidebar { position: static !important; }
}
.pay-section-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .12em; color: var(--pay-muted);
    display: flex; align-items: center; gap: .5rem; margin-bottom: 1rem;
}
.pay-section-label::after { content: ''; flex: 1; height: 1px; background: var(--pay-divider); }
.pay-plans-stack { display: flex; flex-direction: column; gap: .75rem; animation: payFadeUp .5s .1s var(--ease-expo) both; }
.pay-plan-row {
    border-radius: var(--pay-radius-sm); border: 2px solid var(--pay-divider);
    background: var(--pay-white); padding: 1.1rem 1.25rem; cursor: pointer;
    display: flex; align-items: center; gap: 1rem; position: relative; overflow: hidden;
    transition: transform .32s var(--ease-expo), box-shadow .32s, border-color .22s; outline: none;
}
.pay-plan-row::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; border-radius: 0 3px 3px 0; opacity: 0; transition: opacity .22s;
}
.pay-plan-row.basic-row::before    { background: var(--pay-blue); }
.pay-plan-row.premium-row::before  { background: var(--pay-green); }
.pay-plan-row.featured-row::before { background: var(--pay-amber); }
.pay-plan-row:hover { transform: translateX(4px); box-shadow: var(--pay-shadow); }
.pay-plan-row.plan-active {
    border-color: var(--pay-blue);
    box-shadow: 0 0 0 3px rgba(11,61,145,0.11), var(--pay-shadow);
    transform: translateX(4px);
}
.pay-plan-row.plan-active::before { opacity: 1; }
.pay-plan-row-radio {
    width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
    border: 2px solid var(--pay-divider); display: flex; align-items: center; justify-content: center;
    transition: border-color .2s;
}
.pay-plan-row-radio::after {
    content: ''; width: 9px; height: 9px; border-radius: 50%;
    background: var(--pay-blue); transform: scale(0); transition: transform .25s var(--ease-back);
}
.pay-plan-row.plan-active .pay-plan-row-radio { border-color: var(--pay-blue); }
.pay-plan-row.plan-active .pay-plan-row-radio::after { transform: scale(1); }
.pay-plan-row-icon {
    width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: transform .32s var(--ease-back);
}
.pay-plan-row.plan-active .pay-plan-row-icon { transform: scale(1.12) rotate(-5deg); }
.pay-plan-row-body { flex: 1; min-width: 0; }
.pay-plan-row-name { font-size: .9rem; font-weight: 800; color: var(--pay-text); }
.pay-plan-row-feat { font-size: .75rem; color: var(--pay-muted); margin-top: .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pay-plan-row-badge {
    font-size: .58rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; padding: .18rem .5rem; border-radius: 20px; color: #fff;
    position: absolute; top: .65rem; right: .75rem;
}
.premium-row .pay-plan-row-badge  { background: var(--pay-green); }
.featured-row .pay-plan-row-badge { background: var(--pay-amber); color: #7c2d12; }
.pay-plan-row-price { font-size: 1rem; font-weight: 900; color: var(--pay-text); white-space: nowrap; text-align: right; flex-shrink: 0; }
.pay-plan-row-price small { display: block; font-size: .65rem; font-weight: 500; color: var(--pay-muted); margin-top: .05rem; }
.pay-features-box {
    background: rgba(11,61,145,0.03); border: 1px solid rgba(11,61,145,0.08);
    border-radius: var(--pay-radius-sm); padding: .875rem 1rem; margin-top: 1.25rem;
    animation: payFadeUp .5s .18s var(--ease-expo) both;
}
.pay-features-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--pay-blue); margin-bottom: .75rem; }
.pay-features-list { display: flex; flex-direction: column; gap: .45rem; }
.pay-feature-item  { display: flex; align-items: flex-start; gap: .5rem; font-size: .8rem; color: var(--pay-text); }
.pay-feature-check { color: var(--pay-green); flex-shrink: 0; margin-top: 1px; }
.pay-sticky-sidebar { position: sticky; top: 100px; display: flex; flex-direction: column; gap: 1rem; }
.pay-summary-card {
    background: var(--pay-white); border: 1px solid var(--pay-divider);
    border-radius: var(--pay-radius); overflow: hidden; box-shadow: var(--pay-shadow);
    animation: payFadeUp .5s .12s var(--ease-expo) both;
}
.pay-summary-header {
    background: linear-gradient(135deg, var(--pay-blue) 0%, var(--pay-blue-lt) 100%);
    padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: .75rem;
}
.pay-summary-header-icon {
    width: 36px; height: 36px; border-radius: 9px; background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0;
}
.pay-summary-header-title { font-size: 1rem; font-weight: 800; color: #fff; }
.pay-summary-header-sub   { font-size: .72rem; color: rgba(255,255,255,0.7); margin-top: .1rem; }
.pay-summary-body { padding: 1.25rem 1.5rem; }
.pay-summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .6rem 0; border-bottom: 1px solid var(--pay-divider); font-size: .85rem;
}
.pay-summary-row:last-of-type { border-bottom: none; }
.pay-summary-label { color: var(--pay-muted); }
.pay-summary-val   { font-weight: 700; color: var(--pay-text); }
.pay-summary-total {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.5rem; background: rgba(11,61,145,0.035); border-top: 1px solid var(--pay-divider);
}
.pay-total-label  { font-size: .95rem; font-weight: 700; color: var(--pay-text); }
.pay-total-amount { font-size: 1.65rem; font-weight: 900; color: var(--pay-blue); letter-spacing: -.03em; }
.pay-notice {
    margin: 0 1.5rem 1.25rem; background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.25); border-radius: var(--pay-radius-sm);
    padding: .875rem 1rem; font-size: .78rem; color: var(--pay-text); line-height: 1.55;
    display: flex; gap: .65rem; align-items: flex-start;
}
.pay-notice-icon { color: #b45309; flex-shrink: 0; margin-top: 1px; }
.pay-cta-wrap { padding: 0 1.5rem 1.5rem; }
.pay-cta-btn {
    width: 100%; padding: 1.05rem 1.25rem; border-radius: var(--pay-radius-sm);
    border: none; cursor: pointer;
    background: linear-gradient(135deg, var(--pay-blue-lt), var(--pay-blue));
    color: #fff; font-size: 1rem; font-weight: 800; letter-spacing: .01em;
    display: flex; align-items: center; justify-content: center; gap: .65rem;
    box-shadow: 0 6px 24px rgba(11,61,145,0.32);
    transition: transform .3s var(--ease-back), box-shadow .3s, filter .2s;
    position: relative; overflow: hidden;
}
.pay-cta-btn::after {
    content: ''; position: absolute; top: 0; left: -70%; width: 45%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
    transform: skewX(-20deg); animation: payBtnShimmer 2.8s ease-in-out infinite;
}
@keyframes payBtnShimmer { 0% { left:-70%; } 55% { left:115%; } 100% { left:115%; } }
.pay-cta-btn:hover:not(:disabled) { transform: translateY(-3px) scale(1.01); box-shadow: 0 12px 36px rgba(11,61,145,0.42); filter: brightness(1.07); }
.pay-cta-btn:active:not(:disabled) { transform: scale(.98); }
.pay-cta-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; filter: none; }
.pay-secure-note {
    text-align: center; font-size: .72rem; color: var(--pay-muted); margin-top: .65rem;
    display: flex; align-items: center; justify-content: center; gap: .35rem;
}
.pay-disclaimer {
    background: rgba(11,61,145,0.03); border: 1px solid rgba(11,61,145,0.08);
    border-radius: var(--pay-radius-sm); padding: .875rem 1rem;
    font-size: .75rem; color: var(--pay-muted); line-height: 1.6;
    display: flex; gap: .6rem; align-items: flex-start;
    animation: payFadeUp .5s .26s var(--ease-expo) both;
}
.pay-disclaimer a { color: var(--pay-blue); text-decoration: none; }
.pay-disclaimer a:hover { text-decoration: underline; }
.pay-trust-row { display: flex; align-items: center; justify-content: center; gap: 1.5rem; flex-wrap: wrap; margin-top: .25rem; animation: payFadeUp .5s .3s var(--ease-expo) both; }
.pay-trust-item { display: flex; align-items: center; gap: .4rem; font-size: .72rem; color: var(--pay-muted); }
#paymentCsrfToken { display: none; }
@keyframes payFadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }
.pay-cta-btn.is-loading .pay-btn-label::after {
    content: ''; display: inline-block; width: 14px; height: 14px;
    border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%;
    animation: paySpinBtn .7s linear infinite; margin-left: .5rem; vertical-align: middle;
}
@keyframes paySpinBtn { to { transform: rotate(360deg); } }
</style>

<input type="hidden" id="paymentCsrfToken" value="<?= CSRF::token() ?>">

<div class="pay-page">

    <div class="pay-page-hero">
        <div class="pay-page-hero-inner">
            <div class="pay-hero-icon">
                <i data-lucide="credit-card" style="width:26px;height:26px;" aria-hidden="true"></i>
            </div>
            <div>
                <div class="pay-hero-title">Complete Payment</div>
                <div class="pay-hero-sub">
                    <i data-lucide="shield-check" style="width:13px;height:13px;" aria-hidden="true"></i>
                    Secure payment powered by Paystack
                </div>
                <div class="pay-hero-badge">
                    <i data-lucide="lock" style="width:10px;height:10px;" aria-hidden="true"></i>
                    256-bit SSL Encrypted
                </div>
            </div>
        </div>
    </div>

    <div class="pay-layout">
        <div class="pay-left">
            <div class="pay-section-label">
                <i data-lucide="layers" style="width:13px;height:13px;" aria-hidden="true"></i>
                Choose Your Plan
            </div>

            <div class="pay-plans-stack" id="payPlansStack" role="radiogroup" aria-label="Select a subscription plan">
                <?php foreach ($plans as $plan):
                    $pType       = $plan['plan_type'] ?? '';
                    $pAmount     = (int)($plan['amount'] ?? 0);
                    $pNaira      = (int)($plan['amount_naira'] ?? round($pAmount / 100));
                    $features    = parsePlanFeaturesPayment($plan['features'] ?? []);
                    $meta        = $planMeta[$pType] ?? ['icon' => 'zap', 'color' => '#0b3d91', 'bg' => 'rgba(11,61,145,0.08)', 'badge' => '', 'accent' => '#1e5bb8'];
                    $isActive    = $pType === $selectedPlan;
                    $featPreview = implode(' · ', array_slice($features, 0, 2));
                ?>
                <div class="pay-plan-row <?= $pType ?>-row <?= $isActive ? 'plan-active' : '' ?>"
                     data-plan="<?= htmlspecialchars($pType) ?>"
                     data-amount="<?= $pAmount ?>"
                     data-naira="<?= $pNaira ?>"
                     data-features='<?= htmlspecialchars(json_encode($features)) ?>'
                     tabindex="0" role="radio" aria-checked="<?= $isActive ? 'true' : 'false' ?>">

                    <?php if (!empty($meta['badge'])): ?>
                        <div class="pay-plan-row-badge"><?= htmlspecialchars($meta['badge']) ?></div>
                    <?php endif; ?>

                    <div class="pay-plan-row-radio" aria-hidden="true"></div>

                    <div class="pay-plan-row-icon" style="background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>;">
                        <i data-lucide="<?= $meta['icon'] ?>" style="width:18px;height:18px;" aria-hidden="true"></i>
                    </div>

                    <div class="pay-plan-row-body">
                        <div class="pay-plan-row-name"><?= htmlspecialchars(ucfirst($pType)) ?> Plan</div>
                        <div class="pay-plan-row-feat"><?= $featPreview ? htmlspecialchars($featPreview) : 'Campus listing · 180 days' ?></div>
                    </div>

                    <div class="pay-plan-row-price">
                        &#8358;<?= number_format($pNaira) ?>
                        <small>/semester</small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php $initFeatures = parsePlanFeaturesPayment($activePlan['features'] ?? []); ?>
            <div class="pay-features-box" id="payFeaturesBox">
                <div class="pay-features-title" id="payFeaturesTitle">
                    <?= htmlspecialchars(ucfirst($selectedPlan)) ?> Plan — Included Features
                </div>
                <div class="pay-features-list" id="payFeaturesList">
                    <?php if (!empty($initFeatures)): ?>
                        <?php foreach ($initFeatures as $feat): ?>
                        <div class="pay-feature-item">
                            <i data-lucide="check-circle" class="pay-feature-check" style="width:14px;height:14px;" aria-hidden="true"></i>
                            <span><?= htmlspecialchars($feat) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pay-feature-item">
                            <i data-lucide="check-circle" class="pay-feature-check" style="width:14px;height:14px;" aria-hidden="true"></i>
                            <span>Listed in campus directory for 1 semester (180 days)</span>
                        </div>
                        <div class="pay-feature-item">
                            <i data-lucide="check-circle" class="pay-feature-check" style="width:14px;height:14px;" aria-hidden="true"></i>
                            <span>Direct WhatsApp &amp; contact access for students</span>
                        </div>
                        <div class="pay-feature-item">
                            <i data-lucide="check-circle" class="pay-feature-check" style="width:14px;height:14px;" aria-hidden="true"></i>
                            <span>Verified vendor badge on your profile</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pay-trust-row" style="margin-top:1.5rem;">
                <div class="pay-trust-item">
                    <i data-lucide="shield" style="width:14px;height:14px;color:var(--pay-green);" aria-hidden="true"></i>
                    Secure Checkout
                </div>
                <div class="pay-trust-item">
                    <i data-lucide="zap" style="width:14px;height:14px;color:var(--pay-green);" aria-hidden="true"></i>
                    Instant Activation
                </div>
                <div class="pay-trust-item">
                    <i data-lucide="refresh-cw" style="width:14px;height:14px;color:var(--pay-green);" aria-hidden="true"></i>
                    Refund Protected
                </div>
            </div>
        </div>

        <div class="pay-sticky-sidebar">
            <div class="pay-summary-card">
                <div class="pay-summary-header">
                    <div class="pay-summary-header-icon">
                        <i data-lucide="receipt" style="width:17px;height:17px;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="pay-summary-header-title">Payment Summary</div>
                        <div class="pay-summary-header-sub">Review before paying</div>
                    </div>
                </div>

                <div class="pay-summary-body">
                    <div class="pay-summary-row">
                        <span class="pay-summary-label">Business</span>
                        <span class="pay-summary-val"><?= htmlspecialchars($vendor['business_name'] ?? '') ?></span>
                    </div>
                    <div class="pay-summary-row">
                        <span class="pay-summary-label">Plan</span>
                        <span class="pay-summary-val" id="summaryPlan"><?= htmlspecialchars(ucfirst($selectedPlan)) ?></span>
                    </div>
                    <div class="pay-summary-row">
                        <span class="pay-summary-label">Duration</span>
                        <span class="pay-summary-val">180 days (1 semester)</span>
                    </div>
                    <div class="pay-summary-row">
                        <span class="pay-summary-label">Vendor Type</span>
                        <span class="pay-summary-val"><?= htmlspecialchars(ucfirst($vendor['vendor_type'] ?? '')) ?></span>
                    </div>
                </div>

                <div class="pay-summary-total">
                    <span class="pay-total-label">Total</span>
                    <span class="pay-total-amount" id="summaryAmount">
                        &#8358;<?php
                        $initNaira = (int)($activePlan['amount_naira'] ?? round(($activePlan['amount'] ?? 0) / 100));
                        echo number_format($initNaira);
                        ?>
                    </span>
                </div>

                <div class="pay-notice">
                    <i data-lucide="alert-triangle" class="pay-notice-icon" style="width:15px;height:15px;" aria-hidden="true"></i>
                    <div>
                        <strong>Important — Read Before Paying</strong><br>
                        Payment is processed securely through Paystack. Your subscription activates within minutes of successful payment.
                        <strong>Refunds are only issued for duplicate payment or technical error</strong>
                        — see our <a href="<?= SITE_URL ?>/refund-policy" target="_blank">Refund Policy</a>.
                    </div>
                </div>

                <div class="pay-cta-wrap">
                    <button class="pay-cta-btn"
                            id="payCta"
                            data-vendor-id="<?= (int)($vendor['id'] ?? 0) ?>"
                            data-vendor-type="<?= htmlspecialchars($vendor['vendor_type'] ?? '') ?>"
                            data-email="<?= htmlspecialchars($vendor['school_email'] ?? $vendor['working_email'] ?? '') ?>"
                            data-paystack-key="<?= htmlspecialchars(PAYSTACK_PUBLIC_KEY) ?>">
                        <i data-lucide="lock" style="width:18px;height:18px;" aria-hidden="true"></i>
                        <span class="pay-btn-label">Pay Securely with Paystack</span>
                    </button>
                    <div class="pay-secure-note">
                        <i data-lucide="shield-check" style="width:13px;height:13px;" aria-hidden="true"></i>
                        Secured by Paystack &middot; Card, Bank Transfer, USSD
                    </div>
                </div>
            </div>

            <div class="pay-disclaimer">
                <i data-lucide="info" style="width:14px;height:14px;color:var(--pay-blue);flex-shrink:0;margin-top:1px;" aria-hidden="true"></i>
                <div>
                    By completing this payment, you agree to the
                    <a href="<?= SITE_URL ?>/vendor-terms" target="_blank">Vendor Terms of Service</a>
                    and confirm that all information in your profile is accurate and truthful.
                    Fraudulent listings will result in immediate suspension without refund.
                </div>
            </div>
        </div>
    </div>
</div>

<!--
    Paystack SDK loaded WITHOUT defer so PaystackPop is available
    synchronously when the button is clicked.
-->
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
(function () {
    'use strict';

    /* ─────────────────────────────────────────────────────────
       Lucide: the dashboard layout loads lucide with `defer`,
       so window.lucide may not exist yet when this IIFE runs.
       We use a helper that retries until lucide is ready.
    ───────────────────────────────────────────────────────── */
    function initIcons() {
        if (window.lucide) {
            window.lucide.createIcons();
        } else {
            // Lucide CDN not loaded yet — retry after layout scripts settle
            window.addEventListener('load', function () {
                if (window.lucide) window.lucide.createIcons();
            });
        }
    }
    initIcons();

    /* ── state ── */
    let selectedPlan   = <?= json_encode($selectedPlan) ?>;
    let selectedAmount = <?= (int)($activePlan['amount'] ?? 0) ?>;
    let selectedNaira  = <?= (int)($activePlan['amount_naira'] ?? round(($activePlan['amount'] ?? 0) / 100)) ?>;
    let csrfToken      = document.getElementById('paymentCsrfToken')?.value || '';

    /* ── elements ── */
    const planRows    = document.querySelectorAll('.pay-plan-row');
    const summaryPlan = document.getElementById('summaryPlan');
    const summaryAmt  = document.getElementById('summaryAmount');
    const featTitle   = document.getElementById('payFeaturesTitle');
    const featList    = document.getElementById('payFeaturesList');
    const payCta      = document.getElementById('payCta');

    /* ── plan selection ── */
    planRows.forEach(row => {
        row.addEventListener('click',   ()  => activatePlan(row));
        row.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activatePlan(row); }
        });
    });

    function activatePlan(row) {
        planRows.forEach(r => { r.classList.remove('plan-active'); r.setAttribute('aria-checked','false'); });
        row.classList.add('plan-active');
        row.setAttribute('aria-checked', 'true');

        selectedPlan   = row.dataset.plan;
        selectedAmount = parseInt(row.dataset.amount) || 0;
        selectedNaira  = parseInt(row.dataset.naira)  || 0;

        if (summaryPlan) summaryPlan.textContent = cap(selectedPlan) + ' Plan';
        if (summaryAmt)  summaryAmt.innerHTML    = '&#8358;' + fmt(selectedNaira);

        let features = [];
        try { features = JSON.parse(row.dataset.features || '[]'); } catch(e) {}
        renderFeatures(selectedPlan, features);
    }

    function renderFeatures(plan, features) {
        if (featTitle) featTitle.textContent = cap(plan) + ' Plan — Included Features';
        if (!featList) return;
        if (!features.length) {
            features = [
                'Listed in campus directory for 1 semester (180 days)',
                'Direct WhatsApp & contact access for students',
                'Verified vendor badge on your profile',
            ];
        }
        featList.innerHTML = features.map(f =>
            `<div class="pay-feature-item">
                <i data-lucide="check-circle" class="pay-feature-check" style="width:14px;height:14px;" aria-hidden="true"></i>
                <span>${esc(f)}</span>
            </div>`
        ).join('');
        // Re-init icons after injecting new data-lucide elements
        if (window.lucide) window.lucide.createIcons();
    }

    /* ── pay button ── */
    if (payCta) {
        payCta.addEventListener('click', async () => { await initiatePayment(); });
    }

    async function initiatePayment() {
        if (!selectedPlan || !selectedAmount) {
            toast('Please select a plan before proceeding.', 'error');
            return;
        }

        const vendorId    = payCta.dataset.vendorId;
        const vendorType  = payCta.dataset.vendorType;
        const email       = payCta.dataset.email;
        const paystackKey = payCta.dataset.paystackKey || window.PAYSTACK_PUBLIC_KEY;

        if (!paystackKey) {
            toast('Payment configuration error. Please contact support.', 'error');
            return;
        }

        setLoading(true);

        try {
            const res = await fetch('<?= SITE_URL ?>/vendor/payment/initiate', {
                method:      'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type':     'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({
                    csrf_token:  csrfToken,
                    vendor_id:   vendorId,
                    vendor_type: vendorType,
                    plan_type:   selectedPlan,
                }),
            });

            if (!res.ok) {
                const errText = await res.text();
                console.error('Server error:', res.status, errText);
                toast('Server error (' + res.status + '). Please refresh and try again.', 'error');
                return;
            }

            let data;
            try { data = await res.json(); }
            catch(e) {
                toast('Unexpected server response. Please refresh and try again.', 'error');
                return;
            }

            console.log('Initiate response:', data); // ← helpful during debugging

            /* ── refresh CSRF token for next attempt ── */
            if (data.new_csrf_token) {
                csrfToken = data.new_csrf_token;
                const inp = document.getElementById('paymentCsrfToken');
                if (inp) inp.value = csrfToken;
            }

            /*
             * jsonSuccess() in Controller.php does:
             *   json_encode(['status'=>'success','message'=>$msg, ...extraFields])
             * So we check data.status === 'success', NOT data.success
             */
            if (data.status !== 'success') {
                toast(data.message || 'Could not initialize payment. Please try again.', 'error');
                return;
            }

            const reference = data.reference || data.data?.reference;
            const amount    = data.amount    || data.data?.amount || selectedAmount;

            if (!reference) {
                toast('Payment reference missing from server response. Please try again.', 'error');
                return;
            }

            /* ── verify Paystack SDK loaded ── */
            if (typeof PaystackPop === 'undefined') {
                toast('Paystack checkout is not loaded. Please refresh the page.', 'error');
                return;
            }

            openPaystackPopup({ key: paystackKey, email, amount, reference });

        } catch (err) {
            console.error('Fetch error:', err);
            toast('Network error — check your internet connection and try again.', 'error');
        } finally {
            setLoading(false);
        }
    }

    function openPaystackPopup({ key, email, amount, reference }) {
        const handler = PaystackPop.setup({
            key,
            email,
            amount,
            ref:      reference,
            currency: 'NGN',
            metadata: {
                custom_fields: [{
                    display_name:  'Platform',
                    variable_name: 'platform',
                    value:         'CampusLink',
                }],
            },
            onClose: () => {
                toast('Payment window closed. Your payment was not completed.', 'warning');
                refreshCsrf();
            },
            callback: response => {
                handlePaymentSuccess(response);
            },
        });
        handler.openIframe();
    }

    function handlePaymentSuccess(response) {
        const ref = response.reference || response.trxref || '';
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position:fixed;inset:0;background:rgba(255,255,255,0.95);z-index:99999;
            display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:1rem;backdrop-filter:blur(6px);
        `;
        overlay.innerHTML = `
            <div style="width:48px;height:48px;border:3px solid #e9ecef;border-top-color:#0b3d91;
                        border-radius:50%;animation:paySpin .7s linear infinite;"></div>
            <p style="font-size:1rem;font-weight:700;color:#1f2937;">Verifying payment…</p>
            <p style="font-size:.8rem;color:#6b7280;">Please do not close this page.</p>
            <style>@keyframes paySpin{to{transform:rotate(360deg)}}</style>
        `;
        document.body.appendChild(overlay);
        window.location.href = '<?= SITE_URL ?>/vendor/payment/verify?reference=' + encodeURIComponent(ref);
    }

    function setLoading(on) {
        if (!payCta) return;
        payCta.disabled = on;
        const label = payCta.querySelector('.pay-btn-label');
        if (label) label.textContent = on ? 'Initializing…' : 'Pay Securely with Paystack';
        if (on)  payCta.classList.add('is-loading');
        else     payCta.classList.remove('is-loading');
    }

    function refreshCsrf() {
        fetch('<?= SITE_URL ?>/vendor/payment/csrf', {
            method: 'GET', credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.ok ? r.json() : null)
        .then(d => {
            if (d?.token) {
                csrfToken = d.token;
                const inp = document.getElementById('paymentCsrfToken');
                if (inp) inp.value = csrfToken;
            }
        })
        .catch(() => {});
    }
    setInterval(refreshCsrf, 10 * 60 * 1000);

    function cap(s) { return String(s).charAt(0).toUpperCase() + String(s).slice(1); }
    function fmt(n) { return Number(n).toLocaleString('en-NG'); }
    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function toast(msg, type = 'info') {
        if (window.CampusLink?.toast) { window.CampusLink.toast(msg, type); return; }
        const colors = { error:'#ef4444', warning:'#f59e0b', success:'#1ea952', info:'#0b3d91' };
        const t = document.createElement('div');
        t.style.cssText = `position:fixed;bottom:1.5rem;right:1.5rem;z-index:99998;
            background:#fff;border-left:4px solid ${colors[type]||colors.info};
            box-shadow:0 8px 32px rgba(0,0,0,0.14);border-radius:10px;
            padding:.875rem 1.25rem;max-width:360px;font-size:.875rem;color:#1f2937;line-height:1.5;`;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 6000);
    }

})();
</script>
