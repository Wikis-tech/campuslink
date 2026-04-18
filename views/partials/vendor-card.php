<?php
/**
 * Reusable Vendor Card Component
 * $vendor   array   — vendor data with avg_rating, review_count
 * $savedIds array   — list of saved vendor IDs for current user
 * $userId   int     — current user ID (0 if not logged in)
 */
defined('CAMPUSLINK') or die();

$isFeatured = ($vendor['plan_type'] ?? '') === 'featured';
$isSaved    = in_array($vendor['id'], $savedIds ?? []);
$rating     = (float)($vendor['avg_rating'] ?? 0);
$reviews    = (int)($vendor['review_count'] ?? 0);
$initials   = strtoupper(substr($vendor['business_name'] ?? 'V', 0, 2));

function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
?>
<article class="browse-vendor-card <?= $isFeatured ? 'featured' : '' ?>"
         itemscope itemtype="https://schema.org/LocalBusiness">

    <div class="browse-card-top">

        <!-- Logo -->
        <?php if (!empty($vendor['logo'])): ?>
            <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($vendor['logo']) ?>"
                 alt="<?= e($vendor['business_name']) ?> logo"
                 class="browse-vendor-logo"
                 itemprop="image"
                 loading="lazy">
        <?php else: ?>
            <div class="browse-vendor-logo-placeholder" aria-hidden="true">
                <?= e($initials) ?>
            </div>
        <?php endif; ?>

        <!-- Name & Rating -->
        <div class="browse-card-name-area">
            <div class="browse-card-name" itemprop="name">
                <?= e($vendor['business_name']) ?>
            </div>
            <span class="browse-card-category">
                <?= e($vendor['category_name'] ?? '') ?>
            </span>
            <div class="browse-card-rating">
                <div class="browse-card-rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= round($rating) ? '' : 'empty' ?>">
                            <?= lucide_icon(
                                '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                14,
                                $i <= round($rating) ? 'var(--warning-amber)' : 'var(--divider)',
                                $i <= round($rating) ? 'fill:var(--warning-amber);' : 'fill:var(--divider);'
                            ) ?>
                        </span>
                    <?php endfor; ?>
                </div>
                <span class="browse-card-rating-text">
                    <?= $rating > 0 ? number_format($rating, 1) : 'New' ?>
                    <?= $reviews > 0 ? "({$reviews})" : '' ?>
                </span>
            </div>
        </div>

        <!-- Save Button — ♥/♡ → Heart -->
        <button class="browse-save-btn <?= $isSaved ? 'saved' : '' ?>"
                data-vendor-id="<?= (int)$vendor['id'] ?>"
                data-user-id="<?= (int)($userId ?? 0) ?>"
                title="<?= $isSaved ? 'Remove from saved' : 'Save vendor' ?>"
                aria-label="<?= $isSaved ? 'Remove from saved' : 'Save vendor' ?>">
            <?= lucide_icon(
                '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
                18,
                $isSaved ? '#e11d48' : 'currentColor',
                $isSaved ? 'fill:#e11d48;' : ''
            ) ?>
        </button>
    </div>

    <!-- Card Body -->
    <div class="browse-card-body">
        <p class="browse-card-desc" itemprop="description">
            <?= e(truncate($vendor['description'] ?? '', 100)) ?>
        </p>
        <div class="browse-card-meta">
            <?php if (!empty($vendor['price_range'])): ?>
            <!-- 💰 → DollarSign -->
            <span class="browse-card-price"
                  style="display:inline-flex;align-items:center;gap:0.25rem;">
                <?= lucide_icon('<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>', 13, 'currentColor') ?>
                <?= e($vendor['price_range']) ?>
            </span>
            <?php endif; ?>

            <!-- 🎓/🏢 → GraduationCap / Building2 -->
            <span class="browse-card-type"
                  style="display:inline-flex;align-items:center;gap:0.25rem;">
                <?php if ($vendor['vendor_type'] === 'student'): ?>
                    <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 13, 'currentColor') ?>
                    Student
                <?php else: ?>
                    <?= lucide_icon('<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>', 13, 'currentColor') ?>
                    Business
                <?php endif; ?>
            </span>

            <?php if ($isFeatured): ?>
            <!-- ⭐ Featured → Star -->
            <span class="badge badge-featured"
                  style="display:inline-flex;align-items:center;gap:0.25rem;">
                <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 12, 'currentColor', 'fill:currentColor;') ?>
                Featured
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card Footer -->
    <div class="browse-card-footer">
        <?php if (!empty($vendor['whatsapp_number'])): ?>
        <!-- 💬 → MessageCircle -->
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="btn-whatsapp"
           style="display:inline-flex;align-items:center;gap:0.35rem;"
           onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. CampusLink is not responsible for the outcome of this interaction.')">
            <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 15, 'currentColor') ?>
            WhatsApp
        </a>
        <?php endif; ?>

        <?php if (!empty($vendor['phone'])): ?>
        <!-- 📞 → Phone -->
        <a href="tel:<?= e($vendor['phone']) ?>"
           class="btn-call"
           style="display:inline-flex;align-items:center;gap:0.35rem;">
            <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 15, 'currentColor') ?>
            Call
        </a>
        <?php endif; ?>

        <!-- → arrow → ArrowRight -->
        <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
           class="btn-profile"
           style="display:inline-flex;align-items:center;gap:0.35rem;"
           itemprop="url">
            View Profile
            <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 14, 'currentColor') ?>
        </a>
    </div>

</article>