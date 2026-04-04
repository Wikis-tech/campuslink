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
  <title>How It Works — Campuslink</title>
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
      <a href="<?= BASE_PATH ?>/how-it-works" class="nav-link active">How It Works</a>
      <a href="<?= BASE_PATH ?>/pricing" class="nav-link">Pricing</a>
      <a href="<?= BASE_PATH ?>/register" class="nav-link">Register</a>
    </nav>
  </div>
</header>

<main class="legal-page">
  <!-- Hero Section -->
  <section class="editorial-img reveal" style="height: 500px; background: linear-gradient(135deg, rgba(30,169,82,0.9), rgba(11,61,145,0.9)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'); background-size: cover; background-position: center; display: flex; align-items: center;">
    <div class="container">
      <div class="reveal-scale">
        <h1 class="text-gradient" style="color: white; font-size: 3.5rem; margin-bottom: 1rem; text-align: center;">How CampusLink Works</h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1.3rem; max-width: 700px; margin: 0 auto; text-align: center;">A simple, powerful platform connecting students with campus services. Discover, review, and connect in just a few clicks.</p>
      </div>
    </div>
  </section>

  <div class="container breath">
    <!-- For Students Section -->
    <section class="section reveal">
      <div class="section-header">
        <span class="section-eyebrow">For Students</span>
        <h2 class="section-title">Find & Review Campus Services</h2>
        <p class="section-desc">Discover the best services on your campus with real reviews from fellow students.</p>
      </div>

      <div class="organic-grid">
        <div class="layered reveal stagger-1">
          <div class="glass-card" style="padding: 2rem;">
            <i data-lucide="search" style="width: 48px; height: 48px; color: var(--primary); margin-bottom: 1rem;"></i>
            <h3>Search & Discover</h3>
            <p>Browse through categories or search for specific services. Filter by ratings, price, and location.</p>
          </div>
        </div>

        <div class="editorial-img reveal stagger-2 img-interactive" style="height: 300px; background: url('https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

        <div class="editorial-img reveal stagger-3 img-interactive" style="height: 300px; background: url('https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

        <div class="layered reveal stagger-4">
          <div class="glass-card" style="padding: 2rem;">
            <i data-lucide="star" style="width: 48px; height: 48px; color: var(--amber); margin-bottom: 1rem;"></i>
            <h3>Read Reviews</h3>
            <p>Make informed decisions with authentic reviews from verified students. See ratings and photos.</p>
          </div>
        </div>
      </div>

      <div class="organic-grid">
        <div class="layered reveal stagger-5">
          <div class="glass-card" style="padding: 2rem;">
            <i data-lucide="phone" style="width: 48px; height: 48px; color: var(--accent); margin-bottom: 1rem;"></i>
            <h3>Contact & Connect</h3>
            <p>Directly contact vendors through the platform. Book appointments or place orders easily.</p>
          </div>
        </div>

        <div class="editorial-img reveal stagger-6 img-interactive" style="height: 300px; background: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
      </div>
    </section>

    <!-- For Vendors Section -->
    <section class="section reveal">
      <div class="section-header">
        <span class="section-eyebrow">For Vendors</span>
        <h2 class="section-title">Grow Your Campus Business</h2>
        <p class="section-desc">Connect with thousands of students and build your reputation on campus.</p>
      </div>

      <div class="organic-grid">
        <div class="editorial-img reveal img-interactive" style="height: 350px; background: url('https://images.unsplash.com/photo-1556761175-b413da4baf72?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

        <div class="layered reveal">
          <div class="glass-card" style="padding: 2rem;">
            <i data-lucide="user-plus" style="width: 48px; height: 48px; color: var(--primary); margin-bottom: 1rem;"></i>
            <h3>Create Your Profile</h3>
            <p>Set up your business profile with photos, descriptions, pricing, and service details.</p>
          </div>
        </div>
      </div>

      <div class="organic-grid">
        <div class="layered reveal">
          <div class="glass-card" style="padding: 2rem;">
            <i data-lucide="trending-up" style="width: 48px; height: 48px; color: var(--accent); margin-bottom: 1rem;"></i>
            <h3>Attract Customers</h3>
            <p>Get discovered by students searching for your services. Build trust with reviews and ratings.</p>
          </div>
        </div>

        <div class="editorial-img reveal img-interactive" style="height: 350px; background: url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>
      </div>

      <div class="organic-grid">
        <div class="editorial-img reveal img-interactive" style="height: 350px; background: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover;"></div>

        <div class="layered reveal">
          <div class="glass-card" style="padding: 2rem;">
            <i data-lucide="bar-chart-3" style="width: 48px; height: 48px; color: var(--amber); margin-bottom: 1rem;"></i>
            <h3>Manage & Grow</h3>
            <p>Track your performance, respond to reviews, and grow your business with analytics and insights.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="vendor-cta-section reveal">
      <div class="vendor-cta-card">
        <div class="container">
          <div class="asym-left">
            <h2 style="margin-bottom: 1rem;">Ready to Get Started?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Join thousands of students and vendors already using CampusLink.</p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
              <a href="<?= BASE_PATH ?>/register" class="btn btn-white">Join as Student</a>
              <a href="<?= BASE_PATH ?>/vendor-register" class="btn btn-outline-white">Register as Vendor</a>
            </div>
          </div>
        </div>
      </div>
    </section>
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