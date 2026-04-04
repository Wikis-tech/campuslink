<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Complaint — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/legal.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="<?= BASE_PATH ?: '/' ?>" class="logo">Campus<strong>link</strong></a>
    <nav class="main-nav">
      <a href="/" class="nav-link">Home</a>
      <a href="/complaint" class="nav-link active">File Complaint</a>
    </nav>
  </div>
</header>

<main class="legal-page">
  <div class="container">
    <h1>File a Complaint</h1>
    <div class="content">
      <p>Use this form to report issues with campus services.</p>
      <!-- Complaint form would go here -->
    </div>
  </div>
</main>

<footer class="site-footer">
  <div class="container">
    <p>&copy; 2026 Campuslink. All rights reserved.</p>
  </div>
</footer>

<script src="assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>