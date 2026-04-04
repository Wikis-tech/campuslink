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
  <title>Privacy Policy — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/legal.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="<?= BASE_PATH ?: '/' ?>" class="logo">Campus<strong>link</strong></a>
    <nav class="main-nav">
      <a href="<?= BASE_PATH ?>/" class="nav-link">Home</a>
      <a href="<?= BASE_PATH ?>/privacy" class="nav-link active">Privacy</a>
      <a href="<?= BASE_PATH ?>/terms" class="nav-link">Terms</a>
      <a href="<?= BASE_PATH ?>/how-it-works" class="nav-link">How It Works</a>
    </nav>
  </div>
</header>

<main class="legal-page">
  <!-- Hero Section with Image -->
  <section class="editorial-img reveal" style="height: 400px; background: linear-gradient(135deg, rgba(11,61,145,0.9), rgba(30,169,82,0.9)), url('https://images.unsplash.com/photo-1563013544-824ae1b704d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'); background-size: cover; background-position: center; display: flex; align-items: center;">
    <div class="container">
      <div class="reveal-left">
        <h1 class="text-gradient" style="color: white; font-size: 3rem; margin-bottom: 1rem;">Privacy Policy</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; max-width: 600px;">Your privacy is our priority. Learn how we protect and handle your data.</p>
      </div>
    </div>
  </section>

  <div class="container breath">
    <div class="glass-card reveal stagger-1" style="padding: 3rem; margin-bottom: 2rem;">
      <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Last updated: April 4, 2026</p>
    </div>

    <div class="organic-grid">
      <div class="layered reveal stagger-2">
        <h2 class="text-gradient">Information We Collect</h2>
        <p>We collect information you provide directly to us, such as when you create an account, use our services, or contact us for support. This includes:</p>
        <ul>
          <li><strong>Personal Information:</strong> Name, email address, phone number, and payment information.</li>
          <li><strong>Usage Data:</strong> Information about how you interact with our platform, including pages visited and features used.</li>
          <li><strong>Device Information:</strong> IP address, browser type, and device characteristics.</li>
        </ul>
      </div>

      <div class="editorial-img reveal stagger-3" style="height: 300px; background: url('https://images.unsplash.com/photo-1551808525-51a94da548ce?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
    </div>

    <div class="organic-grid">
      <div class="editorial-img reveal stagger-4" style="height: 300px; background: url('https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

      <div class="layered reveal stagger-5">
        <h2 class="text-gradient">How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
          <li>Provide, maintain, and improve our services</li>
          <li>Process transactions and send related information</li>
          <li>Send you technical notices and support messages</li>
          <li>Communicate with you about products, services, and promotions</li>
          <li>Monitor and analyze trends and usage</li>
        </ul>
      </div>
    </div>

    <div class="glass-card reveal" style="padding: 2rem; margin: 2rem 0;">
      <h2 class="text-gradient">Data Sharing and Disclosure</h2>
      <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>
      <p>We may share your information in the following circumstances:</p>
      <ul>
        <li>With service providers who assist us in operating our platform</li>
        <li>To comply with legal obligations</li>
        <li>To protect our rights and prevent fraud</li>
        <li>In connection with a business transfer</li>
      </ul>
    </div>

    <div class="organic-grid">
      <div class="layered reveal">
        <h2 class="text-gradient">Data Security</h2>
        <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
        <p>However, no method of transmission over the internet is 100% secure. We cannot guarantee absolute security.</p>
      </div>

      <div class="editorial-img reveal" style="height: 250px; background: url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
    </div>

    <div class="glass-card reveal" style="padding: 2rem;">
      <h2 class="text-gradient">Your Rights</h2>
      <p>You have the right to:</p>
      <ul>
        <li>Access the personal information we hold about you</li>
        <li>Correct inaccurate or incomplete information</li>
        <li>Request deletion of your personal information</li>
        <li>Object to or restrict processing of your information</li>
        <li>Data portability</li>
      </ul>
      <p>To exercise these rights, please contact us at privacy@campuslink.com</p>
    </div>

    <div class="breath">
      <div class="disclaimer-box reveal">
        <i data-lucide="shield"></i>
        <div>
          <p><strong>Important Notice:</strong> This privacy policy may be updated periodically. We will notify you of any material changes by posting the new policy on this page.</p>
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