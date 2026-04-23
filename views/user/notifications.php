<?php defined('CAMPUSLINK') or die();
$pageTitle = 'Notifications';

if (!function_exists('lucide_icon')) {
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
}


// Lucide SVG paths per notification type
$notifIcons = [
    'payment'   => ['<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',                                                                           'ni-success', '#166534'],
    'review'    => ['<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',                                                       'ni-info',    '#1a56db'],
    'complaint' => ['<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>', 'ni-warning', '#92400e'],
    'approval'  => ['<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',                                                                                  'ni-success', '#166534'],
    'reminder'  => ['<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',                                                                                                     'ni-warning', '#92400e'],
    'warning'   => ['<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>', 'ni-warning', '#92400e'],
    'error'     => ['<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',                                                              'ni-error',   '#dc2626'],
    'success'   => ['<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',                                                                                  'ni-success', '#166534'],
    'system'    => ['<circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M20 12h2M2 12h2M18.66 18.66l-1.41-1.41M6.34 5.34L4.93 4.93"/><circle cx="12" cy="12" r="9"/>', 'ni-system', '#7c3aed'],
    'info'      => ['<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',                                                         'ni-info',    '#1a56db'],
    // default/bell fallback
    'default'   => ['<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',                                                                          'ni-info',    '#1a56db'],
];
?>
<style>
.notif-head{display:flex;align-items:center;justify-content:space-between;
flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;}
.notif-head h1{font-size:1.1rem;font-weight:900;color:#1e293b;margin:0;
               display:flex;align-items:center;gap:0.4rem;}
.notif-list{display:flex;flex-direction:column;gap:0.75rem;}
.notif-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;
padding:1rem 1.1rem;display:flex;gap:0.85rem;align-items:flex-start;
transition:transform 0.15s;}
.notif-card:hover{transform:translateX(3px);}
.notif-card.unread{border-left:3px solid #1a56db;background:#fafbff;}
.notif-icon{width:38px;height:38px;border-radius:10px;
display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ni-info{background:#eff6ff;}
.ni-success{background:#f0fdf4;}
.ni-warning{background:#fffbeb;}
.ni-error{background:#fef2f2;}
.ni-system{background:#f5f3ff;}
.notif-body{flex:1;min-width:0;}
.notif-title{font-size:0.85rem;font-weight:700;color:#1e293b;margin-bottom:0.2rem;}
.notif-msg{font-size:0.78rem;color:#64748b;line-height:1.5;}
.notif-time{font-size:0.7rem;color:#94a3b8;margin-top:0.3rem;}
.notif-unread-dot{width:8px;height:8px;border-radius:50%;
background:#1a56db;flex-shrink:0;margin-top:5px;}
.empty-state{text-align:center;padding:4rem 1rem;background:#fff;
border-radius:14px;border:1px solid #e2e8f0;color:#94a3b8;}
.empty-state .ei{margin-bottom:1rem;display:flex;justify-content:center;}
.empty-state h3{color:#475569;font-size:1rem;margin-bottom:0.5rem;}
</style>

<div class="notif-head">
    <!-- ðŸ”” â†’ Bell -->
    <h1>
        <?= lucide_icon('<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', 22, '#1e293b') ?>
        Notifications
    </h1>
    <span style="font-size:0.82rem;color:#64748b;">
        <?= count($notifications) ?> total
    </span>
</div>

<?php if (!empty($notifications)): ?>
<div class="notif-list">
    <?php foreach ($notifications as $n):
        [$iconPath, $iconClass, $iconColor] = $notifIcons[$n['type'] ?? 'default'] ?? $notifIcons['default'];
        $isUnread = !$n['is_read'];
    ?>
    <div class="notif-card <?= $isUnread ? 'unread' : '' ?>">
        <div class="notif-icon <?= $iconClass ?>">
            <?= lucide_icon($iconPath, 18, $iconColor) ?>
        </div>
        <div class="notif-body">
            <div class="notif-title"><?= e($n['title'] ?? 'Notification') ?></div>
            <div class="notif-msg"><?= e($n['message'] ?? '') ?></div>
            <div class="notif-time">
                <?= date('d M Y, g:ia', strtotime($n['created_at'])) ?>
            </div>
        </div>
        <?php if ($isUnread): ?>
        <div class="notif-unread-dot"></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <!-- ðŸ”” â†’ Bell -->
    <div class="ei">
        <?= lucide_icon('<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', 48, '#94a3b8') ?>
    </div>
    <h3>No notifications yet</h3>
    <p style="font-size:0.85rem;">
        Notifications will appear here when there is activity on your account.
    </p>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/notification-modal.php'; ?>
