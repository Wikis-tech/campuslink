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
.policy-hero{background:linear-gradient(135deg,#064e3b,#0e9f6e);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero .hero-icon{display:flex;justify-content:center;margin-bottom:0.75rem;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#166534;display:flex;align-items:center;gap:0.5rem;}
</style>

<div class="policy-hero">
    <div class="hero-icon">
        <i data-lucide="store" style="width:40px;height:40px;stroke:#fff;stroke-width:1.5;fill:none;"></i>
    </div>
    <h1>Vendor Terms &amp; Conditions</h1>
    <p>Terms for businesses and individuals listed on CampusLink</p>
</div>

<div class="policy-body">
    <div class="policy-meta">
        <i data-lucide="calendar" style="width:13px;height:13px;flex-shrink:0;"></i>
        Last updated: <?= TERMS_DATE ?> &middot; Version <?= TERMS_VERSION ?>
    </div>

    <div class="policy-section">
        <h2>1. Vendor Eligibility</h2>
        <p>Vendors must be either an enrolled student at <?= e(SCHOOL_NAME) ?> (Student Vendor) or a business/individual serving the campus community (Community Vendor). All vendors must provide valid identification for verification.</p>
    </div>
    <div class="policy-section">
        <h2>2. Listing Requirements</h2>
        <ul>
            <li>All information provided must be accurate and truthful</li>
            <li>Business descriptions must reflect actual services offered</li>
            <li>Uploaded images must be original or licensed content</li>
            <li>Contact details must be reachable and up to date</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>3. Subscription &amp; Payment</h2>
        <p>Listings are activated only after payment of the applicable subscription fee. Subscriptions are per semester (180 days). Fees are non-refundable once your listing has been activated. See the <a href="<?= SITE_URL ?>/refund-policy" style="color:#0e9f6e;font-weight:700;">Refund Policy</a> for exceptions.</p>
    </div>
    <div class="policy-section">
        <h2>4. Vendor Conduct</h2>
        <ul>
            <li>Vendors must honour commitments made to students</li>
            <li>Vendors must not engage in fraudulent or deceptive practices</li>
            <li>Vendors must respond professionally to complaints</li>
            <li>Vendors must not solicit fake reviews</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>5. Suspension &amp; Removal</h2>
        <p>CampusLink reserves the right to suspend or remove any vendor listing that receives multiple verified complaints, provides false information, or violates these terms. See the <a href="<?= SITE_URL ?>/suspension-policy" style="color:#0e9f6e;font-weight:700;">Suspension Policy</a>.</p>
    </div>
    <div class="policy-section">
        <h2>6. No Guarantee of Business</h2>
        <p>CampusLink does not guarantee any volume of inquiries, contacts, or revenue. We provide visibility only. Results depend on your service quality, pricing, and responsiveness.</p>
    </div>
    <div class="policy-section">
        <h2>7. Contact</h2>
        <p>Vendor support: <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#0e9f6e;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    if (window.lucide) lucide.createIcons();
});
</script>