<?php defined('CAMPUSLINK') or die(); ?>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>
.lucide{width:15px;height:15px;stroke:currentColor;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;}

.hiw-hero {
    background: linear-gradient(135deg, #0b3d91 0%, #1a56db 55%, #0e9f6e 100%);
    padding: 3rem 1rem 2.5rem;
    text-align: center;
    color: #fff;
}
.hiw-hero h1 {
    font-size: clamp(1.6rem, 5vw, 2.5rem);
    font-weight: 900;
    margin: 0 0 0.5rem;
    letter-spacing: -0.02em;
}
.hiw-hero p {
    font-size: 0.95rem;
    opacity: 0.85;
    max-width: 480px;
    margin: 0 auto;
    line-height: 1.6;
}
.hiw-body {
    max-width: 900px;
    margin: 0 auto;
    padding: 2.5rem 1rem 3rem;
}
.hiw-section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.hiw-section-title .pill {
    background: linear-gradient(135deg, #1a56db, #0e9f6e);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.3rem 0.9rem;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.hiw-section-title h2 {
    font-size: 1.25rem;
    font-weight: 900;
    color: #1e293b;
    margin: 0;
}
.hiw-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
    margin-bottom: 3rem;
}
@media (max-width: 500px) {
    .hiw-steps { grid-template-columns: 1fr; }
}
.hiw-step {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.4rem 1.2rem;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}
.hiw-step:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(26, 86, 219, 0.10);
}
.hiw-step-num {
    position: absolute;
    top: -14px;
    left: 1.2rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a56db, #0e9f6e);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hiw-step-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    margin-top: 0.3rem;
    color: #1a56db;
}
.hiw-step.vendor .hiw-step-icon {
    background: #ecfdf5;
    color: #0e9f6e;
}
.hiw-step h3 {
    font-size: 0.92rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 0.4rem;
}
.hiw-step p {
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}
.hiw-step.vendor .hiw-step-num {
    background: linear-gradient(135deg, #065f46, #0e9f6e);
}
.hiw-step.vendor:hover {
    box-shadow: 0 8px 28px rgba(14, 159, 110, 0.12);
}
.hiw-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    margin: 2.5rem 0;
}
.hiw-disclaimer {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border: 1.5px solid #fde68a;
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 2rem;
}
.hiw-disclaimer .di-top {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.75rem;
    color: #78350f;
}
.hiw-disclaimer h3 {
    font-size: 0.95rem;
    font-weight: 900;
    color: #78350f;
    margin: 0;
}
.hiw-disclaimer p {
    font-size: 0.82rem;
    color: #92400e;
    line-height: 1.7;
    margin: 0;
}
.hiw-cta {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}
.hiw-cta a {
    padding: 0.75rem 1.75rem;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 800;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}
