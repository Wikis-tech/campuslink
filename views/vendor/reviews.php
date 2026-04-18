<?php defined('CAMPUSLINK') or die(); ?>

<?php
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;'.$extra_style.'">'.$path.'</svg>';
}
?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Reviews</h1>
        <p class="dashboard-page-subtitle">
            <?= (int)$pagination['total'] ?> review<?= $pagination['total'] != 1 ? 's' : '' ?>
            · <?= number_format($avgRating, 1) ?> average
        </p>
    </div>
</div>

<!-- Rating Summary -->
<?php if ($pagination['total'] > 0): ?>
<div class="rating-summary" style="margin-bottom:1.5rem;">
    <div class="rating-big-number">
        <div class="rating-big-value"><?= number_format($avgRating, 1) ?></div>
        <div class="rating-big-stars">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span style="color:<?= $i <= round($avgRating) ? 'var(--warning-amber)' : 'var(--divider)' ?>;">
                <?= lucide_icon(
                    $i <= round($avgRating)
                        ? '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'
                        : '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                    20,
                    $i <= round($avgRating) ? 'var(--warning-amber)' : 'var(--divider)',
                    $i <= round($avgRating) ? 'fill:var(--warning-amber);' : 'fill:var(--divider);'
                ) ?>
            </span>
            <?php endfor; ?>
        </div>
        <div class="rating-big-count">
            <?= (int)$pagination['total'] ?> review<?= $pagination['total'] != 1 ? 's' : '' ?>
        </div>
    </div>

    <div class="rating-bars">
        <?php for ($star = 5; $star >= 1; $star--):
            $count = $distribution[$star] ?? 0;
            $pct   = $pagination['total'] > 0 ? round(($count / $pagination['total']) * 100) : 0;
        ?>
        <div class="rating-bar-row">
            <span class="rating-bar-label"><?= $star ?></span>
            <div class="rating-bar-track">
                <div class="rating-bar-fill" data-width="<?= $pct ?>" style="width:0;"></div>
            </div>
            <span class="rating-bar-count"><?= $count ?></span>
        </div>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- Reviews List -->
<?php if (empty($reviews)): ?>
<div class="dash-card">
    <div class="dash-card-body">
        <div class="empty-state">
            <!-- Star icon replacing ⭐ -->
            <div class="empty-icon">
                <?= lucide_icon(
                    '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                    48, 'var(--warning-amber)', 'fill:var(--warning-amber);'
                ) ?>
            </div>
            <h3>No reviews yet</h3>
            <p>Encourage satisfied customers to leave a review on your public profile.</p>
            <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
               target="_blank" class="btn btn-primary">
                Share Your Profile Link
            </a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="dash-card">
    <div class="dash-card-body">
        <div class="review-list">
            <?php foreach ($reviews as $review): ?>
            <div class="review-item" id="review-<?= (int)$review['id'] ?>">
                <div class="review-item-header">
                    <div class="review-user">
                        <div class="review-avatar">
                            <?= strtoupper(substr($review['user_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="review-user-name">
                                <?= e($review['user_name'] ?? 'Anonymous') ?>
                            </div>
                            <div class="review-user-level">
                                <?= e($review['level'] ?? '') ?>
                                <?= !empty($review['department']) ? ' · ' . e($review['department']) : '' ?>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="review-star <?= $i > $review['rating'] ? 'empty' : '' ?>">
                                <?= lucide_icon(
                                    '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                    16,
                                    $i > $review['rating'] ? 'var(--divider)' : 'var(--warning-amber)',
                                    $i > $review['rating'] ? 'fill:var(--divider);' : 'fill:var(--warning-amber);'
                                ) ?>
                            </span>
                            <?php endfor; ?>
                        </div>
                        <span class="review-date" data-time="<?= e($review['created_at']) ?>">
                            <?= date('d M Y', strtotime($review['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <p class="review-text"><?= e($review['review']) ?></p>

                <!-- Vendor Reply -->
                <?php if (!empty($review['vendor_reply'])): ?>
                <div class="review-vendor-reply">
                    <strong>Your Reply:</strong>
                    <?= e($review['vendor_reply']) ?>
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:0.25rem;">
                        <?= date('d M Y', strtotime($review['vendor_reply_at'])) ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="review-actions">
                    <button class="btn btn-sm btn-outline-primary reply-toggle-btn"
                            data-review-id="<?= (int)$review['id'] ?>"
                            style="display:inline-flex;align-items:center;gap:0.4rem;">
                        <!-- MessageSquare icon replacing 💬 -->
                        <?= lucide_icon(
                            '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
                            15, 'currentColor'
                        ) ?>
                        Reply to this review
                    </button>
                </div>

                <form class="reply-form" data-review-id="<?= (int)$review['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group" style="margin-bottom:0.75rem;">
                        <textarea name="reply"
                                  class="form-control"
                                  placeholder="Write a professional, helpful response..."
                                  rows="3"
                                  data-max-chars="500"
                                  required></textarea>
                        <div class="review-char-counter" data-counter-for="reply"></div>
                    </div>
                    <div style="display:flex;gap:0.5rem;">
                        <button type="submit" class="btn btn-primary btn-sm">Post Reply</button>
                        <button type="button"
                                class="btn btn-outline-primary btn-sm reply-toggle-btn"
                                data-review-id="<?= (int)$review['id'] ?>">
                            Cancel
                        </button>
                    </div>
                </form>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>