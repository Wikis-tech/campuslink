<?php defined('CAMPUSLINK') or die(); ?>

<div class="dashboard-page-header">
    <div>
        <h1 class="dashboard-page-title">Notifications</h1>
        <p class="dashboard-page-subtitle">
            <?= (int)($unreadCount ?? 0) ?> unread
        </p>
    </div>
    <?php if (!empty($notifications)): ?>
    <button class="btn btn-outline-primary btn-sm" id="markAllReadBtn">
        <i data-lucide="check" style="width:14px;height:14px;"></i> Mark All as Read
    </button>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
<div class="dash-card">
    <div class="dash-card-body">
        <div class="empty-state">
            <div class="empty-icon"><i data-lucide="bell"></i></div>
            <h3>No notifications</h3>
            <p>You'll be notified about reviews, complaints, subscription reminders, and more.</p>
        </div>
    </div>
</div>
<?php else: ?>

<div class="dash-card">
    <div class="notification-list">
        <?php
        $typeIcons = [
            'payment'   => 'credit-card',
            'review'    => 'star',
            'complaint' => 'clipboard',
            'approval'  => 'check-circle',
            'reminder'  => 'clock',
            'warning'   => 'alert-triangle',
            'error'     => 'x-circle',
            'success'   => 'check-circle',
            'system'    => 'settings',
            'info'      => 'info',
        ];
        foreach ($notifications as $notif):
            $icon = $typeIcons[$notif['type']] ?? 'info';
        ?>
        <a href="<?= e($notif['link'] ?? '#') ?>"
           class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>"
           data-notif-id="<?= (int)$notif['id'] ?>">
            <span class="notification-dot"></span>
            <div class="notification-icon">
                <i data-lucide="<?= $icon ?>"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title"><?= e($notif['title']) ?></div>
                <div class="notification-message"><?= e($notif['message']) ?></div>
            </div>
            <div class="notification-time" data-time="<?= e($notif['created_at']) ?>">
                <?= date('d M', strtotime($notif['created_at'])) ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>

<?php require __DIR__ . '/../partials/notification-modal.php'; ?>

<script>
document.getElementById('markAllReadBtn')?.addEventListener('click', async () => {
    try {
        const res  = await fetch('<?= SITE_URL ?>/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest' },
            body: JSON.stringify({ csrf_token: document.querySelector('meta[name="csrf-token"]')?.content }),
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(el => {
                el.classList.replace('unread', 'read');
            });
            document.querySelector('.dashboard-page-subtitle').textContent = '0 unread';
            CampusLink.toast('All marked as read.', 'success');
        }
    } catch { CampusLink.toast('Could not update.', 'error'); }
});
</script>