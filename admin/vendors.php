<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /admin/');
    exit;
}

$db = Database::getInstance()->getConnection();
$vendors = $db->query("SELECT v.*, u.email, c.name as category_name FROM vendors v JOIN users u ON v.user_id = u.id JOIN categories c ON v.category_id = c.id ORDER BY v.created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Vendors — Campuslink</title>
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
      <a href="/admin/vendors" class="nav-link active">Vendors</a>
    </nav>
  </div>
</header>

<main class="admin-page">
  <div class="container">
    <h1>Manage Vendors</h1>
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Category</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vendors as $vendor): ?>
            <tr>
              <td><?php echo htmlspecialchars($vendor['name']); ?></td>
              <td><?php echo htmlspecialchars($vendor['email']); ?></td>
              <td><?php echo htmlspecialchars($vendor['category_name']); ?></td>
              <td><?php echo htmlspecialchars($vendor['status']); ?></td>
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