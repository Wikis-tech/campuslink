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
  <title>Terms of Service — Campuslink</title>
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
      <a href="<?= BASE_PATH ?>/terms" class="nav-link active">Terms</a>
      <a href="<?= BASE_PATH ?>/privacy" class="nav-link">Privacy</a>
      <a href="<?= BASE_PATH ?>/how-it-works" class="nav-link">How It Works</a>
    </nav>
  </div>
</header>

<main class="legal-page">
  <!-- Hero Section with Image -->
  <section class="editorial-img reveal" style="height: 400px; background: linear-gradient(135deg, rgba(11,61,145,0.9), rgba(245,158,11,0.9)), url('https://images.unsplash.com/photo-1589829545856-d10d557cf95f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'); background-size: cover; background-position: center; display: flex; align-items: center;">
    <div class="container">
      <div class="reveal-right">
        <h1 class="text-gradient" style="color: white; font-size: 3rem; margin-bottom: 1rem;">Terms of Service</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem; max-width: 600px;">Clear guidelines for using CampusLink. Read carefully to understand your rights and responsibilities.</p>
      </div>
    </div>
  </section>

  <div class="container breath">
    <div class="glass-card reveal stagger-1" style="padding: 3rem; margin-bottom: 2rem;">
      <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Last updated: April 4, 2026</p>
    </div>

    <div class="organic-grid">
      <div class="layered reveal stagger-2">
        <h2 class="text-gradient">1. Acceptance of Terms</h2>
        <p>By accessing and using CampusLink ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
        <p>This agreement applies to all users of the Service, including without limitation users who are browsers, vendors, customers, merchants, and/or contributors of content.</p>
      </div>

      <div class="editorial-img reveal stagger-3" style="height: 300px; background: url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
    </div>

    <div class="organic-grid">
      <div class="editorial-img reveal stagger-4" style="height: 300px; background: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

      <div class="layered reveal stagger-5">
        <h2 class="text-gradient">2. Use License</h2>
        <p>Permission is granted to temporarily download one copy of the materials on CampusLink for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
        <ul>
          <li>Modify or copy the materials</li>
          <li>Use the materials for any commercial purpose or for any public display</li>
          <li>Attempt to decompile or reverse engineer any software contained on CampusLink</li>
          <li>Remove any copyright or other proprietary notations from the materials</li>
        </ul>
      </div>
    </div>

    <div class="glass-card reveal" style="padding: 2rem; margin: 2rem 0;">
      <h2 class="text-gradient">3. User Accounts</h2>
      <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for all activities that occur under your account.</p>
      <p>You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>
    </div>

    <div class="organic-grid">
      <div class="layered reveal">
        <h2 class="text-gradient">4. Prohibited Uses</h2>
        <p>You may not use our Service:</p>
        <ul>
          <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
          <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
          <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
          <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
          <li>To submit false or misleading information</li>
        </ul>
      </div>

      <div class="editorial-img reveal" style="height: 250px; background: url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
    </div>

    <div class="glass-card reveal" style="padding: 2rem;">
      <h2 class="text-gradient">5. Content</h2>
      <p>Our Service allows you to post, link, store, share and otherwise make available certain information, text, graphics, or other material ("Content"). You are responsible for the Content that you post to the Service, including its legality, reliability, and appropriateness.</p>
      <p>By posting Content to the Service, you grant us the right and license to use, modify, publicly perform, publicly display, reproduce, and distribute such Content on and through the Service.</p>
    </div>

    <div class="breath">
      <div class="disclaimer-box reveal">
        <i data-lucide="alert-triangle"></i>
        <div>
          <p><strong>Termination:</strong> We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
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