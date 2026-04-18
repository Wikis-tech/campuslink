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
.policy-hero{background:linear-gradient(135deg,#7c2d12,#ea580c);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero .hero-icon{display:flex;justify-content:center;margin-bottom:0.75rem;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#9a3412;display:flex;align-items:center;gap:0.5rem;}
.refund-table{width:100%;border-collapse:collapse;font-size:0.82rem;margin-top:0.75rem;}
.refund-table th{background:#f8fafc;padding:0.6rem 0.85rem;text-align:left;font-weight:700;color:#374151;border:1px solid #e2e8f0;}
.refund-table td{padding:0.6rem 0.85rem;border:1px solid #e2e8f0;color:#374151;}
.refund-table td .cell-inner{display:flex;align-items:center;gap:0.4rem;}
.refund-eligible{color:#16a34a;}
.refund-denied{color:#dc2626;}
</style>

<div class="policy-hero">
    <div class="hero-icon">
        <i data-lucide="receipt" style="width:40px;height:40px;stroke:#fff;stroke-width:1.5;fill:none;"></i>
    </div>
    <h1>Refund Policy</h1>
    <p>Subscription refund rules for CampusLink vendors</p>
</div>

<div class="policy-body">
    <div class="policy-meta">
        <i data-lucide="calendar" style="width:13px;height:13px;flex-shrink:0;"></i>
        Last updated: <?= TERMS_DATE ?> &middot; Version <?= TERMS_VERSION ?>
    </div>

    <div class="policy-section">
        <h2>1. General Policy</h2>
        <p>Subscription fees paid on CampusLink are <strong>generally non-refundable</strong> once your vendor listing has been activated and made visible in the directory.</p>
    </div>

    <div class="policy-section">
        <h2>2. Eligible Refund Scenarios</h2>
        <table class="refund-table">
            <thead>
                <tr><th>Scenario</th><th>Refund Eligibility</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>Payment made but listing never activated</td>
                    <td><span class="cell-inner refund-eligible">
                        <i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                        Full refund within 7 days
                    </span></td>
                </tr>
                <tr>
                    <td>Duplicate payment for same subscription</td>
                    <td><span class="cell-inner refund-eligible">
                        <i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                        Full refund of duplicate
                    </span></td>
                </tr>
                <tr>
                    <td>Technical error causing wrong amount charged</td>
                    <td><span class="cell-inner refund-eligible">
                        <i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                        Difference refunded
                    </span></td>
                </tr>
                <tr>
                    <td>Listing activated and visible but vendor wants to cancel</td>
                    <td><span class="cell-inner refund-denied">
                        <i data-lucide="x-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                        No refund
                    </span></td>
                </tr>
                <tr>
                    <td>Vendor suspended for violating terms</td>
                    <td><span class="cell-inner refund-denied">
                        <i data-lucide="x-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                        No refund
                    </span></td>
                </tr>
                <tr>
                    <td>Vendor dissatisfied with number of inquiries</td>
                    <td><span class="cell-inner refund-denied">
                        <i data-lucide="x-circle" style="width:14px;height:14px;flex-shrink:0;"></i>
                        No refund
                    </span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="policy-section">
        <h2>3. How to Request a Refund</h2>
        <p>Email <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#ea580c;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a> with:</p>
        <ul>
            <li>Your registered email address</li>
            <li>Your Paystack payment reference number</li>
            <li>The reason for your refund request</li>
        </ul>
        <p>Refund requests must be submitted within <strong>7 days</strong> of payment. We will respond within 3 business days.</p>
    </div>

    <div class="policy-section">
        <h2>4. Processing Time</h2>
        <p>Approved refunds are processed back to your original payment method within 5–10 business days via Paystack.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    if (window.lucide) lucide.createIcons();
});
</script>