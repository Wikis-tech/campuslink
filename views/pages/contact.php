<?php defined('CAMPUSLINK') or die(); ?>
<style>
.contact-hero{background:linear-gradient(135deg,#0b3d91,#1a56db);padding:2.5rem 1rem;text-align:center;color:#fff;}
.contact-hero h1{font-size:clamp(1.5rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem;}
.contact-hero p{font-size:0.88rem;opacity:0.85;margin:0;}
.contact-body{max-width:900px;margin:0 auto;padding:2rem 1rem 3rem;}
.contact-grid{display:grid;grid-template-columns:1fr 1.6fr;gap:1.5rem;}
@media(max-width:650px){.contact-grid{grid-template-columns:1fr;}}
.contact-info-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;}
.contact-info-card h3{font-size:0.95rem;font-weight:800;color:#1e293b;margin:0 0 1.25rem;}
.ci-item{display:flex;gap:0.75rem;align-items:flex-start;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #e2e8f0;}
.ci-item:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0;}
.ci-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.ci-label{font-size:0.7rem;font-weight:700;color:#64748b;margin-bottom:0.15rem;text-transform:uppercase;letter-spacing:0.05em;}
.ci-value{font-size:0.85rem;color:#1e293b;font-weight:600;}
.ci-value a{color:#1a56db;text-decoration:none;}
.ci-value a:hover{text-decoration:underline;}
.contact-form-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.5rem;box-shadow:0 2px 12px rgba(0,0,0,0.04);}
.contact-form-card h3{font-size:0.95rem;font-weight:800;color:#1e293b;margin:0 0 1.25rem;}
.cf-group{margin-bottom:1rem;}
.cf-group label{display:block;font-size:0.78rem;font-weight:700;color:#374151;margin-bottom:0.35rem;}
.cf-group input,.cf-group select,.cf-group textarea{width:100%;padding:0.7rem 0.9rem;border:1.5px solid #e2e8f0;border-radius:10px;font-size:0.875rem;outline:none;transition:border 0.2s;box-sizing:border-box;font-family:inherit;color:#1e293b;background:#fff;}
.cf-group input:focus,.cf-group select:focus,.cf-group textarea:focus{border-color:#1a56db;box-shadow:0 0 0 3px rgba(26,86,219,0.08);}
.cf-group textarea{resize:vertical;min-height:120px;}
.cf-row{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;}
@media(max-width:420px){.cf-row{grid-template-columns:1fr;}}
.cf-btn{width:100%;padding:0.8rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);color:#fff;border:none;border-radius:10px;font-weight:800;font-size:0.9rem;cursor:pointer;transition:opacity 0.2s,transform 0.15s;margin-top:0.25rem;}
.cf-btn:hover{opacity:0.9;transform:translateY(-1px);}
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:0.75rem 1rem;border-radius:10px;font-size:0.83rem;margin-bottom:1rem;font-weight:600;}
.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:0.75rem 1rem;border-radius:10px;font-size:0.83rem;margin-bottom:1rem;}
.response-note{margin-top:1rem;padding:0.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;font-size:0.75rem;color:#92400e;line-height:1.5;}
</style>

<div class="contact-hero">
    <div style="font-size:2rem;margin-bottom:0.5rem;">📩</div>
    <h1>Contact Us</h1>
    <p>We are here to help. Reach out anytime.</p>
</div>

<div class="contact-body">
    <?php require VIEWS_PATH . '/partials/flash.php'; ?>

    <div class="contact-grid">

        <!-- Contact Info -->
        <div>
            <div class="contact-info-card">
                <h3>Get In Touch</h3>

                <div class="ci-item">
                    <div class="ci-icon" style="background:#eff6ff;">📧</div>
                    <div>
                        <div class="ci-label">Support Email</div>
                        <div class="ci-value">
                            <a href="mailto:<?= e(SUPPORT_EMAIL) ?>"><?= e(SUPPORT_EMAIL) ?></a>
                        </div>
                    </div>
                </div>

                <div class="ci-item">
                    <div class="ci-icon" style="background:#f0fdf4;">📞</div>
                    <div>
                        <div class="ci-label">Phone</div>
                        <div class="ci-value">
                            <a href="tel:<?= e(SITE_PHONE) ?>"><?= e(SITE_PHONE) ?></a>
                        </div>
                    </div>
                </div>

                <div class="ci-item">
                    <div class="ci-icon" style="background:#fefce8;">🏫</div>
                    <div>
                        <div class="ci-label">Institution</div>
                        <div class="ci-value"><?= e(SCHOOL_NAME) ?></div>
                    </div>
                </div>

                <div class="ci-item">
                    <div class="ci-icon" style="background:#f5f3ff;">⏰</div>
                    <div>
                        <div class="ci-label">Response Time</div>
                        <div class="ci-value">Within 24 hours</div>
                    </div>
                </div>

            </div>

            <div style="margin-top:1rem;padding:1rem;background:#fff;border:1px solid #e2e8f0;border-radius:12px;">
                <div style="font-size:0.78rem;font-weight:800;color:#374151;margin-bottom:0.5rem;">Quick Links</div>
                <div style="display:flex;flex-direction:column;gap:0.4rem;">
                    <a href="<?= SITE_URL ?>/complaints/track"
                       style="font-size:0.78rem;color:#1a56db;text-decoration:none;font-weight:600;">
                        📋 Track a Complaint →
                    </a>
                    <a href="<?= SITE_URL ?>/vendor/register"
                       style="font-size:0.78rem;color:#0e9f6e;text-decoration:none;font-weight:600;">
                        🏪 Register as Vendor →
                    </a>
                    <a href="<?= SITE_URL ?>/about"
                       style="font-size:0.78rem;color:#64748b;text-decoration:none;font-weight:600;">
                        ℹ️ About CampusLink →
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div>
            <div class="contact-form-card">
                <h3>Send a Message</h3>
                <form method="POST" action="<?= SITE_URL ?>/contact">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="cf-row">
                        <div class="cf-group">
                            <label>Your Name *</label>
                            <input type="text" name="name"
                                   placeholder="Full name"
                                   value="<?= e($_SESSION['form_old']['name'] ?? '') ?>"
                                   required>
                        </div>
                        <div class="cf-group">
                            <label>Email Address *</label>
                            <input type="email" name="email"
                                   placeholder="you@email.com"
                                   value="<?= e($_SESSION['form_old']['email'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                    <div class="cf-group">
                        <label>Subject *</label>
                        <select name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="vendor_registration">Vendor Registration Issue</option>
                            <option value="account_problem">Account Problem</option>
                            <option value="payment_issue">Payment Issue</option>
                            <option value="report_vendor">Report a Vendor</option>
                            <option value="general">General Enquiry</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="cf-group">
                        <label>Message *</label>
                        <textarea name="message"
                                  placeholder="Describe your issue or question in detail..."
                                  required><?= e($_SESSION['form_old']['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="cf-btn">📨 Send Message</button>
                </form>

                <div class="response-note">
                    ⚠️ For vendor-related complaints, please use the
                    <a href="<?= SITE_URL ?>/complaints/track"
                       style="color:#92400e;font-weight:700;">complaint tracker</a>
                    instead of this form. Include your business name and registered email for faster support.
                </div>
            </div>
        </div>

    </div>
</div>