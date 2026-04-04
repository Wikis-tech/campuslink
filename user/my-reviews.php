<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'user') {
    header('Location: /login');
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();
$reviews = $db->prepare("
    SELECT r.*, v.name as vendor_name, v.logo
    FROM reviews r
    JOIN vendors v ON r.vendor_id = v.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$reviews->execute([$user['id']]);
$reviews = $reviews->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Reviews — Campuslink</title>
  <link rel="stylesheet" href="../assets/css/main.css" />
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="/" class="logo">Campus<strong>link</strong></a>
    <nav class="main-nav">
      <a href="/user/dashboard" class="nav-link">Dashboard</a>
      <a href="/user/my-reviews" class="nav-link active">My Reviews</a>
    </nav>
  </div>
</header>

<main class="dashboard-page">
  <div class="container">
    <h1>My Reviews</h1>
    <div class="review-list">
      <?php foreach ($reviews as $review): ?>
        <div class="review-card glass-card">
          <h3><?php echo htmlspecialchars($review['vendor_name']); ?></h3>
          <p><?php echo htmlspecialchars($review['comment']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<footer class="site-footer">
  <div class="container">
    <p>&copy; 2026 Campuslink. All rights reserved.</p>
  </div>
</footer>

<script src="../assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>