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
  <title>Verify Email — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/auth.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<header class="site-header glass-header">
  <div class="header-inner container">
    <a href="/" class="logo">Campus<strong>link</strong></a>
  </div>
</header>

<main class="auth-page">
  <div class="container">
    <div class="auth-container">
      <h1>Verify Your Email</h1>
      <p>Check your email for a verification link.</p>
      <!-- Verification form -->
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