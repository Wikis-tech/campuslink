<?php defined('CAMPUSLINK') or die();
$pageTitle = 'My Complaints';

function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
?>
<style>
.comp-head{margin-bottom:1.25rem;}
.comp-head h1{font-size:1.1rem;font-weight:900;color:#1e293b;margin:0 0 0.25rem;
              display:flex;align-items:center;gap:0.45rem;}
.comp-head p{font-size:0.82rem;color:#64748b;margin:0;}
.comp-list{display:flex;flex-direction:column;gap:1rem;}
.comp-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;}
.comp-card-top{display:flex;align-items:flex-start;justify-content:space-between;
gap:1rem;flex-wrap:wrap;margin-bottom:0.75rem;}
.comp-title{font-size:0.92rem;font-weight:800;color:#1e293b;}
.comp-against{font-size:0.78rem;color:#64748b;margin-top:0.2rem;}
.comp-against strong{color:#1e293b;}
.comp-status{display:inline-block;padding:3px 10px;border-radius:20px;
font-size:0.72rem;font-weight:700;}
.s-open{background:#dbeafe;color:#1d4ed8;}
.s-investigating{background:#fef3c7;color:#92400e;}
.s-resolved{background:#dcfce7;color:#166534;}
.s-closed{background:#f1f5f9;color:#64748b;}
.comp-desc{font-size:0.82rem;color:#374151;line-height:1.6;
background:#f8fafc;border-radius:9px;padding:0.85rem;margin-top:0.5rem;}
.comp-ticket{display:inline-block;margin-top:0.75rem;
font-size:0.72rem;color:#64748b;background:#f1f5f9;
padding:3px 10px;border-radius:6px;font-family:monospace;}
.empty-state{text-align:center;padding:4rem 1rem;background:#fff;
border-radius:14px;border:1px solid #e2e8f0;color:#94a3b8;}
.empty-state .ei{font-size:3rem;margin-bottom:1rem;display:flex;justify-content:center;}
.empty-state h3{color:#475569;font-size:1rem;margin-bottom:0.5rem;}
.empty-state p{font-size:0.85rem;margin-bottom:1rem;}
</style>

<div class="comp-head">
    <!-- 🚨 → AlertOctagon -->
    <h1>
        <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 22, '#1e293b') ?>
        My Complaints
    </h1>
    <p>Complaints you have filed against vendors</p>
</div>

<?php if (!empty($complaints)): ?>
<div class="comp-list">
    <?php foreach ($complaints as $c): ?>
    <div class="comp-card">
        <div class="comp-card-top">
            <div>
                <div class="comp-title">
                    <?= e($c['subject'] ?? $c['category'] ?? 'Complaint') ?>
                </div>
                <div class="comp-against">
                    Against: <strong><?= e($c['business_name'] ?? 'Unknown Vendor') ?></strong>
                </div>
                <div style="font-size:0.72rem;color:#94a3b8;margin-top:0.2rem;">
                    Filed <?= date('d M Y', strtotime($c['created_at'])) ?>
                </div>
            </div>
            <span class="comp-status s-<?= e($c['status'] ?? 'open') ?>">
                <?= ucfirst(e($c['status'] ?? 'open')) ?>
            </span>
        </div>
        <?php if (!empty($c['description'])): ?>
        <div class="comp-desc">
            <?= e(substr($c['description'], 0, 200)) ?>
            <?= strlen($c['description']) > 200 ? '…' : '' ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($c['ticket_id'])): ?>
        <div class="comp-ticket">Ticket: <?= e($c['ticket_id']) ?></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <!-- 🚨 empty state → AlertOctagon -->
    <div class="ei">
        <?= lucide_icon('<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 48, '#94a3b8') ?>
    </div>
    <h3>No complaints filed</h3>
    <p>If you have a bad experience with a vendor, you can report them from their profile page.</p>
    <a href="<?= SITE_URL ?>/browse"
       style="display:inline-block;padding:0.6rem 1.5rem;
              background:#1a56db;color:#fff;border-radius:9px;
              font-weight:700;font-size:0.85rem;text-decoration:none;">
        Browse Vendors
    </a>
</div>
<?php endif; ?>