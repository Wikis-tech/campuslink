<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /admin/');
    exit;
}

$csrf = Security::generateCSRF();
$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();

// Get stats
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_vendors' => $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'approved'")->fetchColumn(),
    'pending_vendors' => $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'pending'")->fetchColumn(),
    'total_reviews' => $db->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn(),
    'total_complaints' => $db->query("SELECT COUNT(*) FROM complaints WHERE status != 'closed'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — Campuslink</title>
  <link rel="stylesheet" href="../assets/css/main.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="/" class="logo">Campus<strong>link</strong></a>
    <nav class="main-nav">
      <a href="/admin/dashboard" class="nav-link active">Dashboard</a>
      <a href="/admin/vendors" class="nav-link">Vendors</a>
      <a href="/admin/users" class="nav-link">Users</a>
    </nav>
  </div>
</header>

<main class="admin-dashboard">
  <div class="container">
    <h1>Admin Dashboard</h1>
    <div class="stats-grid">
      <div class="stat-card">
        <h3><?php echo $stats['total_users']; ?></h3>
        <p>Total Users</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $stats['total_vendors']; ?></h3>
        <p>Approved Vendors</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $stats['pending_vendors']; ?></h3>
        <p>Pending Vendors</p>
      </div>
      <div class="stat-card">
        <h3><?php echo $stats['total_reviews']; ?></h3>
        <p>Total Reviews</p>
      </div>
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