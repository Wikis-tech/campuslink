<?php defined('CAMPUSLINK') or die(); ?>

<?php
if (!function_exists('lucide_icon')) {
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
}

?>

<style>
/* ===========================================
   PREMIUM VENDOR PROFILE - GLASSMORPHISM & MOTION
   =========================================== */

:root {
    --glass-bg: rgba(255, 255, 255, 0.08);
    --glass-border: rgba(255, 255, 255, 0.12);
    --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    --glass-hover: rgba(255, 255, 255, 0.15);
    --premium-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --motion-ease: cubic-bezier(0.23, 1, 0.320, 1);
    --motion-spring: cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.vendor-profile-premium {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
    position: relative;
    overflow-x: hidden;
}

.vendor-profile-premium::before {
    content: '';
    position: fixed;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.03) 0%, transparent 70%);
    animation: float 20s ease-in-out infinite;
    pointer-events: none;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -30px) rotate(120deg); }
    66% { transform: translate(-20px, 20px) rotate(240deg); }
}

/* Hero Section - Organic Flow */
.vendor-hero-organic {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    overflow: hidden;
}

.vendor-hero-bg {
    position: absolute;
    inset: 0;
    background: var(--premium-gradient);
    opacity: 0.9;
}

.vendor-hero-bg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="90" cy="30" r="0.5" fill="rgba(255,255,255,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    animation: grain-move 8s ease-in-out infinite;
}

@keyframes grain-move {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-10px, -10px); }
}

.vendor-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 800px;
    animation: slide-up 1s var(--motion-ease);
}

