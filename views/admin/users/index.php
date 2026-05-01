<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Students'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">🎓 Student Accounts</h1>
        <div class="admin-page-sub"><?= number_format($pag['total']) ?> registered students</div>
    </div>
</div>

<form method="GET" class="admin-filter-bar">
    <input type="text" name="q" placeholder="Name, email, matric..."
           value="<?= e($search) ?>">
    <select name="status">
        <option value="">All Statuses</option>
        <option value="active"    <?= $status==='active'    ?'selected':'' ?>>Active</option>
        <option value="suspended" <?= $status==='suspended' ?'selected':'' ?>>Suspended</option>
        <option value="pending"   <?= $status==='pending'   ?'selected':'' ?>>Pending</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= SITE_URL ?>/admin/users" class="btn btn-outline-primary btn-sm">Reset</a>
</form>

<div class="admin-card">
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Matric No</th>
                    <th>Level · Dept</th>
                    <th>Status</th>
                    <th>Reviews</th>
                    <th>Complaints</th>
                    <th>Saved</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div style="font-weight:700;color:var(--text-primary);
                                    font-size:var(--font-size-sm);">
                            <?= e($u['full_name']) ?>
                        </div>
                        <div style="font-size:0.68rem;color:var(--text-muted);">
                            <?= e($u['school_email']) ?>
                        </div>
                    </td>
                    <td style="font-family:monospace;font-size:0.75rem;">
                        <?= e($u['matric_number']) ?>
                    </td>
                    <td style="font-size:0.75rem;">
                        <?= e($u['level'] ?? '—') ?> · <?= e(truncate($u['department'] ?? '', 20)) ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $u['status']==='active'?'active':'suspended' ?>"
                              style="font-size:0.65rem;">
                            <?= ucfirst($u['status']) ?>
                        </span>
                    </td>
                    <td style="text-align:center;font-size:var(--font-size-sm);">
                        <?= (int)$u['review_count'] ?>
                    </td>
                    <td style="text-align:center;font-size:var(--font-size-sm);">
                        <?= (int)$u['complaint_count'] ?>
                    </td>
                    <td style="text-align:center;font-size:var(--font-size-sm);">
                        <?= (int)$u['saved_count'] ?>
                    </td>
                    <td style="font-size:0.7rem;color:var(--text-muted);">
                        <?= date('d M Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                        <div class="admin-action-row">
                            <?php if ($u['status'] === 'active'): ?>
                            <form action="<?= SITE_URL ?>/admin/users/suspend/<?= (int)$u['id'] ?>"
                                  method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <input type="hidden" name="reason"     value="Policy violation.">
                                <button type="submit"
                                        class="btn btn-sm"
                                        style="background:var(--warning-amber);color:#fff;font-size:0.65rem;"
                                        onclick="return confirm('Suspend this student?')">
                                    Suspend
                                </button>
                            </form>
                            <?php else: ?>
                            <form action="<?= SITE_URL ?>/admin/users/reinstate/<?= (int)$u['id'] ?>"
                                  method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <button type="submit"
                                        class="btn btn-sm btn-primary"
                                        style="font-size:0.65rem;"
                                        onclick="return confirm('Reinstate this student?')">
                                    Reinstate
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pagination = $pag; require VIEWS_PATH . '/partials/pagination.php'; ?>