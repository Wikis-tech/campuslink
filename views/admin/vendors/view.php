
<?php defined('CAMPUSLINK') or die(); ?>

<style>
.vv-back{display:inline-flex;align-items:center;gap:0.4rem;font-size:0.82rem;
font-weight:700;color:#64748b;text-decoration:none;margin-bottom:1.25rem;
transition:color 0.15s;}
.vv-back:hover{color:#1a56db;}
.vv-grid{display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;}
@media(max-width:768px){.vv-grid{grid-template-columns:1fr;}}
.vv-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;
overflow:hidden;margin-bottom:1.25rem;}
.vv-card-head{padding:0.85rem 1.1rem;border-bottom:1px solid #e2e8f0;
display:flex;align-items:center;justify-content:space-between;}
.vv-card-head h3{font-size:0.88rem;font-weight:800;color:#1e293b;margin:0;}
.vv-card-body{padding:1.1rem;}
.vv-row{display:flex;gap:0.5rem;padding:0.6rem 0;
border-bottom:1px solid #f1f5f9;font-size:0.82rem;}
.vv-row:last-child{border-bottom:none;}
.vv-label{width:140px;flex-shrink:0;font-weight:700;color:#64748b;}
.vv-val{color:#1e293b;flex:1;word-break:break-word;}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;
font-size:0.72rem;font-weight:700;}
.b-active{background:#dcfce7;color:#166534;}
.b-pending{background:#fef3c7;color:#92400e;}
.b-suspended{background:#fee2e2;color:#dc2626;}
.b-inactive{background:#f1f5f9;color:#64748b;}

/* Action buttons */
.vv-actions{display:flex;flex-direction:column;gap:0.6rem;}
.vv-btn{display:block;width:100%;padding:0.75rem 1rem;border-radius:10px;
font-size:0.85rem;font-weight:800;text-align:center;text-decoration:none;
cursor:pointer;border:none;transition:opacity 0.2s,transform 0.15s;font-family:inherit;}
.vv-btn:hover{opacity:0.88;transform:translateY(-1px);}
.vv-btn-approve{background:linear-gradient(135deg,#166534,#16a34a);color:#fff;}
.vv-btn-suspend{background:linear-gradient(135deg,#7f1d1d,#dc2626);color:#fff;}
.vv-btn-delete{background:#f1f5f9;color:#64748b;border:1.5px solid #e2e8f0;}
.vv-btn-back{background:#eff6ff;color:#1a56db;}

/* ID document preview */
.vv-id-preview{margin-top:0.75rem;}
.vv-id-preview img{width:100%;border-radius:10px;border:1px solid #e2e8f0;
max-height:300px;object-fit:contain;background:#f8fafc;}
.vv-id-link{display:inline-flex;align-items:center;gap:0.4rem;padding:0.6rem 1rem;
background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:0.8rem;
font-weight:700;color:#1a56db;text-decoration:none;margin-top:0.5rem;}
.vv-id-link:hover{background:#eff6ff;}

/* Suspend form */
.suspend-form{display:none;margin-top:0.75rem;}
.suspend-form textarea{width:100%;padding:0.65rem 0.85rem;border:1.5px solid #e2e8f0;
border-radius:9px;font-size:0.82rem;resize:vertical;min-height:80px;
outline:none;box-sizing:border-box;font-family:inherit;}
.suspend-form textarea:focus{border-color:#dc2626;}
.suspend-form button{width:100%;margin-top:0.5rem;padding:0.65rem;
background:#dc2626;color:#fff;border:none;border-radius:9px;
font-weight:800;font-size:0.82rem;cursor:pointer;}

/* Stats row */
.vv-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-bottom:1.25rem;}
.vv-stat{background:#fff;border:1px solid #e2e8f0;border-radius:12px;
padding:1rem;text-align:center;}
.vv-stat .sv{font-size:1.4rem;font-weight:900;color:#1e293b;}
.vv-stat .sl{font-size:0.7rem;color:#64748b;font-weight:600;}

/* Flash */
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;
padding:0.75rem 1rem;border-radius:9px;font-size:0.82rem;margin-bottom:1rem;font-weight:600;}
.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;
padding:0.75rem 1rem;border-radius:9px;font-size:0.82rem;margin-bottom:1rem;font-weight:600;}
</style>

<a href="<?= SITE_URL ?>/admin/vendors" class="vv-back">← Back to Vendors</a>

<?php
$flashes = Session::getAllFlash();
foreach ($flashes as $type => $messages):
    if (!is_array($messages)) $messages = [$messages];
    foreach ($messages as $message):
        if (empty($message)) continue;
        $cls = $type === 'error' ? 'flash-error' : 'flash-success';
?>
<div class="<?= $cls ?>"><?= e($message) ?></div>
<?php endforeach; ?>
<?php endforeach; ?>

<!-- Stats row -->
<div class="vv-stats">
    <div class="vv-stat">
        <div class="sv"><?= number_format((float)($vendor['avg_rating'] ?? 0), 1) ?></div>
        <div class="sl">Avg Rating</div>
    </div>
    <div class="vv-stat">
        <div class="sv"><?= count($reviews ?? []) ?></div>
        <div class="sl">Reviews</div>
    </div>
    <div class="vv-stat">
        <div class="sv"><?= count($complaints ?? []) ?></div>
        <div class="sl">Complaints</div>
    </div>
</div>

<div class="vv-grid">
    <div>
        <!-- Business Info -->
        <div class="vv-card">
            <div class="vv-card-head">
                <h3>🏪 Business Details</h3>
                <span class="badge b-<?= e($vendor['status']) ?>">
                    <?= ucfirst(e($vendor['status'])) ?>
                </span>
            </div>
            <div class="vv-card-body">
                <div class="vv-row">
                    <div class="vv-label">Business Name</div>
                    <div class="vv-val"><strong><?= e($vendor['business_name']) ?></strong></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Vendor Type</div>
                    <div class="vv-val"><?= ucfirst(e($vendor['vendor_type'] ?? 'student')) ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Category</div>
                    <div class="vv-val"><?= e($vendor['category_name'] ?? 'N/A') ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Plan</div>
                    <div class="vv-val"><?= ucfirst(e($vendor['plan_type'] ?? 'basic')) ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Price Range</div>
                    <div class="vv-val"><?= e($vendor['price_range'] ?? 'Not set') ?></div>
                </div>
                <?php if (!empty($vendor['business_address'])): ?>
                <div class="vv-row">
                    <div class="vv-label">Address</div>
                    <div class="vv-val"><?= e($vendor['business_address']) ?></div>
                </div>
                <?php endif; ?>
                <div class="vv-row">
                    <div class="vv-label">Description</div>
                    <div class="vv-val"><?= e($vendor['description'] ?? 'N/A') ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Registered</div>
                    <div class="vv-val"><?= date('d M Y, g:ia', strtotime($vendor['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Owner Info -->
        <div class="vv-card">
            <div class="vv-card-head"><h3>👤 Owner Details</h3></div>
            <div class="vv-card-body">
                <div class="vv-row">
                    <div class="vv-label">Full Name</div>
                    <div class="vv-val"><?= e($vendor['full_name']) ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Email</div>
                    <div class="vv-val">
                        <a href="mailto:<?= e($vendor['working_email']) ?>"
                           style="color:#1a56db;text-decoration:none;">
                            <?= e($vendor['working_email']) ?>
                        </a>
                    </div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Phone</div>
                    <div class="vv-val">
                        <a href="tel:<?= e($vendor['phone'] ?? '') ?>"
                           style="color:#1a56db;text-decoration:none;">
                            <?= e($vendor['phone'] ?? 'N/A') ?>
                        </a>
                    </div>
                </div>
                <?php if (!empty($vendor['whatsapp_number'])): ?>
                <div class="vv-row">
                    <div class="vv-label">WhatsApp</div>
                    <div class="vv-val">
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$vendor['whatsapp_number']) ?>"
                           target="_blank" style="color:#16a34a;text-decoration:none;">
                            <?= e($vendor['whatsapp_number']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($vendor['matric_number'])): ?>
                <div class="vv-row">
                    <div class="vv-label">Matric Number</div>
                    <div class="vv-val"><?= e($vendor['matric_number']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($vendor['id_type'])): ?>
                <div class="vv-row">
                    <div class="vv-label">ID Type</div>
                    <div class="vv-val"><?= strtoupper(e($vendor['id_type'])) ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">ID Number</div>
                    <div class="vv-val"><?= e($vendor['id_number'] ?? 'N/A') ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ID Document -->
        <?php if (!empty($vendor['id_document'])): ?>
        <div class="vv-card">
            <div class="vv-card-head"><h3>🪪 ID Document</h3></div>
            <div class="vv-card-body">
                <?php
                $docPath = UPLOADS_PATH . '/documents/' . $vendor['id_document'];
                $docUrl  = UPLOADS_URL . '/documents/' . $vendor['id_document'];
                $isPdf   = str_ends_with(strtolower($vendor['id_document']), '.pdf');
                ?>
                <?php if ($isPdf): ?>
                    <a href="<?= e($docUrl) ?>" target="_blank" class="vv-id-link">
                        📄 View PDF Document →
                    </a>
                <?php else: ?>
                    <div class="vv-id-preview">
                        <img src="<?= e($docUrl) ?>"
                             alt="ID Document"
                             onerror="this.src='';this.alt='Image not found';">
                    </div>
                    <a href="<?= e($docUrl) ?>" target="_blank" class="vv-id-link">
                        🔍 Open Full Size →
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <?php if (!empty($reviews)): ?>
        <div class="vv-card">
            <div class="vv-card-head"><h3>⭐ Reviews (<?= count($reviews) ?>)</h3></div>
            <div class="vv-card-body" style="padding:0;">
                <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:0.6rem 1rem;text-align:left;color:#64748b;font-size:0.7rem;">Reviewer</th>
                            <th style="padding:0.6rem 1rem;text-align:left;color:#64748b;font-size:0.7rem;">Rating</th>
                            <th style="padding:0.6rem 1rem;text-align:left;color:#64748b;font-size:0.7rem;">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $r): ?>
                        <tr style="border-top:1px solid #f1f5f9;">
                            <td style="padding:0.65rem 1rem;color:#374151;">
                                <?= e($r['first_name'] ?? 'Student') ?>
                            </td>
                            <td style="padding:0.65rem 1rem;color:#f59e0b;">
                                <?= str_repeat('★', (int)$r['rating']) ?>
                            </td>
                            <td style="padding:0.65rem 1rem;color:#64748b;">
                                <?= e(substr($r['comment'] ?? '', 0, 60)) ?>
                                <?= strlen($r['comment'] ?? '') > 60 ? '…' : '' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar actions -->
    <div>
        <div class="vv-card">
            <div class="vv-card-head"><h3>⚡ Actions</h3></div>
            <div class="vv-card-body">
                <div class="vv-actions">

                    <?php if ($vendor['status'] === 'pending'): ?>
                    <!-- Approve -->
                    <a href="<?= SITE_URL ?>/admin/vendors/approve/<?= (int)$vendor['id'] ?>"
                       class="vv-btn vv-btn-approve"
                       onclick="return confirm('Approve <?= e($vendor['business_name']) ?>? They will be notified by email.')">
                        ✅ Approve Vendor
                    </a>
                    <?php endif; ?>

                    <?php if ($vendor['status'] === 'active'): ?>
                    <!-- Suspend -->
                    <button class="vv-btn vv-btn-suspend"
                            onclick="document.getElementById('suspendForm').style.display='block'">
                        ⛔ Suspend Vendor
                    </button>
                    <div class="suspend-form" id="suspendForm">
                        <form method="POST"
                              action="<?= SITE_URL ?>/admin/vendors/suspend/<?= (int)$vendor['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                            <textarea name="reason"
                                      placeholder="Reason for suspension (will be sent to vendor)..."
                                      required></textarea>
                            <button type="submit">Confirm Suspension</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php if ($vendor['status'] === 'suspended'): ?>
                    <!-- Reactivate -->
                    <a href="<?= SITE_URL ?>/admin/vendors/approve/<?= (int)$vendor['id'] ?>"
                       class="vv-btn vv-btn-approve"
                       onclick="return confirm('Reactivate this vendor?')">
                        ✅ Reactivate Vendor
                    </a>
                    <?php endif; ?>

                    <!-- View public profile -->
                    <?php if (!empty($vendor['slug'])): ?>
                    <a href="<?= SITE_URL ?>/browse/<?= e($vendor['slug']) ?>"
                       target="_blank"
                       class="vv-btn"
                       style="background:#f0fdf4;color:#166534;">
                        👁️ View Public Profile
                    </a>
                    <?php endif; ?>

                    <!-- Delete -->
                    <a href="<?= SITE_URL ?>/admin/vendors/delete/<?= (int)$vendor['id'] ?>"
                       class="vv-btn vv-btn-delete"
                       onclick="return confirm('PERMANENTLY delete <?= e($vendor['business_name']) ?>? This cannot be undone.')">
                        🗑️ Delete Vendor
                    </a>

                    <a href="<?= SITE_URL ?>/admin/vendors" class="vv-btn vv-btn-back">
                        ← Back to Vendors
                    </a>
                </div>
            </div>
        </div>

        <!-- Subscription -->
        <?php if (!empty($sub)): ?>
        <div class="vv-card">
            <div class="vv-card-head"><h3>💳 Subscription</h3></div>
            <div class="vv-card-body">
                <div class="vv-row">
                    <div class="vv-label">Status</div>
                    <div class="vv-val">
                        <span class="badge b-<?= e($sub['status']) ?>">
                            <?= ucfirst(e($sub['status'])) ?>
                        </span>
                    </div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Plan</div>
                    <div class="vv-val"><?= ucfirst(e($sub['plan_type'] ?? '')) ?></div>
                </div>
                <div class="vv-row">
                    <div class="vv-label">Expires</div>
                    <div class="vv-val">
                        <?= !empty($sub['expiry_date'])
                            ? date('d M Y', strtotime($sub['expiry_date']))
                            : 'N/A' ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payments -->
        <?php if (!empty($payments)): ?>
        <div class="vv-card">
            <div class="vv-card-head"><h3>💰 Payments</h3></div>
            <div class="vv-card-body" style="padding:0;">
                <?php foreach ($payments as $p): ?>
                <div style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;
                            font-size:0.78rem;display:flex;justify-content:space-between;">
                    <span style="color:#374151;font-weight:600;">
                        ₦<?= number_format(($p['amount'] ?? 0)/100, 2) ?>
                    </span>
                    <span style="color:#94a3b8;">
                        <?= date('d M Y', strtotime($p['created_at'])) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
