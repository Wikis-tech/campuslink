<?php defined('CAMPUSLINK') or die();

if (!function_exists('wa_number')) {
    function wa_number(string $raw): string {
        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($digits, '234') && strlen($digits) >= 13) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '234' . substr($digits, 1);
        }
        if (strlen($digits) === 10) {
            return '234' . $digits;
        }
        return $digits;
    }
}

function parseVendorServicePhotos($raw): array {
    if (empty($raw)) {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return array_values(array_filter($decoded));
    }

    return [$raw];
}

$servicePhotos = parseVendorServicePhotos($vendor['service_photo'] ?? '');
$galleryImages = array_map(function ($filename) {
    return SITE_URL . '/assets/uploads/service-photos/' . e($filename);
}, $servicePhotos);

$reviewCount = $reviewCount ?? ($reviewTotal ?? 0);
$avgRating = $avgRating ?? 0;
$reviews = $reviews ?? [];
$ratingBreakdown = $dist ?? $ratingBreakdown ?? [];
$hasReview = isset($hasReview) ? (bool)$hasReview : !empty($userReview);

$location = '';
if (!empty($vendor['business_address'])) {
    $location = e($vendor['business_address']);
} elseif (!empty($vendor['operating_location'])) {
    $location = e($vendor['operating_location']);
}

$expertise = '';
if (!empty($vendor['years_experience'])) {
    $expertise = (int)$vendor['years_experience'] . ' year' . ((int)$vendor['years_experience'] === 1 ? '' : 's') . ' experience';
} elseif (!empty($vendor['years_operation'])) {
    $expertise = (int)$vendor['years_operation'] . ' year' . ((int)$vendor['years_operation'] === 1 ? '' : 's') . ' in business';
}

$showLocation = !empty($location);
$showExpertise = !empty($expertise);
$showGallery = count($galleryImages) > 0;
$canReview = Auth::isLoggedIn();
$reviewActionUrl = Auth::isLoggedIn()
    ? '#reviews'
    : SITE_URL . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? SITE_URL);
$vendorName = e($vendor['business_name'] ?? 'Vendor');
$heroLogo = !empty($vendor['logo'])
    ? SITE_URL . '/assets/uploads/logos/' . e($vendor['logo'])
    : 'https://via.placeholder.com/260?text=' . urlencode(substr($vendorName, 0, 2));
$ratingDisplay = $reviewCount > 0 ? number_format((float)$avgRating, 1) : '0.0';
?>

<?php if (!Auth::isLoggedIn() && !Auth::isVendorLoggedIn()): ?>
    <!-- Standard Header for non-logged in users -->
    <?php require __DIR__ . '/../partials/header.php'; ?>
