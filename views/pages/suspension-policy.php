<?php defined('CAMPUSLINK') or die(); ?>

<script>
if (!window.lucide) {
    var s = document.createElement('script');
    s.src = 'https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';
    s.onload = function(){ lucide.createIcons(); };
    document.head.appendChild(s);
}
</script>

<style>
.policy-hero{background:linear-gradient(135deg,#7f1d1d,#dc2626);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero .hero-icon{display:flex;justify-content:center;margin-bottom:0.75rem;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#dc2626;display:flex;align-items:center;gap:0.5rem;}
.severity-box{border-radius:10px;padding:1rem;margin:0.75rem 0;}
.sev-high{background:#fef2f2;border:1px solid #fecaca;}
.sev-med{background:#fffbeb;border:1px solid #fde68a;}
.sev-low{background:#f0fdf4;border:1px solid #bbf7d0;}
.sev-title{font-weight:800;font-size:0.82rem;margin-bottom:0.4rem;display:flex;align-items:center;gap:0.4rem;}
.sev-high .sev-title{color:#dc2626;}
.sev-med .sev-title{color:#92400e;}
.sev-low .sev-title{color:#166534;}
</style>

<div class="policy-hero">
    <div class="hero-icon">
        <i data-lucide="ban" style="width:40px;height:40px;stroke:#fff;stroke-width:1.5;fill:none;"></i>
    </div>
    <h1>Suspension Policy</h1>
    <p>How and when CampusLink suspends vendor accounts</p>
</div>

<div class="policy-body">
    <div class="policy-meta">
        <i data-lucide="calendar" style="width:13px;height:13px;flex-shrink:0;"></i>
        Last updated: <?= TERMS_DATE ?> &middot; Version <?= TERMS_VERSION ?>
    </div>

    <div class="policy-section">
        <h2>1. Grounds for Suspension</h2>
        <p>A vendor listing may be suspended or permanently removed for any of the following reasons:</p>

        <div class="severity-box sev-high">
            <div class="sev-title">
                <i data-lucide="x-octagon" style="width:14px;height:14px;flex-shrink:0;"></i>
                Immediate Permanent Removal
            </div>
            <ul style="margin:0;padding-left:1.25rem;">
                <li>Fraud or financial deception of students</li>
                <li>Providing false identity documents</li>
                <li>Threatening or harassing users</li>
                <li>Illegal activities on campus</li>
            </ul>
        </div>

        <div class="severity-box sev-med">
            <div class="sev-title">
                <i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0;"></i>
                Temporary Suspension (7–30 days)
            </div>
            <ul style="margin:0;padding-left:1.25rem;">
                <li>3 or more verified complaints within one semester</li>
                <li>Consistently poor service with evidence</li>
                <li>Soliciting fake reviews</li>
                <li>Providing inaccurate business information</li>
            </ul>
        </div>

        <div class="severity-box sev-low">
            <div class="sev-title">
                <i data-lucide="info" style="width:14px;height:14px;flex-shrink:0;"></i>
                Warning Issued First
            </div>
            <ul style="margin:0;padding-left:1.25rem;">
                <li>Minor inaccuracies in listing</li>
                <li>Slow response to complaints</li>
                <li>Outdated contact information</li>
            </ul>
        </div>
    </div>

    <div class="policy-section">
        <h2>2. Suspension Process</h2>
        <ul>
            <li>Admin reviews complaint evidence</li>
            <li>Vendor is notified by email and given a chance to respond</li>
            <li>Admin makes a final decision within 5 business days</li>
            <li>Decision is communicated to both parties</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>3. Appeals</h2>
        <p>Suspended vendors may appeal within 14 days by emailing <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#dc2626;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a> with evidence supporting their case. Appeals are reviewed by a senior admin within 7 business days.</p>
    </div>
    <div class="policy-section">
        <h2>4. Subscription During Suspension</h2>
        <p>Subscription fees are not refunded for suspended accounts. Suspended vendors whose subscriptions expire during suspension may not renew until the suspension period ends or an appeal is successful.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    if (window.lucide) lucide.createIcons();
});
</script>