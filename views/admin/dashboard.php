<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Dashboard'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Admin Dashboard</h1>
        <div class="admin-page-sub">
            <?= date('l, d F Y') ?> · Logged in as <?= e(AdminAuth::name()) ?>
        </div>
    </div>
    <a href="<?= SITE_URL ?>/admin/vendors/pending" class="btn btn-primary btn-sm">
        ⏳ Pending (<?= (int)$stats['pending_vendors'] ?>)
    </a>
</div>

<!-- Stats Grid -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Vendors</div>
        <div class="admin-stat-value"><?= number_format($stats['total_vendors']) ?></div>
    </div>
    <div class="admin-stat-card amber">
        <div class="admin-stat-label">Pending Approval</div>
        <div class="admin-stat-value"><?= number_format($stats['pending_vendors']) ?></div>
    </div>
    <div class="admin-stat-card green">
        <div class="admin-stat-label">Active Vendors</div>
        <div class="admin-stat-value"><?= number_format($stats['active_vendors']) ?></div>
    </div>
    <div class="admin-stat-card red">
        <div class="admin-stat-label">Suspended</div>
        <div class="admin-stat-value"><?= number_format($stats['suspended_vendors']) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Students</div>
        <div class="admin-stat-value"><?= number_format($stats['total_users']) ?></div>
    </div>
    <div class="admin-stat-card red">
        <div class="admin-stat-label">Open Complaints</div>
        <div class="admin-stat-value"><?= number_format($stats['open_complaints']) ?></div>
    </div>
    <div class="admin-stat-card amber">
        <div class="admin-stat-label">Pending Reviews</div>
        <div class="admin-stat-value"><?= number_format($stats['pending_reviews']) ?></div>
    </div>
    <div class="admin-stat-card green">
        <div class="admin-stat-label">Total Revenue</div>
        <div class="admin-stat-value">
            ₦<?= number_format($stats['total_revenue'] / 100) ?>
        </div>
    </div>
    <div class="admin-stat-card green">
        <div class="admin-stat-label">This Month</div>
        <div class="admin-stat-value">
            ₦<?= number_format($stats['month_revenue'] / 100) ?>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<?php if (!empty($revenueChart)): ?>
<div class="admin-card" style="margin-bottom:1.5rem;">
    <div class="admin-card-header">
        <div class="admin-card-title">📈 Revenue (Last 6 Months)</div>
    </div>
    <div class="admin-card-body">
        <canvas id="adminRevenueChart" height="80"
                data-labels="<?= e(json_encode(array_column($revenueChart, 'month'))) ?>"
                data-amounts="<?= e(json_encode(array_column($revenueChart, 'total'))) ?>">
        </canvas>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
(function(){
    const canvas = document.getElementById('adminRevenueChart');
    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: JSON.parse(canvas.dataset.labels),
            datasets: [{
                label: 'Revenue (₦)',
                data: JSON.parse(canvas.dataset.amounts).map(v => v/100),
                backgroundColor: 'rgba(59,130,246,0.15)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive:true, maintainAspectRatio:true,
            plugins: { legend:{ display:false } },
            scales: {
                x: { grid:{ display:false } },
                y: { ticks:{ callback: v => '₦'+v.toLocaleString() } }
            }
        }
    });
})();
</script>
<?php endif; ?>

<!-- Two-column grid -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    <!-- Pending Vendors -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">⏳ Awaiting Approval</div>
            <a href="<?= SITE_URL ?>/admin/vendors/pending"
               style="font-size:0.75rem;color:var(--primary);font-weight:700;">
                View all →
            </a>
        </div>
        <div>
            <?php if (empty($pendingVendors)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);
                        font-size:var(--font-size-sm);">
                ✅ No pending approvals
            </div>
            <?php else: ?>
            <table class="admin-table">
                <tbody>
                    <?php foreach ($pendingVendors as $v): ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;font-size:var(--font-size-sm);">
                                <?= e($v['business_name']) ?>
                            </div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">
                                <?= ucfirst($v['vendor_type']) ?> ·
                                <?= e($v['category_name'] ?? '—') ?> ·
                                <?= date('d M', strtotime($v['created_at'])) ?>
                            </div>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="<?= SITE_URL ?>/admin/vendors/detail/<?= (int)$v['id'] ?>"
                               class="btn btn-sm btn-primary"
                               style="font-size:0.7rem;padding:0.25rem 0.6rem;">
                                Review →
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Open Complaints -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">🚨 Open Complaints</div>
            <a href="<?= SITE_URL ?>/admin/complaints"
               style="font-size:0.75rem;color:var(--primary);font-weight:700;">
                View all →
            </a>
        </div>
        <div>
            <?php if (empty($openComplaints)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);
                        font-size:var(--font-size-sm);">
                ✅ No open complaints
            </div>
            <?php else: ?>
            <table class="admin-table">
                <tbody>
                    <?php foreach ($openComplaints as $c): ?>
                    <tr>
                        <td>
                            <div style="font-size:0.7rem;font-family:monospace;
                                        color:var(--text-muted);">
                                <?= e($c['ticket_id']) ?>
                            </div>
                            <div style="font-weight:700;font-size:var(--font-size-sm);">
                                <?= e($c['business_name']) ?>
                            </div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">
                                <?= date('d M', strtotime($c['created_at'])) ?>
                            </div>
                        </td>
                        <td style="text-align:right;">
                            <span class="badge badge-status-<?= e($c['status']) ?>"
                                  style="font-size:0.65rem;">
                                <?= ucwords(str_replace('_',' ',$c['status'])) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= SITE_URL ?>/admin/complaints/detail/<?= (int)$c['id'] ?>"
                               style="font-size:0.7rem;color:var(--primary);font-weight:600;">
                                Review →
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>