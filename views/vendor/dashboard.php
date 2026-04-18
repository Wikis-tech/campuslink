<?php defined('CAMPUSLINK') or die(); ?>

<div class="vendor-dashboard-wrapper">
    
    <!-- Hero Header with Gradient Overlay -->
    <section class="dashboard-hero" style="background: linear-gradient(135deg, #0b3d91 0%, #1e5bb8 50%, #1a4fa8 100%); position: relative; overflow: hidden;">
        <div style="position: absolute; inset: 0; background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 400%22><defs><pattern id=%22grid%22 width=%2240%22 height=%2240%22 patternUnits=%22userSpaceOnUse%22><path d=%22M 40 0 L 0 0 0 40%22 fill=%22none%22 stroke=%22rgba(255,255,255,0.05)%22 stroke-width=%221%22/></pattern></defs><rect width=%221200%22 height=%22400%22 fill=%22url(%23grid)%22/></svg>'); opacity: 0.3;"></div>
        <div style="position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(142, 197, 255, 0.1) 0%, transparent 70%); top: -300px; right: -200px; border-radius: 50%;"></div>
        
        <div class="container" style="position: relative; z-index: 1; padding: 4rem 2rem;">
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 3rem; align-items: start;">
                <!-- Header Content -->
                <div data-scroll-reveal style="animation: slideUpFadeIn 0.8s ease-out;">
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 20px; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(255,255,255,0.2); font-size: 2.5rem; font-weight: 800; color: rgba(134, 239, 172, 0.9);">
                            <?= strtoupper(substr(explode(' ', $vendor['full_name'])[0], 0, 1)) ?>
                        </div>
                        <div>
                            <h1 style="color: #ffffff; font-size: 2.5rem; font-weight: 800; margin: 0; line-height: 1.2;">
                                Welcome, <span style="background: linear-gradient(135deg, #86efac, #4ade80); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                    <?= e(explode(' ', $vendor['full_name'])[0]) ?>
                                </span>
                            </h1>
                            <p style="color: rgba(255,255,255,0.75); margin: 0.5rem 0 0 0; font-size: 1.1rem;">
                                <?= e($vendor['business_name']) ?> • <?= ucfirst($vendor['plan_type']) ?> Plan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Header CTA -->
                <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>" target="_blank" 
                   class="btn-hero-view" style="animation: slideUpFadeIn 0.8s ease-out 0.1s both;">
                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="eye" style="width:18px;height:18px;"></i> View Public Profile
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Subscription Status Hero Card -->
    <div class="container" style="margin-top: -2rem; position: relative; z-index: 10;">
        <?php if ($subscription): ?>
            <?php
            $daysLeft    = (int)($subscription['days_left'] ?? 0);
            $bannerClass = 'active';
            if ($daysLeft <= 0)  $bannerClass = 'expired';
            elseif ($daysLeft <= 7)  $bannerClass = 'expiring';
            elseif ($vendor['status'] === 'grace_period') $bannerClass = 'grace';

            $bannerIcons = [
                'active'   => 'check-circle',
                'expiring' => 'alert-circle',
                'expired'  => 'x-circle',
                'grace'    => 'clock',
            ];
            $bannerColors = [
                'active'   => ['bg' => 'rgba(30, 169, 82, 0.08)', 'border' => '#1ea952', 'text' => '#0d5d2a', 'icon' => '#1ea952'],
                'expiring' => ['bg' => 'rgba(245, 158, 11, 0.08)', 'border' => '#f59e0b', 'text' => '#78350f', 'icon' => '#f59e0b'],
                'expired'  => ['bg' => 'rgba(239, 68, 68, 0.08)', 'border' => '#ef4444', 'text' => '#7f1d1d', 'icon' => '#ef4444'],
                'grace'    => ['bg' => 'rgba(59, 130, 246, 0.08)', 'border' => '#3b82f6', 'text' => '#1e40af', 'icon' => '#3b82f6'],
            ];
            $colors = $bannerColors[$bannerClass];
            ?>
            <div class="subscription-hero-card" 
                 style="background: linear-gradient(135deg, <?= $colors['bg'] ?> 0%, rgba(255,255,255,0.3) 100%); border: 2px solid <?= $colors['border'] ?>; backdrop-filter: blur(20px); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; animation: slideUpFadeIn 0.8s ease-out 0.2s both;">
                
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: center;">
                    <div style="display: flex; gap: 1.5rem; align-items: start;">
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: <?= $colors['icon'] ?>; flex-shrink: 0;">
                            <i data-lucide="<?= $bannerIcons[$bannerClass] ?>" style="width:28px;height:28px;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: <?= $colors['text'] ?>;">
                                <?php if ($bannerClass === 'active'): ?>
                                    Subscription Active
                                <?php elseif ($bannerClass === 'expiring'): ?>
                                    Subscription Expiring Soon
                                <?php elseif ($bannerClass === 'expired'): ?>
                                    Subscription Expired
                                <?php else: ?>
                                    Grace Period Active
                                <?php endif; ?>
                            </h3>
                            <p style="margin: 0.5rem 0 0 0; color: rgba(0,0,0,0.6); font-size: 0.95rem;">
                                <?php if ($daysLeft > 0): ?>
                                    Expires on <strong><?= date('d M Y', strtotime($subscription['expiry_date'])) ?></strong>
                                <?php else: ?>
                                    Expired on <strong><?= date('d M Y', strtotime($subscription['expiry_date'])) ?></strong>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; justify-content: flex-end;">
                        <?php if ($daysLeft > 0): ?>
                        <div style="text-align: center; padding: 1rem; background: rgba(255,255,255,0.4); border-radius: 12px; min-width: 80px;">
                            <div style="font-size: 1.75rem; font-weight: 800; color: <?= $colors['text'] ?>;">
                                <?= $daysLeft ?>
                            </div>
                            <div style="font-size: 0.75rem; color: rgba(0,0,0,0.6); font-weight: 600; margin-top: 0.25rem;">
                                days left
                            </div>
                        </div>
                        <?php endif; ?>
                        <a href="<?= SITE_URL ?>/vendor/subscription" 
                           class="btn btn-primary" 
                           style="background: <?= $colors['icon'] ?>; border: none; padding: 0.75rem 1.5rem; font-weight: 600; transition: all 0.3s ease;">
                            <?= $daysLeft > 0 ? 'Manage Plan' : 'Renew Now' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="subscription-hero-card" 
                 style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(255,255,255,0.3) 100%); border: 2px solid #ef4444; backdrop-filter: blur(20px); border-radius: 24px; padding: 2rem; margin-bottom: 2rem; animation: slideUpFadeIn 0.8s ease-out 0.2s both;">
                
                <div style="display: flex; gap: 1.5rem; align-items: center; justify-content: space-between;">
                    <div style="display: flex; gap: 1.5rem; align-items: start; flex: 1;">
                        <div style="width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #ef4444; flex-shrink: 0;">
                            <i data-lucide="x-circle" style="width:28px;height:28px;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #7f1d1d;">
                                No Active Subscription
                            </h3>
                            <p style="margin: 0.5rem 0 0 0; color: rgba(0,0,0,0.6); font-size: 0.95rem;">
                                Your profile is currently hidden from students.
                            </p>
                        </div>
                    </div>
                    <a href="<?= SITE_URL ?>/vendor/subscription" 
                       class="btn btn-primary" 
                       style="background: #ef4444; border: none; padding: 0.75rem 1.5rem; font-weight: 600; white-space: nowrap;">
                        Subscribe Now
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Stats Grid - Premium Cards with Animations -->
    <div class="container" style="margin: 3rem auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
            
            <!-- Total Reviews Card -->
            <div class="stat-card-premium" 
                 style="animation: slideUpFadeIn 0.8s ease-out 0.3s both;">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #0b3d91 0%, rgba(30, 169, 82, 0.1) 100%); border-radius: 20px; opacity: 0.05;"></div>
                <div style="position: relative; z-index: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                        <div>
                            <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Total Reviews
                            </p>
                        </div>
                        <div style="width: 44px; height: 44px; background: rgba(30, 169, 82, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #1ea952;">
                            <i data-lucide="star" style="width:22px;height:22px;"></i>
                        </div>
                    </div>
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 2.5rem; font-weight: 900; color: #1f2937;">
                        <?= (int)$reviewCount ?>
                    </h2>
                    <a href="<?= SITE_URL ?>/vendor/reviews" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; color: #0b3d91; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.3s ease;">
                        View all <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </a>
                </div>
            </div>

            <!-- Avg Rating Card -->
            <div class="stat-card-premium" 
                 style="animation: slideUpFadeIn 0.8s ease-out 0.4s both;">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #1ea952 0%, rgba(30, 169, 82, 0.1) 100%); border-radius: 20px; opacity: 0.05;"></div>
                <div style="position: relative; z-index: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                        <div>
                            <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Avg Rating
                            </p>
                        </div>
                        <div style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                            <i data-lucide="trending-up" style="width:22px;height:22px;"></i>
                        </div>
                    </div>
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 2.5rem; font-weight: 900; color: #1f2937;">
                        <?= $avgRating > 0 ? number_format($avgRating, 1) : '—' ?>
                    </h2>
                    <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">
                        out of 5.0 stars
                    </p>
                </div>
            </div>

            <!-- Open Complaints Card -->
            <div class="stat-card-premium" 
                 style="animation: slideUpFadeIn 0.8s ease-out 0.5s both;">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #f59e0b 0%, rgba(245, 158, 11, 0.1) 100%); border-radius: 20px; opacity: 0.05;"></div>
                <div style="position: relative; z-index: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                        <div>
                            <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Open Complaints
                            </p>
                        </div>
                        <div style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                            <i data-lucide="alert-circle" style="width:22px;height:22px;"></i>
                        </div>
                    </div>
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 2.5rem; font-weight: 900; color: #1f2937;">
                        <?= (int)$openComplaints ?>
                    </h2>
                    <a href="<?= SITE_URL ?>/vendor/complaints" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; color: #f59e0b; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.3s ease;">
                        <?= $openComplaints > 0 ? 'Respond now' : 'View all' ?> <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </a>
                </div>
            </div>

            <!-- Notifications Card -->
            <div class="stat-card-premium" 
                 style="animation: slideUpFadeIn 0.8s ease-out 0.6s both;">
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #3b82f6 0%, rgba(59, 130, 246, 0.1) 100%); border-radius: 20px; opacity: 0.05;"></div>
                <div style="position: relative; z-index: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                        <div>
                            <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Notifications
                            </p>
                        </div>
                        <div style="width: 44px; height: 44px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                            <i data-lucide="bell" style="width:22px;height:22px;"></i>
                        </div>
                    </div>
                    <h2 style="margin: 0 0 0.5rem 0; font-size: 2.5rem; font-weight: 900; color: #1f2937;">
                        <?= (int)$unreadNotifs ?>
                    </h2>
                    <a href="<?= SITE_URL ?>/vendor/notifications" 
                       style="display: inline-flex; align-items: center; gap: 0.5rem; color: #3b82f6; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.3s ease;">
                        <?= $unreadNotifs > 0 ? 'View unread' : 'All clear' ?> <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="container" style="margin: 3rem auto; padding: 0 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem;">
            
            <!-- Left Column: Reviews & Complaints -->
            <div>
                
                <!-- Recent Reviews Card -->
                <div class="dashboard-content-card" style="animation: slideUpFadeIn 0.8s ease-out 0.7s both;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--divider);">
                        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
                            <span style="width: 36px; height: 36px; background: rgba(30, 169, 82, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #1ea952;">
                                <i data-lucide="star" style="width:18px;height:18px;"></i>
                            </span>
                            Recent Reviews
                        </h3>
                        <a href="<?= SITE_URL ?>/vendor/reviews" 
                           style="color: #0b3d91; font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                            View all →
                        </a>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if (empty($recentReviews)): ?>
                            <div style="text-align: center; padding: 2rem;">
                                <div style="width: 56px; height: 56px; background: rgba(30, 169, 82, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #1ea952;">
                                    <i data-lucide="star" style="width:28px;height:28px;"></i>
                                </div>
                                <h4 style="margin: 0 0 0.5rem 0; color: #1f2937;">No reviews yet</h4>
                                <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">
                                    Reviews from students will appear here once you get your first customers.
                                </p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($recentReviews, 0, 3) as $review): ?>
                            <div style="padding: 1.25rem; background: linear-gradient(135deg, rgba(11, 61, 145, 0.03) 0%, rgba(255,255,255,0.3) 100%); border: 1px solid rgba(11, 61, 145, 0.08); border-radius: 16px; transition: all 0.3s ease; cursor: pointer;"
                                 onmouseover="this.style.background='linear-gradient(135deg, rgba(11, 61, 145, 0.08) 0%, rgba(255,255,255,0.4) 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 24px rgba(11, 61, 145, 0.1)';"
                                 onmouseout="this.style.background='linear-gradient(135deg, rgba(11, 61, 145, 0.03) 0%, rgba(255,255,255,0.3) 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                
                                <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0b3d91, #1ea952); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 700; font-size: 0.9rem; flex-shrink: 0;">
                                        <?= strtoupper(substr($review['user_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <p style="margin: 0; font-weight: 600; color: #1f2937;">
                                            <?= e($review['user_name'] ?? 'Anonymous') ?>
                                        </p>
                                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                                            <?= e($review['level'] ?? '') ?> <?= !empty($review['department']) ? '· ' . e($review['department']) : '' ?>
                                        </p>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span style="color: <?= $i <= $review['rating'] ? '#f59e0b' : '#e9ecef' ?>;">
                                        <i data-lucide="star" style="width:16px;height:16px; fill: currentColor;"></i>
                                    </span>
                                    <?php endfor; ?>
                                    <span style="margin-left: auto; font-size: 0.8rem; color: var(--text-muted);">
                                        <?= date('d M Y', strtotime($review['created_at'])) ?>
                                    </span>
                                </div>

                                <p style="margin: 0; color: #555; font-size: 0.95rem; line-height: 1.5;">
                                    <?= e(truncate($review['review'] ?? '', 120)) ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Complaints Card -->
                <?php if (!empty($recentComplaints)): ?>
                <div class="dashboard-content-card" style="margin-top: 2rem; animation: slideUpFadeIn 0.8s ease-out 0.8s both;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--divider);">
                        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
                            <span style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;">
                                <i data-lucide="alert-circle" style="width:18px;height:18px;"></i>
                            </span>
                            Recent Complaints
                            <?php if ($openComplaints > 0): ?>
                            <span style="margin-left: auto; background: #ef4444; color: #ffffff; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 999px; font-weight: 600;">
                                <?= $openComplaints ?>
                            </span>
                            <?php endif; ?>
                        </h3>
                        <a href="<?= SITE_URL ?>/vendor/complaints" 
                           style="color: #0b3d91; font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                            View all →
                        </a>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($recentComplaints as $c): ?>
                        <div style="padding: 1.25rem; background: linear-gradient(135deg, rgba(239, 68, 68, 0.03) 0%, rgba(255,255,255,0.3) 100%); border: 1px solid rgba(239, 68, 68, 0.08); border-radius: 16px; transition: all 0.3s ease; cursor: pointer;"
                             onmouseover="this.style.background='linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(255,255,255,0.4) 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 24px rgba(239, 68, 68, 0.1)';"
                             onmouseout="this.style.background='linear-gradient(135deg, rgba(239, 68, 68, 0.03) 0%, rgba(255,255,255,0.3) 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                <div>
                                    <p style="margin: 0; font-weight: 600; color: #1f2937; font-size: 0.9rem; font-family: monospace;">
                                        Ticket #<?= e($c['ticket_id']) ?>
                                    </p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);">
                                        <?= ucwords(str_replace('_', ' ', $c['category'])) ?>
                                    </p>
                                </div>
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.75rem; border-radius: 6px; background: <?php
                                    $statusColors = [
                                        'open' => 'rgba(239, 68, 68, 0.1)',
                                        'submitted' => 'rgba(245, 158, 11, 0.1)',
                                        'under_review' => 'rgba(59, 130, 246, 0.1)',
                                        'investigating' => 'rgba(59, 130, 246, 0.1)',
                                        'resolved' => 'rgba(30, 169, 82, 0.1)',
                                    ];
                                    echo $statusColors[$c['status']] ?? 'rgba(107, 114, 128, 0.1)';
                                ?>; color: <?php
                                    $statusBadgeColors = [
                                        'open' => '#ef4444',
                                        'submitted' => '#f59e0b',
                                        'under_review' => '#3b82f6',
                                        'investigating' => '#3b82f6',
                                        'resolved' => '#1ea952',
                                    ];
                                    echo $statusBadgeColors[$c['status']] ?? '#6b7280';
                                ?>;">
                                    <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                                </span>
                            </div>

                            <p style="margin: 0; color: #555; font-size: 0.95rem; line-height: 1.5;">
                                <?= e(truncate($c['description'] ?? '', 120)) ?>
                            </p>

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid rgba(0,0,0,0.05);">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?= date('d M Y', strtotime($c['created_at'])) ?>
                                </span>
                                <?php if (empty($c['vendor_response']) && in_array($c['status'], ['submitted','under_review', 'open'])): ?>
                                <a href="<?= SITE_URL ?>/vendor/complaints#complaint-<?= (int)$c['id'] ?>"
                                   style="color: #0b3d91; font-weight: 600; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 0.4rem;">
                                    <i data-lucide="edit-3" style="width:14px;height:14px;"></i> Respond
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Sidebar -->
            <div>
                
                <!-- Profile Health Card -->
                <div class="dashboard-sidebar-card" style="animation: slideUpFadeIn 0.8s ease-out 0.7s both;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <h4 style="margin: 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="bar-chart-2" style="width:18px;height:18px; color:#0b3d91;"></i> Profile Health
                        </h4>
                    </div>

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

                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Profile Score</span>
                            <span style="font-size: 1rem; font-weight: 800; color: <?= $score >= 80 ? '#1ea952' : '#0b3d91' ?>;">
                                <?= $score %>%
                            </span>
                        </div>
                        <div style="height: 8px; background: var(--divider); border-radius: 999px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $score ?>%; background: linear-gradient(90deg, #0b3d91, #1ea952); border-radius: 999px; transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach ($checks as [$label, $done]): ?>
                        <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem;">
                            <span style="display: inline-flex; color: <?= $done ? '#1ea952' : 'var(--text-muted)' ?>; transition: all 0.3s ease;">
                                <i data-lucide="<?= $done ? 'check-circle-2' : 'circle' ?>" style="width:16px;height:16px;"></i>
                            </span>
                            <span style="color: <?= $done ? 'var(--text-secondary)' : 'var(--text-primary)' ?>; font-weight: <?= $done ? '400' : '600' ?>;">
                                <?= e($label) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="dashboard-sidebar-card" style="margin-top: 1.5rem; animation: slideUpFadeIn 0.8s ease-out 0.8s both;">
                    <h4 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="zap" style="width:18px;height:18px; color:#f59e0b;"></i> Quick Actions
                    </h4>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="<?= SITE_URL ?>/vendor/profile"
                           class="dashboard-quick-btn">
                            <i data-lucide="edit-2" style="width:16px;height:16px;"></i> Edit Profile
                        </a>
                        <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
                           target="_blank"
                           class="dashboard-quick-btn">
                            <i data-lucide="eye" style="width:16px;height:16px;"></i> View Profile
                        </a>
                        <a href="<?= SITE_URL ?>/vendor/reviews"
                           class="dashboard-quick-btn">
                            <i data-lucide="star" style="width:16px;height:16px;"></i> Manage Reviews
                        </a>
                        <a href="<?= SITE_URL ?>/vendor/subscription"
                           class="dashboard-quick-btn" style="background: linear-gradient(135deg, #0b3d91, #1e5bb8) !important; color: #ffffff !important; border: none !important;">
                            <i data-lucide="credit-card" style="width:16px;height:16px;"></i> Manage Plan
                        </a>
                    </div>
                </div>

                <!-- Account Status Card -->
                <div class="dashboard-sidebar-card" style="margin-top: 1.5rem; animation: slideUpFadeIn 0.8s ease-out 0.9s both;">
                    <h4 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="shield" style="width:18px;height:18px; color:#1ea952;"></i> Account
                    </h4>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.9rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Status</span>
                            <span style="background: <?= $vendor['status'] === 'active' ? 'rgba(30, 169, 82, 0.1)' : 'rgba(245, 158, 11, 0.1)' ?>; color: <?= $vendor['status'] === 'active' ? '#0d5d2a' : '#78350f' ?>; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
                                <?= ucfirst($vendor['status']) ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Type</span>
                            <span style="font-weight: 600;">
                                <?= ucfirst($vendor['vendor_type']) ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Plan</span>
                            <span style="font-weight: 600; color: #0b3d91;">
                                <?= ucfirst($vendor['plan_type']) ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Email</span>
                            <span style="color: <?= $vendor['email_verified'] ? '#1ea952' : '#ef4444' ?>; display: flex; align-items: center; gap: 0.4rem;">
                                <i data-lucide="<?= $vendor['email_verified'] ? 'check-circle-2' : 'x-circle' ?>" style="width:16px;height:16px;"></i>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Member</span>
                            <span style="font-weight: 600;">
                                <?= date('M Y', strtotime($vendor['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- Premium Animations & Styles -->
<style>
@keyframes slideUpFadeIn {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.vendor-dashboard-wrapper {
    background: linear-gradient(135deg, #f8f9fa 0%, rgba(11, 61, 145, 0.02) 100%);
    min-height: 100vh;
}

.dashboard-hero {
    margin: -2rem -2rem 2rem -2rem;
}

.btn-hero-view {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    padding: 0.875rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
}

.btn-hero-view:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-card-premium {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.6) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(11, 61, 145, 0.1);
    border-radius: 20px;
    padding: 1.75rem;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
    position: relative;
}

.stat-card-premium:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 100%);
    border-color: rgba(11, 61, 145, 0.2);
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(11, 61, 145, 0.12);
}

.dashboard-content-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(11, 61, 145, 0.08);
    border-radius: 20px;
    padding: 2rem;
    transition: all 0.3s ease;
}

.dashboard-sidebar-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.8) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(11, 61, 145, 0.08);
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.dashboard-quick-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(11, 61, 145, 0.06);
    border: 1px solid rgba(11, 61, 145, 0.12);
    color: #0b3d91;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: pointer;
}

.dashboard-quick-btn:hover {
    background: rgba(11, 61, 145, 0.12);
    border-color: rgba(11, 61, 145, 0.2);
    transform: translateX(4px);
}

.subscription-hero-card {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.subscription-hero-card:hover {
    transform: translateY(-4px);
}

@media (max-width: 768px) {
    [style*="grid-template-columns: 1fr 320px"] {
        grid-template-columns: 1fr !important;
    }

    .stat-card-premium {
        padding: 1.25rem;
    }

    .dashboard-content-card {
        padding: 1.25rem;
    }
}
</style>

<!-- Scroll Reveal & Icons Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
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