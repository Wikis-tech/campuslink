<?php defined('CAMPUSLINK') or die(); ?>
<style>
.about-hero{background:linear-gradient(135deg,#0b3d91 0%,#1a56db 55%,#0e9f6e 100%);padding:3rem 1rem;text-align:center;color:#fff;}
.about-hero h1{font-size:clamp(1.6rem,5vw,2.5rem);font-weight:900;margin:0 0 0.5rem;letter-spacing:-0.02em;}
.about-hero p{font-size:0.95rem;opacity:0.85;max-width:500px;margin:0 auto;line-height:1.6;}
.about-body{max-width:800px;margin:0 auto;padding:2.5rem 1rem 3rem;}
.about-section{margin-bottom:2.5rem;}
.about-section h2{font-size:1.15rem;font-weight:900;color:#1e293b;margin:0 0 0.75rem;display:flex;align-items:center;gap:0.5rem;}
.about-section p{color:#374151;line-height:1.8;font-size:0.9rem;margin:0 0 0.75rem;}
.value-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1rem;margin-top:1rem;}
@media(max-width:480px){.value-grid{grid-template-columns:1fr 1fr;}}
.value-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;text-align:center;transition:transform 0.2s,box-shadow 0.2s;}
.value-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(26,86,219,0.1);}
.value-card .vi{font-size:2rem;margin-bottom:0.5rem;}
.value-card h3{font-size:0.85rem;font-weight:800;color:#1e293b;margin:0 0 0.3rem;}
.value-card p{font-size:0.75rem;color:#64748b;margin:0;line-height:1.5;}
.team-note{background:linear-gradient(135deg,#eff6ff,#f0fdf4);border-radius:16px;padding:1.75rem;text-align:center;margin-top:1rem;border:1px solid #e2e8f0;}
.team-note h3{font-size:1rem;font-weight:900;color:#1e293b;margin:0 0 0.5rem;}
.team-note p{font-size:0.875rem;color:#374151;margin:0;line-height:1.6;}
.team-note a{color:#1a56db;font-weight:700;text-decoration:none;}
.about-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:1rem;margin:2rem 0;}
.about-stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;text-align:center;}
.about-stat .asv{font-size:1.6rem;font-weight:900;color:#1a56db;}
.about-stat .asl{font-size:0.72rem;color:#64748b;font-weight:600;margin-top:0.2rem;}
</style>

<div class="about-hero">
    <div style="font-size:2.5rem;margin-bottom:0.75rem;">🎓</div>
    <h1>About CampusLink</h1>
    <p>The trusted campus service directory for <?= e(SCHOOL_NAME) ?></p>
</div>

<div class="about-body">

    <div class="about-section">
        <h2>🎯 Our Mission</h2>
        <p>CampusLink exists to bridge the gap between students and service providers within the university environment. We provide a safe, transparent, and easy-to-use directory that helps students find verified vendors quickly — and helps vendors grow their business on campus.</p>
        <p>We are not a marketplace. We do not process payments or guarantee services. We simply connect people.</p>
    </div>

    <div class="about-stats">
        <div class="about-stat">
            <div class="asv">🏪</div>
            <div class="asl">Verified Vendors</div>
        </div>
        <div class="about-stat">
            <div class="asv">🎓</div>
            <div class="asl">Student Users</div>
        </div>
        <div class="about-stat">
            <div class="asv">⭐</div>
            <div class="asl">Honest Reviews</div>
        </div>
        <div class="about-stat">
            <div class="asv">🔒</div>
            <div class="asl">ID Verified</div>
        </div>
    </div>

    <div class="about-section">
        <h2>🏫 What We Do</h2>
        <p>CampusLink is a digital campus directory exclusively for <?= e(SCHOOL_NAME) ?>. Verified vendors list their services, students browse and contact vendors directly via phone or WhatsApp, and both parties complete transactions offline.</p>
        <p>Our platform provides reviews, complaints handling, and subscription-based listings to ensure quality and accountability on both sides.</p>
    </div>

    <div class="about-section">
        <h2>💡 Our Values</h2>
        <div class="value-grid">
            <div class="value-card">
                <div class="vi">🔒</div>
                <h3>Safety First</h3>
                <p>Every vendor is verified before listing. Students can report bad actors anytime.</p>
            </div>
            <div class="value-card">
                <div class="vi">✅</div>
                <h3>Transparency</h3>
                <p>Honest reviews, clear policies, and no hidden fees or misleading listings.</p>
            </div>
            <div class="value-card">
                <div class="vi">🤝</div>
                <h3>Community</h3>
                <p>Built for and by the campus community. Students and vendors grow together.</p>
            </div>
            <div class="value-card">
                <div class="vi">⚡</div>
                <h3>Simplicity</h3>
                <p>Find what you need fast. No account required to browse vendor listings.</p>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2>⚠️ Important Disclaimer</h2>
        <p>CampusLink is a <strong>directory platform only</strong>. We do not process payments between students and vendors, we do not guarantee service quality, and we are not responsible for the outcome of any transaction. Always verify vendors before making payment.</p>
    </div>

    <div class="team-note">
        <h3>Built for <?= e(SCHOOL_NAME) ?></h3>
        <p>CampusLink is purpose-built for this institution and its community. Have questions, feedback, or want to partner with us?
            <a href="<?= SITE_URL ?>/contact">Contact us →</a>
        </p>
    </div>

</div>