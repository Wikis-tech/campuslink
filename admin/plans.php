<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /admin/');
    exit;
}

$db = Database::getInstance()->getConnection();
$plans = $db->query("SELECT * FROM plans ORDER BY price")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Plans — Campuslink</title>
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
      <a href="/admin/dashboard" class="nav-link">Dashboard</a>
      <a href="/admin/plans" class="nav-link active">Plans</a>
    </nav>
  </div>
</header>

<main class="admin-page">
  <div class="container">
    <h1>Manage Subscription Plans</h1>
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Features</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($plans as $plan): ?>
            <tr>
              <td><?php echo htmlspecialchars($plan['name']); ?></td>
              <td>$<?php echo number_format($plan['price'], 2); ?></td>
              <td><?php echo htmlspecialchars(substr($plan['description'], 0, 50)); ?>...</td>
              <td><?php echo htmlspecialchars($plan['status']); ?></td>
              <td><button>Edit</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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