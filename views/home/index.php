<?php defined('CAMPUSLINK') or die(); ?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero-section" aria-label="Welcome to CampusLink">
    <div class="hero-bg-overlay"></div>
    <div class="container">
        <div class="hero-content">

            <div class="hero-badge">
                <span class="badge-dot"></span>
                <?= e(SCHOOL_NAME) ?> · Verified Campus Directory
            </div>

            <h1 class="hero-headline">
                Find Trusted
                <span class="hero-headline-accent">Campus Services</span>
                Instantly
            </h1>

            <p class="hero-subtext">
                CampusLink connects <?= e(SCHOOL_NAME) ?> students with verified vendors —
                food, tech repairs, tutoring, printing, fashion, and more.
                Browse. Contact. Done.
            </p>

            <!-- Search Form -->
            <div class="hero-search-wrapper">
                <form action="<?= SITE_URL ?>/browse" method="GET"
                      class="hero-search-form" role="search" aria-label="Search vendors">
                    <div class="search-input-group">
                        <div class="search-icon" aria-hidden="true">🔍</div>
                        <input type="text"
                               name="q"
                               class="search-input"
                               placeholder="Search vendors, services, categories..."
                               autocomplete="off"
                               aria-label="Search vendors">
                        <select name="category" class="search-category-select" aria-label="Filter by category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat['slug']) ?>">
                                <?= e($cat['icon']) ?> <?= e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </form>
            </div>

            <!-- CTA Buttons -->
            <div class="hero-cta-group">
                <a href="<?= SITE_URL ?>/browse" class="btn btn-white btn-lg">
                    🔍 Browse All Vendors
                </a>
                <a href="<?= SITE_URL ?>/vendor/register" class="btn btn-outline-white btn-lg">
                    🏪 List Your Business
                </a>
            </div>

            <!-- Stats -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-number"
                          data-count-to="<?= (int)$totalVendors ?>"
                          data-count-suffix="+">0+</span>
                    <span class="hero-stat-label">Verified Vendors</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number"
                          data-count-to="<?= (int)$totalUsers ?>"
                          data-count-suffix="+">0+</span>
                    <span class="hero-stat-label">Students</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number"><?= (int)count($categories) ?></span>
                    <span class="hero-stat-label">Categories</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number"><?= e($avgRating) ?>★</span>
                    <span class="hero-stat-label">Avg Rating</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Wave -->
    <div class="hero-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#f8f9fa"/>
        </svg>
    </div>
</section>

<!-- ============================================================
     CATEGORIES SECTION
     ============================================================ -->
<section class="categories-section" aria-label="Browse by category">
    <div class="container">
        <div class="section-header">
            <span class="section-label">What are you looking for?</span>
            <h2 class="section-title">Browse by Category</h2>
            <p class="section-subtitle">
                From food and fashion to tech support and tutoring —
                find the right service for every need.
            </p>
        </div>

        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>/browse?category=<?= e($cat['slug']) ?>"
               class="category-card fade-in-delay-1"
               aria-label="<?= e($cat['name']) ?> — <?= (int)$cat['vendor_count'] ?> vendors">
                <span class="category-icon" role="img" aria-hidden="true">
                    <?= e($cat['icon']) ?>
                </span>
                <span class="category-name"><?= e($cat['name']) ?></span>
                <span class="category-count">
                    <?= (int)$cat['vendor_count'] ?>
                    <?= $cat['vendor_count'] == 1 ? 'vendor' : 'vendors' ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="section-cta">
            <a href="<?= SITE_URL ?>/categories" class="btn btn-outline-primary">
                View All Categories →
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED VENDORS
     ============================================================ -->
