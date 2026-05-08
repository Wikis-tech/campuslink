<?php defined('CAMPUSLINK') or die(); ?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Payment History</h1>
        <p class="dashboard-page-subtitle">
            All subscription payments for <?= e($vendor['business_name'] ?? 'your account') ?>
        </p>
    </div>
</div>

<?php if (empty($payments)): ?>
<div class="dash-card">
    <div class="dash-card-body">
        <div class="empty-state">
            <div class="empty-icon"><i data-lucide="file-text"></i></div>
            <h3>No payment records yet</h3>
            <p>Your payment history will appear here after your first subscription payment.</p>
            <a href="<?= SITE_URL ?>/vendor/payment" class="btn btn-primary">
                Make First Payment
            </a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="dash-card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <?php
                    // Normalize column names — handle both old & new column naming
                    $ref      = $payment['paystack_reference'] ?? $payment['reference'] ?? $payment['tx_ref'] ?? '';
                    $paidAt   = $payment['paid_at'] ?? $payment['created_at'] ?? date('Y-m-d H:i:s');
                    $subStart = $payment['subscription_start'] ?? $payment['start_date'] ?? null;
                    $subEnd   = $payment['subscription_expiry'] ?? $payment['expiry_date'] ?? $payment['end_date'] ?? null;
                ?>
                <tr>
                    <td>
                        <?php if ($ref): ?>
                        <span style="font-family:monospace;font-size:var(--font-size-xs);
                                     color:var(--text-muted);"
                              data-copy="<?= e($ref) ?>"
                              title="Click to copy">
                            <?= e(substr($ref, 0, 16)) ?>…
                        </span>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:var(--font-size-xs);">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= ucfirst(e($payment['plan_type'] ?? '')) ?></strong>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);">
                            <?= ucfirst(e($payment['vendor_type'] ?? '')) ?>
                        </div>
                    </td>
                    <td class="payment-amount">
                        ₦<?= number_format(($payment['amount'] ?? 0) / 100, 2) ?>
                    </td>
                    <td>
                        <?= date('d M Y', strtotime($paidAt)) ?>
                        <div style="font-size:var(--font-size-xs);color:var(--text-muted);">
                            <?= date('g:ia', strtotime($paidAt)) ?>
                        </div>
                    </td>
                    <td style="font-size:var(--font-size-xs);">
                        <?php if ($subStart && strtotime($subStart)): ?>
                            <?= date('d M Y', strtotime($subStart)) ?>
                            <span style="color:var(--text-muted);">→</span>
                            <?= $subEnd && strtotime($subEnd) ? date('d M Y', strtotime($subEnd)) : 'N/A' ?>
                        <?php else: ?>
                            <span style="color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusClass = match($payment['status'] ?? '') {
                            'success' => 'badge-active',
                            'pending' => 'badge-pending',
                            default   => 'badge-suspended',
                        };
                        ?>
                        <span class="badge <?= $statusClass ?>">
                            <?= ucfirst(e($payment['status'] ?? 'unknown')) ?>
                        </span>
                    </td>
                    <td>
                        <?php if (($payment['status'] ?? '') === 'success'): ?>
                        <a href="<?= SITE_URL ?>/vendor/payment?receipt=<?= (int)$payment['id'] ?>"
                           class="btn btn-sm btn-outline-primary"
                           target="_blank">
                            <i data-lucide="file-text" style="width:14px;height:14px;"></i> Receipt
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>