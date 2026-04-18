<?php defined('CAMPUSLINK') or die(); ?>

<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand Column -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <span style="color:#93c5fd;">Campus</span><span style="color:#34d399;">Link</span>
                </div>
                <p class="footer-tagline">
                    <?= e(SITE_TAGLINE) ?> â€” connecting students with verified campus service providers.
                </p>
                <div class="footer-disclaimer-small">
                    <strong><i data-lucide="alert-triangle" class="footer-icon" aria-hidden="true"></i> Directory Only:</strong> CampusLink is a listing platform.
                    All transactions occur directly between users and vendors.
                    We are not responsible for service quality or outcomes.
                </div>
                <div class="footer-contact-info" style="display:flex;flex-direction:column;gap:0.5rem;">
                    <a href="mailto:<?= e(SUPPORT_EMAIL) ?>">
                        <i data-lucide="mail" class="footer-icon" aria-hidden="true"></i> <?= e(SUPPORT_EMAIL) ?>
                    </a>
                    <?php if (defined('SUPPORT_PHONE') && SUPPORT_PHONE): ?>
                    <a href="tel:<?= e(SUPPORT_PHONE) ?>">
                        <i data-lucide="phone" class="footer-icon" aria-hidden="true"></i> <?= e(SUPPORT_PHONE) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <div class="footer-col-title">Quick Links</div>
                <div class="footer-links">
                    <a href="<?= SITE_URL ?>/browse">Browse Vendors</a>
                    <a href="<?= SITE_URL ?>/categories">All Categories</a>
                    <a href="<?= SITE_URL ?>/how-it-works">How It Works</a>
                    <a href="<?= SITE_URL ?>/about">About Us</a>
                    <a href="<?= SITE_URL ?>/contact">Contact Us</a>
                </div>
            </div>

            <!-- Vendors -->
            <div>
                <div class="footer-col-title">Vendors</div>
                <div class="footer-links">
                    <a href="<?= SITE_URL ?>/vendor/register?type=student">Register as Student</a>
                    <a href="<?= SITE_URL ?>/vendor/register?type=community">Register as Business</a>
                    <a href="<?= SITE_URL ?>/vendor/login">Vendor Login</a>
                    <a href="<?= SITE_URL ?>/vendor-terms">Vendor Terms</a>
                    <a href="<?= SITE_URL ?>/refund-policy">Refund Policy</a>
                </div>
            </div>

            <!-- Legal -->
            <div>
                <div class="footer-col-title">Legal & Policies</div>
                <div class="footer-links">
                    <a href="<?= SITE_URL ?>/general-terms">General Terms</a>
                    <a href="<?= SITE_URL ?>/user-terms">User Terms</a>
                    <a href="<?= SITE_URL ?>/privacy-policy">Privacy Policy</a>
                    <a href="<?= SITE_URL ?>/suspension-policy">Suspension Policy</a>
                    <a href="<?= SITE_URL ?>/complaint-resolution">Complaint Resolution</a>
                    <a href="<?= SITE_URL ?>/data-retention">Data Retention</a>
                </div>
            </div>

        </div><!-- /footer-grid -->

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
            <span>
                Affiliated with <?= e(SCHOOL_NAME) ?> Â· Nigeria ðŸ‡³ðŸ‡¬
            </span>
            <span>
                Powered by <strong style="color:rgba(255,255,255,0.65);">CampusLink v1.0</strong>
            </span>
        </div>
    </div>
</footer>
