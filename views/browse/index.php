<?php defined('CAMPUSLINK') or die(); ?>

<style>
/* ── Browse Page Styles ───────────────────────────────── */
.browse-hero {
    background: linear-gradient(135deg, #0b3d91 0%, #1a56db 50%, #0e9f6e 100%);
    padding: 2.5rem 0 2rem;
    color: #fff;
}
.browse-hero h1 {
    font-size: clamp(1.4rem, 4vw, 2rem);
    font-weight: 900;
    margin: 0 0 0.3rem;
    letter-spacing: -0.02em;
}
.browse-hero p {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
    margin: 0 0 1.25rem;
}
.browse-search-bar {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.browse-search-bar input,
.browse-search-bar select {
    flex: 1;
    min-width: 140px;
    padding: 0.65rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    background: #fff;
    color: #1e293b;
}
.browse-search-bar button {
    padding: 0.65rem 1.5rem;
    background: #f59e0b;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
.browse-search-bar button:hover { background: #d97706; }
.browse-icon,
.category-filter-item i,
.sort-pill i,
.vcard-actions i,
.vcard-price i,
.vbadge i,
.browse-empty .ei i,
.vcard-save i,
.ud-nav-link i,
.ud-logout-btn i,
.vendor-tab-btn i,
.vendor-detail-icon i,
.vendor-profile-actions i {
    width: 1rem;
    height: 1rem;
    display: inline-flex;
}
    .browse-layout {
        grid-template-columns: 1fr;
        padding: 1rem 0.75rem;
    }
    .browse-sidebar { display: none; }
    .browse-sidebar.open { display: block; }
}

/* ── Sidebar ─────────────────────────────────────────── */
.browse-sidebar {
    position: sticky;
    top: 1rem;
    height: fit-content;
}
.sidebar-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    margin-bottom: 1rem;
}
.sidebar-card-title {
    padding: 0.75rem 1rem;
    font-weight: 800;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.category-filter-list { padding: 0.5rem 0; }
.category-filter-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
    text-decoration: none;
    transition: background 0.15s;
    border-left: 3px solid transparent;
}
.category-filter-item:hover {
    background: #f0f9ff;
    color: #1a56db;
}
.category-filter-item.active {
    background: #eff6ff;
    color: #1a56db;
    font-weight: 700;
    border-left-color: #1a56db;
}
.category-filter-item .cat-count {
    background: #e2e8f0;
    color: #64748b;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
}
.category-filter-item.active .cat-count {
    background: #dbeafe;
    color: #1a56db;
}

/* ── Mobile filter toggle ─────────────────────────────── */
.mobile-filter-bar {
    display: none;
    padding: 0.75rem;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}
@media (max-width: 768px) {
    .mobile-filter-bar { display: flex; }
}
.mobile-filter-toggle {
    padding: 0.5rem 1rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    color: #374151;
}
.sort-pills {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    flex: 1;
}
.sort-pill {
    padding: 0.4rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    color: #64748b;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    transition: all 0.15s;
    white-space: nowrap;
}
.sort-pill:hover, .sort-pill.active {
    background: #1a56db;
    color: #fff;
    border-color: #1a56db;
}

/* ── Main content header ──────────────────────────────── */
.browse-main-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.browse-result-count {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
}
.browse-result-count strong { color: #1e293b; }

/* ── Vendor Grid ─────────────────────────────────────── */
.vendor-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
}
@media (max-width: 480px) {
    .vendor-cards-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Vendor Card ─────────────────────────────────────── */
.vcard {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}
.vcard:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.10);
}
.vcard-top {
    padding: 1.1rem 1rem 0.75rem;
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
}
.vcard-logo {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background: linear-gradient(135deg,#1a56db,#0e9f6e);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 900;
    font-size: 1.1rem;
    overflow: hidden;
}
.vcard-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}
.vcard-meta { flex: 1; min-width: 0; }
.vcard-name {
    font-weight: 800;
    font-size: 0.95rem;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 0.15rem;
}
.vcard-cat {
    font-size: 0.73rem;
    color: #64748b;
    margin-bottom: 0.3rem;
}
.vcard-stars {
    display: flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.75rem;
}
.vcard-stars .s { color: #f59e0b; }
.vcard-stars .se { color: #d1d5db; }
.vcard-stars .sn { color: #64748b; margin-left: 2px; }
.vcard-badges {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
    padding: 0 1rem 0.5rem;
}
.vbadge {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 20px;
}
.vbadge-verified { background: #dcfce7; color: #166534; }
.vbadge-featured { background: #fef3c7; color: #92400e; }
.vbadge-premium  { background: #ede9fe; color: #5b21b6; }
.vcard-desc {
    padding: 0 1rem 0.75rem;
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.5;
    flex: 1;
}
.vcard-price {
    padding: 0 1rem 0.65rem;
    font-size: 0.78rem;
    color: #0e9f6e;
    font-weight: 700;
}
.vcard-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-top: 1px solid #f1f5f9;
}
.vcard-actions a {
    padding: 0.6rem;
    text-align: center;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}
.vcard-actions .wa {
    background: #f0fdf4;
    color: #166534;
    border-right: 1px solid #f1f5f9;
}
.vcard-actions .wa:hover { background: #dcfce7; }
.vcard-actions .call {
    background: #eff6ff;
    color: #1a56db;
}
.vcard-actions .call:hover { background: #dbeafe; }
.vcard-actions .view-full {
    grid-column: 1 / -1;
    background: #f8fafc;
    color: #374151;
    border-top: 1px solid #f1f5f9;
    font-size: 0.78rem;
}
.vcard-actions .view-full:hover { background: #f1f5f9; }

/* ── Save button ─────────────────────────────────────── */
.vcard-save {
    position: absolute;
    top: 0.6rem;
    right: 0.6rem;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    transition: transform 0.15s;
}
.vcard-save:hover { transform: scale(1.15); }
.vcard-wrap { position: relative; }

/* ── Empty state ─────────────────────────────────────── */
.browse-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}
.browse-empty .ei { font-size: 3rem; margin-bottom: 0.75rem; }
.browse-empty h3 { color: #475569; font-size: 1.1rem; margin-bottom: 0.5rem; }
.browse-empty p  { font-size: 0.875rem; margin-bottom: 1.25rem; }

/* ── Pagination ──────────────────────────────────────── */
.browse-pagination {
    display: flex;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}
.pag-btn {
    padding: 0.5rem 0.9rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #374151;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
}
.pag-btn:hover { background: #1a56db; color: #fff; border-color: #1a56db; }
.pag-btn.active { background: #1a56db; color: #fff; border-color: #1a56db; }
.pag-btn.disabled { opacity: 0.4; pointer-events: none; }

/* ── Disclaimer box ──────────────────────────────────── */
.browse-disclaimer {
    margin-top: 2rem;
    padding: 0.875rem 1rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    font-size: 0.78rem;
    color: #92400e;
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.browse-disclaimer i {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    margin-top: 0.1rem;
}
</style>

<?php
// Build query string helper
function browseUrl(array $overrides = []): string {
    $params = array_merge([
        'q'        => $_GET['q']        ?? '',
        'category' => $_GET['category'] ?? '',
        'sort'     => $_GET['sort']     ?? 'featured',
        'page'     => $_GET['page']     ?? 1,
    ], $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return SITE_URL . '/browse?' . http_build_query($params);
}

$vendorCount = count($vendors);
?>

<!-- ── Hero / Search ─────────────────────────────────── -->
<div class="browse-hero">
    <div style="max-width:1200px;margin:0 auto;padding:0 1rem;">
        <h1><i data-lucide="search" class="browse-icon"></i> Browse Campus Vendors</h1>
        <p><?= e(SCHOOL_NAME) ?> · Verified service providers</p>
        <form method="GET" action="<?= SITE_URL ?>/browse">
            <div class="browse-search-bar">
                <input type="text" name="q"
                       placeholder="Search vendors or services..."
                       value="<?= e($search) ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"
                        <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                        (<?= (int)$cat['vendor_count'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"><i data-lucide="search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Mobile filter bar ─────────────────────────────── -->
<div class="mobile-filter-bar">
    <button class="mobile-filter-toggle" onclick="toggleSidebar()">
        <i data-lucide="sliders" class="browse-icon"></i> Filters
    </button>
    <div class="sort-pills">
        <a href="<?= browseUrl(['sort'=>'featured','page'=>1]) ?>"
           class="sort-pill <?= $sort==='featured'?'active':'' ?>">
           <i data-lucide="star" class="browse-icon"></i> Featured
        </a>
        <a href="<?= browseUrl(['sort'=>'rating','page'=>1]) ?>"
           class="sort-pill <?= $sort==='rating'?'active':'' ?>">
           <i data-lucide="trending-up" class="browse-icon"></i> Top Rated
        </a>
        <a href="<?= browseUrl(['sort'=>'newest','page'=>1]) ?>"
           class="sort-pill <?= $sort==='newest'?'active':'' ?>">
           <i data-lucide="clock" class="browse-icon"></i> Newest
        </a>
        <a href="<?= browseUrl(['sort'=>'alpha','page'=>1]) ?>"
           class="sort-pill <?= $sort==='alpha'?'active':'' ?>">
           <i data-lucide="sort-asc" class="browse-icon"></i> A-Z
        </a>
    </div>
</div>

<!-- ── Main layout ───────────────────────────────────── -->
<div class="browse-layout">

    <!-- Sidebar -->
    <aside class="browse-sidebar" id="browseSidebar">
        <div class="sidebar-card">
            <div class="sidebar-card-title">Sort By</div>
            <div class="category-filter-list">
                <a href="<?= browseUrl(['sort'=>'featured','page'=>1]) ?>"
                   class="category-filter-item <?= $sort==='featured'?'active':'' ?>">
                    <i data-lucide="star" class="browse-icon"></i> Featured First
                </a>
                <a href="<?= browseUrl(['sort'=>'rating','page'=>1]) ?>"
                   class="category-filter-item <?= $sort==='rating'?'active':'' ?>">
                    <i data-lucide="trending-up" class="browse-icon"></i> Top Rated
                </a>
                <a href="<?= browseUrl(['sort'=>'newest','page'=>1]) ?>"
                   class="category-filter-item <?= $sort==='newest'?'active':'' ?>">
                    <i data-lucide="clock" class="browse-icon"></i> Newest First
                </a>
                <a href="<?= browseUrl(['sort'=>'alpha','page'=>1]) ?>"
                   class="category-filter-item <?= $sort==='alpha'?'active':'' ?>">
                    <i data-lucide="sort-asc" class="browse-icon"></i> A to Z
                </a>
            </div>
        </div>

        <div class="sidebar-card">
            <div class="sidebar-card-title">Category</div>
            <div class="category-filter-list">
                <a href="<?= browseUrl(['category'=>'','page'=>1]) ?>"
                   class="category-filter-item <?= !$categoryId?'active':'' ?>">
                    <i data-lucide="layers" class="browse-icon"></i> All Categories
                    <span class="cat-count">
                        <?= array_sum(array_column($categories,'vendor_count')) ?>
                    </span>
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= browseUrl(['category'=>$cat['id'],'page'=>1]) ?>"
                   class="category-filter-item
                          <?= $categoryId==$cat['id']?'active':'' ?>">
                    <i data-lucide="tag" class="browse-icon"></i> <?= e($cat['name']) ?>
                    <span class="cat-count"><?= (int)$cat['vendor_count'] ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($search || $categoryId): ?>
        <a href="<?= SITE_URL ?>/browse"
           style="display:block;text-align:center;padding:0.6rem;
                  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
                  font-size:0.8rem;font-weight:700;color:#dc2626;
                  text-decoration:none;">
            <i data-lucide="x" class="browse-icon"></i> Clear All Filters
        </a>
        <?php endif; ?>
    </aside>

    <!-- Main content -->
    <main>
        <!-- Result count + desktop sort -->
        <div class="browse-main-header">
            <div class="browse-result-count">
                Showing <strong><?= $vendorCount ?></strong>
                vendor<?= $vendorCount !== 1 ? 's' : '' ?>
                <?php if ($search): ?>
                    for <strong>"<?= e($search) ?>"</strong>
                <?php endif; ?>
                <?php if ($currentCategory): ?>
                    in <strong><?= e($currentCategory['name']) ?></strong>
                <?php endif; ?>
            </div>
            <div class="sort-pills" style="display:flex;">
                <a href="<?= browseUrl(['sort'=>'featured','page'=>1]) ?>"
                   class="sort-pill <?= $sort==='featured'?'active':'' ?>">
                    <i data-lucide="star" class="browse-icon"></i> Featured
                </a>
                <a href="<?= browseUrl(['sort'=>'rating','page'=>1]) ?>"
                   class="sort-pill <?= $sort==='rating'?'active':'' ?>">
                    <i data-lucide="trending-up" class="browse-icon"></i> Top Rated
                </a>
                <a href="<?= browseUrl(['sort'=>'newest','page'=>1]) ?>"
                   class="sort-pill <?= $sort==='newest'?'active':'' ?>">
                    <i data-lucide="clock" class="browse-icon"></i> Newest
                </a>
                <a href="<?= browseUrl(['sort'=>'alpha','page'=>1]) ?>"
                   class="sort-pill <?= $sort==='alpha'?'active':'' ?>">
                    <i data-lucide="type" class="browse-icon"></i> A-Z
                </a>
            </div>
        </div>

        <!-- Vendor cards -->
        <div class="vendor-cards-grid">
            <?php if (!empty($vendors)): ?>
                <?php foreach ($vendors as $v): ?>
                <div class="vcard-wrap">
                    <div class="vcard">

                        <div class="vcard-top">
                            <div class="vcard-logo">
                                <?php if (!empty($v['logo'])): ?>
                                <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($v['logo']) ?>"
                                     alt="<?= e($v['business_name']) ?>"
                                     onerror="this.parentElement.innerHTML='<?= strtoupper(substr($v['business_name'],0,2)) ?>'">
                                <?php else: ?>
                                    <?= strtoupper(substr($v['business_name'],0,2)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="vcard-meta">
                                <div class="vcard-name"><?= e($v['business_name']) ?></div>
                                <div class="vcard-cat">
                                    <i data-lucide="tag" class="browse-icon"></i>
                                    <?= e($v['category_name'] ?? 'General') ?>
                                </div>
                                <div class="vcard-stars">
                                    <?php
                                    $r = round(($v['avg_rating'] ?? 0) * 2) / 2;
                                    for ($i=1;$i<=5;$i++):
                                        if ($i<=$r) echo '<i data-lucide="star" class="s"></i>';
                                        elseif ($i-0.5<=$r) echo '<i data-lucide="star" class="s"></i>';
                                        else echo '<i data-lucide="star" class="se"></i>';
                                    endfor;
                                    ?>
                                    <span class="sn">
                                        <?= number_format($v['avg_rating'] ?? 0,1) ?>
                                        (<?= (int)($v['review_count'] ?? 0) ?>)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="vcard-badges">
                            <span class="vbadge vbadge-verified"><i data-lucide="check-circle"></i> Verified</span>
                            <?php if ($v['plan_type'] === 'featured'): ?>
                            <span class="vbadge vbadge-featured"><i data-lucide="star"></i> Featured</span>
                            <?php elseif ($v['plan_type'] === 'premium'): ?>
                            <span class="vbadge vbadge-premium"><i data-lucide="zap"></i> Premium</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($v['description'])): ?>
                        <div class="vcard-desc">
                            <?= e(substr($v['description'], 0, 90)) ?>
                            <?= strlen($v['description']) > 90 ? '…' : '' ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($v['price_range'])): ?>
                        <div class="vcard-price"><i data-lucide="dollar-sign"></i> <?= e($v['price_range']) ?></div>
                        <?php endif; ?>

                        <div class="vcard-actions">
                            <?php if (!empty($v['whatsapp_number'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$v['whatsapp_number']) ?>?text=Hi%2C+I+found+you+on+CampusLink"
                               target="_blank" rel="noopener" class="wa">
                                <i data-lucide="message-circle"></i> WhatsApp
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($v['phone'])): ?>
                            <a href="tel:<?= preg_replace('/[^0-9+]/','',$v['phone']) ?>"
                               class="call">
                                <i data-lucide="phone"></i> Call
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($v['slug'])): ?>
                            <a href="<?= SITE_URL ?>/browse/<?= e($v['slug']) ?>"
                               class="view-full">
                                View Full Profile <i data-lucide="arrow-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>

                    </div>

                    <?php if ($userId): ?>
                    <button class="vcard-save <?= in_array($v['id'], $savedIds) ? 'saved' : '' ?>"
                            onclick="toggleSave(<?= (int)$v['id'] ?>, this)"
                            title="Save vendor">
                        <i data-lucide="heart"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

            <?php else: ?>
            <div class="browse-empty">
                <div class="ei"><i data-lucide="search"></i></div>
                <h3>No vendors found</h3>
                <p>
                    <?php if ($search || $categoryId): ?>
                        Try a different search or clear your filters.
                    <?php else: ?>
                        No vendors are listed yet. Check back soon!
                    <?php endif; ?>
                </p>
                <?php if ($search || $categoryId): ?>
                <a href="<?= SITE_URL ?>/browse"
                   style="display:inline-block;padding:0.6rem 1.5rem;
                          background:#1a56db;color:#fff;border-radius:8px;
                          font-weight:700;text-decoration:none;font-size:0.875rem;">
                    Clear Filters
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (isset($pag) && $pag['total_pages'] > 1): ?>
        <div class="browse-pagination">
            <?php if ($pag['has_prev']): ?>
            <a href="<?= browseUrl(['page'=>$pag['prev_page']]) ?>"
               class="pag-btn"><i data-lucide="arrow-left"></i> Prev</a>
            <?php else: ?>
            <span class="pag-btn disabled"><i data-lucide="arrow-left"></i> Prev</span>
            <?php endif; ?>

            <?php
            $start = max(1, $pag['current_page'] - 2);
            $end   = min($pag['total_pages'], $pag['current_page'] + 2);
            for ($p = $start; $p <= $end; $p++): ?>
            <a href="<?= browseUrl(['page'=>$p]) ?>"
               class="pag-btn <?= $p===$pag['current_page']?'active':'' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>

            <?php if ($pag['has_next']): ?>
            <a href="<?= browseUrl(['page'=>$pag['next_page']]) ?>"
               class="pag-btn">Next <i data-lucide="arrow-right"></i></a>
            <?php else: ?>
            <span class="pag-btn disabled">Next <i data-lucide="arrow-right"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Disclaimer -->
        <div class="browse-disclaimer">
            <i data-lucide="alert-triangle"></i>
            <span>
                <strong>CampusLink is a directory only.</strong>
                All transactions occur directly between you and the vendor.
                Always verify a vendor before making any payment.
                <a href="<?= SITE_URL ?>/general-terms"
                   style="color:#92400e;font-weight:700;">Learn more →</a>
            </span>
        </div>

    </main>
</div>

<script>
function toggleSidebar() {
    const s = document.getElementById('browseSidebar');
    s.classList.toggle('open');
}

function toggleSave(vendorId, btn) {
    fetch('<?= SITE_URL ?>/saved-vendors/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ vendor_id: vendorId })
    })
    .then(r => r.json())
    .then(data => {
        btn.innerHTML = '<i data-lucide="heart"></i>';
        if (window.lucide) lucide.createIcons();
    })
    .catch(() => {});
}
</script>