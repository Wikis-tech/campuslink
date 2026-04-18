<?php defined('CAMPUSLINK') or die();
$pageTitle = 'Saved Vendors';

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
.sv-page-head{display:flex;align-items:center;justify-content:space-between;
flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;}
.sv-page-head h1{font-size:1.1rem;font-weight:900;color:#1e293b;margin:0;
                 display:flex;align-items:center;gap:0.4rem;}
.sv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;}
@media(max-width:480px){.sv-grid{grid-template-columns:1fr;}}
.svc{background:#fff;border:1px solid #e2e8f0;border-radius:14px;
overflow:hidden;transition:transform 0.2s,box-shadow 0.2s;}
.svc:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.08);}
.svc-top{padding:1rem;display:flex;gap:0.85rem;align-items:flex-start;}
.svc-logo{width:48px;height:48px;border-radius:10px;
background:linear-gradient(135deg,#1a56db,#0e9f6e);
display:flex;align-items:center;justify-content:center;
color:#fff;font-weight:900;font-size:1rem;flex-shrink:0;overflow:hidden;}
.svc-logo img{width:100%;height:100%;object-fit:cover;border-radius:10px;}
.svc-name{font-size:0.9rem;font-weight:800;color:#1e293b;margin-bottom:0.15rem;}
.svc-cat{font-size:0.72rem;color:#64748b;margin-bottom:0.3rem;}
.svc-stars{display:flex;gap:1px;align-items:center;}
.svc-stars span{color:#64748b;font-size:0.7rem;margin-left:3px;}
.svc-desc{padding:0 1rem 0.75rem;font-size:0.78rem;color:#64748b;line-height:1.5;}
.svc-actions{display:grid;grid-template-columns:1fr 1fr;border-top:1px solid #f1f5f9;}
.svc-actions a{padding:0.6rem;text-align:center;font-size:0.78rem;
font-weight:700;text-decoration:none;transition:background 0.15s;
display:flex;align-items:center;justify-content:center;gap:0.3rem;}
.svc-wa{background:#f0fdf4;color:#166534;border-right:1px solid #f1f5f9;}
.svc-wa:hover{background:#dcfce7;}
.svc-view{background:#eff6ff;color:#1a56db;}
.svc-view:hover{background:#dbeafe;}
.svc-unsave{grid-column:1/-1;background:#fef2f2;color:#dc2626;
border-top:1px solid #f1f5f9;font-size:0.75rem;}
.svc-unsave:hover{background:#fee2e2;}
.empty-state{text-align:center;padding:4rem 1rem;color:#94a3b8;
background:#fff;border-radius:14px;border:1px solid #e2e8f0;}
.empty-state .ei{margin-bottom:1rem;display:flex;justify-content:center;}
.empty-state h3{color:#475569;font-size:1rem;margin-bottom:0.5rem;}
.empty-state p{font-size:0.85rem;margin-bottom:1.25rem;}
.empty-state a{padding:0.65rem 1.5rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);
color:#fff;border-radius:9px;font-weight:700;font-size:0.85rem;text-decoration:none;}
</style>

<div class="sv-page-head">
    <!-- â¤ï¸ â†’ Heart -->
    <h1>
        <?= lucide_icon('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', 22, '#e11d48', 'fill:#fecdd3;stroke:#e11d48;') ?>
        Saved Vendors
    </h1>
    <a href="<?= SITE_URL ?>/browse"
       style="padding:0.5rem 1rem;background:#1a56db;color:#fff;
              border-radius:9px;font-size:0.8rem;font-weight:700;text-decoration:none;
              display:inline-flex;align-items:center;gap:0.35rem;">
        <?= lucide_icon('<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>', 14, '#fff') ?>
        Find More
    </a>
</div>

<?php if (!empty($vendors)): ?>
<div class="sv-grid">
    <?php foreach ($vendors as $v): ?>
    <div class="svc" id="vendor-<?= (int)$v['id'] ?>">
        <div class="svc-top">
            <div class="svc-logo">
                <?php if (!empty($v['logo'])): ?>
                <img src="<?= SITE_URL ?>/assets/uploads/logos/<?= e($v['logo']) ?>"
                     alt="" onerror="this.parentElement.innerHTML='<?= strtoupper(substr($v['business_name'],0,2)) ?>'">
                <?php else: ?>
                    <?= strtoupper(substr($v['business_name'],0,2)) ?>
                <?php endif; ?>
            </div>
            <div>
                <div class="svc-name"><?= e($v['business_name']) ?></div>
                <div class="svc-cat"><?= e($v['category_name'] ?? '') ?></div>
                <div class="svc-stars">
                    <?php $r = round(($v['avg_rating']??0)*2)/2;
                    for($i=1;$i<=5;$i++): ?>
                        <?= lucide_icon(
                            '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                            13,
                            $i <= $r ? '#f59e0b' : '#e2e8f0',
                            $i <= $r ? 'fill:#f59e0b;' : 'fill:#e2e8f0;'
                        ) ?>
                    <?php endfor; ?>
                    <span><?= number_format($v['avg_rating']??0,1) ?> (<?= (int)$v['review_count'] ?>)</span>
                </div>
            </div>
        </div>
        <?php if (!empty($v['description'])): ?>
        <div class="svc-desc">
            <?= e(substr($v['description'],0,80)) ?><?= strlen($v['description'])>80?'â€¦':'' ?>
        </div>
        <?php endif; ?>
        <div class="svc-actions">
            <?php if (!empty($v['whatsapp_number'])): ?>
            <!-- ðŸ’¬ â†’ MessageCircle -->
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$v['whatsapp_number']) ?>"
               target="_blank" class="svc-wa">
                <?= lucide_icon('<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>', 14, '#166534') ?>
                WhatsApp
            </a>
            <?php endif; ?>
            <!-- ðŸ‘ï¸ â†’ Eye -->
            <a href="<?= SITE_URL ?>/browse/<?= e($v['slug']??'') ?>" class="svc-view">
                <?= lucide_icon('<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>', 14, '#1a56db') ?>
                View
            </a>
            <!-- ðŸ—‘ï¸ â†’ Trash2 -->
            <a href="#" class="svc-unsave"
               onclick="unsave(<?= (int)$v['id'] ?>,this);return false;"
               style="display:flex;align-items:center;justify-content:center;gap:0.35rem;">
                <?= lucide_icon('<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>', 14, '#dc2626') ?>
                Remove from Saved
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="ei">
        <?= lucide_icon('<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>', 48, '#e11d48', 'fill:#fecdd3;stroke:#e11d48;') ?>
    </div>
    <h3>No saved vendors yet</h3>
    <p>Browse the directory and tap the save button on vendors you like.</p>
    <a href="<?= SITE_URL ?>/browse">Browse Vendors</a>
</div>
<?php endif; ?>

<script>
function unsave(vendorId, btn) {
    if (!confirm('Remove this vendor from your saved list?')) return;
    fetch('<?= SITE_URL ?>/saved-vendors/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= CSRF::token() ?>'
        },
        body: JSON.stringify({ vendor_id: vendorId })
    })
    .then(r => r.json())
    .then(() => {
        var card = document.getElementById('vendor-' + vendorId);
        card.style.transition = 'opacity 0.3s';
        card.style.opacity = '0';
        setTimeout(() => card.remove(), 300);
    })
    .catch(() => alert('Failed to remove. Please try again.'));
}
</script>
