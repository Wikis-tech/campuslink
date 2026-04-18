<?php defined('CAMPUSLINK') or die(); ?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Complaints</h1>
        <p class="dashboard-page-subtitle">
            <?= (int)$pagination['total'] ?> complaint<?= $pagination['total'] != 1 ? 's' : '' ?> filed against your business
        </p>
    </div>
</div>

<?php if ($openCount > 0): ?>
<div class="alert alert-warning">
    <span class="alert-icon"><i data-lucide="alert-triangle"></i></span>
    <div>
        <strong>Action Required:</strong> You have <?= (int)$openCount ?> unresolved
        complaint<?= $openCount != 1 ? 's' : '' ?>.
        Responding promptly can prevent escalation and help your reputation.
    </div>
</div>
<?php endif; ?>

<div class="disclaimer-box" style="margin-bottom:1.5rem;">
    <span class="disclaimer-icon"><i data-lucide="alert-triangle"></i></span>
    <div class="disclaimer-text">
        <strong>Important:</strong> 3 or more verified complaints may result in a
        suspension review by the admin team. Always respond professionally.
        <a href="<?= SITE_URL ?>/complaint-resolution" target="_blank"
           style="color:var(--warning-dark);font-weight:700;">
            Read our Complaint Resolution Policy →
        </a>
    </div>
</div>

<?php if (empty($complaints)): ?>
<div class="dash-card">
    <div class="dash-card-body">
        <div class="empty-state">
            <div class="empty-icon"><i data-lucide="clipboard"></i></div>
            <h3>No complaints yet</h3>
            <p>Great job! Keep delivering excellent service to maintain your clean record.</p>
        </div>
    </div>
</div>
<?php else: ?>

<div class="dash-card">
    <div class="dash-card-body" style="padding:0;">
        <div class="complaint-list" style="padding:1.25rem;">
            <?php foreach ($complaints as $complaint): ?>
            <div class="complaint-item" id="complaint-<?= (int)$complaint['id'] ?>">
                <div class="complaint-item-header">
                    <div>
                        <span class="complaint-ticket"><?= e($complaint['ticket_id']) ?></span>
                        <div class="complaint-category">
                            <?= ucwords(str_replace('_', ' ', $complaint['category'])) ?>
                        </div>
                        <div class="complaint-vendor" style="color:var(--text-muted);font-size:var(--font-size-xs);">
                            Filed: <?= date('d M Y', strtotime($complaint['created_at'])) ?>
                        </div>
                    </div>
                    <span class="badge badge-status-<?= e($complaint['status']) ?>">
                        <?= ucwords(str_replace('_', ' ', $complaint['status'])) ?>
                    </span>
                </div>

                <p class="complaint-text"><?= e($complaint['description']) ?></p>

                <?php if (!empty($complaint['evidence_file'])): ?>
                <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-bottom:0.5rem;display:flex;align-items:center;gap:0.3rem;">
                    <i data-lucide="paperclip" style="width:12px;height:12px;"></i> Evidence file attached
                </div>
                <?php endif; ?>

                <?php if (!empty($complaint['admin_note'])): ?>
                <div class="alert alert-info" style="font-size:var(--font-size-xs);margin-bottom:0.75rem;">
                    <span class="alert-icon"><i data-lucide="info"></i></span>
                    <strong>Admin note:</strong> <?= e($complaint['admin_note']) ?>
                </div>
                <?php endif; ?>

                <!-- Vendor Response -->
                <?php if (!empty($complaint['vendor_response'])): ?>
                <div class="review-vendor-reply">
                    <strong>Your Response:</strong>
                    <?= e($complaint['vendor_response']) ?>
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);margin-top:0.25rem;">
                        Responded <?= date('d M Y', strtotime($complaint['vendor_responded_at'])) ?>
                    </div>
                </div>
                <?php elseif (in_array($complaint['status'], ['submitted', 'under_review'])): ?>
                <div class="review-actions">
                    <button class="btn btn-sm btn-outline-primary complaint-response-toggle"
                            data-complaint-id="<?= (int)$complaint['id'] ?>">
                        <i data-lucide="edit-3" style="width:14px;height:14px;"></i> Respond to this complaint
                    </button>
                </div>

                <div class="complaint-response-form reply-form"
                     data-complaint-id="<?= (int)$complaint['id'] ?>"
                     style="margin-top:0.75rem;">
                    <form action="<?= SITE_URL ?>/vendor/complaints"
                          method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <input type="hidden" name="complaint_id" value="<?= (int)$complaint['id'] ?>">
                        <div class="form-group" style="margin-bottom:0.75rem;">
                            <textarea name="response"
                                      class="form-control"
                                      placeholder="Provide a professional explanation or resolution..."
                                      rows="4"
                                      data-max-chars="800"
                                      required></textarea>
                            <div class="review-char-counter"
                                 data-counter-for="response-<?= (int)$complaint['id'] ?>"></div>
                        </div>
                        <div style="display:flex;gap:0.5rem;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Submit Response
                            </button>
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm complaint-response-toggle"
                                    data-complaint-id="<?= (int)$complaint['id'] ?>">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>