<?php elseif (Auth::isLoggedIn()): ?>
    <!-- User Sidebar for logged in users -->
    <?php require __DIR__ . '/../partials/user-sidebar.php'; ?>

    <!-- Mobile Header for logged-in users -->
    <header class="mobile-header lg:hidden">
        <div class="mobile-header-content">
            <div class="mobile-header-left">
                <button class="mobile-menu-btn" aria-label="Open navigation menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
                <a href="<?= SITE_URL ?>" class="mobile-logo">
                    <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
                </a>
            </div>
            <div class="mobile-header-right">
                <!-- School Logo -->
                <?php if (defined('SCHOOL_LOGO') && SCHOOL_LOGO): ?>
                <div class="header-school-logo">
                    <img src="<?= SITE_URL ?>/assets/img/<?= e(SCHOOL_LOGO) ?>"
                         alt="<?= e(SCHOOL_NAME) ?>" title="<?= e(SCHOOL_NAME) ?>">
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Nav Overlay -->
    <div class="mobile-nav-overlay" role="presentation"></div>

    <!-- Mobile Nav Drawer -->
    <nav class="mobile-nav" aria-label="Mobile navigation">
        <button class="mobile-nav-close" aria-label="Close menu">&times;</button>

        <div class="mobile-nav-logo">
            <span class="logo-campus">Campus</span><span class="logo-link">Link</span>
        </div>

        <div class="mobile-nav-user">
            <div class="mobile-nav-avatar">
                <?= strtoupper(substr(Session::get('user_name', 'U'), 0, 1)) ?>
            </div>
            <div class="mobile-nav-user-info">
                <div class="mobile-nav-user-name"><?= e(Session::get('user_name', 'Student')) ?></div>
                <div class="mobile-nav-user-role">Student Account</div>
            </div>
        </div>

        <a href="<?= SITE_URL ?>/user/dashboard" class="mobile-nav-link">
            <i data-lucide="layout-dashboard" class="nav-icon" aria-hidden="true"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/browse" class="mobile-nav-link active">
            <i data-lucide="search" class="nav-icon" aria-hidden="true"></i> Browse Vendors
        </a>
        <a href="<?= SITE_URL ?>/user/saved-vendors" class="mobile-nav-link">
            <i data-lucide="heart" class="nav-icon" aria-hidden="true"></i> Saved Vendors
        </a>
        <a href="<?= SITE_URL ?>/user/my-reviews" class="mobile-nav-link">
            <i data-lucide="star" class="nav-icon" aria-hidden="true"></i> My Reviews
        </a>
        <a href="<?= SITE_URL ?>/user/my-complaints" class="mobile-nav-link">
            <i data-lucide="alert-circle" class="nav-icon" aria-hidden="true"></i> My Complaints
        </a>
        <a href="<?= SITE_URL ?>/user/notifications" class="mobile-nav-link">
            <i data-lucide="bell" class="nav-icon" aria-hidden="true"></i> Notifications
        </a>
        <a href="<?= SITE_URL ?>/user/profile" class="mobile-nav-link">
            <i data-lucide="user" class="nav-icon" aria-hidden="true"></i> My Profile
        </a>

        <div style="border-top:1px solid var(--divider);padding-top:1rem;margin-top:1rem;">
            <a href="<?= SITE_URL ?>/logout" class="mobile-nav-link" style="color:var(--danger);">
                <i data-lucide="log-out" class="nav-icon" aria-hidden="true"></i> Sign Out
            </a>
        </div>
    </nav>
<?php endif; ?>

