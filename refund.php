<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Refund Policy — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/legal.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="<?= BASE_PATH ?>/" class="logo">Campus<strong>link</strong></a>
    <nav class="main-nav">
      <a href="<?= BASE_PATH ?>/" class="nav-link">Home</a>
      <a href="<?= BASE_PATH ?>/refund" class="nav-link active">Refunds</a>
      <a href="<?= BASE_PATH ?>/pricing" class="nav-link">Pricing</a>
      <a href="<?= BASE_PATH ?>/contact" class="nav-link">Contact</a>
    </nav>
  </div>
</header>

<main class="legal-page">
  <!-- Hero Section with Image -->
  <section class="editorial-img reveal" style="height: 400px; background: linear-gradient(135deg, rgba(245,158,11,0.9), rgba(11,61,145,0.9)), url('https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'); background-size: cover; background-position: center; display: flex; align-items: center;">
    <div class="container">
      <div class="reveal-left">
        <h1 class="text-gradient" style="color: white; font-size: 3rem; margin-bottom: 1rem;">Refund Policy</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; max-width: 600px;">We stand behind our service. Learn about our fair and transparent refund process.</p>
      </div>
    </div>
  </section>

  <div class="container breath">
    <div class="glass-card reveal stagger-1" style="padding: 3rem; margin-bottom: 2rem;">
      <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Last updated: April 4, 2026</p>
    </div>

    <div class="organic-grid">
      <div class="layered reveal stagger-2">
        <h2 class="text-gradient">Subscription Refunds</h2>
        <p>We offer a 30-day money-back guarantee for all subscription plans. If you're not satisfied with CampusLink within the first 30 days of your subscription, we'll provide a full refund.</p>
        <p><strong>Eligibility:</strong> Refunds are available for annual and monthly subscriptions purchased directly through our website.</p>
        <p><strong>Process:</strong> Contact our support team at refunds@campuslink.com with your account details and reason for refund.</p>
      </div>

      <div class="editorial-img reveal stagger-3" style="height: 300px; background: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
    </div>

    <div class="organic-grid">
      <div class="editorial-img reveal stagger-4" style="height: 300px; background: url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

      <div class="layered reveal stagger-5">
        <h2 class="text-gradient">One-Time Payments</h2>
        <p>For one-time purchases such as premium features or add-ons, refunds are available within 7 days of purchase if the service has not been used.</p>
        <p><strong>Exceptions:</strong> No refunds for services that have been fully delivered or consumed.</p>
      </div>
    </div>

    <div class="glass-card reveal" style="padding: 2rem; margin: 2rem 0;">
      <h2 class="text-gradient">Processing Time</h2>
      <p>Approved refunds are typically processed within 5-10 business days. The time for the refund to appear in your account depends on your payment method:</p>
      <ul>
        <li><strong>Credit/Debit Cards:</strong> 3-5 business days</li>
        <li><strong>PayPal:</strong> 1-3 business days</li>
        <li><strong>Bank Transfer:</strong> 5-10 business days</li>
      </ul>
    </div>

    <div class="organic-grid">
      <div class="layered reveal">
        <h2 class="text-gradient">Contact Us</h2>
        <p>If you have questions about our refund policy or need to request a refund, please contact us:</p>
        <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <i data-lucide="mail" style="width: 24px; height: 24px; color: var(--primary);"></i>
            <span>refunds@campuslink.com</span>
          </div>
          <div style="display: flex; align-items: center; gap: 1rem;">
            <i data-lucide="phone" style="width: 24px; height: 24px; color: var(--primary);"></i>
            <span>+1 (555) 123-4567</span>
          </div>
          <div style="display: flex; align-items: center; gap: 1rem;">
            <i data-lucide="clock" style="width: 24px; height: 24px; color: var(--primary);"></i>
            <span>Monday - Friday, 9 AM - 6 PM EST</span>
          </div>
        </div>
      </div>

      <div class="editorial-img reveal" style="height: 250px; background: url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
    </div>

    <div class="breath">
      <div class="disclaimer-box reveal">
        <i data-lucide="info"></i>
        <div>
          <p><strong>Important:</strong> All refund requests are reviewed on a case-by-case basis. We reserve the right to deny refunds for accounts that violate our terms of service.</p>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-layout">
      <div class="footer-brand">
        <div class="footer-logo">Campus<strong>link</strong></div>
        <p class="footer-desc">A secure, lightweight digital campus service directory connecting students with verified vendors within the university environment.</p>
        <p class="footer-desc">CampusLink is a directory platform only. We do not provide services, process transactions, or mediate between users and vendors.</p>
        <p class="footer-contact">📧 campuslinkd@gmail.com</p>
      </div>
      <div class="footer-links">
        <h4>Quick Links</h4>
        <a href="<?= BASE_PATH ?>/">Home</a>
        <a href="<?= BASE_PATH ?>/browse">Browse Services</a>
        <a href="<?= BASE_PATH ?>/categories">All Categories</a>
        <a href="<?= BASE_PATH ?>/how-it-works">How It Works</a>
        <a href="#about">About CampusLink</a>
        <a href="#contact">Contact Us</a>
      </div>
      <div class="footer-links">
        <h4>For Vendors</h4>
        <a href="<?= BASE_PATH ?>/vendor-register">Register as Student Vendor</a>
        <a href="<?= BASE_PATH ?>/vendor-register">Register as Community Vendor</a>
        <a href="<?= BASE_PATH ?>/vendor/login">Vendor Login</a>
        <a href="<?= BASE_PATH ?>/vendor-terms">Vendor Terms & Conditions</a>
        <a href="#suspension">Suspension Policy</a>
        <a href="#complaints">Complaint Resolution</a>
      </div>
      <div class="footer-links">
        <h4>Legal & Policies</h4>
        <a href="<?= BASE_PATH ?>/terms">General Terms & Conditions</a>
        <a href="<?= BASE_PATH ?>/terms">User Terms & Conditions</a>
        <a href="<?= BASE_PATH ?>/terms">Vendor Terms & Conditions</a>
        <a href="<?= BASE_PATH ?>/privacy">Privacy Policy</a>
        <a href="<?= BASE_PATH ?>/refund">Refund Policy</a>
        <a href="#data-retention">Data Retention Policy</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 CampusLink. All rights reserved. | Governed by Nigerian Law</p>
      <p>Built for the campus community 🎓</p>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>