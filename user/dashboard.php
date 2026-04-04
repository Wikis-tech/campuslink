<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

// Check if user is logged in and is a regular user
if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'user') {
    header('Location: /login');
    exit;
}

$csrf = Security::generateCSRF();
$config = APP_CONFIG;
$user = $_SESSION['user'];

// Get user's saved vendors
$db = Database::getInstance()->getConnection();
$savedVendors = $db->prepare("
    SELECT v.*, c.name as category_name
    FROM saved_vendors sv
    JOIN vendors v ON sv.vendor_id = v.id
    JOIN categories c ON v.category_id = c.id
    WHERE sv.user_id = ? AND v.status = 'approved'
    ORDER BY sv.created_at DESC
    LIMIT 6
");
$savedVendors->execute([$user['id']]);
$savedVendors = $savedVendors->fetchAll(PDO::FETCH_ASSOC);

// Get user's recent reviews
$recentReviews = $db->prepare("
    SELECT r.*, v.name as vendor_name, v.logo
    FROM reviews r
    JOIN vendors v ON r.vendor_id = v.id
    WHERE r.user_id = ? AND r.status = 'approved'
    ORDER BY r.created_at DESC
    LIMIT 3
");
$recentReviews->execute([$user['id']]);
$recentReviews = $recentReviews->fetchAll(PDO::FETCH_ASSOC);

// Get user's recent complaints
$recentComplaints = $db->prepare("
    SELECT c.*, v.name as vendor_name
    FROM complaints c
    JOIN vendors v ON c.vendor_id = v.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 3
");
$recentComplaints->execute([$user['id']]);
$recentComplaints = $recentComplaints->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Your Campuslink dashboard - manage your saved vendors, reviews, and complaints." />
  <meta name="theme-color" content="#0b3d91" />
  <meta name="csrf-token" content="<?= $csrf ?>" />
  <title>Dashboard — Campuslink</title>
  <link rel="stylesheet" href="../assets/css/main.css" />
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>

<header class="site-header glass-header" id="siteHeader">
  <div class="header-inner container">
    <a href="/" class="logo">
      <div class="logo-mark">
        <img src="../assets/images/campuslink-logo-white.png" alt="Campuslink Logo" width="36" height="36" onerror="this.style.display='none'" />
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
      <a href="/" class="nav-link">Home</a>
      <a href="/browse" class="nav-link">Browse</a>
      <a href="/user/dashboard" class="nav-link active">Dashboard</a>
    </nav>

    <div class="header-actions">
      <div class="user-menu">
        <button class="user-menu-trigger glass-button">
          <i data-lucide="user" class="user-icon"></i>
          <span><?php echo htmlspecialchars($user['first_name']); ?></span>
          <i data-lucide="chevron-down" class="dropdown-icon"></i>
        </button>
        <div class="user-dropdown glass-menu">
          <a href="/user/profile"><i data-lucide="user"></i> Profile</a>
          <a href="/user/saved-vendors"><i data-lucide="heart"></i> Saved Vendors</a>
          <a href="/user/my-reviews"><i data-lucide="star"></i> My Reviews</a>
          <a href="/user/my-complaints"><i data-lucide="alert-triangle"></i> My Complaints</a>
          <a href="/logout"><i data-lucide="log-out"></i> Logout</a>
        </div>
      </div>
    </div>
  </div>
</header>

<main class="dashboard-page">
  <div class="dashboard-container container">
    <!-- Welcome Section -->
    <section class="welcome-section">
      <div class="welcome-card glass-card">
        <div class="welcome-content">
          <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
          <p>Here's what's happening with your Campuslink account</p>
        </div>
        <div class="welcome-actions">
          <a href="/browse" class="cta-button primary">
            <i data-lucide="search" class="button-icon"></i>
            Find Services
          </a>
          <a href="/user/profile" class="cta-button secondary">
            <i data-lucide="settings" class="button-icon"></i>
            Update Profile
          </a>
        </div>
      </div>
    </section>

    <!-- Quick Stats -->
    <section class="stats-section">
      <div class="stats-grid">
        <div class="stat-card glass-card">
          <div class="stat-icon">
            <i data-lucide="heart"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo count($savedVendors); ?></h3>
            <p>Saved Vendors</p>
          </div>
        </div>

        <div class="stat-card glass-card">
          <div class="stat-icon">
            <i data-lucide="star"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo count($recentReviews); ?></h3>
            <p>Reviews Written</p>
          </div>
        </div>

        <div class="stat-card glass-card">
          <div class="stat-icon">
            <i data-lucide="alert-triangle"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo count($recentComplaints); ?></h3>
            <p>Active Complaints</p>
          </div>
        </div>

        <div class="stat-card glass-card">
          <div class="stat-icon">
            <i data-lucide="bell"></i>
          </div>
          <div class="stat-content">
            <h3>0</h3>
            <p>New Notifications</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Activity -->
    <section class="activity-section">
      <div class="activity-grid">
        <!-- Saved Vendors -->
        <div class="activity-card glass-card">
          <div class="activity-header">
            <h3>Saved Vendors</h3>
            <a href="/user/saved-vendors" class="view-all">View All</a>
          </div>
          <div class="activity-content">
            <?php if (empty($savedVendors)): ?>
              <div class="empty-state">
                <i data-lucide="heart" class="empty-icon"></i>
                <p>No saved vendors yet</p>
                <a href="/browse" class="empty-action">Browse Services</a>
              </div>
            <?php else: ?>
              <div class="vendor-list">
                <?php foreach ($savedVendors as $vendor): ?>
                  <div class="vendor-item">
                    <div class="vendor-image">
                      <img src="<?php echo htmlspecialchars($vendor['logo'] ?: '../assets/images/default-vendor.jpg'); ?>" alt="<?php echo htmlspecialchars($vendor['name']); ?>">
                    </div>
                    <div class="vendor-info">
                      <h4><?php echo htmlspecialchars($vendor['name']); ?></h4>
                      <p><?php echo htmlspecialchars($vendor['category_name']); ?></p>
                    </div>
                    <a href="/vendor/<?php echo $vendor['id']; ?>" class="vendor-link">
                      <i data-lucide="external-link"></i>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Reviews -->
        <div class="activity-card glass-card">
          <div class="activity-header">
            <h3>Recent Reviews</h3>
            <a href="/user/my-reviews" class="view-all">View All</a>
          </div>
          <div class="activity-content">
            <?php if (empty($recentReviews)): ?>
              <div class="empty-state">
                <i data-lucide="star" class="empty-icon"></i>
                <p>No reviews yet</p>
                <a href="/browse" class="empty-action">Find Vendors to Review</a>
              </div>
            <?php else: ?>
              <div class="review-list">
                <?php foreach ($recentReviews as $review): ?>
                  <div class="review-item">
                    <div class="review-header">
                      <div class="vendor-info">
                        <img src="<?php echo htmlspecialchars($review['logo'] ?: '../assets/images/default-vendor.jpg'); ?>" alt="<?php echo htmlspecialchars($review['vendor_name']); ?>">
                        <div>
                          <h4><?php echo htmlspecialchars($review['vendor_name']); ?></h4>
                          <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                              <i data-lucide="star" class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                            <?php endfor; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <p class="review-text"><?php echo htmlspecialchars(substr($review['comment'], 0, 100)); ?>...</p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Complaints -->
        <div class="activity-card glass-card">
          <div class="activity-header">
            <h3>Active Complaints</h3>
            <a href="/user/my-complaints" class="view-all">View All</a>
          </div>
          <div class="activity-content">
            <?php if (empty($recentComplaints)): ?>
              <div class="empty-state">
                <i data-lucide="check-circle" class="empty-icon"></i>
                <p>No active complaints</p>
              </div>
            <?php else: ?>
              <div class="complaint-list">
                <?php foreach ($recentComplaints as $complaint): ?>
                  <div class="complaint-item">
                    <div class="complaint-header">
                      <h4><?php echo htmlspecialchars($complaint['title']); ?></h4>
                      <span class="complaint-status status-<?php echo $complaint['status']; ?>">
                        <?php echo ucfirst($complaint['status']); ?>
                      </span>
                    </div>
                    <p><?php echo htmlspecialchars($complaint['vendor_name']); ?></p>
                    <small><?php echo date('M j, Y', strtotime($complaint['created_at'])); ?></small>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
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

<script src="../assets/js/main.js"></script>
<script src="../assets/js/dashboard.js"></script>
<script>
  lucide.createIcons();

  // User menu toggle
  const userMenuTrigger = document.querySelector('.user-menu-trigger');
  const userDropdown = document.querySelector('.user-dropdown');

  userMenuTrigger.addEventListener('click', function(e) {
    e.stopPropagation();
    userDropdown.classList.toggle('active');
  });

  document.addEventListener('click', function(e) {
    if (!userMenuTrigger.contains(e.target)) {
      userDropdown.classList.remove('active');
    }
  });
</script>

</body>
</html>