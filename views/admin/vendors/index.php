<?php defined('CAMPUSLINK') or die(); ?>

<style>
.av-head{display:flex;align-items:center;justify-content:space-between;
flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;}
.av-head h1{font-size:1.2rem;font-weight:900;color:#1e293b;margin:0;}
.av-filters{display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem;}
.av-filter-btn{padding:0.4rem 1rem;border-radius:20px;font-size:0.78rem;
font-weight:700;text-decoration:none;border:1.5px solid #e2e8f0;
color:#64748b;background:#fff;transition:all 0.15s;}
.av-filter-btn:hover,.av-filter-btn.active{background:#1a56db;
color:#fff;border-color:#1a56db;}
.av-filter-btn.pending.active{background:#f59e0b;border-color:#f59e0b;}
.av-filter-btn.suspended.active{background:#dc2626;border-color:#dc2626;}
.av-search{display:flex;gap:0.5rem;margin-bottom:1.25rem;}
.av-search input{flex:1;padding:0.6rem 0.9rem;border:1.5px solid #e2e8f0;
border-radius:9px;font-size:0.875rem;outline:none;}
.av-search input:focus{border-color:#1a56db;}
.av-search button{padding:0.6rem 1.25rem;background:#1a56db;color:#fff;
border:none;border-radius:9px;font-weight:700;font-size:0.82rem;cursor:pointer;}
.av-table-wrap{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;}
.av-table{width:100%;border-collapse:collapse;font-size:0.82rem;}
.av-table th{padding:0.7rem 1rem;text-align:left;font-size:0.72rem;
font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;
background:#f8fafc;border-bottom:1px solid #e2e8f0;}
.av-table td{padding:0.8rem 1rem;border-bottom:1px solid #f1f5f9;
color:#374151;vertical-align:middle;}
.av-table tr:last-child td{border-bottom:none;}
.av-table tr:hover td{background:#f8fafc;}
.av-biz{font-weight:700;color:#1e293b;}
.av-email{font-size:0.75rem;color:#94a3b8;}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;
font-size:0.68rem;font-weight:700;}
.b-active{background:#dcfce7;color:#166534;}
.b-pending{background:#fef3c7;color:#92400e;}
.b-suspended{background:#fee2e2;color:#dc2626;}
.b-inactive{background:#f1f5f9;color:#64748b;}
.av-actions{display:flex;gap:0.4rem;flex-wrap:wrap;}
.av-actions a{padding:3px 10px;border-radius:6px;font-size:0.72rem;
font-weight:700;text-decoration:none;transition:opacity 0.15s;}
.av-actions a:hover{opacity:0.8;}
.a-view{background:#eff6ff;color:#1a56db;}
.a-approve{background:#dcfce7;color:#166534;}
.a-suspend{background:#fee2e2;color:#dc2626;}
.av-empty{text-align:center;padding:3rem;color:#94a3b8;}
.av-empty .ei{font-size:2.5rem;margin-bottom:0.75rem;}
.pag{display:flex;justify-content:center;gap:0.4rem;margin-top:1.25rem;flex-wrap:wrap;}
.pag a,.pag span{padding:0.45rem 0.85rem;border-radius:8px;border:1px solid #e2e8f0;
font-size:0.82rem;font-weight:600;text-decoration:none;color:#374151;}
.pag a:hover{background:#1a56db;color:#fff;border-color:#1a56db;}
.pag .active{background:#1a56db;color:#fff;border-color:#1a56db;}
.pag .disabled{opacity:0.4;pointer-events:none;}
.av-count{font-size:0.82rem;color:#64748b;margin-bottom:0.75rem;}
</style>

<?php
$statusLabels = [
    ''          => 'All Vendors',
    'pending'   => 'Pending',
    'active'    => 'Active',
    'suspended' => 'Suspended',
    'inactive'  => 'Inactive',
];
$currentStatus = $status ?? '';
$currentSearch = $search ?? '';
?>

<div class="av-head">
    <h1>🏪 Vendors
        <?php if ($currentStatus === 'pending'): ?>
            <span style="background:#fef3c7;color:#92400e;font-size:0.75rem;
                         padding:2px 10px;border-radius:20px;font-weight:700;
                         margin-left:0.5rem;">
                Pending Approval
            </span>
        <?php endif; ?>
    </h1>
</div>

<!-- Search -->
<form method="GET" action="<?= SITE_URL ?>/admin/vendors" class="av-search">
    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
    <input type="text" name="q"
           placeholder="Search by business name or email..."
           value="<?= e($currentSearch) ?>">
    <button type="submit">🔍 Search</button>
</form>

<!-- Status filters -->
<div class="av-filters">
    <?php foreach ($statusLabels as $val => $label): ?>
    <a href="<?= SITE_URL ?>/admin/vendors<?= $val ? '?status='.$val : '' ?>"
       class="av-filter-btn <?= $val ?> <?= $currentStatus===$val?'active':'' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="av-count">
    Showing <strong><?= count($vendors) ?></strong>
    of <strong><?= (int)($total ?? 0) ?></strong> vendors
</div>

<div class="av-table-wrap">
    <?php if (!empty($vendors)): ?>
    <table class="av-table">
        <thead>
            <tr>
                <th>Business</th>
                <th>Type</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vendors as $v): ?>
            <tr>
                <td>
                    <div class="av-biz"><?= e($v['business_name']) ?></div>
                    <div class="av-email"><?= e($v['working_email']) ?></div>
                </td>
                <td><?= ucfirst(e($v['vendor_type'] ?? 'student')) ?></td>
                <td><?= ucfirst(e($v['plan_type'] ?? 'basic')) ?></td>
                <td>
                    <span class="badge b-<?= e($v['status']) ?>">
                        <?= ucfirst(e($v['status'])) ?>
                    </span>
                </td>
                <td><?= date('d M Y', strtotime($v['created_at'])) ?></td>
                <td>
                    <div class="av-actions">
                        <a href="<?= SITE_URL ?>/admin/vendors/view/<?= (int)$v['id'] ?>"
                           class="a-view">View</a>
                        <?php if ($v['status'] === 'pending'): ?>
                        <a href="<?= SITE_URL ?>/admin/vendors/approve/<?= (int)$v['id'] ?>"
                           class="a-approve"
                           onclick="return confirm('Approve <?= e($v['business_name']) ?>?')">
                           Approve
                        </a>
                        <?php endif; ?>
                        <?php if ($v['status'] === 'active'): ?>
                        <a href="<?= SITE_URL ?>/admin/vendors/suspend/<?= (int)$v['id'] ?>"
                           class="a-suspend"
                           onclick="return confirm('Suspend this vendor?')">
                           Suspend
                        </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="av-empty">
        <div class="ei">🏪</div>
        <h3>No vendors found</h3>
        <p>
            <?php if ($currentStatus === 'pending'): ?>
                No pending vendor applications at the moment.
            <?php elseif ($currentSearch): ?>
                No vendors match your search.
            <?php else: ?>
                No vendors registered yet.
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if (isset($pag) && $pag['total_pages'] > 1): ?>
<div class="pag">
    <?php if ($pag['has_prev']): ?>
    <a href="?status=<?= e($currentStatus) ?>&q=<?= e($currentSearch) ?>&page=<?= $pag['prev_page'] ?>">← Prev</a>
    <?php else: ?>
    <span class="disabled">← Prev</span>
    <?php endif; ?>

    <?php for ($p = max(1,$pag['current_page']-2); $p <= min($pag['total_pages'],$pag['current_page']+2); $p++): ?>
    <a href="?status=<?= e($currentStatus) ?>&q=<?= e($currentSearch) ?>&page=<?= $p ?>"
       class="<?= $p===$pag['current_page']?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>

    <?php if ($pag['has_next']): ?>
    <a href="?status=<?= e($currentStatus) ?>&q=<?= e($currentSearch) ?>&page=<?= $pag['next_page'] ?>">Next →</a>
    <?php else: ?>
    <span class="disabled">Next →</span>
    <?php endif; ?>
</div>
<?php endif; ?>