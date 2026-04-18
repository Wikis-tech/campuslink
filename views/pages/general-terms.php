<?php defined('CAMPUSLINK') or die(); ?>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>
.policy-hero{background:linear-gradient(135deg,#0b3d91,#1a56db);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero .hero-icon{display:flex;justify-content:center;margin-bottom:0.75rem;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;display:flex;align-items:center;justify-content:center;gap:0.5rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#64748b;display:flex;align-items:center;gap:0.5rem;}
</style>

<div class="policy-hero">
    <div class="hero-icon">
        <i data-lucide="file-text" style="width:40px;height:40px;stroke:#fff;stroke-width:1.5;fill:none;"></i>
    </div>
    <h1>General Terms &amp; Conditions</h1>
    <p>Please read these terms carefully before using CampusLink</p>
</div>

<div class="policy-body">
    <div class="policy-meta">
        <i data-lucide="calendar" style="width:13px;height:13px;flex-shrink:0;"></i>
        Last updated: <?= TERMS_DATE ?> &middot; Version <?= TERMS_VERSION ?> &middot; Governed by Nigerian Law
    </div>
    <div class="policy-section">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using CampusLink, you agree to be bound by these General Terms and Conditions. If you do not agree, you must not use this platform.</p>
    </div>
    <div class="policy-section">
        <h2>2. Nature of Platform</h2>
        <p>CampusLink is a <strong>digital directory platform only</strong>. We list verified vendors operating within or serving <?= e(SCHOOL_NAME) ?>. We do not:</p>
        <ul>
            <li>Process payments between users and vendors</li>
            <li>Guarantee the quality of any service or product</li>
            <li>Mediate, arbitrate, or resolve commercial disputes</li>
            <li>Act as an agent for any vendor or user</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>3. Eligibility</h2>
        <p>CampusLink is available to students, staff, and community members affiliated with <?= e(SCHOOL_NAME) ?>. Users must be at least 16 years old. Vendors must be at least 18 years old.</p>
    </div>
    <div class="policy-section">
        <h2>4. User Conduct</h2>
        <p>All users agree not to:</p>
        <ul>
            <li>Post false, misleading, or fraudulent information</li>
            <li>Harass, threaten, or abuse other users or vendors</li>
            <li>Attempt to hack, disrupt, or damage the platform</li>
            <li>Use the platform for any illegal activity</li>
            <li>Create multiple accounts to circumvent bans or restrictions</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>5. Intellectual Property</h2>
        <p>All content on CampusLink including logos, text, and code is owned by CampusLink. Vendors retain ownership of their uploaded images and descriptions. You may not copy or reproduce platform content without written permission.</p>
    </div>
    <div class="policy-section">
        <h2>6. Limitation of Liability</h2>
        <p>CampusLink is not liable for any loss, damage, or harm arising from transactions between users and vendors. Use the platform at your own risk. Always verify vendors before making payments.</p>
    </div>
    <div class="policy-section">
        <h2>7. Changes to Terms</h2>
        <p>We may update these terms at any time. Continued use of the platform after changes constitutes acceptance of the new terms.</p>
    </div>
    <div class="policy-section">
        <h2>8. Contact</h2>
        <p>Questions about these terms? Email us at <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#1a56db;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a></p>
    </div>
</div>

<script>lucide.createIcons();</script>