<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';
$csrf = Security::generateCSRF();
$config = APP_CONFIG;

// Get categories for the form
$db = Database::getInstance();
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Register your business on Campuslink. Join our trusted network of campus service providers." />
  <meta name="theme-color" content="#0b3d91" />
  <meta name="csrf-token" content="<?= $csrf ?>" />
  <title>Register as Vendor — Campuslink</title>
  <link rel="stylesheet" href="assets/css/main.css" />
  <link rel="stylesheet" href="assets/css/vendor-register.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>

<header class="site-header glass-header" id="siteHeader">
  <div class="header-inner container">
    <a href="/" class="logo">
      <div class="logo-mark">
        <img src="assets/images/campuslink-logo-white.png" alt="Campuslink Logo" width="36" height="36" onerror="this.style.display='none'" />
        <svg class="logo-fallback" width="36" height="36" viewBox="0 0 36 36" fill="none">
          <rect width="36" height="36" rx="10" fill="#0b3d91"/>
          <path d="M9 18C9 12.477 13.477 8 19 8s10 4.477 10 10" stroke="white" stroke-width="3" stroke-linecap="round"/>
          <circle cx="18" cy="22" r="3.5" fill="#1ea952"/>
          <path d="M14 28h8" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="logo-text">Campus<strong>link</strong></span>
    </a>

    <nav class="main-nav" id="mainNav">
      <a href="/" class="nav-link">Home</a>
      <a href="/browse" class="nav-link">Browse Services</a>
      <a href="/vendor-register" class="nav-link active">Become a Vendor</a>
    </nav>

    <div class="header-actions">
      <a href="/login" class="auth-link">Login</a>
      <a href="/register" class="cta-button primary">Sign Up</a>
    </div>
  </div>
</header>

<main class="vendor-register-page">
  <!-- Hero Section -->
  <section class="register-hero">
    <div class="hero-background">
      <div class="hero-image" style="background-image: url('https://images.unsplash.com/photo-1556761175-b413da4baf72?ixlib=rb-4.0.3&auto=format&fit=crop&w=1974&q=80')"></div>
      <div class="hero-overlay"></div>
    </div>
    <div class="hero-content container">
      <h1 class="hero-title">Join Campuslink</h1>
      <p class="hero-subtitle">Connect with thousands of students and grow your campus business</p>
    </div>
  </section>

  <!-- Registration Form -->
  <section class="register-section">
    <div class="container">
      <div class="register-container">
        <div class="register-header">
          <h2>Create Your Vendor Account</h2>
          <p>Join our verified network of campus service providers</p>
        </div>

        <form class="register-form glass-card" action="/api/vendor-register.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

          <!-- Personal Information -->
          <div class="form-section">
            <h3 class="section-title">Personal Information</h3>

            <div class="form-row">
              <div class="form-group">
                <label for="first_name" class="form-label">First Name *</label>
                <input type="text" id="first_name" name="first_name" class="form-input" required>
                <div class="form-error" id="first_name_error"></div>
              </div>

              <div class="form-group">
                <label for="last_name" class="form-label">Last Name *</label>
                <input type="text" id="last_name" name="last_name" class="form-input" required>
                <div class="form-error" id="last_name_error"></div>
              </div>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email" class="form-input" required>
              <div class="form-error" id="email_error"></div>
            </div>

            <div class="form-group">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" id="phone" name="phone" class="form-input">
              <div class="form-error" id="phone_error"></div>
            </div>
          </div>

          <!-- Business Information -->
          <div class="form-section">
            <h3 class="section-title">Business Information</h3>

            <div class="form-group">
              <label for="business_name" class="form-label">Business Name *</label>
              <input type="text" id="business_name" name="business_name" class="form-input" required>
              <div class="form-error" id="business_name_error"></div>
            </div>

            <div class="form-group">
              <label for="category" class="form-label">Business Category *</label>
              <select id="category" name="category_id" class="form-select" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-error" id="category_error"></div>
            </div>

            <div class="form-group">
              <label for="description" class="form-label">Business Description *</label>
              <textarea id="description" name="description" class="form-textarea" rows="4" placeholder="Describe your business, services offered, and what makes you unique..." required></textarea>
              <div class="form-error" id="description_error"></div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="location" class="form-label">Location *</label>
                <input type="text" id="location" name="location" class="form-input" placeholder="Campus location or address" required>
                <div class="form-error" id="location_error"></div>
              </div>

              <div class="form-group">
                <label for="website" class="form-label">Website (optional)</label>
                <input type="url" id="website" name="website" class="form-input" placeholder="https://yourwebsite.com">
                <div class="form-error" id="website_error"></div>
              </div>
            </div>
          </div>

          <!-- Services & Hours -->
          <div class="form-section">
            <h3 class="section-title">Services & Operating Hours</h3>

            <div class="form-group">
              <label for="services" class="form-label">Services Offered</label>
              <textarea id="services" name="services" class="form-textarea" rows="3" placeholder="List your main services (one per line)"></textarea>
              <small class="form-help">Separate multiple services with new lines</small>
            </div>

            <div class="form-group">
              <label class="form-label">Operating Hours</label>
              <div class="hours-grid">
                <div class="day-hours">
                  <label>Monday</label>
                  <input type="text" name="hours[monday]" placeholder="9:00 AM - 5:00 PM">
                </div>
                <div class="day-hours">
                  <label>Tuesday</label>
                  <input type="text" name="hours[tuesday]" placeholder="9:00 AM - 5:00 PM">
                </div>
                <div class="day-hours">
                  <label>Wednesday</label>
                  <input type="text" name="hours[wednesday]" placeholder="9:00 AM - 5:00 PM">
                </div>
                <div class="day-hours">
                  <label>Thursday</label>
                  <input type="text" name="hours[thursday]" placeholder="9:00 AM - 5:00 PM">
                </div>
                <div class="day-hours">
                  <label>Friday</label>
                  <input type="text" name="hours[friday]" placeholder="9:00 AM - 5:00 PM">
                </div>
                <div class="day-hours">
                  <label>Saturday</label>
                  <input type="text" name="hours[saturday]" placeholder="Closed">
                </div>
                <div class="day-hours">
                  <label>Sunday</label>
                  <input type="text" name="hours[sunday]" placeholder="Closed">
                </div>
              </div>
            </div>
          </div>

          <!-- Logo Upload -->
          <div class="form-section">
            <h3 class="section-title">Business Logo</h3>

            <div class="form-group">
              <label for="logo" class="form-label">Upload Logo</label>
              <div class="file-upload">
                <input type="file" id="logo" name="logo" accept="image/*" class="file-input">
                <div class="file-upload-area">
                  <i data-lucide="upload" class="upload-icon"></i>
                  <p>Click to upload or drag and drop</p>
                  <small>PNG, JPG up to 5MB</small>
                </div>
                <div class="file-preview" id="logo_preview"></div>
              </div>
              <div class="form-error" id="logo_error"></div>
            </div>
          </div>

          <!-- Password -->
          <div class="form-section">
            <h3 class="section-title">Account Security</h3>

            <div class="form-row">
              <div class="form-group">
                <label for="password" class="form-label">Password *</label>
                <input type="password" id="password" name="password" class="form-input" required>
                <div class="form-error" id="password_error"></div>
              </div>

              <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                <div class="form-error" id="confirm_password_error"></div>
              </div>
            </div>
          </div>

          <!-- Terms & Submit -->
          <div class="form-section">
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" name="terms" required>
                <span class="checkmark"></span>
                I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a>
              </label>
              <div class="form-error" id="terms_error"></div>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" name="marketing">
                <span class="checkmark"></span>
                I would like to receive marketing communications and updates
              </label>
            </div>

            <button type="submit" class="submit-button primary" id="submit_btn">
              <span class="button-text">Create Vendor Account</span>
              <div class="button-loader" style="display: none;">
                <div class="spinner"></div>
              </div>
            </button>
          </div>
        </form>

        <div class="register-footer">
          <p>Already have an account? <a href="/login">Sign in here</a></p>
        </div>
      </div>
    </div>
  </section>
