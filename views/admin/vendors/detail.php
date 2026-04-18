<?php defined('CAMPUSLINK') or die();
$pageTitle = 'Vendor: ' . ($vendor['business_name'] ?? ''); ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">
            <?= e($vendor['business_name']) ?>
        </h1>
        <div class="admin-page-sub">
            <?= ucfirst($vendor['vendor_type']) ?> Vendor ·
            <?= e($vendor['category_name'] ?? '') ?> ·
            Registered <?= date('d M Y', strtotime($vendor['created_at'])) ?>
        </div>
    </div>
    <div class="admin-action-row">
        <span class="badge badge-<?= $vendor['status'] === 'active' ? 'active' : ($vendor['status'] === 'pending' ? 'pending' : 'suspended') ?>">
            <?= ucfirst($vendor['status']) ?>
        </span>
        <a href="<?= SITE_URL ?>/vendor/<?= e($vendor['slug']) ?>"
           target="_blank" class="btn btn-sm btn-outline-primary">
            👁️ Public Profile
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;">

    <!-- Left: Info -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        <!-- Profile Info -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">👤 Vendor Information</div>
            </div>
            <div class="admin-card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;
                            font-size:var(--font-size-sm);">
                    <?php
                    $fields = [
                        ['Full Name',    $vendor['full_name']],
                        ['Business',     $vendor['business_name']],
                        ['Vendor Type',  ucfirst($vendor['vendor_type'])],
                        ['Category',     $vendor['category_name'] ?? '—'],
                        ['School Email', $vendor['school_email'] ?? '—'],
                        ['Working Email',$vendor['working_email'] ?? '—'],
                        ['Phone',        $vendor['phone']],
                        ['WhatsApp',     $vendor['whatsapp_number'] ?? '—'],
                        ['Plan Type',    ucfirst($vendor['plan_type'] ?? '—')],
                        ['Price Range',  $vendor['price_range']    ?? '—'],
                        ['Location',     $vendor['operating_location'] ?? $vendor['business_address'] ?? '—'],
                        ['Matric / ID',  $vendor['matric_number'] ?? ($vendor['id_type'] ? ucfirst($vendor['id_type']).' · '.$vendor['id_number'] : '—')],
                    ];
                    foreach ($fields as [$label, $value]):
                    ?>
                    <div>
                        <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                    color:var(--text-muted);margin-bottom:0.2rem;">
                            <?= $label ?>
                        </div>
                        <div style="font-weight:600;color:var(--text-primary);
                                    word-break:break-all;">
                            <?= e($value) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($vendor['description'])): ?>
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--divider);">
                    <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                color:var(--text-muted);margin-bottom:0.5rem;">
                        Description
                    </div>
                    <p style="font-size:var(--font-size-sm);color:var(--text-secondary);
                               line-height:1.7;margin:0;">
                        <?= e($vendor['description']) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- ID Document -->
                <?php if (!empty($vendor['id_document'])): ?>
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--divider);">
                    <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                color:var(--text-muted);margin-bottom:0.5rem;">
                        Identity Document (<?= ucfirst($vendor['id_type'] ?? 'ID') ?>)
                    </div>
                    <a href="<?= SITE_URL ?>/assets/uploads/documents/<?= e($vendor['id_document']) ?>"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        📄 View Document
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subscription & Payments -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">💳 Subscription & Payments</div>
            </div>
            <div class="admin-card-body">
                <?php if ($subscription): ?>
                <div style="display:flex;gap:1.5rem;flex-wrap:wrap;
                            font-size:var(--font-size-sm);margin-bottom:1rem;">
                    <div>
                        <span style="color:var(--text-muted);">Plan</span><br>
                        <strong><?= ucfirst($subscription['plan_type']) ?></strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);">Status</span><br>
                        <span class="badge badge-<?= $subscription['status']==='active' ? 'active':'inactive' ?>">
                            <?= ucfirst($subscription['status']) ?>
                        </span>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);">Expiry</span><br>
                        <strong><?= date('d M Y', strtotime($subscription['expiry_date'])) ?></strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted);">Days Left</span><br>
                        <strong><?= max(0,(int)$subscription['days_left']) ?></strong>
                    </div>
                </div>
                <?php else: ?>
                <p style="font-size:var(--font-size-sm);color:var(--text-muted);">
                    No active subscription.
                </p>
                <?php endif; ?>

                <?php if (!empty($payments)): ?>
                <table class="admin-table" style="margin-top:0.75rem;">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:0.7rem;">
                                <?= e(substr($p['paystack_reference'],0,16)) ?>…
                            </td>
                            <td><?= ucfirst($p['plan_type']) ?></td>
                            <td class="payment-amount">
                                ₦<?= number_format($p['amount']/100,2) ?>
                            </td>
                            <td style="font-size:0.7rem;">
                                <?= date('d M Y', strtotime($p['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge <?= $p['status']==='success' ? 'badge-active':'badge-pending' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews -->
        <?php if (!empty($reviews)): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">⭐ Recent Reviews</div>
            </div>
            <div class="admin-card-body">
                <div class="review-list">
                    <?php foreach ($reviews as $r): ?>
                    <div class="review-item" style="padding:0.75rem 0;">
                        <div style="display:flex;justify-content:space-between;">
                            <div style="font-weight:700;font-size:var(--font-size-sm);">
                                <?= e($r['user_name'] ?? 'Anonymous') ?>
                            </div>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <div class="review-stars">
                                    <?php for ($i=1;$i<=5;$i++): ?>
                                    <span class="review-star <?= $i>$r['rating']?'empty':'' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="badge badge-<?= $r['status']==='approved'?'active':'pending' ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </div>
                        </div>
                        <p style="font-size:var(--font-size-xs);color:var(--text-secondary);
                                   margin:0.35rem 0 0;">
                            <?= e(truncate($r['review'],120)) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Right: Actions -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        <!-- Quick Actions -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">⚡ Actions</div>
            </div>
            <div class="admin-card-body" style="display:flex;flex-direction:column;gap:0.75rem;">

                <?php if ($vendor['status'] === 'pending'): ?>
                <form action="<?= SITE_URL ?>/admin/vendors/approve/<?= (int)$vendor['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <button type="submit" class="btn btn-primary btn-full"
                            style="background:var(--accent-green);"
                            onclick="return confirm('Approve this vendor?')">
                        ✅ Approve Vendor
                    </button>
                </form>
                <?php endif; ?>

                <?php if (in_array($vendor['status'], ['pending','active','suspended'])): ?>
                <form action="<?= SITE_URL ?>/admin/vendors/reject/<?= (int)$vendor['id'] ?>"
                      method="POST" id="rejectForm">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <textarea name="reason" class="form-control" rows="2"
                                  placeholder="Rejection reason (required)..."
                                  required style="font-size:0.8rem;resize:none;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-full"
                            style="background:var(--danger);color:#fff;"
                            onclick="return confirm('Reject this vendor?')">
                        ❌ Reject
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($vendor['status'] === 'active'): ?>
                <form action="<?= SITE_URL ?>/admin/vendors/suspend/<?= (int)$vendor['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <input type="text" name="reason" class="form-control"
                               placeholder="Suspension reason..."
                               required style="font-size:0.8rem;">
                    </div>
                    <button type="submit"
                            class="btn btn-sm btn-full"
                            style="background:var(--warning-amber);color:#fff;"
                            onclick="return confirm('Suspend this vendor?')">
                        ⚠️ Suspend
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($vendor['status'] === 'suspended'): ?>
                <form action="<?= SITE_URL ?>/admin/vendors/reinstate/<?= (int)$vendor['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <button type="submit"
                            class="btn btn-primary btn-full"
                            onclick="return confirm('Reinstate this vendor?')">
                        ✅ Reinstate
                    </button>
                </form>
                <?php endif; ?>

                <a href="<?= SITE_URL ?>/admin/vendors" class="btn btn-outline-primary btn-full">
                    ← Back to Vendors
                </a>
            </div>
        </div>

        <!-- Complaints Summary -->
        <?php if (!empty($complaints)): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">📋 Complaints</div>
            </div>
            <div class="admin-card-body">
                <?php foreach ($complaints as $c): ?>
                <div style="padding:0.5rem 0;border-bottom:1px solid var(--divider);
                            font-size:var(--font-size-xs);">
                    <div style="display:flex;justify-content:space-between;margin-bottom:0.2rem;">
                        <span style="font-family:monospace;color:var(--text-muted);">
                            <?= e($c['ticket_id']) ?>
                        </span>
                        <span class="badge badge-status-<?= e($c['status']) ?>"
                              style="font-size:0.6rem;">
                            <?= ucwords(str_replace('_',' ',$c['status'])) ?>
                        </span>
                    </div>
                    <div style="color:var(--text-secondary);">
                        <?= e(truncate($c['description'],80)) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="<?= SITE_URL ?>/admin/complaints?vendor_id=<?= (int)$vendor['id'] ?>"
                   style="font-size:0.75rem;color:var(--primary);font-weight:700;
                          display:block;margin-top:0.75rem;">
                    View all →
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>