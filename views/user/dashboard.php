<?php defined('CAMPUSLINK') or die();
$pageTitle = 'Dashboard';

function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
?>
<style>
.dash-welcome {
    background: linear-gradient(135deg, #0b3d91, #1a56db, #0e9f6e);
    border-radius: 16px;
    padding: 1.5rem;
    color: #fff;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    animation: fadeIn 0.4s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.dash-welcome h1 { font-size: 1.2rem; font-weight: 900; margin: 0 0 0.25rem;
                   display:flex;align-items:center;gap:0.45rem; }
.dash-welcome p  { font-size: 0.82rem; opacity: 0.85; margin: 0; }
.dash-welcome-btns { display: flex; gap: 0.6rem; flex-wrap: wrap; }
.dwb {
    padding: 0.5rem 1rem;
    border-radius: 9px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: opacity 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.dwb-white  { background: #fff; color: #1a56db; }
.dwb-outline {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,0.35);
}
.dwb:hover { opacity: 0.88; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.25rem;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
    animation: fadeIn 0.4s ease both;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(26,86,219,0.1);
}
.stat-card .si { margin-bottom: 0.4rem; display:flex; justify-content:center; }
.stat-card .sv {
    font-size: 1.8rem;
    font-weight: 900;
    color: #1e293b;
    line-height: 1;
    margin-bottom: 0.2rem;
}
.stat-card .sl { font-size: 0.72rem; color: #64748b; font-weight: 600; }

.dash-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 1.25rem;
}
@media (max-width: 700px) { .dash-grid { grid-template-columns: 1fr; } }

.dash-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    animation: fadeIn 0.4s ease both;
}
.dash-card-head {
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dash-card-head h3 {
    font-size: 0.88rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.dash-card-head a {
    font-size: 0.75rem;
    color: #1a56db;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.dash-card-body { padding: 1rem; }

.sv-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s;
}
.sv-item:last-child { border-bottom: none; }
.sv-item:hover { background: #f8fafc; border-radius: 8px; padding-left: 0.5rem; }
.sv-logo {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #1a56db, #0e9f6e);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 900;
    font-size: 0.85rem;
    flex-shrink: 0;
    overflow: hidden;
}
.sv-logo img {
    width: 100%; height: 100%;
    object-fit: cover;
    border-radius: 10px;
}
.sv-info { flex: 1; min-width: 0; }
.sv-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sv-cat { font-size: 0.72rem; color: #64748b; }
.sv-actions { display: flex; gap: 0.35rem; }
.sv-wa {
    padding: 4px 8px;
    background: #f0fdf4;
    color: #166534;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.sv-view {
    padding: 4px 8px;
    background: #eff6ff;
    color: #1a56db;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    text-decoration: none;
}

.rev-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.rev-item:last-child { border-bottom: none; }
.rev-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}
.rev-biz { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
.rev-stars { display:flex; gap:1px; }
.rev-date { font-size: 0.7rem; color: #94a3b8; }
.rev-text { font-size: 0.78rem; color: #64748b; line-height: 1.5; }

.ql-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}
.ql-item {
    padding: 0.65rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    text-align: center;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    color: #374151;
    transition: all 0.15s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
}
.ql-item .qi { display:flex; justify-content:center; }
.ql-item:hover {
    border-color: #1a56db;
    color: #1a56db;
    background: #eff6ff;
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #94a3b8;
}
.empty-state .ei { margin-bottom: 0.5rem; display:flex; justify-content:center; }
.empty-state p { font-size: 0.82rem; margin-bottom: 0.75rem; }
.empty-state a {
    font-size: 0.8rem;
    color: #1a56db;
    font-weight: 700;
    text-decoration: none;
}
</style>

<!-- Welcome banner -->
<div class="dash-welcome">
    <div>
        <!-- 👋 → Hand (wave) -->
        <h1>
            <?= lucide_icon('<path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>', 22, '#fff') ?>
            Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?>!
        </h1>
        <p><?= e(SCHOOL_NAME) ?> · Student Account</p>
    </div>
    <div class="dash-welcome-btns">
        <!-- 🔍 → Search -->
        <a href="<?= SITE_URL ?>/browse" class="dwb dwb-white">
            <?= lucide_icon('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>', 14, '#1a56db') ?>
            Browse Vendors
        </a>
        <!-- ✏️ → PenLine -->
        <a href="<?= SITE_URL ?>/user/profile" class="dwb dwb-outline">
            <?= lucide_icon('<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>', 14, '#fff') ?>
            Edit Profile
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card" style="animation-delay:0.05s;">
        <!-- ❤️ → Heart -->
        <div class="si">
            <?= lucide_icon('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', 28, '#e11d48', 'fill:#fecdd3;stroke:#e11d48;') ?>
        </div>
        <div class="sv"><?= $savedCount ?></div>
        <div class="sl">Saved Vendors</div>
    </div>
    <div class="stat-card" style="animation-delay:0.1s;">
        <!-- ⭐ → Star -->
        <div class="si">
            <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 28, '#f59e0b', 'fill:#fde68a;stroke:#f59e0b;') ?>
        </div>
        <div class="sv"><?= $reviewCount ?></div>
        <div class="sl">Reviews Given</div>
    </div>
    <div class="stat-card" style="animation-delay:0.15s;">
        <!-- 🚨 → AlertOctagon -->
        <div class="si">
            <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 28, '#dc2626', 'fill:#fee2e2;stroke:#dc2626;') ?>
        </div>
        <div class="sv"><?= $complaintCount ?></div>
        <div class="sl">Complaints Filed</div>
    </div>
    <div class="stat-card" style="animation-delay:0.2s;">
        <!-- 🔔 → Bell -->
        <div class="si">
            <?= lucide_icon('<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', 28, '#1a56db', 'fill:#dbeafe;stroke:#1a56db;') ?>
        </div>
        <div class="sv"><?= $unreadNotifs ?></div>
        <div class="sl">Unread Alerts</div>
    </div>
</div>

<!-- Main grid -->
<div class="dash-grid">
    <div>
        <!-- Saved vendors -->
        <div class="dash-card" style="margin-bottom:1.25rem;">
            <div class="dash-card-head">
                <h3>
                    <?= lucide_icon('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', 15, '#e11d48', 'fill:#fecdd3;stroke:#e11d48;') ?>
                    Saved Vendors
                </h3>
                <a href="<?= SITE_URL ?>/user/saved-vendors">
                    View all
                    <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 13, '#1a56db') ?>
                </a>
            </div>
            <div class="dash-card-body">
                <?php if (!empty($savedVendors)): ?>
                    <?php foreach ($savedVendors as $sv): ?>
                    <div class="sv-item">
                        <div class="sv-logo">
                            <?php if (!empty($sv['logo'])): ?>
                            <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($sv['logo']) ?>"
                                 alt="" onerror="this.parentElement.innerHTML='<?= strtoupper(substr($sv['business_name'],0,2)) ?>'">
                            <?php else: ?>
                                <?= strtoupper(substr($sv['business_name'],0,2)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="sv-info">
                            <div class="sv-name"><?= e($sv['business_name']) ?></div>
                            <div class="sv-cat"><?= e($sv['category_name'] ?? '') ?></div>
                        </div>
                        <div class="sv-actions">
                            <?php if (!empty($sv['whatsapp_number'])): ?>
                            <!-- 💬 → MessageCircle -->
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$sv['whatsapp_number']) ?>"
                               target="_blank" class="sv-wa">
                                <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 14, '#166634') ?>
                            </a>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/browse/<?= e($sv['slug'] ?? '') ?>"
                               class="sv-view">View</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state">
                    <div class="ei">
                        <?= lucide_icon('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', 32, '#e11d48', 'fill:#fecdd3;stroke:#e11d48;') ?>
                    </div>
                    <p>No saved vendors yet</p>
                    <a href="<?= SITE_URL ?>/browse">Browse vendors →</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent reviews -->
        <div class="dash-card">
            <div class="dash-card-head">
                <h3>
                    <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 15, '#f59e0b', 'fill:#fde68a;stroke:#f59e0b;') ?>
                    Recent Reviews
                </h3>
                <a href="<?= SITE_URL ?>/user/my-reviews">
                    View all
                    <?= lucide_icon('<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>', 13, '#1a56db') ?>
                </a>
            </div>
            <div class="dash-card-body">
                <?php if (!empty($recentReviews)): ?>
                    <?php foreach ($recentReviews as $rev): ?>
                    <div class="rev-item">
                        <div class="rev-top">
                            <span class="rev-biz"><?= e($rev['business_name']) ?></span>
                            <span class="rev-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= lucide_icon(
                                        '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                        12,
                                        $i <= (int)$rev['rating'] ? '#f59e0b' : '#e2e8f0',
                                        $i <= (int)$rev['rating'] ? 'fill:#f59e0b;' : 'fill:#e2e8f0;'
                                    ) ?>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <div class="rev-date"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                        <div class="rev-text">
                            <?= e(substr($rev['comment'] ?? '', 0, 80)) ?>
                            <?= strlen($rev['comment'] ?? '') > 80 ? '…' : '' ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state">
                    <div class="ei">
                        <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 32, '#f59e0b', 'fill:#fde68a;stroke:#f59e0b;') ?>
                    </div>
                    <p>No reviews yet</p>
                    <a href="<?= SITE_URL ?>/browse">Find a vendor to review →</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick actions -->
    <div>
        <div class="dash-card">
            <div class="dash-card-head">
                <h3>
                    <!-- ⚡ → Zap -->
                    <?= lucide_icon('<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>', 15, '#f59e0b', 'fill:#fde68a;stroke:#f59e0b;') ?>
                    Quick Actions
                </h3>
            </div>
            <div class="dash-card-body">
                <div class="ql-grid">
                    <a href="<?= SITE_URL ?>/browse" class="ql-item">
                        <span class="qi"><?= lucide_icon('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>', 22, 'currentColor') ?></span>
                        Browse
                    </a>
                    <a href="<?= SITE_URL ?>/user/profile" class="ql-item">
                        <span class="qi"><?= lucide_icon('<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>', 22, 'currentColor') ?></span>
                        Profile
                    </a>
                    <a href="<?= SITE_URL ?>/user/saved-vendors" class="ql-item">
                        <span class="qi"><?= lucide_icon('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', 22, 'currentColor') ?></span>
                        Saved
                    </a>
                    <a href="<?= SITE_URL ?>/user/my-reviews" class="ql-item">
                        <span class="qi"><?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 22, 'currentColor') ?></span>
                        Reviews
                    </a>
                    <a href="<?= SITE_URL ?>/user/my-complaints" class="ql-item">
                        <span class="qi"><?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 22, 'currentColor') ?></span>
                        Complaints
                    </a>
                    <a href="<?= SITE_URL ?>/user/notifications" class="ql-item">
                        <span class="qi"><?= lucide_icon('<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', 22, 'currentColor') ?></span>
                        Alerts
                    </a>
                </div>
            </div>
        </div>

        <!-- Account info -->
        <div class="dash-card" style="margin-top:1.25rem;">
            <div class="dash-card-head">
                <h3>
                    <?= lucide_icon('<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>', 15, 'currentColor') ?>
                    Account Info
                </h3>
            </div>
            <div class="dash-card-body">
                <?php
                // Icon paths for account rows
                $rowIcons = [
                    'name'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',           // User
                    'email'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>', // Mail
                    'matric' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',                   // GraduationCap
                    'phone'  => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18v-.26z"/>', // Phone
                ];
                $rows = [
                    ['name',   'Name',   $user['full_name']],
                    ['email',  'Email',  $user['personal_email'] ?? $user['school_email'] ?? ''],
                    ['matric', 'Matric', $user['matric_number'] ?? 'Not set'],
                    ['phone',  'Phone',  $user['phone'] ?? 'Not set'],
                ];
                foreach ($rows as [$iconKey, $label, $value]):
                ?>
                <div style="display:flex;gap:0.6rem;padding:0.5rem 0;
                            border-bottom:1px solid #f1f5f9;font-size:0.8rem;
                            align-items:center;">
                    <span style="width:20px;display:flex;justify-content:center;flex-shrink:0;">
                        <?= lucide_icon($rowIcons[$iconKey], 14, '#64748b') ?>
                    </span>
                    <span style="color:#64748b;width:50px;flex-shrink:0;"><?= $label ?></span>
                    <span style="color:#1e293b;font-weight:600;
                                 white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;">
                        <?= e($value) ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <!-- ✏️ → PenLine -->
                <a href="<?= SITE_URL ?>/user/profile"
                   style="display:flex;align-items:center;justify-content:center;gap:0.4rem;
                          margin-top:0.85rem;padding:0.55rem;background:#eff6ff;color:#1a56db;
                          border-radius:8px;font-weight:700;font-size:0.78rem;text-decoration:none;">
                    <?= lucide_icon('<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>', 14, '#1a56db') ?>
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>