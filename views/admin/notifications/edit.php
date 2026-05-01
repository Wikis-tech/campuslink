<?php defined('CAMPUSLINK') or die(); $pageTitle = 'Edit Notification'; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">✏️ Edit Notification</h1>
        <div class="admin-page-sub">Modify the notification details</div>
    </div>
    <a href="<?= SITE_URL ?>/admin/notifications" class="btn btn-outline-primary">
        ← Back to Notifications
    </a>
</div>

<div class="admin-card" style="max-width:600px;">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

        <div class="form-group">
            <label>Notification Type</label>
            <select name="type" class="form-control">
                <option value="info" <?= $notification['type'] === 'info' ? 'selected' : '' ?>>ℹ️ Info</option>
                <option value="warning" <?= $notification['type'] === 'warning' ? 'selected' : '' ?>>⚠️ Warning</option>
                <option value="success" <?= $notification['type'] === 'success' ? 'selected' : '' ?>>✅ Success</option>
                <option value="reminder" <?= $notification['type'] === 'reminder' ? 'selected' : '' ?>>⏰ Reminder</option>
                <option value="system" <?= $notification['type'] === 'system' ? 'selected' : '' ?>>⚙️ System</option>
            </select>
        </div>

        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" class="form-control"
                   value="<?= e($notification['title']) ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label>Message *</label>
            <textarea name="message" class="form-control" rows="4"
                      required maxlength="500"><?= e($notification['message']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Link (optional)</label>
            <input type="text" name="link" class="form-control"
                   value="<?= e($notification['link'] ?? '') ?>" placeholder="e.g. /vendor/dashboard">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Update Notification</button>
            <a href="<?= SITE_URL ?>/admin/notifications" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>