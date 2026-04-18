<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Send Notifications'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">📢 Send Notifications</h1>
        <div class="admin-page-sub">Broadcast messages to students or vendors</div>
    </div>
</div>

<div style="max-width:640px;">
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">Compose Notification</div>
        </div>
        <div class="admin-card-body">
            <form action="<?= SITE_URL ?>/admin/notifications/send"
                  method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

                <div class="form-group">
                    <label class="form-label" for="recipient_type">
                        Send To <span class="required">*</span>
                    </label>
                    <select id="recipient_type" name="recipient_type"
                            class="form-control" required
                            onchange="toggleRecipientId(this.value)">
                        <option value="">Select recipients</option>
                        <option value="all_users">🎓 All Students</option>
                        <option value="all_vendors">🏪 All Active Vendors</option>
                        <option value="single_user">👤 Single Student (by ID)</option>
                        <option value="single_vendor">🏪 Single Vendor (by ID)</option>
                    </select>
                </div>

                <!-- Single recipient ID (shown conditionally) -->
                <div class="form-group" id="recipientIdGroup" style="display:none;">
                    <label class="form-label" for="recipient_id">
                        Recipient ID <span class="required">*</span>
                    </label>
                    <input type="number" id="recipient_id" name="recipient_id"
                           class="form-control"
                           placeholder="Enter user or vendor database ID">
                    <span class="form-hint">
                        Find the ID from the vendors or users list.
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notif_type">
                        Notification Type <span class="required">*</span>
                    </label>
                    <select id="notif_type" name="notif_type" class="form-control" required>
                        <option value="info">ℹ️ Info</option>
                        <option value="success">✅ Success</option>
                        <option value="warning">⚠️ Warning</option>
                        <option value="error">❌ Alert</option>
                        <option value="system">⚙️ System</option>
                        <option value="reminder">⏰ Reminder</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">
                        Title <span class="required">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           class="form-control"
                           placeholder="Notification title"
                           required data-max-chars="100">
                </div>

                <div class="form-group">
                    <label class="form-label" for="message">
                        Message <span class="required">*</span>
                    </label>
                    <textarea id="message" name="message"
                              class="form-control" rows="4"
                              placeholder="Notification message body..."
                              required data-max-chars="500"></textarea>
                    <div class="review-char-counter" data-counter-for="message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="link">
                        Link (Optional)
                    </label>
                    <input type="text" id="link" name="link"
                           class="form-control"
                           placeholder="e.g. /vendor/subscription or /browse">
                    <span class="form-hint">
                        Relative URL — users will be taken here when they click the notification.
                    </span>
                </div>

                <div class="disclaimer-box" style="margin-bottom:1.5rem;">
                    <span class="disclaimer-icon">⚠️</span>
                    <div class="disclaimer-text">
                        <strong>Broadcast notifications cannot be undone.</strong>
                        Sending to "All Students" or "All Vendors" will notify
                        every active account. Use sparingly.
                    </div>
                </div>

                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Send this notification? This cannot be undone.')">
                        📤 Send Notification
                    </button>
                    <a href="<?= SITE_URL ?>/admin/dashboard"
                       class="btn btn-outline-primary">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Quick Templates -->
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-title">📋 Quick Templates</div>
        </div>
        <div class="admin-card-body" style="display:flex;flex-direction:column;gap:0.75rem;">
            <?php
            $templates = [
                [
                    'label'   => '🔔 Subscription Expiry Reminder',
                    'type'    => 'reminder',
                    'title'   => 'Your Subscription Expires Soon!',
                    'message' => 'Your CampusLink subscription is expiring within 7 days. Renew now to stay visible to students and keep receiving contact requests.',
                    'link'    => '/vendor/subscription',
                ],
                [
                    'label'   => '🎉 New Feature Announcement',
                    'type'    => 'info',
                    'title'   => 'New Features on CampusLink!',
                    'message' => 'We\'ve added new features to improve your CampusLink experience. Log in to explore what\'s new.',
                    'link'    => '/vendor/dashboard',
                ],
                [
                    'label'   => '⚠️ Platform Maintenance Notice',
                    'type'    => 'warning',
                    'title'   => 'Scheduled Maintenance',
                    'message' => 'CampusLink will undergo scheduled maintenance on [DATE] from [TIME]. The platform may be temporarily unavailable during this period.',
                    'link'    => '',
                ],
                [
                    'label'   => '📢 Semester Reminder (Students)',
                    'type'    => 'info',
                    'title'   => 'New Semester — Discover Campus Vendors!',
                    'message' => 'A new semester is here! Browse verified student and community vendors on CampusLink for all your campus service needs.',
                    'link'    => '/browse',
                ],
            ];
            foreach ($templates as $tpl):
            ?>
            <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    style="text-align:left;justify-content:flex-start;"
                    onclick="fillTemplate(
                        '<?= e(addslashes($tpl['type'])) ?>',
                        '<?= e(addslashes($tpl['title'])) ?>',
                        '<?= e(addslashes($tpl['message'])) ?>',
                        '<?= e(addslashes($tpl['link'])) ?>'
                    )">
                <?= $tpl['label'] ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
function toggleRecipientId(val) {
    const group = document.getElementById('recipientIdGroup');
    const input = document.getElementById('recipient_id');
    const show  = val === 'single_user' || val === 'single_vendor';
    group.style.display = show ? 'block' : 'none';
    input.required      = show;
}

function fillTemplate(type, title, message, link) {
    document.getElementById('notif_type').value = type;
    document.getElementById('title').value       = title;
    document.getElementById('message').value     = message;
    document.getElementById('link').value        = link;
    // Scroll to form
    document.getElementById('title').scrollIntoView({ behavior:'smooth', block:'center' });
    document.getElementById('title').focus();
}
</script>