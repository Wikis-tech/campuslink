<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'vendor') {
    header('Location: /login');
    exit;
}

$csrf = Security::generateCSRF();
$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();
$vendor = $db->prepare("SELECT * FROM vendors WHERE user_id = ?");
$vendor->execute([$user['id']]);
$vendor = $vendor->fetch(PDO::FETCH_ASSOC);

// Get current subscription
$subscription = $db->prepare("SELECT s.*, p.name as plan_name, p.price, p.features FROM subscriptions s JOIN plans p ON s.plan_id = p.id WHERE s.vendor_id = ? AND s.status = 'active'");
$subscription->execute([$vendor['id']]);
$subscription = $subscription->fetch(PDO::FETCH_ASSOC);

// Get all plans
$plans = $db->query("SELECT * FROM plans WHERE status = 'active' ORDER BY price")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Subscription — Campuslink</title>
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
      <a href="/vendor/dashboard" class="nav-link">Dashboard</a>
      <a href="/vendor/subscription" class="nav-link active">Subscription</a>
    </nav>
  </div>
</header>

<main class="dashboard-page">
  <div class="container">
    <h1>Subscription Management</h1>
    <div class="subscription-info">
      <p>Current Plan: <?php echo htmlspecialchars($subscription['plan_name'] ?? 'Free'); ?></p>
    </div>
    <div class="plans-grid">
      <?php foreach ($plans as $plan): ?>
        <div class="plan-card glass-card">
          <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
          <p>$<?php echo number_format($plan['price'], 2); ?>/month</p>
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