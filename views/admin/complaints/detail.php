<?php defined('CAMPUSLINK') or die();
$pageTitle = 'Complaint: ' . $complaint['ticket_id']; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title" style="font-family:monospace;">
            <?= e($complaint['ticket_id']) ?>
        </h1>
        <div class="admin-page-sub">
            Against: <strong><?= e($complaint['business_name']) ?></strong> ·
            Filed: <?= date('d M Y', strtotime($complaint['created_at'])) ?>
        </div>
    </div>
    <div class="admin-action-row">
        <span class="badge badge-status-<?= e($complaint['status']) ?>">
            <?= ucwords(str_replace('_',' ',$complaint['status'])) ?>
        </span>
        <a href="<?= SITE_URL ?>/admin/complaints" class="btn btn-sm btn-outline-primary">
            ← Back
        </a>
    </div>
</div>

<?php if ($verifiedCount >= 2): ?>
<div class="alert alert-warning">
    <span class="alert-icon">⚠️</span>
    <strong>Warning:</strong> This vendor already has <?= (int)$verifiedCount ?> other verified complaint(s).
    Verifying this one will trigger a suspension review.
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.25rem;">

    <!-- Left: Complaint Detail -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">📋 Complaint Details</div>
            </div>
            <div class="admin-card-body" style="font-size:var(--font-size-sm);">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                    color:var(--text-muted);margin-bottom:0.25rem;">Filed By</div>
                        <strong><?= e($complaint['user_name'] ?? 'Unknown') ?></strong>
                        <div style="font-size:0.7rem;color:var(--text-muted);">
                            <?= e($complaint['user_email'] ?? '') ?>
                            · <?= e($complaint['user_level'] ?? '') ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                    color:var(--text-muted);margin-bottom:0.25rem;">Category</div>
                        <strong><?= ucwords(str_replace('_',' ',$complaint['category'])) ?></strong>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                color:var(--text-muted);margin-bottom:0.35rem;">Description</div>
                    <div style="background:var(--bg);border-radius:var(--radius-lg);
                                padding:1rem;border:1px solid var(--divider);
                                line-height:1.7;color:var(--text-secondary);">
                        <?= e($complaint['description']) ?>
                    </div>
                </div>

                <?php if (!empty($complaint['evidence_file'])): ?>
                <div style="margin-bottom:1rem;">
                    <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                color:var(--text-muted);margin-bottom:0.35rem;">Evidence</div>
                    <a href="<?= SITE_URL ?>/assets/uploads/evidence/<?= e($complaint['evidence_file']) ?>"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        📎 View Evidence File
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($complaint['vendor_response'])): ?>
                <div>
                    <div style="font-size:0.68rem;text-transform:uppercase;letter-spacing:0.05em;
                                color:var(--text-muted);margin-bottom:0.35rem;">Vendor Response</div>
                    <div class="review-vendor-reply">
                        <?= e($complaint['vendor_response']) ?>
                        <div style="font-size:0.68rem;color:var(--text-muted);margin-top:0.3rem;">
                            <?= date('d M Y', strtotime($complaint['vendor_responded_at'] ?? 'now')) ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info" style="font-size:var(--font-size-xs);">
                    <span class="alert-icon">ℹ️</span>
                    Vendor has not responded yet.
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <!-- Right: Admin Actions -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="admin-card-title">⚡ Admin Actions</div>
            </div>
            <div class="admin-card-body" style="display:flex;flex-direction:column;gap:0.75rem;">

                <?php if (in_array($complaint['status'], ['submitted','under_review'])): ?>

                <!-- Mark Under Review -->
                <form action="<?= SITE_URL ?>/admin/complaints/detail/<?= (int)$complaint['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <input type="hidden" name="_action"    value="under_review">
                    <button type="submit" class="btn btn-outline-primary btn-full btn-sm">
                        🔍 Mark Under Review
                    </button>
                </form>

                <!-- Verify -->
                <form action="<?= SITE_URL ?>/admin/complaints/verify/<?= (int)$complaint['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <textarea name="admin_note" class="form-control" rows="2"
                                  placeholder="Admin note..."
                                  style="font-size:0.8rem;resize:none;"></textarea>
                    </div>
                    <button type="submit"
                            class="btn btn-sm btn-full"
                            style="background:var(--danger);color:#fff;"
                            onclick="return confirm('Verify this complaint? This may trigger a vendor suspension review.')">
                        ⚠️ Verify Complaint
                    </button>
                </form>

                <!-- Dismiss -->
                <form action="<?= SITE_URL ?>/admin/complaints/dismiss/<?= (int)$complaint['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <input type="text" name="admin_note" class="form-control"
                               placeholder="Reason for dismissal..."
                               style="font-size:0.8rem;">
                    </div>
                    <button type="submit"
                            class="btn btn-sm btn-full btn-outline-primary"
                            onclick="return confirm('Dismiss this complaint?')">
                        ❌ Dismiss
                    </button>
                </form>

                <!-- Resolve -->
                <form action="<?= SITE_URL ?>/admin/complaints/resolve/<?= (int)$complaint['id'] ?>"
                      method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <input type="text" name="admin_note" class="form-control"
                               placeholder="Resolution note..."
                               style="font-size:0.8rem;">
                    </div>
                    <button type="submit"
                            class="btn btn-primary btn-sm btn-full"
                            style="background:var(--accent-green);"
                            onclick="return confirm('Resolve this complaint?')">
                        ✅ Resolve
                    </button>
                </form>

                <?php else: ?>
                <div style="text-align:center;padding:1rem;color:var(--text-muted);
                            font-size:var(--font-size-sm);">
                    This complaint has been
                    <strong><?= $complaint['status'] ?></strong>.
                    <br>No further actions available.
                </div>
                <?php endif; ?>

                <a href="<?= SITE_URL ?>/admin/vendors/detail/<?= (int)$complaint['vendor_id'] ?>"
                   class="btn btn-outline-primary btn-sm btn-full">
                    🏪 View Vendor Profile
                </a>
            </div>
        </div>
    </div>

</div>