@keyframes slide-up {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.vendor-logo-premium {
    width: 140px;
    height: 140px;
    margin: 0 auto 2rem;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    animation: logo-bounce 1.2s var(--motion-spring);
}

@keyframes logo-bounce {
    0% { transform: scale(0.8) rotate(-10deg); opacity: 0; }
    50% { transform: scale(1.1) rotate(5deg); }
    100% { transform: scale(1) rotate(0deg); opacity: 1; }
}

.vendor-logo-premium img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.vendor-name-premium {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 900;
    color: #ffffff;
    margin-bottom: 1rem;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    letter-spacing: -0.02em;
    line-height: 1.1;
}

.vendor-badges-premium {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.vendor-badge-premium {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    color: #ffffff;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: var(--glass-shadow);
    transition: all 0.3s var(--motion-ease);
    animation: badge-fade-in 0.8s var(--motion-ease) backwards;
}

.vendor-badge-premium:nth-child(1) { animation-delay: 0.1s; }
.vendor-badge-premium:nth-child(2) { animation-delay: 0.2s; }
.vendor-badge-premium:nth-child(3) { animation-delay: 0.3s; }

@keyframes badge-fade-in {
    from { opacity: 0; transform: translateY(20px) scale(0.9); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.vendor-badge-premium:hover {
    background: var(--glass-hover);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.vendor-rating-premium {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.vendor-stars-premium {
    display: flex;
    gap: 0.25rem;
}

.vendor-stars-premium svg {
    width: 24px;
    height: 24px;
    fill: #fbbf24;
    stroke: #fbbf24;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.vendor-rating-text-premium {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
    font-size: 1.1rem;
}

.vendor-actions-premium {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.vendor-action-premium {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    color: #ffffff;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s var(--motion-ease);
    box-shadow: var(--glass-shadow);
    animation: action-slide-in 1s var(--motion-ease) backwards;
}

.vendor-action-premium:nth-child(1) { animation-delay: 0.4s; }
.vendor-action-premium:nth-child(2) { animation-delay: 0.5s; }
.vendor-action-premium:nth-child(3) { animation-delay: 0.6s; }

@keyframes action-slide-in {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}

.vendor-action-premium:hover {
    background: var(--glass-hover);
    transform: translateY(-3px);
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
}

/* Content Section - Organic Layout */
.vendor-content-organic {
    padding: 6rem 2rem 4rem;
    position: relative;
    z-index: 3;
}

.vendor-content-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 3rem;
    align-items: start;
}

/* Main Content Card - Glassmorphism */
.vendor-main-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 3rem;
    box-shadow: var(--glass-shadow);
    animation: card-appear 1.2s var(--motion-ease) backwards;
    animation-delay: 0.8s;
}

@keyframes card-appear {
    from { opacity: 0; transform: translateY(40px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Tab Navigation - Premium */
.vendor-tabs-premium {
    display: flex;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 16px;
    padding: 0.5rem;
    margin-bottom: 3rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.vendor-tab-premium {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.7);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.3s var(--motion-ease);
    position: relative;
    overflow: hidden;
}

.vendor-tab-premium::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.vendor-tab-premium:hover::before {
    opacity: 1;
}

.vendor-tab-premium.active {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.15);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.vendor-tab-premium.active::before {
    opacity: 1;
}

/* Tab Content */
.vendor-tab-content-premium {
    display: none;
    animation: tab-fade 0.4s ease;
}

.vendor-tab-content-premium.active {
    display: block;
}

@keyframes tab-fade {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* About Section */
.vendor-about-premium {
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.8;
    font-size: 1.1rem;
    margin-bottom: 3rem;
}

.vendor-details-organic {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.vendor-detail-organic {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 2rem;
    transition: all 0.3s var(--motion-ease);
    animation: detail-float 0.8s var(--motion-ease) backwards;
}

.vendor-detail-organic:nth-child(1) { animation-delay: 1s; }
.vendor-detail-organic:nth-child(2) { animation-delay: 1.1s; }
.vendor-detail-organic:nth-child(3) { animation-delay: 1.2s; }
.vendor-detail-organic:nth-child(4) { animation-delay: 1.3s; }

@keyframes detail-float {
    from { opacity: 0; transform: translateY(30px) rotate(-2deg); }
    to { opacity: 1; transform: translateY(0) rotate(0deg); }
}

.vendor-detail-organic:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
}

.vendor-detail-icon-organic {
    width: 48px;
    height: 48px;
    background: var(--premium-gradient);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: #ffffff;
}

.vendor-detail-label-organic {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.6);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.vendor-detail-value-organic {
    font-size: 1.1rem;
    color: #ffffff;
    font-weight: 700;
}

/* Sidebar - Contact Card */
.vendor-sidebar-premium {
    position: sticky;
    top: 2rem;
}

.vendor-contact-glass {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 2.5rem;
    box-shadow: var(--glass-shadow);
    animation: sidebar-slide 1s var(--motion-ease) backwards;
    animation-delay: 1s;
}

@keyframes sidebar-slide {
    from { opacity: 0; transform: translateX(40px); }
    to { opacity: 1; transform: translateX(0); }
}

.vendor-contact-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 2rem;
    text-align: center;
}

.vendor-contact-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.vendor-contact-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s var(--motion-ease);
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.vendor-contact-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--premium-gradient);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.vendor-contact-btn:hover::before {
    opacity: 1;
}

.vendor-contact-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.vendor-contact-btn span:first-child {
    position: relative;
    z-index: 2;
    color: #ffffff;
}

.vendor-contact-btn span:last-child {
    position: relative;
    z-index: 2;
    color: #ffffff;
    font-size: 0.9rem;
    opacity: 0.9;
}

.vendor-whatsapp-btn {
    background: #25d366;
}

.vendor-call-btn {
    background: #10b981;
}

.vendor-actions-secondary {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.vendor-action-secondary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s var(--motion-ease);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.vendor-action-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .vendor-content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .vendor-sidebar-premium {
        position: static;
    }
}

@media (max-width: 768px) {
    .vendor-hero-organic {
        min-height: 60vh;
        padding: 3rem 1rem;
    }

    .vendor-name-premium {
        font-size: clamp(2rem, 8vw, 3rem);
    }

    .vendor-badges-premium {
        gap: 0.75rem;
    }

    .vendor-badge-premium {
        padding: 0.6rem 1.2rem;
        font-size: 0.8rem;
    }

    .vendor-actions-premium {
        gap: 0.75rem;
    }

    .vendor-action-premium {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }

    .vendor-main-card {
        padding: 2rem 1.5rem;
    }

    .vendor-tabs-premium {
        padding: 0.375rem;
        margin-bottom: 2rem;
    }

    .vendor-tab-premium {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }

    .vendor-details-organic {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .vendor-contact-glass {
        padding: 2rem 1.5rem;
    }
}
</style>

<!-- Premium Vendor Profile -->
<div class="vendor-profile-premium">
    <!-- Hero Section -->
    <section class="vendor-hero-organic">
        <div class="vendor-hero-bg"></div>
        <div class="vendor-hero-content">

            <!-- Logo -->
            <div class="vendor-logo-premium">
                <?php if (!empty($vendor['logo'])): ?>
                <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($vendor['logo']) ?>"
                     alt="<?= e($vendor['business_name']) ?>">
                <?php else: ?>
                <div style="width:100%;height:100%;background:var(--premium-gradient);display:flex;align-items:center;justify-content:center;font-size:3rem;font-weight:900;color:#ffffff;">
                    <?= strtoupper(substr($vendor['business_name'], 0, 2)) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Name -->
            <h1 class="vendor-name-premium">
                <?= e($vendor['business_name']) ?>
            </h1>

            <!-- Badges -->
            <div class="vendor-badges-premium">
                <!-- Verified -->
                <span class="vendor-badge-premium">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Verified Vendor
                </span>

                <?php if ($vendor['plan_type'] === 'featured'): ?>
                <!-- Featured -->
                <span class="vendor-badge-premium">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    Featured
                </span>
                <?php endif; ?>

                <!-- Vendor Type -->
                <span class="vendor-badge-premium">
                    <?php if ($vendor['vendor_type'] === 'student'): ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/>
                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                    </svg>
                    <?php endif; ?>
                    <?= ucfirst($vendor['vendor_type']) ?> Vendor
                </span>
            </div>

            <!-- Rating -->
            <div class="vendor-rating-premium">
                <div class="vendor-stars-premium">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" class="<?= $i > round($avgRating) ? 'empty' : '' ?>">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <span class="vendor-rating-text-premium">
                    <?= $avgRating > 0 ? number_format($avgRating, 1) . '/5' : 'No reviews yet' ?>
                    <?= $reviewTotal > 0 ? " ({$reviewTotal} review" . ($reviewTotal != 1 ? 's' : '') . ")" : '' ?>
                </span>
            </div>

            <!-- Actions -->
            <div class="vendor-actions-premium">
                <?php if (!empty($vendor['whatsapp_number'])): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="vendor-action-premium"
                   onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. CampusLink is not responsible for transactions or outcomes.')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                    WhatsApp
                </a>
                <?php endif; ?>

                <?php if (!empty($vendor['phone'])): ?>
                <a href="tel:<?= e($vendor['phone']) ?>" class="vendor-action-premium">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>
                    </svg>
                    Call
                </a>
                <?php endif; ?>

                <button class="vendor-action-premium share-vendor-btn"
                        data-title="<?= e($vendor['business_name']) ?> on CampusLink">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                    Share
                </button>
            </div>

        </div>
    </section>

    <!-- Content Section -->
    <section class="vendor-content-organic">
        <div class="vendor-content-grid">

            <!-- Main Content -->
            <div class="vendor-main-card">

                <!-- Tabs -->
                <div class="vendor-tabs-premium" role="tablist">
                    <button class="vendor-tab-premium active"
                            data-tab="about" role="tab" aria-selected="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="2" width="6" height="4" rx="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <line x1="12" y1="11" x2="16" y2="11"/>
                            <line x1="12" y1="16" x2="16" y2="16"/>
                            <line x1="8" y1="11" x2="8.01" y2="11"/>
                            <line x1="8" y1="16" x2="8.01" y2="16"/>
                        </svg>
                        About
                    </button>
                    <button class="vendor-tab-premium"
                            data-tab="reviews" role="tab" aria-selected="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Reviews (<?= (int)$reviewTotal ?>)
                    </button>
                    <?php if (!empty($vendor['service_photo'])): ?>
                    <button class="vendor-tab-premium"
                            data-tab="photos" role="tab" aria-selected="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        Photos
                    </button>
                    <?php endif; ?>
                </div>

                <!-- About Tab -->
                <div class="vendor-tab-content-premium active" data-tab="about">
                    <p class="vendor-about-premium">
                        <?= nl2br(e($vendor['description'] ?? '')) ?>
                    </p>

                    <div class="vendor-details-organic">
                        <?php if (!empty($vendor['price_range'])): ?>
                        <div class="vendor-detail-organic">
                            <div class="vendor-detail-icon-organic">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"/>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </div>
                            <div>
                                <div class="vendor-detail-label-organic">Price Range</div>
                                <div class="vendor-detail-value-organic">
                                    <?= e($vendor['price_range']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($vendor['operating_location']) || !empty($vendor['business_address'])): ?>
                        <div class="vendor-detail-organic">
                            <div class="vendor-detail-icon-organic">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </div>
                            <div>
                                <div class="vendor-detail-label-organic">Location</div>
                                <div class="vendor-detail-value-organic">
                                    <?= e($vendor['operating_location'] ?? $vendor['business_address'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php $exp = $vendor['years_experience'] ?? $vendor['years_operation'] ?? null; ?>
                        <?php if ($exp !== null && $exp > 0): ?>
                        <div class="vendor-detail-organic">
                            <div class="vendor-detail-icon-organic">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                            <div>
                                <div class="vendor-detail-label-organic">Experience</div>
                                <div class="vendor-detail-value-organic">
                                    <?= (int)$exp ?> year<?= $exp != 1 ? 's' : '' ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="vendor-detail-organic">
                            <div class="vendor-detail-icon-organic">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                                </svg>
                            </div>
                            <div>
                                <div class="vendor-detail-label-organic">Vendor Type</div>
                                <div class="vendor-detail-value-organic">
                                    <?= ucfirst($vendor['vendor_type']) ?> Vendor
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($vendor['category_name'])): ?>
                        <div class="vendor-detail-organic">
                            <div class="vendor-detail-icon-organic">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"/>
                                    <rect x="14" y="3" width="7" height="7"/>
                                    <rect x="14" y="14" width="7" height="7"/>
                                    <rect x="3" y="14" width="7" height="7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="vendor-detail-label-organic">Category</div>
                                <div class="vendor-detail-value-organic">
                                    <?= e($vendor['category_name']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="vendor-detail-organic">
                            <div class="vendor-detail-icon-organic">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                            <div>
                                <div class="vendor-detail-label-organic">Listed Since</div>
                                <div class="vendor-detail-value-organic">
                                    <?= date('M Y', strtotime($vendor['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disclaimer -->
                    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 16px; padding: 2rem; margin-top: 3rem;">
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="flex-shrink: 0; margin-top: 0.25rem;">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <div>
                                <strong style="color: #92400e;">Disclaimer:</strong>
                                <span style="color: rgba(146, 64, 14, 0.9); line-height: 1.6;">
                                    CampusLink is a directory platform only. All transactions occur directly between you and this vendor.
                                    We are not responsible for service quality, pricing accuracy, or outcomes of any interaction.
                                    <a href="<?= SITE_URL ?>/how-it-works" style="color: #d97706; font-weight: 700;">Learn more →</a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div class="vendor-tab-content-premium" data-tab="reviews">
                    <!-- Reviews content here -->
                    <div style="color: rgba(255, 255, 255, 0.9); text-align: center; padding: 3rem;">
                        Reviews functionality coming soon...
                    </div>
                </div>

                <!-- Photos Tab -->
                <?php if (!empty($vendor['service_photo'])): ?>
                <div class="vendor-tab-content-premium" data-tab="photos">
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 2rem; text-align: center;">
                        <img src="<?= SITE_URL ?>/assets/uploads/service-photos/<?= e($vendor['service_photo']) ?>"
                             alt="<?= e($vendor['business_name']) ?> service photo"
                             style="max-width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);">
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="vendor-sidebar-premium">
                <div class="vendor-contact-glass">
                    <h3 class="vendor-contact-title">Contact Vendor</h3>

                    <div class="vendor-contact-buttons">
                        <?php if (!empty($vendor['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="vendor-contact-btn vendor-whatsapp-btn"
                           onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. All transactions are between you and the vendor.')">
                            <span>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                </svg>
                            </span>
                            <span>
                                <div style="font-weight: 800;">Chat on WhatsApp</div>
                                <div style="font-size: 0.85rem; opacity: 0.9;"><?= e($vendor['whatsapp_number']) ?></div>
                            </span>
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($vendor['phone'])): ?>
                        <a href="tel:<?= e($vendor['phone']) ?>"
                           class="vendor-contact-btn vendor-call-btn">
                            <span>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>
                                </svg>
                            </span>
                            <span>
                                <div style="font-weight: 800;">Call Vendor</div>
                                <div style="font-size: 0.85rem; opacity: 0.9;"><?= e($vendor['phone']) ?></div>
                            </span>
                        </a>
                        <?php endif; ?>
                    </div>

                    <div class="vendor-actions-secondary">
                        <button class="vendor-action-secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                            Save Vendor
                        </button>
                        <button class="vendor-action-secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Report Vendor
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- JavaScript for Tab Switching -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.vendor-tab-premium');
    const contents = document.querySelectorAll('.vendor-tab-content-premium');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');

            // Hide all content
            contents.forEach(content => content.classList.remove('active'));
            // Show target content
            document.querySelector(`.vendor-tab-content-premium[data-tab="${targetTab}"]`).classList.add('active');
        });
    });
});
</script><?php defined('CAMPUSLINK') or die(); ?>

<?php
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
                    <!-- âœ“ Verified â†’ ShieldCheck -->
                    <span class="vendor-profile-badge verified"
                          style="display:inline-flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>', 14, 'currentColor') ?>
                        Verified Vendor
                    </span>
                    <?php if ($vendor['plan_type'] === 'featured'): ?>
                    <!-- â­ Featured â†’ Star -->
                    <span class="vendor-profile-badge featured"
                          style="display:inline-flex;align-items:center;gap:0.3rem;">
                        <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 14, 'currentColor', 'fill:currentColor;') ?>
                        Featured
                    </span>
                    <?php endif; ?>
                    <!-- ðŸŽ“/ðŸ¢ vendor type â†’ GraduationCap / Building2 -->
                    <span class="vendor-profile-badge"
                          style="display:inline-flex;align-items:center;gap:0.3rem;">
                        <?php if ($vendor['vendor_type'] === 'student'): ?>
                            <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 14, 'currentColor') ?>
                            Student
                        <?php else: ?>
                            <?= lucide_icon('<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><line x1="10" y1="6" x2="10" y2="6"/><line x1="14" y1="6" x2="14" y2="6"/><line x1="10" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="14" y2="10"/><line x1="10" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="14" y2="14"/>', 14, 'currentColor') ?>
                            Business
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Stars -->
                <div class="vendor-profile-rating">
                    <div class="vendor-profile-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i > round($avgRating) ? 'empty' : '' ?>">
                            <?= lucide_icon(
                                '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                18,
                                $i > round($avgRating) ? 'var(--divider)' : 'var(--warning-amber)',
                                $i > round($avgRating) ? 'fill:var(--divider);' : 'fill:var(--warning-amber);'
                            ) ?>
                        </span>
                        <?php endfor; ?>
                    </div>
                    <span class="vendor-profile-rating-text">
                        <?= $avgRating > 0 ? number_format($avgRating, 1) . '/5' : 'No reviews yet' ?>
                        <?= $reviewTotal > 0 ? "({$reviewTotal} review" . ($reviewTotal != 1 ? 's' : '') . ")" : '' ?>
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
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>"
                       target="_blank" rel="noopener noreferrer"
                       class="btn btn-whatsapp"
                       style="display:inline-flex;align-items:center;gap:0.4rem;"
                       onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. CampusLink is not responsible for transactions or outcomes.')">
                        <!-- MessageCircle replacing ðŸ’¬ -->
                        <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 16, 'currentColor') ?>
                        WhatsApp
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($vendor['phone'])): ?>
                    <a href="tel:<?= e($vendor['phone']) ?>" class="btn btn-call"
                       style="display:inline-flex;align-items:center;gap:0.4rem;">
                        <!-- Phone replacing ðŸ“ž -->
                        <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 16, 'currentColor') ?>
                        Call
                    </a>
                    <?php endif; ?>

                    <button class="btn btn-outline-white share-vendor-btn"
                            style="display:inline-flex;align-items:center;gap:0.4rem;"
                            data-title="<?= e($vendor['business_name']) ?> on CampusLink">
                        <!-- Link2 replacing ðŸ”— -->
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
                    <!-- Tabs -->
                    <div class="vendor-info-card">
                        <div class="vendor-info-tabs" role="tablist">
                            <!-- ðŸ“‹ About â†’ ClipboardList -->
                            <button class="vendor-tab-btn active"
                                    data-tab="about" role="tab" aria-selected="true"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon('<rect x="9" y="2" width="6" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><line x1="12" y1="11" x2="16" y2="11"/><line x1="12" y1="16" x2="16" y2="16"/><line x1="8" y1="11" x2="8.01" y2="11"/><line x1="8" y1="16" x2="8.01" y2="16"/>', 16, 'currentColor') ?>
                                About
                            </button>
                            <!-- â­ Reviews â†’ Star -->
                            <button class="vendor-tab-btn"
                                    data-tab="reviews" role="tab" aria-selected="false"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 16, 'currentColor') ?>
                                Reviews (<?= (int)$reviewTotal ?>)
                            </button>
                            <?php if (!empty($vendor['service_photo'])): ?>
                            <!-- ðŸ–¼ï¸ Photos â†’ Image -->
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
                                    <!-- ðŸ’° â†’ DollarSign -->
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Price Range</div>
                                        <div class="vendor-detail-value">
                                            <?= e($vendor['price_range']) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($vendor['operating_location']) || !empty($vendor['business_address'])): ?>
                                <div class="vendor-detail-item">
                                    <!-- ðŸ“ â†’ MapPin -->
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Location</div>
                                        <div class="vendor-detail-value">
                                            <?= e($vendor['operating_location'] ?? $vendor['business_address'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php $exp = $vendor['years_experience'] ?? $vendor['years_operation'] ?? null; ?>
                                <?php if ($exp !== null && $exp > 0): ?>
                                <div class="vendor-detail-item">
                                    <!-- ðŸ“… â†’ Calendar -->
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Experience</div>
                                        <div class="vendor-detail-value">
                                            <?= (int)$exp ?> year<?= $exp != 1 ? 's' : '' ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="vendor-detail-item">
                                    <!-- ðŸŽ“ â†’ GraduationCap -->
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Vendor Type</div>
                                        <div class="vendor-detail-value">
                                            <?= ucfirst($vendor['vendor_type']) ?> Vendor
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($vendor['category_name'])): ?>
                                <div class="vendor-detail-item">
                                    <!-- category_icon (was DB emoji) â†’ LayoutGrid -->
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Category</div>
                                        <div class="vendor-detail-value">
                                            <?= e($vendor['category_name']) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="vendor-detail-item">
                                    <!-- ðŸ“… â†’ Calendar -->
                                    <span class="vendor-detail-icon">
                                        <?= lucide_icon('<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>', 18, 'var(--primary)') ?>
                                    </span>
                                    <div>
                                        <div class="vendor-detail-label">Listed Since</div>
                                        <div class="vendor-detail-value">
                                            <?= date('M Y', strtotime($vendor['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Disclaimer -->
                            <div class="vendor-profile-disclaimer"
                                 style="display:flex;align-items:flex-start;gap:0.5rem;">
                                <!-- âš ï¸ â†’ AlertTriangle -->
                                <?= lucide_icon('<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 16, '#92400e', 'flex-shrink:0;margin-top:2px;') ?>
                                <span>
                                    <strong>Disclaimer:</strong>
                                    CampusLink is a directory platform only.
                                    All transactions occur directly between you and this vendor.
                                    We are not responsible for service quality, pricing accuracy,
                                    or outcomes of any interaction.
                                    <a href="<?= SITE_URL ?>/how-it-works"
                                       style="font-weight:700;color:#92400e;">
                                        Learn more â†’
                                    </a>
                                </span>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="vendor-tab-content" data-tab="reviews">

                            <!-- Rating Summary -->
                            <?php if ($reviewTotal > 0): ?>
                            <div class="rating-summary" style="margin-bottom:1.5rem;">
                                <div class="rating-big-number">
                                    <div class="rating-big-value">
                                        <?= number_format($avgRating, 1) ?>
                                    </div>
                                    <div class="rating-big-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span style="font-size:1.1rem;">
                                            <?= lucide_icon(
                                                '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                                20,
                                                $i <= round($avgRating) ? 'var(--warning-amber)' : 'var(--divider)',
                                                $i <= round($avgRating) ? 'fill:var(--warning-amber);' : 'fill:var(--divider);'
                                            ) ?>
                                        </span>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="rating-big-count">
                                        <?= (int)$reviewTotal ?> review<?= $reviewTotal != 1 ? 's' : '' ?>
                                    </div>
                                </div>
                                <div class="rating-bars">
                                    <?php for ($s = 5; $s >= 1; $s--):
                                        $cnt = $distribution[$s] ?? 0;
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

                            <!-- Write Review -->
                            <?php if ($currentUserReview): ?>
                            <div class="already-reviewed-notice"
                                 style="display:flex;align-items:flex-start;gap:0.6rem;">
                                <!-- âœ… â†’ CheckCircle -->
                                <?= lucide_icon('<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>', 24, 'var(--accent-green)') ?>
                                <div>
                                    <strong>You've already reviewed this vendor.</strong><br>
                                    Your review is <em><?= $currentUserReview['status'] ?></em>.
                                    <?php if ($currentUserReview['status'] === 'pending'): ?>
                                    You can edit it before it's approved.
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php elseif ($isUserLoggedIn): ?>
                            <div class="review-submit-form" style="margin-bottom:1.5rem;">
                                <!-- âœï¸ â†’ PenLine -->
                                <div class="review-submit-title"
                                     style="display:flex;align-items:center;gap:0.4rem;">
                                    <?= lucide_icon('<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>', 18, 'currentColor') ?>
                                    Write a Review
                                </div>
                                <form novalidate>
                                    <input type="hidden" name="vendor_id" value="<?= (int)$vendor['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                                    <div class="form-group">
                                        <label class="form-label">
                                            Your Rating <span class="required">*</span>
                                        </label>
                                        <div class="review-stars-input">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating"
                                                   id="star<?= $i ?>" value="<?= $i ?>">
                                            <label for="star<?= $i ?>"
                                                   title="<?= $i ?> star<?= $i != 1 ? 's' : '' ?>">â˜…</label>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="selected-rating-label"
                                             style="font-size:var(--font-size-sm);
                                                    color:var(--text-muted);margin-top:0.25rem;">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="reviewText">
                                            Your Review <span class="required">*</span>
                                        </label>
                                        <textarea id="reviewText"
                                                  name="review"
                                                  class="form-control"
                                                  placeholder="Share your honest experience (min 10 characters)..."
                                                  rows="4"
                                                  data-max-chars="500"
                                                  required></textarea>
                                        <div class="review-char-counter"></div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        Submit Review
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                            <div class="write-review-login-prompt" style="margin-bottom:1.5rem;">
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
                            <div class="empty-state" style="padding:2rem 1rem;">
                                <div class="empty-icon">
                                    <!-- â­ â†’ Star -->
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
                                                <div class="review-user-name">
                                                    <?= e($review['user_name'] ?? 'Anonymous') ?>
                                                </div>
                                                <div class="review-user-level">
                                                    <?= e($review['level'] ?? '') ?>
                                                </div>
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
                                            <span class="review-date"
                                                  data-time="<?= e($review['created_at']) ?>">
                                                <?= date('d M Y', strtotime($review['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <p class="review-text"><?= e($review['review']) ?></p>

                                    <?php if (!empty($review['vendor_reply'])): ?>
                                    <div class="review-vendor-reply">
                                        <strong>Vendor Reply:</strong>
                                        <?= e($review['vendor_reply']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($reviewPagination['total_pages'] > 1): ?>
                            <div style="margin-top:1.5rem;">
                                <?php
                                $pagination = $reviewPagination;
                                require __DIR__ . '/../partials/pagination.php';
                                ?>
                            </div>
                            <?php endif; ?>

                            <?php endif; ?>
                        </div>

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
                                <!-- âœ• â†’ X -->
                                <button class="lightbox-close" aria-label="Close">
                                    <?= lucide_icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>', 20, 'currentColor') ?>
                                </button>
                                <!-- â€¹ â†’ ChevronLeft -->
                                <button class="lightbox-prev" aria-label="Previous">
                                    <?= lucide_icon('<polyline points="15 18 9 12 15 6"/>', 24, 'currentColor') ?>
                                </button>
                                <img class="lightbox-img" src="" alt="Photo">
                                <!-- â€º â†’ ChevronRight -->
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
                            <!-- ðŸ“ž â†’ Phone -->
                            <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 16, 'currentColor') ?>
                            Contact Vendor
                        </div>
                        <div class="contact-buttons">
                            <?php if (!empty($vendor['whatsapp_number'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $vendor['whatsapp_number']) ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="contact-btn-large contact-btn-whatsapp"
                               onclick="return confirm('You are leaving CampusLink to contact this vendor on WhatsApp. All transactions are between you and the vendor.')">
                                <span class="contact-btn-icon">
                                    <!-- ðŸ’¬ â†’ MessageCircle -->
                                    <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 22, 'currentColor') ?>
                                </span>
                                <div>
                                    Chat on WhatsApp
                                    <span class="contact-btn-number">
                                        <?= e($vendor['whatsapp_number']) ?>
                                    </span>
                                </div>
                            </a>
                            <?php endif; ?>

                            <?php if (!empty($vendor['phone'])): ?>
                            <a href="tel:<?= e($vendor['phone']) ?>"
                               class="contact-btn-large contact-btn-call">
                                <span class="contact-btn-icon">
                                    <!-- ðŸ“ž â†’ Phone -->
                                    <?= lucide_icon('<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', 22, 'currentColor') ?>
                                </span>
                                <div>
                                    Call Vendor
                                    <span class="contact-btn-number">
                                        <?= e($vendor['phone']) ?>
                                    </span>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>

                        <div class="contact-safety-note"
                             style="display:flex;align-items:flex-start;gap:0.4rem;">
                            <!-- âš ï¸ â†’ AlertTriangle -->
                            <?= lucide_icon('<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 14, 'currentColor', 'flex-shrink:0;margin-top:1px;') ?>
                            Always verify before paying.
                            CampusLink is not responsible for transactions.
                        </div>
                    </div>

                    <!-- Save & Report -->
                    <div class="contact-card">
                        <div class="vendor-secondary-actions">
                            <button class="vendor-action-btn <?= $isSaved ? 'saved' : '' ?>"
                                    data-vendor-id="<?= (int)$vendor['id'] ?>"
                                    data-user-id="<?= (int)($currentUserId ?? 0) ?>"
                                    data-save="1"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <!-- â™¥/â™¡ â†’ Heart -->
                                <?= lucide_icon(
                                    '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
                                    16,
                                    $isSaved ? '#e11d48' : 'currentColor',
                                    $isSaved ? 'fill:#e11d48;' : ''
                                ) ?>
                                <?= $isSaved ? 'Saved' : 'Save' ?>
                            </button>
                            <?php if ($isUserLoggedIn): ?>
                            <button class="vendor-action-btn"
                                    data-open-modal="complaint"
                                    style="display:inline-flex;align-items:center;gap:0.4rem;">
                                <!-- ðŸš¨ â†’ AlertOctagon -->
                                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 16, 'currentColor') ?>
                                Report
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Business Quick Info -->
                    <div class="contact-card">
                        <div class="contact-card-title"
                             style="display:flex;align-items:center;gap:0.4rem;">
                            <!-- â„¹ï¸ â†’ Info -->
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
                        </div>
                    </div>

                </div><!-- /vendor-contact-sidebar -->

            </div><!-- /vendor-profile-layout -->
        </div>
    </div>

</div><!-- /vendor-profile-page -->

<!-- Complaint Modal -->
<?php if ($isUserLoggedIn): ?>
<div class="modal-overlay" id="complaintModal" role="dialog"
     aria-modal="true" aria-labelledby="complaintModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="complaintModalTitle"
                style="display:flex;align-items:center;gap:0.5rem;">
                <!-- ðŸš¨ â†’ AlertOctagon -->
                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 22, 'currentColor') ?>
                File a Complaint
            </h2>
            <!-- âœ• â†’ X -->
            <button class="modal-close" aria-label="Close modal">
                <?= lucide_icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>', 20, 'currentColor') ?>
            </button>
        </div>

        <p style="font-size:var(--font-size-sm);color:var(--text-secondary);margin-bottom:1.25rem;">
            Against: <strong><?= e($vendor['business_name']) ?></strong>
        </p>

        <form enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
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
                <div class="review-char-counter" data-counter-for="complaint_desc"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="complaint_evidence">
                    Evidence (Optional)
                </label>
                <div class="file-upload-area">
                    <input type="file"
                           id="complaint_evidence"
                           name="evidence"
                           accept="image/jpeg,image/png,application/pdf"
                           data-max-mb="2">
                    <!-- ðŸ“Ž â†’ Paperclip -->
                    <div class="file-upload-icon">
                        <?= lucide_icon('<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>', 28, 'var(--text-muted)') ?>
                    </div>
                    <div class="file-upload-text">
                        Attach screenshot or PDF (optional)
                    </div>
                    <div class="file-upload-hint">JPG, PNG or PDF Â· Max 2MB</div>
                </div>
            </div>

            <div class="disclaimer-box" style="margin-bottom:1.25rem;">
                <!-- âš ï¸ â†’ AlertTriangle -->
                <span class="disclaimer-icon">
                    <?= lucide_icon('<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 18, 'var(--warning-dark)') ?>
                </span>
                <div class="disclaimer-text">
                    False complaints are a violation of our
                    <a href="<?= SITE_URL ?>/user-terms" target="_blank">User Terms</a>
                    and may result in account suspension.
                </div>
            </div>

            <button type="submit" class="btn btn-danger btn-full"
                    style="display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;">
                <!-- ðŸš¨ â†’ AlertOctagon -->
                <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 16, '#fff') ?>
                Submit Complaint
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
