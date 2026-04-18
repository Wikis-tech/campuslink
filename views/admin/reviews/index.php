<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Review Moderation'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">⭐ Review Moderation</h1>
        <div class="admin-page-sub"><?= number_format($pag['total']) ?> reviews</div>
    </div>
</div>

<!-- Status Tabs -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$label): ?>
    <a href="?status=<?= $val ?>"
       class="btn btn-sm <?= $status===$val ? 'btn-primary' : 'btn-outline-primary' ?>"
       style="font-size:0.75rem;">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="admin-card">
    <div style="display:flex;flex-direction:column;gap:0;">
        <?php if (empty($reviews)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:var(--font-size-sm);">
            ✅ No <?= $status ?> reviews.
        </div>
        <?php else: ?>
        <?php foreach ($reviews as $review): ?>
        <div style="padding:1.1rem 1.25rem;border-bottom:1px solid var(--divider);">
            <div style="display:flex;justify-content:space-between;
                        align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <div style="display:flex;align-items:center;gap:0.75rem;
                                margin-bottom:0.35rem;">
                        <div class="review-stars">
                            <?php for($i=1;$i<=5;$i++): ?>
                            <span class="review-star <?= $i>$review['rating']?'empty':'' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span style="font-size:0.7rem;color:var(--text-muted);">
                            by <?= e($review['user_name'] ?? 'Unknown') ?>
                            <?= !empty($review['user_level']) ? '· '.$review['user_level'] : '' ?>
                        </span>
                    </div>
                    <div style="font-size:var(--font-size-sm);color:var(--text-secondary);
                                line-height:1.6;margin-bottom:0.35rem;">
                        <?= e($review['review']) ?>
                    </div>
                    <div style="font-size:0.7rem;color:var(--text-muted);">
                        Vendor:
                        <a href="<?= SITE_URL ?>/vendor/<?= e($review['vendor_slug']) ?>"
                           target="_blank"
                           style="color:var(--primary);font-weight:700;">
                            <?= e($review['business_name']) ?>
                        </a>
                        · <?= date('d M Y', strtotime($review['created_at'])) ?>
                    </div>
                </div>

                <?php if ($review['status'] === 'pending'): ?>
                <div class="admin-action-row" style="flex-shrink:0;">
                    <form action="<?= SITE_URL ?>/admin/reviews/approve/<?= (int)$review['id'] ?>"
                          method="POST">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <button type="submit"
                                class="btn btn-sm btn-primary"
                                style="background:var(--accent-green);font-size:0.7rem;">
                            ✅ Approve
                        </button>
                    </form>
                    <form action="<?= SITE_URL ?>/admin/reviews/reject/<?= (int)$review['id'] ?>"
                          method="POST"
                          style="display:flex;gap:0.35rem;align-items:center;">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                        <input type="text" name="reason"
                               class="form-control"
                               placeholder="Reason..."
                               style="width:130px;font-size:0.7rem;padding:0.3rem 0.5rem;">
                        <button type="submit"
                                class="btn btn-sm"
                                style="background:var(--danger);color:#fff;font-size:0.7rem;">
                            ❌ Reject
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <span class="badge badge-<?= $review['status']==='approved'?'active':'suspended' ?>"
                      style="font-size:0.65rem;align-self:flex-start;">
                    <?= ucfirst($review['status']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php $pagination = $pag; require VIEWS_PATH . '/partials/pagination.php'; ?>