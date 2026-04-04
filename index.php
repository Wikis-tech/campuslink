<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
$config = APP_CONFIG;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Campuslink — Find trusted campus services instantly. Connect with verified vendors within your university community." />
  <meta name="theme-color" content="#0b3d91" />
  <meta name="csrf-token" content="<?= $csrf ?>" />
  <title>Campuslink — Campus Service Directory</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/hero.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="header-inner container">
    <a href="/" class="logo">
      <div class="logo-mark">
        <img src="assets/images/campuslink-logo-white.png" alt="Campuslink Logo" width="36" height="36" onerror="this.style.display='none'" />
        <svg class="logo-fallback" width="36" height="36" viewBox="0 0 36 36" fill="none">
          <rect width="36" height="36" rx="10" fill="#0b3d91"/>
          <path d="M9 18C9 12.477 13.477 8 19 8s10 4.477 10 10" stroke="white" stroke-width="3" stroke-linecap="round"/>
          <circle cx="18" cy="22" r="3.5" fill="#1ea952"/>
          <path d="M14 28h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="logo-text">Campus<strong>link</strong></span>
    </a>

    <nav class="main-nav" id="mainNav">
      <a href="<?= BASE_PATH ?: '/' ?>" class="nav-link active">Home</a>
      <a href="<?= BASE_PATH ?>/browse" class="nav-link">Browse</a>
      <a href="<?= BASE_PATH ?>/categories" class="nav-link">Categories</a>
      <a href="<?= BASE_PATH ?>/how-it-works" class="nav-link">How It Works</a>
      <a href="<?= BASE_PATH ?>/vendor-register" class="nav-link nav-vendor-btn"><i data-lucide="store"></i> Register</a>
      <a href="<?= BASE_PATH ?>/login" class="nav-link nav-login-btn">Login</a>
    </nav>

    <div class="header-school-logo">
      <img src="assets/images/Uat logo.png" alt="University Logo" width="40" height="40" onerror="this.style.display='none'" />
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- HERO -->
<section class="hero" id="hero">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>

  <div class="container hero-content">
    <div class="hero-badge animate-fade-up">
      <i data-lucide="shield-check"></i>
      <span>Verified Campus Vendors Only</span>
    </div>

    <h1 class="hero-headline animate-slide-up">
      Find trusted campus<br />services <em>instantly.</em>
    </h1>

    <p class="hero-sub animate-fade-up delay-2">
      Campuslink connects students and campus community members with verified, reviewed service providers within your university — safely, transparently, and at no cost to browse.
    </p>

    <div class="hero-search animate-fade-up delay-3">
      <div class="search-wrap">
        <div class="search-category">
          <i data-lucide="layout-grid"></i>
          <select id="searchCat" aria-label="Select category">
            <option value="">All Categories</option>
            <option value="beauty">Beauty & Grooming</option>
            <option value="tech">Tech & Gadgets</option>
            <option value="academic">Academic Support</option>
            <option value="food">Food & Catering</option>
            <option value="fashion">Fashion & Tailoring</option>
            <option value="printing">Printing</option>
            <option value="repairs">Repairs</option>
            <option value="photography">Photography</option>
            <option value="tutoring">Tutoring</option>
          </select>
        </div>
        <div class="search-divider"></div>
        <div class="search-input-wrap">
          <i data-lucide="search"></i>
          <input type="text" id="searchInput" placeholder="Search vendors or services…" aria-label="Search vendors" />
        </div>
        <button class="search-btn" onclick="doSearch()">
          <i data-lucide="search"></i>
          <span>Search</span>
        </button>
      </div>
    </div>

    <div class="hero-actions animate-fade-up delay-4">
      <a href="<?= BASE_PATH ?>/browse" class="btn btn-white">
        <i data-lucide="compass"></i> Browse Services
      </a>
      <a href="<?= BASE_PATH ?>/vendor-register" class="btn btn-outline-white">
        <i data-lucide="store"></i> Register as Vendor
      </a>
    </div>

    <div class="hero-trust animate-fade-up delay-5">
      <div class="trust-pill"><i data-lucide="check-circle"></i> Admin Verified</div>
      <div class="trust-pill"><i data-lucide="lock"></i> Secure Payments</div>
      <div class="trust-pill"><i data-lucide="star"></i> Moderated Reviews</div>
    </div>
  </div>

  <div class="hero-scroll-indicator">
    <div class="scroll-dot"></div>
  </div>
</section>

