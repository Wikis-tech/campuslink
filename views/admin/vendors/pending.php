<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Pending Approvals'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">⏳ Pending Vendor Approvals</h1>
        <div class="admin-page-sub"><?= count($vendors) ?> vendor(s) awaiting review</div>
    </div>
</div>

<?php if (empty($vendors)): ?>
<div class="admin-card">
    <div class="admin-card-body" style="text-align:center;padding:3rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:0.5rem;">
            All caught up!
        </h3>
        <p style="color:var(--text-muted);font-size:var(--font-size-sm);">
            No vendor applications are waiting for review.
        </p>
    </div>
</div>
<?php else: ?>

<div class="admin-card">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Business</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Contact</th>
                    <th>Applied</th>
                    <th>ID Docs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vendors as $v): ?>
                <tr>
                    <td>
                        <div style="font-weight:700;color:var(--text-primary);">
                            <?= e($v['business_name']) ?>
                        </div>
                        <div style="font-size:0.7rem;color:var(--text-muted);">
                            <?= e($v['full_name']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $v['vendor_type']==='student' ? 'badge-verified' : 'badge-featured' ?>">
                            <?= ucfirst($v['vendor_type']) ?>
                        </span>
                    </td>
                    <td style="font-size:var(--font-size-xs);">
                        <?= e($v['category_name'] ?? '—') ?>
                    </td>
                    <td style="font-size:0.7rem;">
                        <?= e($v['school_email'] ?? $v['working_email'] ?? '') ?><br>
                        <?= e($v['phone']) ?>
                    </td>
                    <td style="font-size:0.7rem;color:var(--text-muted);">
                        <?= date('d M Y', strtotime($v['created_at'])) ?>
                    </td>
                    <td>
                        <?php if (!empty($v['id_document'])): ?>
                        <a href="<?= SITE_URL ?>/assets/uploads/documents/<?= e($v['id_document']) ?>"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary"
                           style="font-size:0.65rem;padding:0.2rem 0.5rem;">
                            📄 View ID
                        </a>
                        <?php else: ?>
                        <span style="font-size:0.7rem;color:var(--text-muted);">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="admin-action-row">
                            <a href="<?= SITE_URL ?>/admin/vendors/detail/<?= (int)$v['id'] ?>"
                               class="btn btn-sm btn-outline-primary"
                               style="font-size:0.7rem;">
                                🔍 Review
                            </a>
                            <form action="<?= SITE_URL ?>/admin/vendors/approve/<?= (int)$v['id'] ?>"
                                  method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <button type="submit"
                                        class="btn btn-sm btn-primary"
                                        style="font-size:0.7rem;background:var(--accent-green);"
                                        onclick="return confirm('Approve <?= e(addslashes($v['business_name'])) ?>?')">
                                    ✅ Approve
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>