<?php defined('CAMPUSLINK') or die(); ?>
<style>
.policy-hero{background:linear-gradient(135deg,#0b3d91,#1a56db);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#64748b;}
</style>

<div class="policy-hero">
    <h1>🎓 User Terms & Conditions</h1>
    <p>Terms for students and community members using CampusLink</p>
</div>
<div class="policy-body">
    <div class="policy-meta">
        Last updated: <?= TERMS_DATE ?> · Version <?= TERMS_VERSION ?>
    </div>
    <div class="policy-section">
        <h2>1. Who This Applies To</h2>
        <p>These terms apply to all registered students and community members who use CampusLink to browse, contact, or review vendors at <?= e(SCHOOL_NAME) ?>.</p>
    </div>
    <div class="policy-section">
        <h2>2. Account Registration</h2>
        <p>To access certain features you must register with a valid school email address and matric number. You are responsible for keeping your login credentials secure.</p>
    </div>
    <div class="policy-section">
        <h2>3. Permitted Use</h2>
        <ul>
            <li>Browse vendor listings and contact vendors directly</li>
            <li>Save vendors to your personal list</li>
            <li>Submit honest reviews based on real experiences</li>
            <li>File formal complaints about vendors</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>4. Reviews Policy</h2>
        <p>Reviews must be honest, based on a real experience, and respectful. You may not post fake reviews, reviews for vendors you have not used, or reviews containing threats or hate speech. Violating this policy may result in account suspension.</p>
    </div>
    <div class="policy-section">
        <h2>5. Complaints Policy</h2>
        <p>Complaints must be genuine and supported by evidence where possible. Filing false or malicious complaints is a violation of these terms and may result in account suspension.</p>
    </div>
    <div class="policy-section">
        <h2>6. No Transaction Guarantee</h2>
        <p>CampusLink does not guarantee any transaction. Always verify a vendor in person before making payment. Never send money to an unverified account.</p>
    </div>
    <div class="policy-section">
        <h2>7. Account Termination</h2>
        <p>We may suspend or delete your account if you violate these terms, file false complaints, post fake reviews, or engage in any fraudulent activity.</p>
    </div>
    <div class="policy-section">
        <h2>8. Contact</h2>
        <p>Questions? Email <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#1a56db;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a></p>
    </div>
</div>