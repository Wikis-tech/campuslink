<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
$config = APP_CONFIG;

// Get categories for filter
$db = Database::getInstance();
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get featured vendors
$featuredVendors = $db->query("
    SELECT v.*, c.name as category_name, u.first_name, u.last_name
    FROM vendors v
    JOIN categories c ON v.category_id = c.id
    JOIN users u ON v.user_id = u.id
    WHERE v.status = 'approved' AND v.subscription_status IN ('premium', 'enterprise')
    ORDER BY v.rating DESC, v.review_count DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Browse trusted campus services and vendors. Find everything you need within your university community." />
  <meta name="theme-color" content="#0b3d91" />
  <meta name="csrf-token" content="<?= $csrf ?>" />
  <title>Browse Services — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/browse.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>

<header class="site-header glass-header" id="siteHeader">
  <div class="header-inner container">
    <a href="<?= BASE_PATH ?: '/' ?>" class="logo">
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
      <a href="<?= BASE_PATH ?: '/' ?>" class="nav-link">Home</a>
      <a href="<?= BASE_PATH ?>/browse" class="nav-link active">Browse Services</a>
      <a href="<?= BASE_PATH ?>/categories" class="nav-link">Categories</a>
      <a href="<?= BASE_PATH ?>/how-it-works" class="nav-link">How It Works</a>
    </nav>

    <div class="header-actions">
      <?php if (isLoggedIn('user')): ?>
        <div class="user-menu">
          <button class="user-menu-trigger glass-button">
            <i data-lucide="user" class="user-icon"></i>
            <span><?php echo htmlspecialchars($_SESSION['user']['first_name']); ?></span>
            <i data-lucide="chevron-down" class="dropdown-icon"></i>
          </button>
          <div class="user-dropdown glass-menu">
            <a href="<?= BASE_PATH ?>/user/dashboard"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            <a href="<?= BASE_PATH ?>/user/profile"><i data-lucide="user"></i> Profile</a>
            <a href="<?= BASE_PATH ?>/logout"><i data-lucide="log-out"></i> Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= BASE_PATH ?>/login" class="auth-link">Login</a>
        <a href="<?= BASE_PATH ?>/register" class="cta-button primary">Get Started</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="browse-page">
  <!-- Hero Section -->
  <section class="browse-hero">
    <div class="hero-background">
      <div class="hero-image" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80')"></div>
      <div class="hero-overlay"></div>
    </div>
    <div class="hero-content container">
      <h1 class="hero-title">Discover Campus Services</h1>
      <p class="hero-subtitle">Find trusted vendors and services within your university community</p>

      <!-- Search Bar -->
      <div class="search-container glass-card">
        <form class="search-form" action="/api/live-search.php" method="GET">
          <div class="search-input-group">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" name="q" placeholder="Search for services, vendors, or categories..." class="search-input" autocomplete="off">
          </div>
          <button type="submit" class="search-button">
            <i data-lucide="search" class="button-icon"></i>
            Search
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- Filters Section -->
  <section class="filters-section">
    <div class="container">
      <div class="filters-bar glass-card">
        <div class="filter-group">
          <label class="filter-label">Category</label>
          <select name="category" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Location</label>
          <select name="location" class="filter-select">
            <option value="">All Locations</option>
            <option value="campus">On Campus</option>
            <option value="nearby">Nearby</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Rating</label>
          <select name="rating" class="filter-select">
            <option value="">Any Rating</option>
            <option value="4">4+ Stars</option>
            <option value="3">3+ Stars</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Sort By</label>
          <select name="sort" class="filter-select">
            <option value="rating">Highest Rated</option>
            <option value="reviews">Most Reviews</option>
            <option value="newest">Newest</option>
          </select>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Vendors -->
  <section class="featured-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Featured Vendors</h2>
        <p class="section-subtitle">Top-rated services trusted by the campus community</p>
      </div>

      <div class="vendors-grid">
        <?php foreach ($featuredVendors as $vendor): ?>
          <div class="vendor-card glass-card">
            <div class="vendor-image">
              <img src="<?php echo htmlspecialchars($vendor['logo'] ?: 'assets/images/default-vendor.jpg'); ?>" alt="<?php echo htmlspecialchars($vendor['name']); ?>" loading="lazy">
              <div class="vendor-badge premium">Premium</div>
            </div>
            <div class="vendor-content">
              <h3 class="vendor-name"><?php echo htmlspecialchars($vendor['name']); ?></h3>
              <p class="vendor-category"><?php echo htmlspecialchars($vendor['category_name']); ?></p>
              <div class="vendor-rating">
                <div class="stars">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i data-lucide="star" class="star <?php echo $i <= round($vendor['rating']) ? 'filled' : ''; ?>"></i>
                  <?php endfor; ?>
                </div>
                <span class="rating-text"><?php echo number_format($vendor['rating'], 1); ?> (<?php echo $vendor['review_count']; ?> reviews)</span>
              </div>
              <p class="vendor-description"><?php echo htmlspecialchars(substr($vendor['description'], 0, 100)); ?>...</p>
              <a href="/vendor/<?php echo $vendor['id']; ?>" class="vendor-link">View Details</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Categories Grid -->
  <section class="categories-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Browse by Category</h2>
        <p class="section-subtitle">Find exactly what you're looking for</p>
      </div>

      <div class="categories-grid">
        <?php foreach ($categories as $category): ?>
          <a href="/browse?category=<?php echo $category['id']; ?>" class="category-card glass-card">
            <div class="category-icon">
              <i data-lucide="<?php echo htmlspecialchars($category['icon'] ?: 'grid-3x3'); ?>"></i>
            </div>
            <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
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
<script src="assets/js/live-search.js"></script>
<script>
  // Initialize Lucide icons
  lucide.createIcons();

  // Search functionality
  const searchInput = document.querySelector('.search-input');
  const searchResults = document.createElement('div');
  searchResults.className = 'search-results glass-menu';
  searchInput.parentElement.appendChild(searchResults);

  let searchTimeout;
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
      searchResults.style.display = 'none';
      return;
    }

    searchTimeout = setTimeout(() => {
      fetch(`/api/live-search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.vendors && data.vendors.length > 0) {
            searchResults.innerHTML = data.vendors.map(vendor =>
              `<a href="/vendor/${vendor.id}" class="search-result-item">
                <div class="result-image">
                  <img src="${vendor.logo || '/assets/images/default-vendor.jpg'}" alt="${vendor.name}">
                </div>
                <div class="result-content">
                  <h4>${vendor.name}</h4>
                  <p>${vendor.category_name}</p>
                </div>
              </a>`
            ).join('');
            searchResults.style.display = 'block';
          } else {
            searchResults.style.display = 'none';
          }
        });
    }, 300);
  });

  // Hide search results when clicking outside
  document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
      searchResults.style.display = 'none';
    }
  });
</script>

<script src="assets/js/main.js"></script>
</body>
</html>