<?php defined('CAMPUSLINK') or die();
$pageTitle = 'My Reviews';

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
.rev-head{margin-bottom:1.25rem;}
.rev-head h1{font-size:1.1rem;font-weight:900;color:#1e293b;margin:0 0 0.25rem;
             display:flex;align-items:center;gap:0.4rem;}
.rev-head p{font-size:0.82rem;color:#64748b;margin:0;}
.rev-list{display:flex;flex-direction:column;gap:1rem;}
.rev-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;}
.rev-card-top{display:flex;align-items:flex-start;justify-content:space-between;
gap:1rem;flex-wrap:wrap;margin-bottom:0.75rem;}
.rev-biz{font-size:0.95rem;font-weight:800;color:#1e293b;}
.rev-biz a{color:#1a56db;text-decoration:none;}
.rev-biz a:hover{text-decoration:underline;}
.rev-meta{display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;}
.rev-stars{display:flex;gap:1px;}
.rev-date{font-size:0.75rem;color:#94a3b8;}
.rev-status{display:inline-block;padding:2px 8px;border-radius:20px;
font-size:0.7rem;font-weight:700;}
.s-approved{background:#dcfce7;color:#166534;}
.s-pending{background:#fef3c7;color:#92400e;}
.s-rejected{background:#fee2e2;color:#dc2626;}
.rev-comment{font-size:0.85rem;color:#374151;line-height:1.7;
background:#f8fafc;border-radius:9px;padding:0.85rem;margin-top:0.5rem;}
.empty-state{text-align:center;padding:4rem 1rem;background:#fff;
border-radius:14px;border:1px solid #e2e8f0;color:#94a3b8;}
.empty-state .ei{margin-bottom:1rem;display:flex;justify-content:center;}
.empty-state h3{color:#475569;font-size:1rem;margin-bottom:0.5rem;}
.empty-state a{color:#1a56db;font-weight:700;text-decoration:none;}
</style>

<div class="rev-head">
    <!-- â­ â†’ Star -->
    <h1>
        <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 22, '#f59e0b', 'fill:#fde68a;stroke:#f59e0b;') ?>
        My Reviews
    </h1>
    <p>Reviews you have submitted for campus vendors</p>
</div>

<?php if (!empty($reviews)): ?>
<div class="rev-list">
    <?php foreach ($reviews as $rev): ?>
    <div class="rev-card">
        <div class="rev-card-top">
            <div>
                <div class="rev-biz">
                    <a href="<?= SITE_URL ?>/browse/<?= e($rev['slug'] ?? '') ?>">
                        <?= e($rev['business_name']) ?>
                    </a>
                </div>
                <div class="rev-date" style="margin-top:0.2rem;">
                    <?= date('d M Y', strtotime($rev['created_at'])) ?>
                </div>
            </div>
            <div class="rev-meta">
                <div class="rev-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= lucide_icon(
                            '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                            16,
                            $i <= (int)$rev['rating'] ? '#f59e0b' : '#e2e8f0',
                            $i <= (int)$rev['rating'] ? 'fill:#f59e0b;' : 'fill:#e2e8f0;'
                        ) ?>
                    <?php endfor; ?>
                </div>
                <span class="rev-status s-<?= e($rev['status'] ?? 'pending') ?>">
                    <?= ucfirst(e($rev['status'] ?? 'pending')) ?>
                </span>
            </div>
        </div>
        <?php if (!empty($rev['comment'])): ?>
        <div class="rev-comment">"<?= e($rev['comment']) ?>"</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="ei">
        <?= lucide_icon('<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>', 48, '#f59e0b', 'fill:#fde68a;stroke:#f59e0b;') ?>
    </div>
    <h3>No reviews yet</h3>
    <p>After using a vendor's service, visit their profile to leave a review.</p>
    <a href="<?= SITE_URL ?>/browse">Find vendors to review â†’</a>
</div>
<?php endif; ?>