</main>

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
<script src="assets/js/auth-canvas.js"></script>
<script>
  lucide.createIcons();

  // Form validation and submission
  const form = document.querySelector('.register-form');
  const submitBtn = document.getElementById('submit_btn');
  const buttonText = submitBtn.querySelector('.button-text');
  const buttonLoader = submitBtn.querySelector('.button-loader');

  // File upload preview
  const logoInput = document.getElementById('logo');
  const logoPreview = document.getElementById('logo_preview');

  logoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        logoPreview.innerHTML = `<img src="${e.target.result}" alt="Logo preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">`;
      };
      reader.readAsDataURL(file);
    }
  });

  // Form submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Show loading state
    submitBtn.disabled = true;
    buttonText.style.opacity = '0';
    buttonLoader.style.display = 'block';

    try {
      const formData = new FormData(form);

      const response = await fetch('/api/vendor-register.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        // Success - redirect or show success message
        window.location.href = '/vendor/dashboard?welcome=1';
      } else {
        // Show errors
        Object.keys(result.errors || {}).forEach(field => {
          const errorDiv = document.getElementById(field + '_error');
          if (errorDiv) {
            errorDiv.textContent = result.errors[field];
            errorDiv.style.display = 'block';
          }
        });

        if (result.message) {
          alert(result.message);
        }
      }
    } catch (error) {
      console.error('Registration error:', error);
      alert('An error occurred. Please try again.');
    } finally {
      // Hide loading state
      submitBtn.disabled = false;
      buttonText.style.opacity = '1';
      buttonLoader.style.display = 'none';
    }
  });

  // Real-time validation
  const inputs = form.querySelectorAll('input, select, textarea');
  inputs.forEach(input => {
    input.addEventListener('blur', function() {
      validateField(this);
    });
  });

  function validateField(field) {
    const errorDiv = document.getElementById(field.id + '_error');
    if (errorDiv) {
      errorDiv.style.display = 'none';
    }

    // Basic validation
    if (field.hasAttribute('required') && !field.value.trim()) {
      showError(field, 'This field is required');
      return false;
    }

    if (field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
      showError(field, 'Please enter a valid email address');
      return false;
    }

    if (field.id === 'confirm_password') {
      const password = document.getElementById('password').value;
      if (field.value && field.value !== password) {
        showError(field, 'Passwords do not match');
        return false;
      }
    }

    return true;
  }

  function showError(field, message) {
    const errorDiv = document.getElementById(field.id + '_error');
    if (errorDiv) {
      errorDiv.textContent = message;
      errorDiv.style.display = 'block';
    }
  }
</script>

</body>
</html>