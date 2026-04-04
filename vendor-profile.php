<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(404);
    echo 'Vendor not found.';
    exit;
}

try {
    $pdo = Database::getInstance();

    // Get vendor details with category, ratings, etc.
    $stmt = $pdo->prepare("
        SELECT v.*, c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(r.id) as review_count,
               p.name as plan_name, p.slug as plan_slug
        FROM vendors v
        LEFT JOIN categories c ON v.category_id = c.id
        LEFT JOIN reviews r ON v.id = r.vendor_id
        LEFT JOIN subscriptions s ON v.id = s.vendor_id AND s.status = 'active'
        LEFT JOIN plans p ON s.plan_id = p.id
        WHERE v.id = ? AND v.status = 'active' AND v.is_verified = 1
        GROUP BY v.id
    ");
    $stmt->execute([$id]);
    $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vendor) {
        http_response_code(404);
        echo 'Vendor not found.';
        exit;
    }

    // Get recent reviews
    $reviewsStmt = $pdo->prepare("
        SELECT r.*, u.first_name, u.last_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.vendor_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $reviewsStmt->execute([$id]);
    $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo 'Server error.';
    exit;
}

$csrf = Security::generateCSRF();
$config = APP_CONFIG;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars($vendor['business_name']) ?> — Verified campus service provider on Campuslink." />
  <meta name="csrf-token" content="<?= $csrf ?>" />
  <title><?= htmlspecialchars($vendor['business_name']) ?> — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/profile.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>

<header class="site-header scrolled" id="siteHeader">
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
      <a href="<?= BASE_PATH ?>/browse" class="nav-link">Browse</a>
      <a href="<?= BASE_PATH ?>/categories" class="nav-link">Categories</a>
      <a href="<?= BASE_PATH ?>/how-it-works" class="nav-link">How It Works</a>
      <a href="<?= BASE_PATH ?>/vendor-register" class="nav-link nav-vendor-btn"><i data-lucide="store"></i> Register</a>
      <a href="<?= BASE_PATH ?>/login" class="nav-link nav-login-btn">Login</a>
    </nav>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<div class="profile-banner">
  <img src="<?= $vendor['service_photo_path'] ? 'uploads/vendors/' . htmlspecialchars($vendor['service_photo_path']) : 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=1200&auto=format&fit=crop&q=70' ?>" alt="<?= htmlspecialchars($vendor['business_name']) ?> banner" />
  <div class="profile-banner-overlay"></div>
</div>

<div class="profile-layout container">

  <!-- ===== LEFT SIDEBAR ===== -->
  <aside class="profile-sidebar">
    <div class="profile-card">
      <div class="profile-logo-wrap">
        <img src="<?= $vendor['logo_path'] ? 'uploads/logos/' . htmlspecialchars($vendor['logo_path']) : 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=200&auto=format&fit=crop&q=80' ?>" alt="<?= htmlspecialchars($vendor['business_name']) ?> logo" class="profile-logo" />
        <div class="profile-verified-ring" title="Verified Vendor"></div>
      </div>

      <h1 class="profile-name"><?= htmlspecialchars($vendor['business_name']) ?></h1>

      <div class="profile-badges">
        <span class="badge badge-verified"><i data-lucide="shield-check"></i> Verified</span>
        <?php if ($vendor['plan_slug'] === 'featured'): ?>
        <span class="badge badge-featured"><i data-lucide="award"></i> Featured</span>
        <?php endif; ?>
        <span class="badge" style="background:rgba(11,61,145,0.08);color:var(--primary)">
          <i data-lucide="graduation-cap"></i> <?= ucfirst($vendor['vendor_type']) ?> Vendor
        </span>
      </div>

      <div class="profile-rating-row">
        <span class="stars" style="font-size:1.1rem"><?= str_repeat('★', floor($vendor['avg_rating'])) . (fmod($vendor['avg_rating'], 1) ? '½' : '') ?></span>
        <strong><?= number_format($vendor['avg_rating'], 1) ?></strong>
        <span class="text-muted">(<?= (int)$vendor['review_count'] ?> reviews)</span>
      </div>

      <div class="profile-category-pill"><?= htmlspecialchars($vendor['category_name']) ?></div>

      <div class="profile-contact-actions">
        <a href="tel:<?= htmlspecialchars($vendor['phone']) ?>" class="btn btn-primary w-full">
          <i data-lucide="phone"></i> Call Vendor
        </a>
        <a href="https://wa.me/<?= preg_replace('/\D/', '', $vendor['whatsapp'] ?? $vendor['phone']) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp w-full">
          <i data-lucide="message-circle"></i> WhatsApp
        </a>
      </div>

      <div class="contact-disclaimer">
        <i data-lucide="info"></i>
        <p>All communication and transactions happen directly between you and the vendor. Campuslink does not participate in any service delivery or payment.</p>
      </div>

      <div class="profile-meta-list">
        <div class="pm-item"><i data-lucide="map-pin"></i><span><?= htmlspecialchars($vendor['operating_location'] ?: 'Campus') ?></span></div>
        <div class="pm-item"><i data-lucide="tag"></i><span><?= htmlspecialchars($vendor['price_range'] ?: 'Contact for pricing') ?></span></div>
        <div class="pm-item"><i data-lucide="briefcase"></i><span><?= $vendor['years_operation'] ? $vendor['years_operation'] . ' years experience' : 'Experienced' ?></span></div>
        <div class="pm-item"><i data-lucide="calendar"></i><span>Member since <?= date('M Y', strtotime($vendor['created_at'])) ?></span></div>
      </div>

      <button class="btn btn-outline w-full" id="saveBtn" onclick="toggleSaveVendor(<?= $vendor['id'] ?>, this)">
        <i data-lucide="bookmark"></i> Save Vendor
      </button>
    </div>
  </aside>

  <!-- ===== MAIN CONTENT ===== -->
  <main class="profile-main">

    <!-- About -->
    <div class="profile-section">
      <h2 class="section-title">About</h2>
      <p class="profile-desc"><?= nl2br(htmlspecialchars($vendor['description'])) ?></p>
    </div>

    <!-- Reviews -->
    <?php if ($reviews): ?>
    <div class="profile-section">
      <h2 class="section-title">Recent Reviews</h2>
      <div class="reviews-list">
        <?php foreach ($reviews as $review): ?>
        <div class="review-item">
          <div class="review-header">
            <strong><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></strong>
            <span class="review-rating"><?= str_repeat('★', $review['rating']) ?></span>
            <span class="review-date"><?= date('M j, Y', strtotime($review['created_at'])) ?></span>
          </div>
          <p class="review-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if ($vendor['review_count'] > 5): ?>
      <a href="#" class="btn btn-outline">View All Reviews (<?= $vendor['review_count'] ?>)</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>