.hiw-cta a:hover {
    opacity: 0.88;
    transform: translateY(-2px);
}
.hiw-cta .btn-student {
    background: linear-gradient(135deg, #1a56db, #0b3d91);
    color: #fff;
}
.hiw-cta .btn-vendor {
    background: linear-gradient(135deg, #0e9f6e, #065f46);
    color: #fff;
}
</style>

<!-- Hero -->
<div class="hiw-hero">
    <div style="display:flex;justify-content:center;margin-bottom:0.6rem;">
        <i data-lucide="zap" style="width:44px;height:44px;stroke:#fff;fill:none;stroke-width:1.5;"></i>
    </div>
    <h1>How CampusLink Works</h1>
    <p>Simple for students. Straightforward for vendors. Safe for everyone.</p>
</div>

<div class="hiw-body">

    <!-- For Students -->
    <div class="hiw-section-title">
        <span class="pill">
            <i data-lucide="graduation-cap" style="width:12px;height:12px;"></i> For Students
        </span>
        <h2>Finding a Vendor</h2>
    </div>

    <div class="hiw-steps">
        <div class="hiw-step">
            <div class="hiw-step-num">1</div>
            <div class="hiw-step-icon">
                <i data-lucide="search" style="width:20px;height:20px;"></i>
            </div>
            <h3>Browse or Search</h3>
            <p>Use the search bar or category filters to find the type of service you need — food, tech, fashion, tutoring, and more.</p>
        </div>
        <div class="hiw-step">
            <div class="hiw-step-num">2</div>
            <div class="hiw-step-icon">
                <i data-lucide="shield-check" style="width:20px;height:20px;"></i>
            </div>
            <h3>Check the Profile</h3>
            <p>View vendor ratings, reviews from real students, price range, and verification badge before reaching out.</p>
        </div>
        <div class="hiw-step">
            <div class="hiw-step-num">3</div>
            <div class="hiw-step-icon">
                <i data-lucide="smartphone" style="width:20px;height:20px;"></i>
            </div>
            <h3>Contact Directly</h3>
            <p>Use the WhatsApp or Call button to contact the vendor directly. All communication is between you and the vendor.</p>
        </div>
        <div class="hiw-step">
            <div class="hiw-step-num">4</div>
            <div class="hiw-step-icon">
                <i data-lucide="handshake" style="width:20px;height:20px;"></i>
            </div>
            <h3>Transact Privately</h3>
            <p>Negotiate, agree on terms, and complete your transaction directly with the vendor. CampusLink is not involved.</p>
        </div>
        <div class="hiw-step">
            <div class="hiw-step-num">5</div>
            <div class="hiw-step-icon">
                <i data-lucide="star" style="width:20px;height:20px;"></i>
            </div>
            <h3>Leave a Review</h3>
            <p>After your experience, visit the vendor's profile and submit an honest star rating and review to help other students.</p>
        </div>
        <div class="hiw-step">
            <div class="hiw-step-num">6</div>
            <div class="hiw-step-icon">
                <i data-lucide="flag" style="width:20px;height:20px;"></i>
            </div>
            <h3>Report Problems</h3>
            <p>If you have a bad experience, file a formal complaint from the vendor's profile. Our team reviews all complaints.</p>
        </div>
    </div>

    <div class="hiw-divider"></div>

    <!-- For Vendors -->
    <div class="hiw-section-title">
        <span class="pill" style="background:linear-gradient(135deg,#065f46,#0e9f6e);">
            <i data-lucide="store" style="width:12px;height:12px;"></i> For Vendors
        </span>
        <h2>Getting Listed</h2>
    </div>

    <div class="hiw-steps">
        <div class="hiw-step vendor">
            <div class="hiw-step-num">1</div>
            <div class="hiw-step-icon">
                <i data-lucide="clipboard-pen" style="width:20px;height:20px;"></i>
            </div>
            <h3>Register</h3>
            <p>Choose Student or Community vendor type and complete your registration. Provide your ID for manual verification.</p>
        </div>
        <div class="hiw-step vendor">
            <div class="hiw-step-num">2</div>
            <div class="hiw-step-icon">
                <i data-lucide="clock" style="width:20px;height:20px;"></i>
            </div>
            <h3>Wait for Approval</h3>
            <p>Our admin team reviews your application within 24–48 hours. You'll be notified by email when approved.</p>
        </div>
        <div class="hiw-step vendor">
            <div class="hiw-step-num">3</div>
            <div class="hiw-step-icon">
                <i data-lucide="credit-card" style="width:20px;height:20px;"></i>
            </div>
            <h3>Pay &amp; Go Live</h3>
            <p>After approval, pay your subscription fee via Paystack. Your profile activates automatically after payment.</p>
        </div>
        <div class="hiw-step vendor">
            <div class="hiw-step-num">4</div>
            <div class="hiw-step-icon">
                <i data-lucide="phone-call" style="width:20px;height:20px;"></i>
            </div>
            <h3>Get Discovered</h3>
            <p>Students can find and contact you via WhatsApp or phone directly from your profile in the directory.</p>
        </div>
        <div class="hiw-step vendor">
            <div class="hiw-step-num">5</div>
            <div class="hiw-step-icon">
                <i data-lucide="trending-up" style="width:20px;height:20px;"></i>
            </div>
            <h3>Build Your Reputation</h3>
            <p>Respond to reviews professionally and maintain a good rating to attract more students.</p>
        </div>
        <div class="hiw-step vendor">
            <div class="hiw-step-num">6</div>
            <div class="hiw-step-icon">
                <i data-lucide="refresh-cw" style="width:20px;height:20px;"></i>
            </div>
            <h3>Renew Each Semester</h3>
            <p>Subscriptions are per semester (180 days). Renew before expiry to stay listed continuously.</p>
        </div>
    </div>

    <div class="hiw-divider"></div>

    <!-- Disclaimer -->
    <div class="hiw-disclaimer">
        <div class="di-top">
            <i data-lucide="alert-triangle" style="width:20px;height:20px;flex-shrink:0;"></i>
            <h3>Critical Disclaimer</h3>
        </div>
        <p>
            CampusLink is a <strong>directory and listing platform only</strong>.
            We do not process payments between students and vendors, we do not mediate
            or guarantee transactions, and we are not responsible for the quality,
            pricing, delivery, or outcome of any services rendered by listed vendors.
            Always exercise caution, verify vendors before paying, and never send money
            to someone you haven't verified in person.
        </p>
    </div>

    <!-- CTAs -->
    <div class="hiw-cta">
        <a href="<?= SITE_URL ?>/register" class="btn-student">
            <i data-lucide="graduation-cap" style="width:16px;height:16px;"></i>
            Join as Student
        </a>
        <a href="<?= SITE_URL ?>/vendor/register" class="btn-vendor">
            <i data-lucide="store" style="width:16px;height:16px;"></i>
            Become a Vendor
        </a>
    </div>

</div>

<script>lucide.createIcons();</script>