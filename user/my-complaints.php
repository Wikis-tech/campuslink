<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'user') {
    header('Location: /login');
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();
$complaints = $db->prepare("
    SELECT c.*, v.name as vendor_name
    FROM complaints c
    JOIN vendors v ON c.vendor_id = v.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$complaints->execute([$user['id']]);
$complaints = $complaints->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Complaints — Campuslink</title>
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
      <a href="/user/my-complaints" class="nav-link active">My Complaints</a>
    </nav>
  </div>
</header>

<main class="dashboard-page">
  <div class="container">
    <h1>My Complaints</h1>
    <div class="complaint-list">
      <?php foreach ($complaints as $complaint): ?>
        <div class="complaint-card glass-card">
          <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
          <p><?php echo htmlspecialchars($complaint['vendor_name']); ?></p>
          <span class="status"><?php echo htmlspecialchars($complaint['status']); ?></span>
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