<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if (!Security::isLoggedIn() || $_SESSION['user']['role'] !== 'vendor') {
    header('Location: /login');
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();
$vendor = $db->prepare("SELECT * FROM vendors WHERE user_id = ?");
$vendor->execute([$user['id']]);
$vendor = $vendor->fetch(PDO::FETCH_ASSOC);

$notifications = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifications->execute([$user['id']]);
$notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Notifications — Campuslink</title>
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
      <a href="/vendor/notifications" class="nav-link active">Notifications</a>
    </nav>
  </div>
</header>

<main class="dashboard-page">
  <div class="container">
    <h1>Notifications</h1>
    <div class="notification-list">
      <?php foreach ($notifications as $notification): ?>
        <div class="notification-card glass-card <?php echo $notification['read_status'] ? '' : 'unread'; ?>">
          <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
          <p><?php echo htmlspecialchars($notification['message']); ?></p>
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