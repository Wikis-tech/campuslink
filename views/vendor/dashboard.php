<?php defined('CAMPUSLINK') or die(); ?>

<!-- Page Header -->
<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">
            Welcome, <?= e(explode(' ', $vendor['full_name'])[0]) ?>!
        </h1>
        <p class="dashboard-page-subtitle">
            <?= e($vendor['business_name']) ?> · <?= ucfirst($vendor['plan_type']) ?> Plan
        </p>
    </div>
    <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
       class="btn btn-outline-primary" target="_blank">
        <i data-lucide="eye" style="width:15px;height:15px;"></i> View Public Profile
    </a>
</div>

<!-- Subscription Status Banner -->
<?php if ($subscription): ?>
<?php
$daysLeft    = (int)$subscription['days_left'];
$bannerClass = 'active';
if ($daysLeft <= 0)  $bannerClass = 'expired';
elseif ($daysLeft <= 7)  $bannerClass = 'expiring';
elseif ($daysLeft <= 14) $bannerClass = 'expiring';
elseif ($vendor['status'] === 'grace_period') $bannerClass = 'grace';

$bannerIcons = [
    'active'   => 'check-circle',
    'expiring' => 'alert-triangle',
    'expired'  => 'x-circle',
    'grace'    => 'clock',
];
?>
<div class="subscription-banner <?= $bannerClass ?>">
    <div class="sub-banner-left">
        <span class="sub-banner-icon">
            <i data-lucide="<?= $bannerIcons[$bannerClass] ?>"></i>
        </span>
        <div>
            <div class="sub-banner-title">
                <?php if ($bannerClass === 'active'): ?>
                    Subscription Active — <?= ucfirst($vendor['plan_type']) ?> Plan
                <?php elseif ($bannerClass === 'expiring'): ?>
                    Subscription Expiring Soon
                <?php elseif ($bannerClass === 'expired'): ?>
                    Subscription Expired — Renew to stay listed
                <?php else: ?>
                    Grace Period — Profile still visible
                <?php endif; ?>
            </div>
            <div class="sub-banner-text">
                <?php if ($daysLeft > 0): ?>
                    Expires <?= date('d M Y', strtotime($subscription['expiry_date'])) ?>
                <?php else: ?>
                    Expired on <?= date('d M Y', strtotime($subscription['expiry_date'])) ?>.
                    <a href="<?= SITE_URL ?>/vendor/subscription" style="font-weight:700;">Renew now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <?php if ($daysLeft > 0): ?>
        <div class="sub-banner-days">
            <span class="sub-banner-days-number"><?= $daysLeft ?></span>
            <span class="sub-banner-days-label">days left</span>
        </div>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/vendor/subscription" class="btn btn-sm btn-primary">
            <?= $daysLeft > 0 ? 'Manage Plan' : 'Renew Now' ?>
        </a>
    </div>
</div>
<?php else: ?>
<div class="subscription-banner expired">
    <div class="sub-banner-left">
        <span class="sub-banner-icon"><i data-lucide="x-circle"></i></span>
        <div>
            <div class="sub-banner-title">No Active Subscription</div>
            <div class="sub-banner-text">Your profile is not visible to students.</div>
        </div>
    </div>
    <a href="<?= SITE_URL ?>/vendor/subscription" class="btn btn-primary btn-sm">
        Subscribe Now →
    </a>
</div>
<?php endif; ?>

<!-- Stats Row -->
<div class="dashboard-stats-row">
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-card-label">Total Reviews</span>
            <span class="stat-card-icon"><i data-lucide="star"></i></span>
        </div>
        <div class="stat-card-value"><?= (int)$reviewCount ?></div>
        <div class="stat-card-sub">
            <a href="<?= SITE_URL ?>/vendor/reviews"
               style="color:var(--primary);font-weight:600;font-size:var(--font-size-xs);">
                View all →
            </a>
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-card-top">
            <span class="stat-card-label">Avg Rating</span>
            <span class="stat-card-icon"><i data-lucide="trending-up"></i></span>
        </div>
        <div class="stat-card-value">
            <?= $avgRating > 0 ? number_format($avgRating, 1) : '—' ?>
        </div>
        <div class="stat-card-sub" style="color:var(--text-muted);font-size:var(--font-size-xs);">
            out of 5.0
        </div>
    </div>

    <div class="stat-card amber">
        <div class="stat-card-top">
            <span class="stat-card-label">Open Complaints</span>
            <span class="stat-card-icon"><i data-lucide="clipboard"></i></span>
        </div>
        <div class="stat-card-value"><?= (int)$openComplaints ?></div>
        <div class="stat-card-sub">
            <a href="<?= SITE_URL ?>/vendor/complaints"
               style="color:var(--warning-dark);font-weight:600;font-size:var(--font-size-xs);">
                <?= $openComplaints > 0 ? 'Respond now →' : 'View all →' ?>
            </a>
        </div>
    </div>

    <div class="stat-card <?= $unreadNotifs > 0 ? 'danger' : '' ?>">
        <div class="stat-card-top">
            <span class="stat-card-label">Notifications</span>
            <span class="stat-card-icon"><i data-lucide="bell"></i></span>
        </div>
        <div class="stat-card-value"><?= (int)$unreadNotifs ?></div>
        <div class="stat-card-sub">
            <a href="<?= SITE_URL ?>/vendor/notifications"
               style="color:var(--primary);font-weight:600;font-size:var(--font-size-xs);">
                <?= $unreadNotifs > 0 ? 'View unread →' : 'All clear' ?>
            </a>
        </div>
    </div>
