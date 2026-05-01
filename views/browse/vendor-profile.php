<?php defined('CAMPUSLINK') or die(); ?>
<?php
// Format phone number to international format for wa.me (Nigerian numbers: 0XXXXXXXXXX → 234XXXXXXXXXX)
if (!function_exists('wa_number')) {
    function wa_number(string $raw): string {
        $digits = preg_replace('/[^0-9]/', '', $raw);
        // Already has country code 234
        if (str_starts_with($digits, '234') && strlen($digits) >= 13) return $digits;
        // Starts with 0 (local format: 08012345678)
        if (str_starts_with($digits, '0') && strlen($digits) === 11) return '234' . substr($digits, 1);
        // Bare 10-digit number (8012345678)
        if (strlen($digits) === 10) return '234' . $digits;
        return $digits; // fallback
    }
}
if (!function_exists('lucide_icon')) {
    function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                     viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                     style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
    }
}
?>

<!-- Profile Hero -->
<div class="vendor-profile-page">
    <div class="vendor-profile-hero">
        <div class="container vendor-profile-hero-content">

            <!-- Logo -->
            <div class="vendor-profile-logo-wrap">
                <?php if (!empty($vendor['logo'])): ?>
                <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($vendor['logo']) ?>"
                     alt="<?= e($vendor['business_name']) ?>"
                     class="vendor-profile-logo"
                     itemprop="image">
                <?php else: ?>
                <div class="vendor-profile-logo-placeholder">
                    <?= strtoupper(substr($vendor['business_name'], 0, 2)) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="vendor-profile-hero-info">
                <h1 class="vendor-profile-name" itemprop="name">
                    <?= e($vendor['business_name']) ?>
                </h1>

                <div class="vendor-profile-badges">
                    <span class="vendor-profile-badge verified"
                          style="display:inline-flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>', 14, 'currentColor') ?>
                        Verified Vendor
                    </span>
                    <?php if (!empty($vendor['plan_type']) && $vendor['plan_type'] === 'featured'): ?>
                    <span class="vendor-profile-badge featured"
                          style="display:inline-flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 14, 'currentColor', 'fill:currentColor;') ?>
                        Featured
                    </span>
                    <?php endif; ?>
                    <span class="vendor-profile-badge"
                          style="display:inline-flex;align-items:center;gap:0.3rem;">
                        <?php if ($vendor['vendor_type'] === 'student'): ?>
                            <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 14, 'currentColor') ?>
                            Student
                        <?php else: ?>
                            <?= lucide_icon('<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>', 14, 'currentColor') ?>
                            Business
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Stars — only filled when there are real reviews -->
                <div class="vendor-profile-rating">
                    <div class="vendor-profile-stars">
                        <?php
                        $displayRating = ($reviewTotal > 0) ? $avgRating : 0;
                        for ($i = 1; $i <= 5; $i++):
                            $filled = ($displayRating > 0) && ($i <= round($displayRating));
                        ?>
                        <span class="star <?= $filled ? '' : 'empty' ?>">
                            <?= lucide_icon(
                                '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                18,
                                $filled ? 'var(--warning-amber)' : 'var(--divider)',
                                $filled ? 'fill:var(--warning-amber);' : 'fill:var(--divider);'
                            ) ?>
                        </span>
                        <?php endfor; ?>
                    </div>
                    <span class="vendor-profile-rating-text">
                        <?php if ($reviewTotal > 0): ?>
                            <?= number_format($avgRating, 1) ?>/5
                            (<?= (int)$reviewTotal ?> review<?= $reviewTotal != 1 ? 's' : '' ?>)
                        <?php else: ?>
                            No reviews yet
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Category -->
                <div class="vendor-profile-category"
                     style="display:inline-flex;align-items:center;gap:0.4rem;">
                    <?= lucide_icon('<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>', 16, 'currentColor') ?>
                    <?= e($vendor['category_name'] ?? '') ?>
                </div>

                <!-- Action Buttons -->
                <div class="vendor-profile-actions">
                    <?php if (!empty($vendor['whatsapp_number'])): ?>
                    <a href="https://wa.me/<?= wa_number($vendor['whatsapp_number']) ?>?text=Hi%2C+I+found+you+on+CampusLink"
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-whatsapp"
                       style="display:inline-flex;align-items:center;gap:0.4rem;"
                       onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. CampusLink is not responsible for transactions or outcomes.')">
                        <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 16, 'currentColor') ?>
                        WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($vendor['phone'])): ?>
                    <a href="tel:<?= e($vendor['phone']) ?>" class="btn btn-call"
                       style="display:inline-flex;align-items:center;gap:0.4rem;">
                        <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 16, 'currentColor') ?>
                        Call
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-outline-white share-vendor-btn"
                            style="display:inline-flex;align-items:center;gap:0.4rem;"
                            data-title="<?= e($vendor['business_name']) ?> on CampusLink">
                        <?= lucide_icon('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>', 16, 'currentColor') ?>
                        Share
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Profile Body -->
    <div class="vendor-profile-body">
        <div class="container">
            <div class="vendor-profile-layout">

                <!-- Main Content -->
                <div>
                    <div class="vendor-info-card">
                        <div class="vendor-info-tabs" role="tablist">
                            <button class="vendor-tab-btn active"
                                    data-tab="about" role="tab" aria-selected="true"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon('<rect x="9" y="2" width="6" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><line x1="12" y1="11" x2="16" y2="11"/><line x1="12" y1="16" x2="16" y2="16"/><line x1="8" y1="11" x2="8.01" y2="11"/><line x1="8" y1="16" x2="8.01" y2="16"/>', 16, 'currentColor') ?>
                                About
                            </button>
                            <button class="vendor-tab-btn"
                                    data-tab="reviews" role="tab" aria-selected="false"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 16, 'currentColor') ?>
                                Reviews (<?= (int)$reviewTotal ?>)
                            </button>
                            <?php if (!empty($vendor['service_photo'])): ?>
                            <button class="vendor-tab-btn"
                                    data-tab="photos" role="tab" aria-selected="false"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon('<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>', 16, 'currentColor') ?>
                                Photos
                            </button>
                            <?php endif; ?>
                        </div>

                        <!-- About Tab -->
                        <div class="vendor-tab-content active" data-tab="about">
                            <p class="vendor-description-full">
                                <?= nl2br(e($vendor['description'] ?? '')) ?>
                            </p>

                            <div class="vendor-details-grid">
                                <?php if (!empty($vendor['price_range'])): ?>
                                <div class="vendor-detail-item">
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Price Range</div>
                                        <div class="vendor-detail-value"><?= e($vendor['price_range']) ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($vendor['operating_location']) || !empty($vendor['business_address'])): ?>
                                <div class="vendor-detail-item">
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Location</div>
                                        <div class="vendor-detail-value"><?= e($vendor['operating_location'] ?? $vendor['business_address'] ?? '') ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php $exp = $vendor['years_experience'] ?? $vendor['years_operation'] ?? null; ?>
                                <?php if ($exp !== null && $exp > 0): ?>
                                <div class="vendor-detail-item">
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Experience</div>
                                        <div class="vendor-detail-value"><?= (int)$exp ?> year<?= $exp != 1 ? 's' : '' ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="vendor-detail-item">
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Vendor Type</div>
                                        <div class="vendor-detail-value"><?= ucfirst($vendor['vendor_type']) ?> Vendor</div>
                                    </div>
                                </div>

                                <?php if (!empty($vendor['category_name'])): ?>
                                <div class="vendor-detail-item">
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Category</div>
                                        <div class="vendor-detail-value"><?= e($vendor['category_name']) ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="vendor-detail-item">
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Listed Since</div>
                                        <div class="vendor-detail-value"><?= date('M Y', strtotime($vendor['created_at'])) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Disclaimer -->
                            <div class="vendor-profile-disclaimer"
                                 style="display:flex;align-items:flex-start;gap:0.5rem;">
                                <?= lucide_icon('<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 16, '#92400e', 'flex-shrink:0;margin-top:2px;') ?>
                                <span>
                                    <strong>Disclaimer:</strong>
                                    CampusLink is a directory platform only.
                                    All transactions occur directly between you and this vendor.
                                    We are not responsible for service quality, pricing accuracy,
                                    or outcomes of any interaction.
                                    <a href="<?= SITE_URL ?>/how-it-works"
                                       style="font-weight:700;color:#92400e;">
                                        Learn more &rarr;
                                    </a>
                                </span>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="vendor-tab-content" data-tab="reviews">

                            <!-- Rating Summary (only when reviews exist) -->
                            <?php if ($reviewTotal > 0): ?>
                            <div class="rating-summary">
                                <div class="rating-big-number">
                                    <div class="rating-big-value"><?= number_format($avgRating, 1) ?></div>
                                    <div class="rating-big-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span>
                                            <?= lucide_icon(
                                                '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                                18,
                                                $i <= round($avgRating) ? 'var(--warning-amber)' : 'var(--divider)',
                                                $i <= round($avgRating) ? 'fill:var(--warning-amber);' : 'fill:var(--divider);'
                                            ) ?>
                                        </span>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="rating-big-count"><?= (int)$reviewTotal ?> review<?= $reviewTotal != 1 ? 's' : '' ?></div>
                                </div>
                                <div class="rating-bars">
                                    <?php for ($s = 5; $s >= 1; $s--):
                                        $cnt = $ratingBreakdown[$s] ?? 0;
                                        $pct = $reviewTotal > 0 ? round(($cnt / $reviewTotal) * 100) : 0;
                                    ?>
                                    <div class="rating-bar-row">
                                        <span class="rating-bar-label"><?= $s ?></span>
                                        <div class="rating-bar-track">
                                            <div class="rating-bar-fill"
                                                 data-width="<?= $pct ?>"
                                                 style="width:0;"></div>
                                        </div>
                                        <span class="rating-bar-count"><?= $cnt ?></span>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Write Review Section -->
                            <?php if ($currentUserReview): ?>
                                <!-- Already reviewed -->
                                <div class="already-reviewed-notice">
                                    <?= lucide_icon('<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', 24, 'var(--accent-green)') ?>
                                    <div>
                                        <strong>You have already reviewed this vendor.</strong><br>
                                        Your review is <em><?= e($currentUserReview['status']) ?></em>.
                                        <?php if ($currentUserReview['status'] === 'pending'): ?>
                                        It will appear after admin approval.
                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php elseif (Auth::isLoggedIn()): ?>
                                <!-- Review form (AJAX-powered) -->
                                <div class="review-submit-form">
                                    <div class="review-submit-title">
                                        <?= lucide_icon('<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>', 18, 'currentColor') ?>
                                        Write a Review
                                    </div>
                                    <form id="reviewForm" novalidate>
                                        <input type="hidden" name="vendor_id" value="<?= (int)$vendor['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">

                                        <div class="form-group">
                                            <label class="form-label">
                                                Your Rating <span class="required">*</span>
                                            </label>
                                            <div class="review-stars-input" id="starRatingInput">
                                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>">
                                                <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i != 1 ? 's' : '' ?>">&#9733;</label>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="selected-rating-label" id="ratingLabel"
                                                 style="font-size:0.85rem;color:var(--text-muted);margin-top:0.25rem;min-height:1.2em;">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="reviewText">
                                                Your Review <span class="required">*</span>
                                            </label>
                                            <textarea id="reviewText"
                                                      name="review"
                                                      class="form-control"
                                                      placeholder="Share your honest experience with this vendor (min 10 characters)..."
                                                      rows="4"
                                                      data-max-chars="500"
                                                      required></textarea>
                                            <div class="review-char-counter" id="reviewCounter"></div>
                                        </div>

                                        <button type="submit" class="btn btn-primary" id="reviewSubmitBtn">
                                            <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 16, 'currentColor') ?>
                                            Submit Review
                                        </button>
                                    </form>
                                </div>

                            <?php else: ?>
                                <!-- Not logged in -->
                                <div class="write-review-login-prompt">
                                    <p>
                                        <a href="<?= SITE_URL ?>/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                                            Sign in
                                        </a>
                                        to leave a review for this vendor.
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Reviews List -->
                            <?php if (empty($reviews)): ?>
                            <div class="empty-state" style="padding:2.5rem 1rem;">
                                <div class="empty-icon">
                                    <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 40, 'var(--warning-amber)', 'fill:var(--warning-amber);') ?>
                                </div>
                                <h3>No approved reviews yet</h3>
                                <p>Be the first to review this vendor!</p>
                            </div>
                            <?php else: ?>
                            <div class="review-list">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-item-header">
                                        <div class="review-user">
                                            <div class="review-avatar">
                                                <?= strtoupper(substr($review['user_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="review-user-name"><?= e($review['user_name'] ?? 'Anonymous') ?></div>
                                                <?php if (!empty($review['user_level'])): ?>
                                                <div class="review-user-level"><?= e($review['user_level']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div class="review-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="review-star <?= $i > $review['rating'] ? 'empty' : '' ?>">
                                                    <?= lucide_icon(
                                                        '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                                        15,
                                                        $i > $review['rating'] ? 'var(--divider)' : 'var(--warning-amber)',
                                                        $i > $review['rating'] ? 'fill:var(--divider);' : 'fill:var(--warning-amber);'
                                                    ) ?>
                                                </span>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="review-date">
                                                <?= date('d M Y', strtotime($review['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <p class="review-text"><?= nl2br(e($review['review'])) ?></p>
                                    <?php if (!empty($review['vendor_reply'])): ?>
                                    <div class="review-vendor-reply">
                                        <strong>Vendor Reply:</strong>
                                        <?= nl2br(e($review['vendor_reply'])) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (!empty($reviewsPag) && $reviewsPag['total_pages'] > 1): ?>
                            <div style="margin-top:1.5rem;">
                                <?php
                                $pagination = $reviewsPag;
                                require __DIR__ . '/../partials/pagination.php';
                                ?>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>

                        </div><!-- /reviews tab -->

                        <!-- Photos Tab -->
                        <?php if (!empty($vendor['service_photo'])): ?>
                        <div class="vendor-tab-content" data-tab="photos">
                            <div class="service-photos-grid">
                                <img src="<?= SITE_URL ?>/assets/uploads/service-photos/<?= e($vendor['service_photo']) ?>"
                                     alt="<?= e($vendor['business_name']) ?> service photo"
                                     class="service-photo-thumb"
                                     loading="lazy">
                            </div>
                            <div class="photo-lightbox">
                                <button class="lightbox-close" aria-label="Close">
                                    <?= lucide_icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>', 20, 'currentColor') ?>
                                </button>
                                <button class="lightbox-prev" aria-label="Previous">
                                    <?= lucide_icon('<polyline points="15 18 9 12 15 6"/>', 24, 'currentColor') ?>
                                </button>
                                <img class="lightbox-img" src="" alt="Photo">
                                <button class="lightbox-next" aria-label="Next">
                                    <?= lucide_icon('<polyline points="9 18 15 12 9 6"/>', 24, 'currentColor') ?>
                                </button>
                                <div class="lightbox-counter"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div><!-- /vendor-info-card -->
                </div>

                <!-- Contact Sidebar -->
                <div class="vendor-contact-sidebar">

                    <!-- Contact Card -->
                    <div class="contact-card">
                        <div class="contact-card-title"
                             style="display:flex;align-items:center;gap:0.4rem;">
                            <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 16, 'currentColor') ?>
                            Contact Vendor
                        </div>
                        <div class="contact-buttons">
                            <?php if (!empty($vendor['whatsapp_number'])): ?>
                            <a href="https://wa.me/<?= wa_number($vendor['whatsapp_number']) ?>?text=Hi%2C+I+found+you+on+CampusLink"
                               target="_blank" rel="noopener noreferrer"
                               class="contact-btn-large contact-btn-whatsapp"
                               onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. All transactions are between you and the vendor.')">
                                <span class="contact-btn-icon">
                                    <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 22, 'currentColor') ?>
                                </span>
                                <div>
                                    Chat on WhatsApp
                                    <span class="contact-btn-number"><?= e($vendor['whatsapp_number']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>

                            <?php if (!empty($vendor['phone'])): ?>
                            <a href="tel:<?= e($vendor['phone']) ?>"
                               class="contact-btn-large contact-btn-call">
                                <span class="contact-btn-icon">
                                    <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 22, 'currentColor') ?>
                                </span>
                                <div>
                                    Call Vendor
                                    <span class="contact-btn-number"><?= e($vendor['phone']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>

                        <div class="contact-safety-note"
                             style="display:flex;align-items:flex-start;gap:0.4rem;">
                            <?= lucide_icon('<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 14, 'currentColor', 'flex-shrink:0;margin-top:1px;') ?>
                            Always verify before paying. CampusLink is not responsible for transactions.
                        </div>
                    </div>

                    <!-- Save & Report -->
                    <div class="contact-card">
                        <div class="vendor-secondary-actions">
                            <button class="vendor-action-btn <?= $isSaved ? 'saved' : '' ?>"
                                    data-vendor-id="<?= (int)$vendor['id'] ?>"
                                    data-user-id="<?= (int)($userId ?? 0) ?>"
                                    data-save="1"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon(
                                    '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
                                    16,
                                    $isSaved ? '#e11d48' : 'currentColor',
                                    $isSaved ? 'fill:#e11d48;' : ''
                                ) ?>
                                <?= $isSaved ? 'Saved' : 'Save Vendor' ?>
                            </button>
                            <?php if (Auth::isLoggedIn()): ?>
                            <button class="vendor-action-btn"
                                    data-open-modal="complaint"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 16, 'currentColor') ?>
                                Report Vendor
                            </button>
                            <?php else: ?>
                            <a href="<?= SITE_URL ?>/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                               class="vendor-action-btn"
                               style="display:inline-flex;align-items:center;gap:0.4rem;text-decoration:none;">
                                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 16, 'currentColor') ?>
                                Report Vendor
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="contact-card">
                        <div class="contact-card-title"
                             style="display:flex;align-items:center;gap:0.4rem;">
                            <?= lucide_icon('<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>', 16, 'currentColor') ?>
                            Quick Info
                        </div>
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <?php if (!empty($vendor['price_range'])): ?>
                            <div style="font-size:var(--font-size-sm);">
                                <span style="color:var(--text-muted);">Price Range</span><br>
                                <strong><?= e($vendor['price_range']) ?></strong>
                            </div>
                            <?php endif; ?>
                            <div style="font-size:var(--font-size-sm);">
                                <span style="color:var(--text-muted);">Category</span><br>
                                <strong><?= e($vendor['category_name'] ?? '') ?></strong>
                            </div>
                            <div style="font-size:var(--font-size-sm);">
                                <span style="color:var(--text-muted);">Vendor Type</span><br>
                                <strong style="display:inline-flex;align-items:center;gap:0.3rem;">
                                    <?php if ($vendor['vendor_type'] === 'student'): ?>
                                        <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 14, 'currentColor') ?>
                                        Student
                                    <?php else: ?>
                                        <?= lucide_icon('<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>', 14, 'currentColor') ?>
                                        Business
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <!-- Rating summary in sidebar -->
                            <div style="font-size:var(--font-size-sm);">
                                <span style="color:var(--text-muted);">Rating</span><br>
                                <?php if ($reviewTotal > 0): ?>
                                <strong style="color:var(--warning-amber);">
                                    <?= number_format($avgRating, 1) ?>/5
                                </strong>
                                <span style="color:var(--text-muted);font-size:0.8rem;">
                                    (<?= (int)$reviewTotal ?> review<?= $reviewTotal != 1 ? 's' : '' ?>)
                                </span>
                                <?php else: ?>
                                <strong style="color:var(--text-muted);">No reviews yet</strong>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div><!-- /vendor-contact-sidebar -->

            </div><!-- /vendor-profile-layout -->
        </div>
    </div>

</div><!-- /vendor-profile-page -->

<!-- Complaint Modal -->
<?php if (Auth::isLoggedIn()): ?>
<div class="modal-overlay" id="complaintModal" role="dialog"
     aria-modal="true" aria-labelledby="complaintModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="complaintModalTitle"
                style="display:flex;align-items:center;gap:0.5rem;">
                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 22, 'currentColor') ?>
                File a Complaint
            </h2>
            <button class="modal-close" aria-label="Close modal">
                <?= lucide_icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>', 20, 'currentColor') ?>
            </button>
        </div>

        <p style="font-size:var(--font-size-sm);color:var(--text-secondary);margin-bottom:1.25rem;">
            Against: <strong><?= e($vendor['business_name']) ?></strong>
        </p>

        <form id="complaintForm" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
            <input type="hidden" name="vendor_id" value="<?= (int)$vendor['id'] ?>">

            <div class="form-group">
                <label class="form-label" for="complaint_category">
                    Complaint Category <span class="required">*</span>
                </label>
                <select id="complaint_category" name="category"
                        class="form-control" required>
                    <option value="">Select category</option>
                    <option value="fraud">Fraud / Scam</option>
                    <option value="poor_service">Poor Service Quality</option>
                    <option value="no_show">No-Show / Abandoned Order</option>
                    <option value="overcharging">Overcharging</option>
                    <option value="fake_listing">Fake / Misleading Listing</option>
                    <option value="harassment">Harassment or Threats</option>
                    <option value="impersonation">Impersonation</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="complaint_desc">
                    Describe the Issue <span class="required">*</span>
                </label>
                <textarea id="complaint_desc"
                          name="description"
                          class="form-control"
                          rows="5"
                          placeholder="Describe what happened in detail (min 30 characters)..."
                          required
                          data-min="30"
                          data-max-chars="1000"></textarea>
                <div class="review-char-counter" id="complaintCounter"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="complaint_evidence">
                    Evidence (Optional)
                </label>
                <div class="file-upload-area" onclick="document.getElementById('complaint_evidence').click()">
                    <input type="file"
                           id="complaint_evidence"
                           name="evidence"
                           accept="image/jpeg,image/png,application/pdf"
                           style="display:none;">
                    <div class="file-upload-icon">
                        <?= lucide_icon('<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>', 28, 'var(--text-muted)') ?>
                    </div>
                    <div class="file-upload-text">Attach screenshot or PDF (optional)</div>
                    <div class="file-upload-hint" id="evidenceFileName">JPG, PNG or PDF &middot; Max 2MB</div>
                </div>
            </div>

            <div class="disclaimer-box" style="margin-bottom:1.25rem;">
                <span class="disclaimer-icon">
                    <?= lucide_icon('<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 18, 'var(--warning-dark)') ?>
                </span>
                <div class="disclaimer-text">
                    False complaints are a violation of our
                    <a href="<?= SITE_URL ?>/user-terms" target="_blank">User Terms</a>
                    and may result in account suspension.
                </div>
            </div>

            <button type="submit" class="btn btn-danger btn-full" id="complaintSubmitBtn"
                    style="display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;">
                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 16, '#fff') ?>
                Submit Complaint
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Inline JS for this page -->
<script>
(function() {
    'use strict';

    // ── Tab switching ───────────────────────────────────────────────
    document.querySelectorAll('.vendor-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;
            document.querySelectorAll('.vendor-tab-btn').forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            document.querySelectorAll('.vendor-tab-content').forEach(c => c.classList.remove('active'));
            const panel = document.querySelector(`.vendor-tab-content[data-tab="${target}"]`);
            if (panel) panel.classList.add('active');
        });
    });

    // ── Rating bar animation ─────────────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.rating-bar-fill').forEach(bar => {
            bar.style.transition = 'width 0.7s ease';
            bar.style.width = (bar.dataset.width || '0') + '%';
        });
    }, 300);

    // ── Star rating input ────────────────────────────────────────────
    const ratingLabels = { 1:'Poor', 2:'Fair', 3:'Good', 4:'Very Good', 5:'Excellent' };
    const ratingLabel  = document.getElementById('ratingLabel');
    document.querySelectorAll('#reviewForm input[name="rating"]').forEach(input => {
        input.addEventListener('change', () => {
            if (ratingLabel) ratingLabel.textContent = ratingLabels[input.value] || '';
        });
    });

    // ── Review char counter ──────────────────────────────────────────
    const reviewTextarea = document.getElementById('reviewText');
    const reviewCounter  = document.getElementById('reviewCounter');
    if (reviewTextarea && reviewCounter) {
        const max = parseInt(reviewTextarea.dataset.maxChars) || 500;
        reviewTextarea.addEventListener('input', () => {
            const len = reviewTextarea.value.length;
            reviewCounter.textContent = len + ' / ' + max;
            reviewCounter.className = 'review-char-counter';
            if (len > max - 50) reviewCounter.classList.add('warning');
            if (len > max - 20) reviewCounter.classList.add('danger');
        });
    }

    // ── Complaint char counter ───────────────────────────────────────
    const complaintTextarea = document.getElementById('complaint_desc');
    const complaintCounter  = document.getElementById('complaintCounter');
    if (complaintTextarea && complaintCounter) {
        const max = parseInt(complaintTextarea.dataset.maxChars) || 1000;
        complaintTextarea.addEventListener('input', () => {
            const len = complaintTextarea.value.length;
            complaintCounter.textContent = len + ' / ' + max;
            complaintCounter.className = 'review-char-counter';
            if (len > max - 100) complaintCounter.classList.add('warning');
            if (len > max - 50)  complaintCounter.classList.add('danger');
        });
    }

    // ── File name display ────────────────────────────────────────────
    const evidenceInput = document.getElementById('complaint_evidence');
    const evidenceLabel = document.getElementById('evidenceFileName');
    if (evidenceInput && evidenceLabel) {
        evidenceInput.addEventListener('change', () => {
            const file = evidenceInput.files[0];
            evidenceLabel.textContent = file ? file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)' : 'JPG, PNG or PDF · Max 2MB';
        });
    }

    // ── Review form and complaint modal behavior is handled by assets/js/browse.js.
    // This inline page script keeps only the tab switching and visual feedback logic.
})();
</script>