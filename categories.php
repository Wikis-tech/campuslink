<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();

// Get categories
$db = Database::getInstance();
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Categories — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/browse.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="<?= BASE_PATH ?: '/' ?>" class="logo">Campus<strong>link</strong></a>
    <nav class="main-nav">
      <a href="<?= BASE_PATH ?>/" class="nav-link">Home</a>
      <a href="<?= BASE_PATH ?>/categories" class="nav-link active">Categories</a>
    </nav>
  </div>
</header>

<main class="browse-page">
  <div class="container">
    <h1>Service Categories</h1>
    <div class="categories-grid">
      <?php foreach ($categories as $category): ?>
        <a href="/browse?category=<?php echo $category['id']; ?>" class="category-card glass-card">
          <div class="category-icon">
            <i data-lucide="<?php echo htmlspecialchars($category['icon'] ?: 'grid-3x3'); ?>"></i>
          </div>
          <h3><?php echo htmlspecialchars($category['name']); ?></h3>
          <p><?php echo htmlspecialchars($category['description']); ?></p>
        </a>
      <?php endforeach; ?>
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