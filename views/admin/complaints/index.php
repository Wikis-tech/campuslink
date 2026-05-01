<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Complaints'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">📋 Complaints</h1>
        <div class="admin-page-sub"><?= number_format($pag['total']) ?> total</div>
    </div>
</div>

<!-- Status Tabs -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    <?php
    $counts  = array_column($statusCounts,'cnt','status');
    $tabs    = ['' => 'All', 'submitted' => 'Submitted',
                'under_review' => 'Under Review', 'verified' => 'Verified',
                'resolved' => 'Resolved', 'dismissed' => 'Dismissed'];
    foreach ($tabs as $val => $label):
        $cnt    = $val ? ($counts[$val] ?? 0) : array_sum($counts);
        $active = $status === $val;
    ?>
    <a href="?status=<?= $val ?><?= $search ? '&q='.urlencode($search) : '' ?>"
       class="btn btn-sm <?= $active ? 'btn-primary' : 'btn-outline-primary' ?>"
       style="font-size:0.75rem;">
        <?= $label ?> (<?= $cnt ?>)
    </a>
    <?php endforeach; ?>
</div>

<form method="GET" class="admin-filter-bar">
    <input type="hidden" name="status" value="<?= e($status) ?>">
    <input type="text" name="q" placeholder="Search ticket or vendor..."
           value="<?= e($search ?? '') ?>">
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
</form>

<div class="admin-card">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Vendor</th>
                    <th>Filed By</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Filed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $c): ?>
                <tr>
                    <td style="font-family:monospace;font-size:0.72rem;">
                        <?= e($c['ticket_id']) ?>
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:var(--font-size-sm);">
                            <?= e($c['vendor_name'] ?? $c['business_name'] ?? '—') ?>
                        </div>
                    </td>
                    <td style="font-size:0.75rem;">
                        <?= e($c['user_name'] ?? 'Unknown') ?>
                        <div style="font-size:0.65rem;color:var(--text-muted);">
                            <?= e($c['user_level'] ?? '') ?>
                        </div>
                    </td>
                    <td style="font-size:0.75rem;">
                        <?= ucwords(str_replace('_',' ',$c['category'])) ?>
                    </td>
                    <td>
                        <span class="badge badge-status-<?= e($c['status']) ?>"
                              style="font-size:0.65rem;">
                            <?= ucwords(str_replace('_',' ',$c['status'])) ?>
                        </span>
                    </td>
                    <td style="font-size:0.7rem;color:var(--text-muted);">
                        <?= date('d M Y', strtotime($c['created_at'])) ?>
                    </td>
                    <td>
                        <a href="<?= SITE_URL ?>/admin/complaints/detail/<?= (int)$c['id'] ?>"
                           style="font-size:0.75rem;color:var(--primary);font-weight:700;">
                            Review →
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pagination = $pag; require VIEWS_PATH . '/partials/pagination.php'; ?>