</div>

<!-- Main Grid -->
<div class="dashboard-grid-main-side">

    <!-- Left: Reviews & Complaints -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        <!-- Recent Reviews -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-title-icon"><i data-lucide="star"></i></span>
                    Recent Reviews
                </div>
                <a href="<?= SITE_URL ?>/vendor/reviews" class="dash-card-link">View all →</a>
            </div>
            <div class="dash-card-body">
                <?php if (empty($recentReviews)): ?>
                <div class="empty-state" style="padding:2rem;">
                    <div class="empty-icon"><i data-lucide="star"></i></div>
                    <h3>No reviews yet</h3>
                    <p>Reviews from students will appear here after you get your first customers.</p>
                </div>
                <?php else: ?>
                <div class="review-list">
                    <?php foreach ($recentReviews as $review): ?>
                    <div class="review-item">
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
                                        <?= !empty($review['department']) ? '· ' . e($review['department']) : '' ?>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="review-star <?= $i > $review['rating'] ? 'empty' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="review-date" data-time="<?= e($review['created_at']) ?>">
                                    <?= date('d M', strtotime($review['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        <p class="review-text"><?= e(truncate($review['review'], 120)) ?></p>

                        <?php if (empty($review['vendor_reply'])): ?>
                        <div class="review-actions">
                            <button class="btn btn-sm btn-outline-primary reply-toggle-btn"
                                    data-review-id="<?= (int)$review['id'] ?>">
                                <i data-lucide="message-square" style="width:14px;height:14px;"></i> Reply
                            </button>
                        </div>
                        <div class="reply-form" data-review-id="<?= (int)$review['id'] ?>">
                            <textarea class="form-control"
                                      placeholder="Write a professional response..."
                                      rows="3"
                                      data-max-chars="500"></textarea>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Post Reply
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="review-vendor-reply">
                            <strong>Your Reply:</strong>
                            <?= e(truncate($review['vendor_reply'], 100)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Complaints -->
        <?php if (!empty($recentComplaints)): ?>
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-title-icon"><i data-lucide="clipboard"></i></span>
                    Recent Complaints
                    <?php if ($openComplaints > 0): ?>
                    <span class="sidebar-nav-badge" style="margin-left:0.5rem;">
                        <?= $openComplaints ?>
                    </span>
                    <?php endif; ?>
                </div>
                <a href="<?= SITE_URL ?>/vendor/complaints" class="dash-card-link">
                    View all →
                </a>
            </div>
            <div class="dash-card-body">
                <div class="complaint-list">
                    <?php foreach ($recentComplaints as $c): ?>
                    <div class="complaint-item">
                        <div class="complaint-item-header">
                            <div>
                                <span class="complaint-ticket"><?= e($c['ticket_id']) ?></span>
                                <div class="complaint-category">
                                    <?= ucwords(str_replace('_', ' ', $c['category'])) ?>
                                </div>
                            </div>
                            <span class="badge badge-status-<?= e($c['status']) ?>">
                                <?= ucwords(str_replace('_', ' ', $c['status'])) ?>
                            </span>
                        </div>
                        <p class="complaint-text"><?= e($c['description']) ?></p>
                        <div class="complaint-footer">
                            <span class="complaint-date" data-time="<?= e($c['created_at']) ?>">
                                <?= date('d M Y', strtotime($c['created_at'])) ?>
                            </span>
                            <?php if (empty($c['vendor_response']) && in_array($c['status'], ['submitted','under_review'])): ?>
                            <a href="<?= SITE_URL ?>/vendor/complaints#complaint-<?= (int)$c['id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i data-lucide="edit-3" style="width:14px;height:14px;"></i> Respond
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right Sidebar -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        <!-- Profile Completeness -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-title-icon"><i data-lucide="bar-chart-2"></i></span>
                    Profile Health
                </div>
                <a href="<?= SITE_URL ?>/vendor/profile" class="dash-card-link">Edit →</a>
            </div>
            <div class="dash-card-body">
                <?php
                $score    = 0;
                $checks   = [];
                if (!empty($vendor['logo']))        { $score += 20; $checks[] = ['Logo uploaded', true]; }
                else                                { $checks[] = ['Upload a logo', false]; }
                if (!empty($vendor['description'])) { $score += 20; $checks[] = ['Description added', true]; }
                else                                { $checks[] = ['Add a description', false]; }
                if (!empty($vendor['price_range'])) { $score += 20; $checks[] = ['Price range set', true]; }
                else                                { $checks[] = ['Set price range', false]; }
                if (!empty($vendor['whatsapp_number'])) { $score += 20; $checks[] = ['WhatsApp linked', true]; }
                else                                { $checks[] = ['Add WhatsApp number', false]; }
                if ($reviewCount > 0)               { $score += 20; $checks[] = ['Has reviews', true]; }
                else                                { $checks[] = ['Get your first review', false]; }
                ?>
                <div style="margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
                        <span style="font-size:var(--font-size-sm);font-weight:600;">
                            Profile Score
                        </span>
                        <span style="font-size:var(--font-size-sm);font-weight:800;color:<?= $score >= 80 ? 'var(--accent-green)' : 'var(--warning-dark)' ?>;">
                            <?= $score ?>%
                        </span>
                    </div>
                    <div style="height:8px;background:var(--divider);border-radius:var(--radius-full);overflow:hidden;">
                        <div style="height:100%;width:<?= $score ?>%;background:<?= $score >= 80 ? 'var(--accent-green)' : 'var(--primary)' ?>;border-radius:var(--radius-full);transition:width 0.8s ease;"></div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <?php foreach ($checks as [$label, $done]): ?>
                    <div style="display:flex;align-items:center;gap:0.5rem;font-size:var(--font-size-xs);">
                        <span style="display:inline-flex;width:14px;height:14px;color:<?= $done ? 'var(--accent-green)' : 'var(--text-muted)' ?>;">
                            <i data-lucide="<?= $done ? 'check-circle' : 'circle' ?>" style="width:14px;height:14px;"></i>
                        </span>
                        <span style="color:<?= $done ? 'var(--text-secondary)' : 'var(--text-primary)' ?>;
                                    font-weight:<?= $done ? '400' : '600' ?>;">
                            <?= e($label) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-title-icon"><i data-lucide="zap"></i></span>
                    Quick Actions
                </div>
            </div>
            <div class="dash-card-body" style="display:flex;flex-direction:column;gap:0.75rem;">
                <a href="<?= SITE_URL ?>/vendor/profile"
                   class="btn btn-outline-primary btn-full">
                    <i data-lucide="edit-2" style="width:15px;height:15px;"></i> Edit Profile
                </a>
                <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
                   class="btn btn-outline-primary btn-full" target="_blank">
                    <i data-lucide="eye" style="width:15px;height:15px;"></i> View Public Profile
                </a>
                <a href="<?= SITE_URL ?>/vendor/reviews"
                   class="btn btn-outline-primary btn-full">
                    <i data-lucide="star" style="width:15px;height:15px;"></i> Manage Reviews
                </a>
                <a href="<?= SITE_URL ?>/vendor/subscription"
                   class="btn btn-primary btn-full">
                    <i data-lucide="credit-card" style="width:15px;height:15px;"></i> Manage Subscription
                </a>
            </div>
        </div>

        <!-- Account Status -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <span class="dash-card-title-icon"><i data-lucide="shield"></i></span>
                    Account Status
                </div>
            </div>
            <div class="dash-card-body">
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:var(--font-size-sm);">
                        <span style="color:var(--text-muted);">Status</span>
                        <span class="badge badge-<?= $vendor['status'] === 'active' ? 'active' : 'pending' ?>">
                            <?= ucfirst($vendor['status']) ?>
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:var(--font-size-sm);">
                        <span style="color:var(--text-muted);">Vendor Type</span>
                        <span style="font-weight:600;">
                            <?= ucfirst($vendor['vendor_type']) ?>
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:var(--font-size-sm);">
                        <span style="color:var(--text-muted);">Plan</span>
                        <span style="font-weight:600;color:var(--primary);">
                            <?= ucfirst($vendor['plan_type']) ?>
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:var(--font-size-sm);">
                        <span style="color:var(--text-muted);">Email Verified</span>
                        <span style="display:inline-flex;color:<?= $vendor['email_verified'] ? 'var(--accent-green)' : 'var(--danger)' ?>;">
                            <i data-lucide="<?= $vendor['email_verified'] ? 'check-circle' : 'x-circle' ?>" style="width:16px;height:16px;"></i>
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:var(--font-size-sm);">
                        <span style="color:var(--text-muted);">Member Since</span>
                        <span style="font-weight:600;">
                            <?= date('M Y', strtotime($vendor['created_at'])) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>