<!-- STATS STRIP -->
<section class="stats-strip">
  <div class="container">
    <div class="stats-inner">
      <div class="stat-pill" data-count="340" data-suffix="+">
        <i data-lucide="store"></i>
        <div>
          <strong class="stat-num">0</strong>
          <span>Verified Vendors</span>
        </div>
      </div>
      <div class="strip-divider"></div>
      <div class="stat-pill" data-count="5200" data-suffix="+">
        <i data-lucide="users"></i>
        <div>
          <strong class="stat-num">0</strong>
          <span>Registered Users</span>
        </div>
      </div>
      <div class="strip-divider"></div>
      <div class="stat-pill" data-count="12" data-suffix="">
        <i data-lucide="layout-grid"></i>
        <div>
          <strong class="stat-num">0</strong>
          <span>Categories</span>
        </div>
      </div>
      <div class="strip-divider"></div>
      <div class="stat-pill" data-count="4.8" data-suffix="★" data-decimal="true">
        <i data-lucide="star"></i>
        <div>
          <strong class="stat-num">0</strong>
          <span>Avg. Rating</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="section categories-section" id="categories">
  <div class="container">
    <div class="section-label" data-aos="fade-up">
      <div class="label-line"></div>
      <span>Explore</span>
      <div class="label-line"></div>
    </div>
    <h2 class="section-headline" data-aos="fade-up" data-aos-delay="50">Browse by Category</h2>
    <p class="section-sub" data-aos="fade-up" data-aos-delay="100">Discover verified service providers across every campus need.</p>
    <div class="categories-grid" id="categoriesGrid"></div>
  </div>
</section>

<!-- FEATURED VENDORS -->
<section class="section featured-section" id="featured">
  <div class="container">
    <div class="featured-header">
      <div>
        <div class="section-label" data-aos="fade-up">
          <div class="label-line"></div>
          <span>Handpicked</span>
          <div class="label-line"></div>
        </div>
        <h2 class="section-headline" data-aos="fade-up" data-aos-delay="50">Featured Vendors</h2>
        <p class="section-sub" data-aos="fade-up" data-aos-delay="100">Top-rated, verified service providers trusted by your peers.</p>
      </div>
      <a href="<?= BASE_PATH ?>/browse" class="btn btn-outline-primary" data-aos="fade-up" data-aos-delay="150">
        View All <i data-lucide="arrow-right"></i>
      </a>
    </div>
    <div class="vendors-grid" id="vendorsGrid"></div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section how-section" id="how">
  <div class="container">
    <div class="section-label" data-aos="fade-up">
      <div class="label-line"></div>
      <span>Simple Process</span>
      <div class="label-line"></div>
    </div>
    <h2 class="section-headline" data-aos="fade-up" data-aos-delay="50">How Campuslink Works</h2>
    <div class="steps-timeline">
      <div class="step-item" data-aos="fade-right" data-aos-delay="0">
        <div class="step-connector-line"></div>
        <div class="step-dot"><span>01</span></div>
        <div class="step-content glass-card">
          <div class="step-icon-wrap"><i data-lucide="search"></i></div>
          <h3>Browse Vendors</h3>
          <p>Search by category, name, price range, or rating. Filter to find the perfect service provider for your need.</p>
        </div>
      </div>
      <div class="step-item right" data-aos="fade-left" data-aos-delay="100">
        <div class="step-dot"><span>02</span></div>
        <div class="step-content glass-card">
          <div class="step-icon-wrap"><i data-lucide="phone-call"></i></div>
          <h3>Contact Directly</h3>
          <p>Reach vendors instantly via phone call or WhatsApp. No in-app messaging — all communication is direct and personal.</p>
        </div>
        <div class="step-connector-line"></div>
      </div>
      <div class="step-item" data-aos="fade-right" data-aos-delay="200">
        <div class="step-connector-line"></div>
        <div class="step-dot"><span>03</span></div>
        <div class="step-content glass-card">
          <div class="step-icon-wrap"><i data-lucide="handshake"></i></div>
          <h3>Complete Offline</h3>
          <p>Negotiate, agree, and complete your transaction directly with the vendor. Campuslink is not involved in this step.</p>
        </div>
      </div>
      <div class="step-item right" data-aos="fade-left" data-aos-delay="300">
        <div class="step-dot"><span>04</span></div>
        <div class="step-content glass-card">
          <div class="step-icon-wrap"><i data-lucide="star"></i></div>
          <h3>Leave a Review</h3>
          <p>Rate your experience and help fellow students make better decisions. All reviews are moderated before publication.</p>
        </div>
        <div class="step-connector-line"></div>
      </div>
      <div class="step-item" data-aos="fade-right" data-aos-delay="400">
        <div class="step-dot"><span>05</span></div>
        <div class="step-content glass-card">
          <div class="step-icon-wrap"><i data-lucide="flag"></i></div>
          <h3>Report Issues</h3>
          <p>Encountered a problem? Submit a complaint with evidence and our admin team investigates within 48 hours.</p>
        </div>
      </div>
    </div>

    <div class="disclaimer-pill" data-aos="fade-up">
      <i data-lucide="info"></i>
      <p>Campuslink operates as a digital directory only. We do not provide services, process service payments, or facilitate in-app messaging.</p>
    </div>
  </div>
