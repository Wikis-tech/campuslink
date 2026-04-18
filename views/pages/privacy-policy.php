<?php defined('CAMPUSLINK') or die(); ?>
<style>
.policy-hero{background:linear-gradient(135deg,#1e1b4b,#4338ca);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#3730a3;}
</style>

<div class="policy-hero">
    <h1>🔒 Privacy Policy</h1>
    <p>How CampusLink collects, uses, and protects your data</p>
</div>
<div class="policy-body">
    <div class="policy-meta">
        Last updated: <?= TERMS_DATE ?> · Version <?= PRIVACY_VERSION ?>
    </div>
    <div class="policy-section">
        <h2>1. Data We Collect</h2>
        <p>We collect the following information when you use CampusLink:</p>
        <ul>
            <li><strong>Students:</strong> Name, school email, matric number, phone number</li>
            <li><strong>Vendors:</strong> Business name, contact details, ID documents, payment records</li>
            <li><strong>All users:</strong> IP address, browser type, pages visited, session data</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>2. How We Use Your Data</h2>
        <ul>
            <li>To verify and activate vendor accounts</li>
            <li>To display vendor profiles in the directory</li>
            <li>To process subscription payments via Paystack</li>
            <li>To send important notifications and email alerts</li>
            <li>To investigate complaints and enforce platform rules</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>3. Data Sharing</h2>
        <p>We do not sell your personal data. We share data only with:</p>
        <ul>
            <li>Paystack — for payment processing</li>
            <li>Our email provider — for transactional emails</li>
            <li>Law enforcement — if legally required</li>
        </ul>
    </div>
    <div class="policy-section">
        <h2>4. Data Security</h2>
        <p>Passwords are hashed using bcrypt. ID documents are stored in a protected directory not accessible via the web. Sessions use secure, HTTP-only cookies.</p>
    </div>
    <div class="policy-section">
        <h2>5. Your Rights</h2>
        <ul>
            <li>Request a copy of your personal data</li>
            <li>Request correction of inaccurate data</li>
            <li>Request deletion of your account and data</li>
        </ul>
        <p>To exercise these rights, email <a href="mailto:<?= e(SUPPORT_EMAIL) ?>" style="color:#4338ca;font-weight:700;"><?= e(SUPPORT_EMAIL) ?></a></p>
    </div>
    <div class="policy-section">
        <h2>6. Cookies</h2>
        <p>We use session cookies to keep you logged in. We do not use tracking or advertising cookies.</p>
    </div>
    <div class="policy-section">
        <h2>7. Data Retention</h2>
        <p>See our <a href="<?= SITE_URL ?>/data-retention" style="color:#4338ca;font-weight:700;">Data Retention Policy</a> for full details on how long we keep your data.</p>
    </div>
</div>