<main class="bg-slate-50 min-h-screen <?= Auth::isLoggedIn() ? 'lg:ml-60' : '' ?> pt-16 lg:pt-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 lg:py-12">
        <div class="grid gap-6 lg:gap-8 lg:grid-cols-[minmax(280px,400px)_1fr] xl:grid-cols-[minmax(320px,400px)_1fr] items-start">
            <div class="rounded-[24px] sm:rounded-[32px] border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5">
                    <div class="h-20 w-20 sm:h-24 sm:w-24 overflow-hidden rounded-2xl sm:rounded-3xl bg-slate-100 shadow-sm flex-shrink-0">
                        <img src="<?= $heroLogo ?>" alt="<?= $vendorName ?> logo" class="h-full w-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 break-words leading-tight"><?= $vendorName ?></h1>
                        <div class="mt-2 flex flex-wrap gap-2 sm:gap-3 text-sm text-slate-600">
                            <?php if ($showLocation): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 whitespace-nowrap">📍 <?= $location ?></span>
                            <?php endif; ?>
                            <?php if ($showExpertise): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 whitespace-nowrap">🎓 <?= $expertise ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:mt-8 flex flex-wrap items-center gap-3 sm:gap-4">
                    <div class="rounded-2xl sm:rounded-3xl bg-slate-900 px-3 sm:px-4 py-2 text-sm font-semibold text-white flex-shrink-0"><?= $ratingDisplay ?> / 5</div>
                    <div class="text-sm text-slate-700 flex-shrink-0"><?= (int)$reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></div>
                </div>
            </div>
            <div class="rounded-[24px] sm:rounded-[32px] border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500 mb-4 sm:mb-6">Profile summary</div>
                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                    <?php if (!empty($vendor['category_name'])): ?>
                    <div class="rounded-2xl sm:rounded-3xl bg-slate-50 p-4">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Category</div>
                        <div class="mt-2 font-semibold text-slate-900 break-words"><?= e($vendor['category_name']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($vendor['price_range'])): ?>
                    <div class="rounded-2xl sm:rounded-3xl bg-slate-50 p-4">
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Price range</div>
                        <div class="mt-2 font-semibold text-slate-900 break-words"><?= e($vendor['price_range']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mt-6 sm:mt-8">
                    <a href="<?= $reviewActionUrl ?>"
                       class="inline-flex items-center justify-center rounded-full bg-primary px-4 sm:px-5 py-2.5 sm:py-3 text-sm font-semibold text-on-primary transition hover:opacity-95 w-full sm:w-auto">
                        <?= Auth::isLoggedIn() ? 'Write a Review' : 'Sign in to Review' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-10 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-12">
            <section class="rounded-[24px] sm:rounded-[32px] border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl sm:text-2xl font-semibold text-slate-900 flex items-center gap-3">
                    <i data-lucide="info" class="w-6 h-6 text-slate-600"></i>
                    About this service
                </h2>
                <p class="mt-4 leading-relaxed text-slate-700 text-sm sm:text-base">
                    <?= nl2br(e($vendor['description'] ?? 'This vendor has not added a description yet.')) ?>
                </p>
            </section>

            <?php if ($showGallery): ?>
            <section class="rounded-[24px] sm:rounded-[32px] border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <h2 class="text-xl sm:text-2xl font-semibold text-slate-900 flex items-center gap-3">
                        <i data-lucide="image" class="w-6 h-6 text-slate-600"></i>
                        Service showcase
                    </h2>
                </div>
                <div class="mt-6 grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($galleryImages as $index => $imageSrc): ?>
                    <div class="overflow-hidden rounded-[20px] sm:rounded-[28px] bg-slate-100 shadow-sm">
                        <img src="<?= $imageSrc ?>" alt="Service photo <?= $index + 1 ?>" class="h-48 sm:h-64 w-full object-cover transition duration-300 hover:scale-105" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <section id="reviews" class="rounded-[24px] sm:rounded-[32px] border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900 flex items-center gap-3">
                            <i data-lucide="star" class="w-6 h-6 text-slate-600"></i>
                            Client reviews
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">Read what others say about this vendor.</p>
                    </div>
                </div>

                <?php if (Auth::isLoggedIn()): ?>
                <!-- Review Form for Logged-In Users -->
                <div class="mt-6 sm:mt-8 rounded-[20px] sm:rounded-[28px] border border-slate-200 bg-slate-50 p-4 sm:p-6">
                    <h3 class="font-semibold text-slate-900 text-base sm:text-lg">Share your experience</h3>
                    <form id="reviewForm" class="mt-4 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <input type="hidden" name="vendor_id" value="<?= (int)$vendor['id'] ?>">
                        
                        <!-- Star Rating -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">Rating</label>
                            <div class="review-stars-input" id="ratingStars" role="radiogroup" aria-label="Star rating">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="reviewStar<?= $i ?>" name="rating" value="<?= $i ?>">
                                <label for="reviewStar<?= $i ?>" aria-label="Rate <?= $i ?> star<?= $i !== 1 ? 's' : '' ?>">★</label>
                                <?php endfor; ?>
                            </div>
                            <p id="ratingValue" class="mt-2 text-sm text-slate-600">No rating selected.</p>
                            <p class="mt-2 text-xs text-slate-500">Click a star to set your rating before submitting your review.</p>
                        </div>

                        <!-- Review Text -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-2">Your review</label>
                            <textarea name="review" id="reviewText" class="w-full rounded-[12px] sm:rounded-[16px] border border-slate-200 px-3 sm:px-4 py-3 text-sm focus:outline-none focus:border-primary resize-none" rows="4" placeholder="Share your experience..." required></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full rounded-full bg-primary px-4 sm:px-5 py-2.5 sm:py-3 text-sm font-semibold text-on-primary transition hover:opacity-95">Submit Review</button>
                    </form>
                    <div id="reviewMessage" class="mt-4 hidden text-sm font-semibold px-3 sm:px-4 py-3 rounded-[12px] sm:rounded-[16px]"></div>
                </div>
                <?php elseif (!empty($reviews)): ?>
                <!-- Show Reviews List if not logged in -->
                <div class="mt-6 sm:mt-8 space-y-6">
                    <?php foreach ($reviews as $review): ?>
                    <article class="rounded-[20px] sm:rounded-[28px] border border-slate-200 bg-slate-50 p-4 sm:p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900 text-sm sm:text-base"><?= e($review['user_name'] ?? 'Anonymous') ?></p>
                                <p class="mt-1 text-sm text-slate-500"><?= date('M d, Y', strtotime($review['created_at'])) ?></p>
                            </div>
                            <div class="flex items-center gap-1 text-amber-500">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="text-base sm:text-lg <?= $i <= $review['rating'] ? '' : 'opacity-30' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="mt-4 text-slate-700 leading-relaxed text-sm sm:text-base">"<?= e($review['review']) ?>"</p>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="mt-6 sm:mt-8 rounded-[20px] sm:rounded-[28px] border border-dashed border-slate-200 bg-slate-50 p-6 sm:p-8 text-center text-slate-600">
                    <div class="text-base sm:text-lg font-semibold text-slate-900">No reviews yet</div>
                    <p class="mt-2 text-sm sm:text-base">Be the first to share your experience with this vendor.</p>
                    <p class="mt-4 text-sm text-slate-500"><a href="<?= SITE_URL ?>/login" class="text-primary font-semibold hover:underline">Login</a> to write a review</p>
                </div>
                <?php endif; ?>

                <?php if (!empty($reviews)): ?>
                <!-- Reviews List -->
                <div class="mt-6 sm:mt-8 space-y-4 sm:space-y-6">
                    <?php foreach ($reviews as $review): ?>
                    <article class="rounded-[20px] sm:rounded-[28px] border border-slate-200 bg-slate-50 p-4 sm:p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900 text-sm sm:text-base"><?= e($review['user_name'] ?? 'Anonymous') ?></p>
                                <p class="mt-1 text-sm text-slate-500"><?= date('M d, Y', strtotime($review['created_at'])) ?></p>
                            </div>
                            <div class="flex items-center gap-1 text-amber-500">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="text-base sm:text-lg <?= $i <= $review['rating'] ? '' : 'opacity-30' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="mt-4 text-slate-700 leading-relaxed text-sm sm:text-base">"<?= e($review['review']) ?>"</p>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>

        <aside class="space-y-8">
            <!-- Complaints Section -->
            <section class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-slate-600"></i>
                    Report an issue
                </h3>
                <p class="mt-2 text-sm text-slate-600">If you experienced a problem, let us know.</p>
                <?php if (Auth::isLoggedIn()): ?>
                <button onclick="openComplaintForm()" class="mt-4 w-full rounded-full bg-red-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-600">File a Complaint</button>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/login" class="mt-4 block w-full rounded-full bg-red-500 px-5 py-3 text-sm font-semibold text-white text-center transition hover:bg-red-600">Login to File Complaint</a>
                <?php endif; ?>
            </section>

            <section class="rounded-[32px] border border-slate-200 bg-white p-8 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-3">
                    <i data-lucide="phone" class="w-5 h-5 text-slate-600"></i>
                    Contact vendor
                </h3>
                <div class="mt-6 space-y-4">
                    <?php if (!empty($vendor['phone'])): ?>
                    <a href="tel:<?= e($vendor['phone']) ?>" class="block rounded-[24px] bg-slate-900 px-5 py-4 text-center text-sm font-semibold text-white transition hover:bg-slate-800">Call Vendor</a>
                    <?php endif; ?>
                    <?php if (!empty($vendor['whatsapp_number'])): ?>
                    <a href="https://wa.me/<?= wa_number($vendor['whatsapp_number']) ?>?text=Hi%2C+I+found+you+on+CampusLink"
                       target="_blank" rel="noreferrer noopener"
                       class="block rounded-[24px] bg-emerald-500 px-5 py-4 text-center text-sm font-semibold text-white transition hover:bg-emerald-600">
                        Chat on WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php 
                        $isSaved = false;
                        if (Auth::isLoggedIn() && !empty($vendor['id'])) {
                            require_once __DIR__ . '/../../models/SavedVendorModel.php';
                            $savedModel = new SavedVendorModel();
                            $isSaved = $savedModel->isSaved((int)Auth::userId(), (int)$vendor['id']);
                        }
                    ?>
                    <?php if (Auth::isLoggedIn()): ?>
                    <button class="browse-save-btn vendor-profile-save-btn <?= $isSaved ? 'saved' : '' ?>"
                            data-vendor-id="<?= (int)$vendor['id'] ?>"
                            data-user-id="<?= (int)Auth::userId() ?>"
                            style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; border-radius: 24px; font-size: 14px; font-weight: 600; border: 1px solid <?= $isSaved ? '#fbcfe8' : '#e2e8f0' ?>; background: <?= $isSaved ? '#fce7f3' : '#f1f5f9' ?>; color: <?= $isSaved ? '#e11d48' : '#64748b' ?>; cursor: pointer; transition: all 0.3s ease; font-family: inherit;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $isSaved ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" xmlns="http://www.w3.org/2000/svg" style="transition: fill 0.3s ease;">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                        <span><?= $isSaved ? 'Saved' : 'Save Vendor' ?></span>
                    </button>
                    <?php else: ?>
                    <a href="<?= SITE_URL ?>/login?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? SITE_URL) ?>"
                       class="block rounded-[24px] bg-slate-100 px-5 py-4 text-center text-sm font-semibold text-slate-900 transition hover:bg-slate-200">
                        Login to Save
                    </a>
                    <?php endif; ?>
                </div>
                <p class="mt-6 text-sm text-slate-500">CampusLink does not handle payments for this vendor. Please keep records of your conversations and agreements.</p>
            </section>
        </aside>
    </div>

<?php if (!Auth::isLoggedIn() && !Auth::isVendorLoggedIn()): ?>
    <!-- Standard Footer for non-logged in users -->
    <?php require __DIR__ . '/../partials/footer.php'; ?>
<?php endif; ?>

<div class="h-16 md:hidden"></div>
</main>

<!-- Complaint Modal -->
<div id="complaintModal" class="hidden fixed inset-0 bg-black/50 z-[999] flex items-center justify-center p-4">
    <div class="bg-white rounded-[32px] shadow-lg max-w-md w-full">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h2 class="text-xl font-semibold text-slate-900">File a Complaint</h2>
            <button onclick="closeComplaintForm()" class="text-slate-500 hover:text-slate-900">✕</button>
        </div>
        <form id="complaintForm" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
            <input type="hidden" name="vendor_id" value="<?= (int)$vendor['id'] ?>">
            
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Issue Type</label>
                <select name="complaint_type" class="w-full rounded-[16px] border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:border-primary" required>
                    <option value="">Select an issue</option>
                    <option value="poor_service">Poor Service Quality</option>
                    <option value="overcharge">Overcharged</option>
                    <option value="incomplete">Incomplete Work</option>
                    <option value="fraud">Suspected Fraud</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Description</label>
                <textarea name="description" class="w-full rounded-[16px] border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:border-primary resize-none" rows="4" placeholder="Describe what happened..." required></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeComplaintForm()" class="flex-1 rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-300">Cancel</button>
                <button type="submit" class="flex-1 rounded-full bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">Submit</button>
            </div>
        </form>
        <div id="complaintMessage" class="hidden px-6 pb-6 text-sm font-semibold px-4 py-3 rounded-[16px]"></div>
    </div>
</div>

<script>
// Review functionality
document.addEventListener('DOMContentLoaded', function() {
    const starRateContainer = document.querySelector('#ratingStars');
    const ratingInputs = starRateContainer ? starRateContainer.querySelectorAll('input[name="rating"]') : [];
    const ratingValueText = document.getElementById('ratingValue');

    const updateRatingValue = rating => {
        const numericRating = Number(rating) || 0;
        if (ratingValueText) {
            ratingValueText.textContent = numericRating > 0 ? `${numericRating} of 5 stars selected` : 'No rating selected.';
        }
    };

    if (ratingInputs.length) {
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                updateRatingValue(this.value);
            });
        });

        const checked = Array.from(ratingInputs).find(input => input.checked);
        updateRatingValue(checked ? checked.value : 0);
    }

    const getCsrfToken = form => {
        return form?.querySelector('input[name="csrf_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.content
            || '';
    };

    const submitJsonForm = async (url, payload) => {
        const headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        };

        if (payload.csrf_token) {
            headers['X-CSRF-TOKEN'] = payload.csrf_token;
        }

        const response = await fetch(url, {
            method: 'POST',
            headers,
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        });

        let data = null;
        try {
            data = await response.json();
        } catch (err) {
            throw new Error('Invalid JSON response from server.');
        }

        if (!response.ok) {
            throw new Error(data?.message || `Request failed with status ${response.status}`);
        }

        return data;
    };

    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const rating = Number(reviewForm.querySelector('input[name="rating"]:checked')?.value || 0);
            const review = reviewForm.querySelector('textarea[name="review"]')?.value || '';

            if (!rating) {
                showReviewMessage('Please select a star rating.', false);
                return;
            }

            if (!review.trim()) {
                showReviewMessage('Please enter a review.', false);
                return;
            }

            if (review.trim().length < 10) {
                showReviewMessage('Review must be at least 10 characters.', false);
                return;
            }

            const csrfToken = reviewForm.querySelector('input[name="csrf_token"]')?.value || CampusLink.getCsrf();
            const vendorId = reviewForm.querySelector('input[name="vendor_id"]')?.value || '';
            const submitBtn = reviewForm.querySelector('[type="submit"]');

            if (!csrfToken) {
                showReviewMessage('Session expired. Please refresh and try again.', false);
                return;
            }

            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
            }

            try {
                const data = await CampusLink.ajax('/reviews/submit', 'POST', {
                    csrf_token: csrfToken,
                    vendor_id: vendorId,
                    rating: rating,
                    review,
                });

                if (!data.success && data.status !== 'success') {
                    throw new Error(data.message || 'Request failed.');
                }

                showReviewMessage(data.message || 'Review submitted successfully!', true);
                reviewForm.reset();
                setRating(0);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
                    submitBtn.classList.remove('btn-loading');
                }
            } catch (error) {
                console.error('Review submit error:', error);
                showReviewMessage(error.message || 'Error submitting review', false);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-loading');
                }
            }
        });
    }

    const complaintForm = document.getElementById('complaintForm');
    if (complaintForm) {
        complaintForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const complaintType = complaintForm.querySelector('select[name="complaint_type"]')?.value || '';
            const description = complaintForm.querySelector('textarea[name="description"]')?.value || '';
            const csrfToken = CampusLink.getCsrf();
            const vendorId = complaintForm.querySelector('input[name="vendor_id"]')?.value || '';

            if (!complaintType) {
                showComplaintMessage('Please select an issue type', false);
                return;
            }

            if (description.trim().length < 10) {
                showComplaintMessage('Description must be at least 10 characters', false);
                return;
            }

            if (!csrfToken) {
                showComplaintMessage('Session expired. Please refresh the page and try again.', false);
                return;
            }

            try {
                const data = await CampusLink.ajax('/complaints/submit', 'POST', {
                    csrf_token: csrfToken,
                    vendor_id: vendorId,
                    complaint_type: complaintType,
                    description,
                });

                if (!data.success && data.status !== 'success') {
                    throw new Error(data.message || 'Request failed.');
                }

                showComplaintMessage('Complaint submitted successfully!', true);
                complaintForm.reset();
                setTimeout(closeComplaintForm, 1500);
            } catch (error) {
                console.error('Complaint submit error:', error);
                showComplaintMessage(error.message || 'Failed to submit complaint', false);
            }
        });
    }
});

function showReviewMessage(msg, success) {
    const msgDiv = document.getElementById('reviewMessage');
    if (!msgDiv) return;
    msgDiv.textContent = msg;
    msgDiv.className = 'mt-4 text-sm font-semibold px-4 py-3 rounded-[16px]';
    msgDiv.classList.add(success ? 'bg-green-100' : 'bg-red-100', success ? 'text-green-800' : 'text-red-800');
    msgDiv.classList.remove('hidden');
}

function openComplaintForm() {
    document.getElementById('complaintModal')?.classList.remove('hidden');
}

function closeComplaintForm() {
    document.getElementById('complaintModal')?.classList.add('hidden');
    document.getElementById('complaintForm')?.reset();
    document.getElementById('complaintMessage')?.classList.add('hidden');
}

function showComplaintMessage(msg, success) {
    const msgDiv = document.getElementById('complaintMessage');
    if (!msgDiv) return;
    msgDiv.textContent = msg;
    msgDiv.className = 'text-sm font-semibold px-4 py-3 rounded-[16px]';
    msgDiv.classList.add(success ? 'bg-green-100' : 'bg-red-100', success ? 'text-green-800' : 'text-red-800');
    msgDiv.classList.remove('hidden');
}
</script>
