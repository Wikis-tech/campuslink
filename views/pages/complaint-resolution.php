<?php defined('CAMPUSLINK') or die(); ?>

<?php
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
?>
<style>
.policy-hero{background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:2.5rem 1rem;text-align:center;color:#fff;}
.policy-hero h1{font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;
                display:flex;align-items:center;justify-content:center;gap:0.55rem;}
.policy-hero p{font-size:0.85rem;opacity:0.8;margin:0;}
.policy-body{max-width:780px;margin:0 auto;padding:2rem 1rem 3rem;}
.policy-section{margin-bottom:2rem;}
.policy-section h2{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 0.6rem;
                   padding-bottom:0.4rem;border-bottom:2px solid #e2e8f0;}
.policy-section p,.policy-section li{font-size:0.875rem;color:#374151;line-height:1.8;margin:0 0 0.5rem;}
.policy-section ul{padding-left:1.25rem;margin:0.5rem 0;}
.policy-meta{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;
             padding:0.85rem 1rem;margin-bottom:1.5rem;font-size:0.78rem;color:#1d4ed8;}
.step-row{display:flex;gap:1rem;align-items:flex-start;margin-bottom:1rem;
          padding:0.85rem;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;}
.step-num{width:28px;height:28px;border-radius:50%;
          background:linear-gradient(135deg,#1a56db,#0e9f6e);color:#fff;
          display:flex;align-items:center;justify-content:center;
          font-weight:900;font-size:0.75rem;flex-shrink:0;}
.step-info h4{font-size:0.85rem;font-weight:800;color:#1e293b;margin:0 0 0.2rem;}
.step-info p{font-size:0.78rem;color:#64748b;margin:0;line-height:1.5;}
</style>

<div class="policy-hero">
    <!-- ⚖️ → Scale -->
    <h1>
        <?= lucide_icon('<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>', 32, '#fff') ?>
        Complaint Resolution Policy
    </h1>
    <p>How CampusLink handles disputes between students and vendors</p>
</div>

<div class="policy-body">
    <div class="policy-meta">
        Last updated: <?= TERMS_DATE ?> · Version <?= TERMS_VERSION ?>
    </div>

    <div class="policy-section">
        <h2>1. Our Role</h2>
        <p>CampusLink is a directory platform. We do not mediate commercial disputes or guarantee outcomes. However, we take complaints seriously and use them to maintain the quality and safety of our vendor directory.</p>
    </div>

    <div class="policy-section">
        <h2>2. How to File a Complaint</h2>

        <!-- Step 1 — Store / vendor profile -->
        <div class="step-row">
            <div class="step-num">1</div>
            <div class="step-info">
                <h4>Visit the vendor's profile</h4>
                <p>Go to the vendor's listing page and click "Report This Vendor"</p>
            </div>
        </div>

        <!-- Step 2 — ClipboardList / fill form -->
        <div class="step-row">
            <div class="step-num">2</div>
            <div class="step-info">
                <h4>Fill the complaint form</h4>
                <p>Select the complaint category, describe what happened, and attach any evidence (screenshots, receipts)</p>
            </div>
        </div>

        <!-- Step 3 — Ticket / Hash -->
        <div class="step-row">
            <div class="step-num">3</div>
            <div class="step-info">
                <h4>Receive a ticket ID</h4>
                <p>You will receive a unique complaint ticket ID to track your complaint status</p>
            </div>
        </div>

        <!-- Step 4 — Search / investigation -->
        <div class="step-row">
            <div class="step-num">4</div>
            <div class="step-info">
                <h4>Admin investigation</h4>
                <p>Our team reviews the complaint and contacts the vendor for their response within 5 business days</p>
            </div>
        </div>

        <!-- Step 5 — CheckCircle / resolution -->
        <div class="step-row">
            <div class="step-num">5</div>
            <div class="step-info">
                <h4>Resolution</h4>
                <p>A decision is made and both parties are notified. Serious violations result in vendor suspension</p>
            </div>
        </div>
    </div>

    <div class="policy-section">
        <h2>3. What We Can Do</h2>
        <ul>
            <li>Issue formal warnings to vendors</li>
            <li>Temporarily suspend vendor listings</li>
            <li>Permanently remove vendors with repeated violations</li>
            <li>Flag vendors with unresolved complaints in their profile</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>4. What We Cannot Do</h2>
        <ul>
            <li>Force vendors to issue refunds</li>
            <li>Recover money paid outside the platform</li>
            <li>Guarantee outcomes of commercial disputes</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>5. Track Your Complaint</h2>
        <p>
            Use your ticket ID to track your complaint at any time:
            <a href="<?= SITE_URL ?>/complaints/track"
               style="color:#1a56db;font-weight:700;display:inline-flex;align-items:center;gap:0.3rem;">
                Track Complaint
                <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 14, '#1a56db') ?>
            </a>
        </p>
    </div>
</div>