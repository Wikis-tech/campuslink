<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Payments'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">💳 Payment Records</h1>
        <div class="admin-page-sub"><?= number_format($pag['total']) ?> transactions</div>
    </div>
</div>

<!-- Totals -->
<div class="admin-stats-grid" style="margin-bottom:1.5rem;">
    <div class="admin-stat-card green">
        <div class="admin-stat-label">Total Collected</div>
        <div class="admin-stat-value">
            ₦<?= number_format(($totals['total_success'] ?? 0) / 100) ?>
        </div>
    </div>
    <div class="admin-stat-card amber">
        <div class="admin-stat-label">Pending</div>
        <div class="admin-stat-value">
            ₦<?= number_format(($totals['total_pending'] ?? 0) / 100) ?>
        </div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Successful Txns</div>
        <div class="admin-stat-value"><?= number_format($totals['count_success'] ?? 0) ?></div>
    </div>
</div>

<form method="GET" class="admin-filter-bar">
    <input type="text" name="q" placeholder="Search reference or vendor..."
           value="<?= e($search) ?>">
    <select name="status">
        <option value="">All Statuses</option>
        <option value="success" <?= $status==='success'?'selected':'' ?>>Success</option>
        <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
        <option value="failed"  <?= $status==='failed' ?'selected':'' ?>>Failed</option>
    </select>
    <input type="date" name="from" value="<?= e($from) ?>" title="From date">
    <input type="date" name="to"   value="<?= e($to)   ?>" title="To date">
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= SITE_URL ?>/admin/payments" class="btn btn-outline-primary btn-sm">Reset</a>
</form>

<div class="admin-card">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Vendor</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Subscription Period</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td style="font-family:monospace;font-size:0.7rem;color:var(--text-muted);"
                        data-copy="<?= e($p['paystack_reference']) ?>">
                        <?= e(substr($p['paystack_reference'],0,14)) ?>…
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:var(--font-size-sm);">
                            <?= e($p['business_name']) ?>
                        </div>
                    </td>
                    <td style="font-size:0.75rem;">
                        <?= $p['vendor_type']==='student' ? '🎓' : '🏢' ?>
                        <?= ucfirst($p['vendor_type']) ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $p['plan_type']==='featured'?'featured':'active' ?>"
                              style="font-size:0.65rem;">
                            <?= ucfirst($p['plan_type']) ?>
                        </span>
                    </td>
                    <td class="payment-amount" style="font-weight:800;">
                        ₦<?= number_format($p['amount']/100, 2) ?>
                    </td>
                    <td>
                        <span class="badge <?= $p['status']==='success'?'badge-active':($p['status']==='pending'?'badge-pending':'badge-suspended') ?>"
                              style="font-size:0.65rem;">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.7rem;color:var(--text-muted);">
                        <?= date('d M Y', strtotime($p['created_at'])) ?>
                    </td>
                    <td style="font-size:0.7rem;color:var(--text-muted);">
                        <?php if (!empty($p['subscription_start'])): ?>
                        <?= date('d M Y', strtotime($p['subscription_start'])) ?>
                        → <?= date('d M Y', strtotime($p['subscription_expiry'])) ?>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pagination = $pag; require VIEWS_PATH . '/partials/pagination.php'; ?>