</section>

<!-- TRUST SECTION -->
<section class="section trust-section" id="trust">
  <div class="trust-bg-img">
    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1400&auto=format&fit=crop&q=50" alt="" aria-hidden="true" loading="lazy" />
    <div class="trust-bg-overlay"></div>
  </div>
  <div class="container">
    <div class="trust-grid">
      <div class="trust-content" data-aos="fade-right">
        <div class="section-label light">
          <div class="label-line light"></div>
          <span>Your Safety First</span>
          <div class="label-line light"></div>
        </div>
        <h2 class="section-headline light">Built on Trust &amp; Transparency</h2>
        <p class="section-sub light">Every vendor undergoes rigorous verification before listing. Our systems protect both students and vendors.</p>
        <div class="trust-feats">
          <div class="trust-feat-item" data-aos="fade-up" data-aos-delay="100">
            <div class="tfi-icon"><i data-lucide="shield-check"></i></div>
            <div>
              <h4>Admin Verification</h4>
              <p>Every vendor is reviewed and approved by our team before activation.</p>
            </div>
          </div>
          <div class="trust-feat-item" data-aos="fade-up" data-aos-delay="200">
            <div class="tfi-icon"><i data-lucide="lock"></i></div>
            <div>
              <h4>Secured Subscriptions</h4>
              <p>All payments processed via Paystack with strict server-side verification.</p>
            </div>
          </div>
          <div class="trust-feat-item" data-aos="fade-up" data-aos-delay="300">
            <div class="tfi-icon"><i data-lucide="gavel"></i></div>
            <div>
              <h4>Complaint Resolution</h4>
              <p>Three verified complaints trigger a suspension review. Every issue investigated.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="trust-cards-col" data-aos="fade-left" data-aos-delay="200">
        <div class="tc-card glass-card-dark">
          <div class="tcc-icon"><i data-lucide="award"></i></div>
          <strong>Vendor Verified</strong>
          <span>ID, student card &amp; business documents confirmed</span>
        </div>
        <div class="tc-card glass-card-dark shift">
          <div class="tcc-stars">★★★★★</div>
          <p>"Excellent service! Very professional and affordable."</p>
          <span>— Verified Student Review</span>
        </div>
        <div class="tc-card glass-card-dark">
          <div class="tcc-stat">5,200+</div>
          <strong>Active Students</strong>
          <span>Browsing campus vendors</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- VENDOR CTA -->
<section class="section vendor-cta-section" id="vendor-cta">
  <div class="container">
    <div class="vcta-card" data-aos="fade-up">
      <div class="vcta-bg-canvas" id="vctaCanvas" aria-hidden="true"></div>
      <div class="vcta-inner">
        <div class="vcta-text">
          <div class="section-label light">
            <div class="label-line light"></div>
            <span>For Service Providers</span>
            <div class="label-line light"></div>
          </div>
          <h2>Grow Your Business <br/>on Campus</h2>
          <p>Join hundreds of verified vendors reaching thousands of UAT students every semester. Choose a plan that fits your goals.</p>
        </div>
        <div class="vcta-plans">
          <div class="vcta-plan">
            <span class="vp-label">Basic</span>
            <strong class="vp-price">₦2,000</strong>
            <span class="vp-period">/semester</span>
          </div>
          <div class="vcta-plan featured">
            <div class="vp-popular">Popular</div>
            <span class="vp-label">Premium</span>
            <strong class="vp-price">₦5,000</strong>
            <span class="vp-period">/semester</span>
          </div>
          <div class="vcta-plan">
            <span class="vp-label">Featured</span>
            <strong class="vp-price">₦10,000</strong>
            <span class="vp-period">/semester</span>
          </div>
        </div>
        <div class="vcta-actions">
          <a href="<?= BASE_PATH ?>/vendor-register" class="btn btn-vcta-primary">
            <i data-lucide="store"></i> Register as Vendor
          </a>
          <a href="<?= BASE_PATH ?>/pricing" class="btn btn-vcta-ghost">
            View All Plans <i data-lucide="arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
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
</body>
</html>