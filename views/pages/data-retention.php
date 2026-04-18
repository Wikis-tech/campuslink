<?php defined('CAMPUSLINK') or die(); ?>
<style>
.policy-hero{background:linear-gradient(135deg,#134e4a,#0d9488);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-hero-icon{display:flex;justify-content:center;margin-bottom:0.75rem;color:#fff;}
.policy-hero-icon svg{width:44px;height:44px;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#f0fdfa;border:1px solid #99f6e4;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#0f766e;}
.retention-table{width:100%;border-collapse:collapse;font-size:0.8rem;margin-top:0.75rem;}
.retention-table th{background:#f8fafc;padding:0.6rem 0.85rem;text-align:left;font-weight:700;color:#374151;border:1px solid #e2e8f0;}
.retention-table td{padding:0.6rem 0.85rem;border:1px solid #e2e8f0;color:#374151;vertical-align:top;}
.retention-table tr:nth-child(even) td{background:#f8fafc;}
</style>

<div class="policy-hero">
    <div class="policy-hero-icon">
        <i data-lucide="database" style="width:44px;height:44px;"></i>
    </div>
    <h1>Data Retention Policy</h1>
    <p>How long CampusLink keeps your personal data</p>
</div>
<div class="policy-body">
    <div class="policy-meta">
        Last updated: <?= TERMS_DATE ?> · Version <?= TERMS_VERSION ?>
    </div>
    <div class="policy-section">
        <h2>1. Retention Schedule</h2>
        <table class="retention-table">
            <thead>
                <tr><th>Data Type</th><th>Retention Period</th><th>Reason</th></tr>
            </thead>
            <tbody>
                <tr><td>Student account data</td><td>Account lifetime + 30 days after deletion</td><td>Service delivery</td></tr>
                <tr><td>Vendor account data</td><td>Account lifetime + 30 days after deletion</td><td>Service delivery</td></tr>
                <tr><td>ID verification documents</td><td>90 days after review</td><td>Verification only</td></tr>
                <tr><td>Payment records</td><td>7 years</td><td>Legal requirement</td></tr>
                <tr><td>Reviews</td><td>2 years</td><td>Platform integrity</td></tr>
                <tr><td>Complaints & evidence</td><td>2 years</td><td>Dispute resolution</td></tr>
                <tr><td>Notifications</td><td>90 days</td><td>User experience</td></tr>
                <tr><td>Session data</td><td>24 hours inactivity / 7 days max</td><td>Security</td></tr>
                <tr><td>Error & audit logs</td><td>90 days</td><td>Security monitoring</td></tr>
                <tr><td>Admin action logs</td><td>1 year</td><td>Accountability</td></tr>
            </tbody>
        </table>
    </div>
    <div class="policy-section">
        <h2>2. Account Deletion</h2>
        <p>When you delete your account, your personal data is anonymised within 30 days. Payment records are retained for 7 years as required by Nigerian financial regulations. Anonymised data (with no personal identifiers) may be retained for platform analytics.</p>
    </div>
    <div class="policy-section">
        <h2>3. Data Access Requests</h2>
        <p>You may request a copy of all personal data we hold about you. We will respond within 14 days. Email <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#0d9488;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a> with the subject line "Data Access Request".</p>
    </div>
    <div class="policy-section">
        <h2>4. Data Deletion Requests</h2>
        <p>You may request deletion of your personal data at any time by deleting your account or emailing us. We will process deletion within 30 days subject to legal retention requirements.</p>
    </div>
</div>