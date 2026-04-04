<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf   = Security::generateCSRF();
$config = APP_CONFIG;

try {
    $pdo   = Database::getInstance();
    $plans = $pdo->query(
        "SELECT * FROM plans WHERE is_active = 1 ORDER BY vendor_type, price_kobo ASC"
    )->fetchAll();

    $studentPlans   = array_filter($plans, fn($p) => $p['vendor_type'] === 'student');
    $communityPlans = array_filter($plans, fn($p) => $p['vendor_type'] === 'community');
} catch (\Throwable $e) {
    error_log('[CL Pricing] ' . $e->getMessage());
    $studentPlans = $communityPlans = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pricing — Campuslink</title>
  <meta name="csrf-token" content="<?= $csrf ?>" />
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/hero.css" />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>

<!-- HEADER -->
<header class="site-header scrolled" id="siteHeader">
  <div class="header-inner container">
    <a href="/" class="logo">
      <div class="logo-mark">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
          <rect width="36" height="36" rx="10" fill="#0b3d91"/>
          <path d="M9 18C9 12.477 13.477 8 19 8s10 4.477 10 10" stroke="white" stroke-width="3" stroke-linecap="round"/>
          <circle cx="18" cy="22" r="3.5" fill="#1ea952"/>
          <path d="M14 28h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="logo-text" style="color:var(--primary)">Campus<strong>link</strong></span>
    </a>
    <nav class="main-nav" id="mainNav">
      <a href="/" class="nav-link">Home</a>
      <a href="/browse" class="nav-link">Browse</a>
      <a href="/pricing" class="nav-link active">Pricing</a>
      <a href="/vendor-register" class="nav-link nav-vendor-btn"><i data-lucide="store"></i> Register as Vendor</a>
      <a href="/login" class="nav-link nav-login-btn">Login</a>
    </nav>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<!-- HERO -->
<section class="page-hero" style="background:var(--hero-gradient);padding:calc(var(--header-h) + 60px) 0 60px;text-align:center">
  <div class="container">
    <div class="section-label" style="justify-content:center;margin-bottom:16px">
      <div class="label-line"></div><span>Transparent</span><div class="label-line"></div>
    </div>
    <h1 style="color:white;font-family:var(--font-head);font-size:clamp(2rem,4vw,3rem);font-weight:900;letter-spacing:-0.02em;margin-bottom:14px">Simple, Fair Pricing</h1>
    <p style="color:rgba(255,255,255,0.75);font-size:1.05rem;max-width:480px;margin:0 auto">Choose the plan that fits your goals. All plans give you access to our verified campus directory.</p>
  </div>
</section>

<section style="padding:72px 0;background:var(--bg)">
  <div class="container">
    <!-- Toggle -->
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:48px">
      <button id="btnStudent" onclick="showTab('student')"
              style="padding:10px 28px;border-radius:999px;font-family:var(--font-head);font-size:0.875rem;font-weight:700;cursor:pointer;border:2px solid var(--primary);background:var(--primary);color:white;transition:all 0.22s ease">
        Student Vendors
      </button>
      <button id="btnCommunity" onclick="showTab('community')"
              style="padding:10px 28px;border-radius:999px;font-family:var(--font-head);font-size:0.875rem;font-weight:700;cursor:pointer;border:2px solid var(--border);background:white;color:var(--text-muted);transition:all 0.22s ease">
        Community Vendors
      </button>
    </div>

    <!-- Student Plans -->
    <div id="student-plans" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:960px;margin:0 auto">
      <?php foreach ($studentPlans as $plan):
        $isPopular = str_contains($plan['slug'], 'premium');
        $features  = json_decode($plan['features'] ?? '[]', true) ?: [];
        $price     = formatNaira((int)$plan['price_kobo']);
      ?>
      <div style="background:white;border-radius:20px;border:2px solid <?= $isPopular ? 'var(--primary)' : 'var(--divider)' ?>;padding:32px 28px;position:relative;box-shadow:<?= $isPopular ? 'var(--shadow-hover)' : 'var(--shadow-card)' ?>;transition:all 0.3s ease" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
        <?php if ($isPopular): ?>
        <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--primary);color:white;font-family:var(--font-head);font-size:0.65rem;font-weight:800;padding:5px 16px;border-radius:999px;text-transform:uppercase;letter-spacing:0.1em;white-space:nowrap">Most Popular</div>
        <?php endif; ?>
        <div style="font-family:var(--font-head);font-size:0.72rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted);margin-bottom:12px"><?= e($plan['name']) ?></div>
        <div style="font-family:var(--font-head);font-size:2.4rem;font-weight:900;color:var(--primary);line-height:1;margin-bottom:6px"><?= $price ?></div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:24px">per semester (~6 months)</div>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:28px">
          <?php
          $defaultFeatures = match(true) {
            str_contains($plan['slug'],'featured') => ['Listed in directory','Call & WhatsApp buttons','Enhanced profile page','Priority search ranking','Photo gallery (6 photos)','Featured badge & placement','Homepage showcase','Top search position','Priority admin support'],
            str_contains($plan['slug'],'premium')  => ['Listed in directory','Call & WhatsApp buttons','Enhanced profile page','Priority search ranking','Analytics dashboard','Photo gallery (6 photos)'],
            default                                => ['Listed in directory','Call & WhatsApp buttons','Basic vendor profile','Review management'],
          };
          foreach ($defaultFeatures as $feat):
          ?>
          <li style="display:flex;align-items:center;gap:10px;font-size:0.875rem;color:var(--text-secondary)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1ea952" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
            <?= e($feat) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <a href="/vendor-register"
           style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;background:<?= $isPopular ? 'var(--primary)' : 'transparent' ?>;color:<?= $isPopular ? 'white' : 'var(--primary)' ?>;border:2px solid var(--primary);border-radius:12px;font-family:var(--font-head);font-size:0.875rem;font-weight:700;text-decoration:none;transition:all 0.22s ease" onmouseover="this.style.background='var(--primary)';this.style.color='white'" onmouseout="this.style.background='<?= $isPopular ? 'var(--primary)' : 'transparent' ?>'; this.style.color='<?= $isPopular ? 'white' : 'var(--primary)' ?>'">
          Get Started <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Community Plans -->
    <div id="community-plans" style="display:none;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:960px;margin:0 auto">
      <?php foreach ($communityPlans as $plan):
        $isPopular = str_contains($plan['slug'], 'premium');
        $price     = formatNaira((int)$plan['price_kobo']);
      ?>
      <div style="background:white;border-radius:20px;border:2px solid <?= $isPopular ? 'var(--primary)' : 'var(--divider)' ?>;padding:32px 28px;position:relative;box-shadow:<?= $isPopular ? 'var(--shadow-hover)' : 'var(--shadow-card)' ?>;transition:all 0.3s ease" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
        <?php if ($isPopular): ?>
        <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--primary);color:white;font-family:var(--font-head);font-size:0.65rem;font-weight:800;padding:5px 16px;border-radius:999px;text-transform:uppercase;letter-spacing:0.1em;white-space:nowrap">Most Popular</div>
        <?php endif; ?>
        <div style="font-family:var(--font-head);font-size:0.72rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;color:var(--text-muted);margin-bottom:12px"><?= e($plan['name']) ?></div>
        <div style="font-family:var(--font-head);font-size:2.4rem;font-weight:900;color:var(--primary);line-height:1;margin-bottom:6px"><?= $price ?></div>
        <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:24px">per semester (~6 months)</div>
        <a href="/vendor-register"
           style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;background:<?= $isPopular ? 'var(--primary)' : 'transparent' ?>;color:<?= $isPopular ? 'white' : 'var(--primary)' ?>;border:2px solid var(--primary);border-radius:12px;font-family:var(--font-head);font-size:0.875rem;font-weight:700;text-decoration:none;transition:all 0.22s ease">
          Get Started
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Guarantee strip -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;max-width:900px;margin:64px auto 0">
      <?php $guarantees = [['shield-check','Admin Verified','Every vendor reviewed before listing'],['credit-card','Paystack Secured','All payments verified server-side'],['refresh-cw','Grace Period','2-day buffer after subscription expires'],['headphones','Admin Support','Dedicated support for vendors']]; ?>
      <?php foreach ($guarantees as [$icon,$title,$desc]): ?>
      <div style="text-align:center;padding:24px 16px">
        <div style="width:48px;height:48px;border-radius:14px;background:rgba(11,61,145,0.08);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
          <i data-lucide="<?= $icon ?>" style="width:22px;height:22px;color:var(--primary)"></i>
        </div>
        <strong style="display:block;font-family:var(--font-head);font-size:0.9rem;margin-bottom:6px"><?= $title ?></strong>
        <span style="font-size:0.8rem;color:var(--text-muted)"><?= $desc ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-layout">
      <div class="footer-brand">
        <div class="footer-logo">Campus<strong>link</strong></div>
        <p class="footer-desc">A secure, lightweight digital campus service directory connecting students with verified vendors within the university environment.</p>
        <p class="footer-desc">CampusLink is a directory platform only. We do not provide services, process transactions, or mediate between users and vendors.</p>
        <p class="footer-contact">📧 campuslinkd@gmail.com</p>
      </div>
      <div class="footer-links">
        <h4>Quick Links</h4>
        <a href="<?= BASE_PATH ?>/">Home</a>
        <a href="<?= BASE_PATH ?>/browse">Browse Services</a>
        <a href="<?= BASE_PATH ?>/categories">All Categories</a>
        <a href="<?= BASE_PATH ?>/how-it-works">How It Works</a>
        <a href="#about">About CampusLink</a>
        <a href="#contact">Contact Us</a>
      </div>
      <div class="footer-links">
        <h4>For Vendors</h4>
        <a href="<?= BASE_PATH ?>/vendor-register">Register as Student Vendor</a>
        <a href="<?= BASE_PATH ?>/vendor-register">Register as Community Vendor</a>
        <a href="<?= BASE_PATH ?>/vendor/login">Vendor Login</a>
        <a href="<?= BASE_PATH ?>/vendor-terms">Vendor Terms & Conditions</a>
        <a href="#suspension">Suspension Policy</a>
        <a href="#complaints">Complaint Resolution</a>
      </div>
      <div class="footer-links">
        <h4>Legal & Policies</h4>
        <a href="<?= BASE_PATH ?>/terms">General Terms & Conditions</a>
        <a href="<?= BASE_PATH ?>/terms">User Terms & Conditions</a>
        <a href="<?= BASE_PATH ?>/terms">Vendor Terms & Conditions</a>
        <a href="<?= BASE_PATH ?>/privacy">Privacy Policy</a>
        <a href="<?= BASE_PATH ?>/refund">Refund Policy</a>
        <a href="#data-retention">Data Retention Policy</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 CampusLink. All rights reserved. | Governed by Nigerian Law</p>
      <p>Built for the campus community 🎓</p>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
<script>
function showTab(tab) {
  const sp = document.getElementById('student-plans');
  const cp = document.getElementById('community-plans');
  const bs = document.getElementById('btnStudent');
  const bc = document.getElementById('btnCommunity');
  if (tab === 'student') {
    sp.style.display = 'grid'; cp.style.display = 'none';
    bs.style.background = 'var(--primary)'; bs.style.color = 'white'; bs.style.borderColor = 'var(--primary)';
    bc.style.background = 'white'; bc.style.color = 'var(--text-muted)'; bc.style.borderColor = 'var(--border)';
  } else {
    sp.style.display = 'none'; cp.style.display = 'grid';
    bc.style.background = 'var(--primary)'; bc.style.color = 'white'; bc.style.borderColor = 'var(--primary)';
    bs.style.background = 'white'; bs.style.color = 'var(--text-muted)'; bs.style.borderColor = 'var(--border)';
  }
}
document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
</body>
</html>