<section class="featured-section" aria-label="Featured vendors">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Verified & Active</span>
            <h2 class="section-title">Featured Campus Vendors</h2>
            <p class="section-subtitle">
                Top-rated vendors ready to serve you right here on campus.
            </p>
        </div>

        <div class="vendors-grid">
            <?php if (empty($featuredVendors)): ?>
            <div class="vendors-empty-state" style="grid-column:1/-1;">
                <div class="empty-icon">🏪</div>
                <h3>No vendors listed yet</h3>
                <p>Be the first to list your campus service!</p>
                <a href="<?= SITE_URL ?>/vendor/register" class="btn btn-primary">List Your Business</a>
            </div>
            <?php else: ?>
            <?php foreach ($featuredVendors as $vendor):
                $isFeatured = $vendor['plan_type'] === 'featured';
                $rating     = (float)($vendor['avg_rating'] ?? 0);
                $initials   = strtoupper(substr($vendor['business_name'], 0, 2));
            ?>
            <article class="vendor-card fade-in-delay-2"
                     itemscope itemtype="https://schema.org/LocalBusiness">
                <div class="vendor-card-badges">
                    <span class="badge badge-verified">✓ Verified</span>
                    <?php if ($isFeatured): ?>
                    <span class="badge badge-featured">⭐ Featured</span>
                    <?php endif; ?>
                </div>

                <div class="vendor-logo-wrap">
                    <?php if (!empty($vendor['logo'])): ?>
                    <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($vendor['logo']) ?>"
                         alt="<?= e($vendor['business_name']) ?>"
                         class="vendor-logo"
                         itemprop="image"
                         loading="lazy">
                    <?php else: ?>
                    <div class="vendor-logo-placeholder" aria-hidden="true">
                        <?= e($initials) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="vendor-info">
                    <h3 class="vendor-name" itemprop="name">
                        <?= e($vendor['business_name']) ?>
                    </h3>
                    <span class="vendor-category">
                        <?= e($vendor['category_name'] ?? '') ?>
                    </span>
                    <div class="vendor-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i <= round($rating) ? 'full' : 'empty' ?>">
                            <?= $i <= round($rating) ? '★' : '☆' ?>
                        </span>
                        <?php endfor; ?>
                        <span class="rating-number">
                            <?= $rating > 0 ? number_format($rating, 1) : 'New' ?>
                        </span>
                        <?php if (($vendor['review_count'] ?? 0) > 0): ?>
                        <span class="rating-count">(<?= (int)$vendor['review_count'] ?>)</span>
                        <?php endif; ?>
                    </div>
                    <p class="vendor-description" itemprop="description">
                        <?= e(truncate($vendor['description'] ?? '', 100)) ?>
                    </p>
                    <?php if (!empty($vendor['price_range'])): ?>
                    <span class="vendor-price">💰 <?= e($vendor['price_range']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="vendor-actions">
                    <?php if (!empty($vendor['whatsapp_number'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="btn-whatsapp btn-sm"
                       onclick="return confirm('You are leaving CampusLink to contact this vendor. CampusLink is not responsible for outcomes.')">
                        💬 WhatsApp
                    </a>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
                       class="btn-profile btn-sm" itemprop="url">
                        View Profile →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-cta">
            <a href="<?= SITE_URL ?>/browse" class="btn btn-primary btn-lg">
                Browse All Vendors →
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     HOW IT WORKS
     ============================================================ -->
<section class="how-it-works-section" aria-label="How CampusLink works">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Simple & Safe</span>
            <h2 class="section-title">How CampusLink Works</h2>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <span class="step-number">1</span>
                <span class="step-icon">🔍</span>
                <h3>Browse & Search</h3>
                <p>Search by category, name, or service type to find the right vendor.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-card">
                <span class="step-number">2</span>
                <span class="step-icon">✅</span>
                <h3>Check Profile</h3>
                <p>View ratings, reviews, price range, and vendor verification status.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-card">
                <span class="step-number">3</span>
                <span class="step-icon">📲</span>
                <h3>Contact Directly</h3>
                <p>Tap WhatsApp or Call to reach the vendor directly from their profile.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-card">
                <span class="step-number">4</span>
                <span class="step-icon">🤝</span>
                <h3>Transact Offline</h3>
                <p>Negotiate and complete your order directly with the vendor.</p>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-card">
                <span class="step-number">5</span>
                <span class="step-icon">⭐</span>
                <h3>Leave a Review</h3>
                <p>Share your honest experience to help fellow students.</p>
            </div>
        </div>

        <div class="how-disclaimer">
            <strong>📌 Important:</strong> CampusLink is a <strong>directory platform only</strong>.
            We connect you with vendors — all transactions happen directly between you and the vendor.
            CampusLink does not process payments, mediate orders, or guarantee service quality.
            <a href="<?= SITE_URL ?>/how-it-works" style="color:var(--primary);font-weight:700;">Learn more →</a>
        </div>
    </div>
</section>

<!-- ============================================================
     TRUST SECTION
     ============================================================ -->
<section class="trust-section" aria-label="Why trust CampusLink">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Safety First</span>
            <h2 class="section-title">Why Students Trust CampusLink</h2>
        </div>

        <div class="trust-grid">
            <div class="trust-card fade-in-delay-1">
                <span class="trust-icon">🔍</span>
                <h3>Manual Verification</h3>
                <p>Every vendor is manually verified with identity documents before going live. No fake listings.</p>
            </div>
            <div class="trust-card fade-in-delay-2">
                <span class="trust-icon">⭐</span>
                <h3>Honest Reviews</h3>
                <p>Only registered students can leave reviews, and all reviews are moderated by our admin team.</p>
            </div>
            <div class="trust-card fade-in-delay-3">
                <span class="trust-icon">📋</span>
                <h3>Complaint System</h3>
                <p>File formal complaints against bad vendors. Three verified complaints triggers a suspension review.</p>
            </div>
            <div class="trust-card fade-in-delay-1">
                <span class="trust-icon">🔐</span>
                <h3>Your Data is Safe</h3>
                <p>We use industry-standard encryption, secure sessions, and never sell your personal data.</p>
            </div>
            <div class="trust-card fade-in-delay-2">
                <span class="trust-icon">🎓</span>
                <h3>Campus Focused</h3>
                <p>Exclusively for <?= e(SCHOOL_NAME) ?> — vendors operate within or near the campus community.</p>
            </div>
            <div class="trust-card fade-in-delay-3">
                <span class="trust-icon">⚡</span>
                <h3>Always Up to Date</h3>
                <p>Expired subscriptions are deactivated automatically, so you only see currently active vendors.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     VENDOR CTA SECTION
     ============================================================ -->
<section class="vendor-cta-section" aria-label="Become a vendor">
    <div class="container">
        <div class="vendor-cta-inner">
            <div class="vendor-cta-text">
                <span class="section-label" style="text-align:left;display:inline-block;">For Vendors</span>
                <h2>Reach Thousands of Students Every Semester</h2>
                <p>
                    List your campus service on CampusLink and get discovered by students
                    actively looking for what you offer. Verified badge, WhatsApp button,
                    reviews, and more — starting from just ₦2,000/semester.
                </p>
                <ul class="vendor-cta-benefits">
                    <li>✅ Verified badge builds instant trust</li>
                    <li>✅ Direct WhatsApp & phone contact from your profile</li>
                    <li>✅ Honest star ratings from real students</li>
                    <li>✅ Featured plans get priority placement</li>
                    <li>✅ No commission — keep 100% of your earnings</li>
                    <li>✅ Manage everything from your vendor dashboard</li>
                </ul>
                <div style="display:flex;gap:1rem;margin-top:1.5rem;flex-wrap:wrap;">
                    <a href="<?= SITE_URL ?>/vendor/register?type=student"
                       class="btn btn-primary">
                        🎓 Student Vendor — from ₦2,000
                    </a>
                    <a href="<?= SITE_URL ?>/vendor/register?type=community"
                       class="btn btn-outline-primary">
                        🏢 Business Vendor — from ₦4,000
                    </a>
                </div>
            </div>

            <!-- Plan Preview Cards -->
            <div class="vendor-cta-cards">
                <div class="plan-preview-card">
                    <div class="plan-preview-type">🎓 Student Vendor Plans</div>
                    <div class="plan-preview-plans">
                        <div class="plan-preview-item">
                            <span class="plan-name">Basic</span>
                            <span class="plan-price">₦2,000 <small>/semester</small></span>
                        </div>
                        <div class="plan-preview-item popular">
                            <span class="plan-name">Premium ⭐</span>
                            <span class="plan-price">₦5,000 <small>/semester</small></span>
                        </div>
                        <div class="plan-preview-item">
                            <span class="plan-name">Featured 🔥</span>
                            <span class="plan-price">₦10,000 <small>/semester</small></span>
                        </div>
                    </div>
                    <a href="<?= SITE_URL ?>/vendor/register?type=student"
                       class="btn btn-primary btn-full">Get Started →</a>
                </div>

                <div class="plan-preview-card">
                    <div class="plan-preview-type">🏢 Community Vendor Plans</div>
                    <div class="plan-preview-plans">
                        <div class="plan-preview-item">
                            <span class="plan-name">Basic</span>
                            <span class="plan-price">₦4,000 <small>/semester</small></span>
                        </div>
                        <div class="plan-preview-item popular">
                            <span class="plan-name">Premium ⭐</span>
                            <span class="plan-price">₦7,000 <small>/semester</small></span>
                        </div>
                        <div class="plan-preview-item">
                            <span class="plan-name">Featured 🔥</span>
                            <span class="plan-price">₦12,000 <small>/semester</small></span>
                        </div>
                    </div>
                    <a href="<?= SITE_URL ?>/vendor/register?type=community"
                       class="btn btn-outline-primary btn-full">Get Started →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     STATS SECTION
     ============================================================ -->
<section class="stats-section" aria-label="Platform statistics">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number"
                      data-count-to="<?= (int)$totalVendors ?>"
                      data-count-suffix="+">0+</span>
                <span class="stat-label">Verified Vendors</span>
                <span class="stat-icon">🏪</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"
                      data-count-to="<?= (int)$totalUsers ?>"
                      data-count-suffix="+">0+</span>
                <span class="stat-label">Registered Students</span>
                <span class="stat-icon">🎓</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= (int)count($categories) ?></span>
                <span class="stat-label">Service Categories</span>
                <span class="stat-icon">📂</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= e($avgRating) ?>★</span>
                <span class="stat-label">Average Vendor Rating</span>
                <span class="stat-icon">⭐</span>
            </div>
        </div>
    </div>
</section>