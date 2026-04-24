<?php
/**
 * CampusLink - In-App Notification Manager
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Notification
{
    // Notification types
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_PAYMENT = 'payment';
    const TYPE_EXPIRY_REMINDER = 'expiry_reminder';
    const TYPE_EXPIRY = 'expiry';
    const TYPE_APPROVAL = 'approval';

    private static ?Database $db = null;

    private static function db(): Database
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    // ============================================================
    // Send notification to a user
    // ============================================================
    public static function sendToUser(
        int    $userId,
        string $title,
        string $message,
        string $type = 'info',
        string $link = ''
    ): bool {
        return self::insert('user', $userId, $title, $message, $type, $link);
    }

    // ============================================================
    // Send notification to a vendor
    // ============================================================
    public static function sendToVendor(
        int    $vendorId,
        string $title,
        string $message,
        string $type = 'info',
        string $link = ''
    ): bool {
        return self::insert('vendor', $vendorId, $title, $message, $type, $link);
    }

    // ============================================================
    // Send notification to admin
    // ============================================================
    public static function sendToAdmin(
        string $title,
        string $message,
        string $type = 'info',
        string $link = ''
    ): bool {
        return self::insert('admin', 0, $title, $message, $type, $link);
    }

    // ============================================================
    // Get unread notifications for a user
    // ============================================================
    public static function getUnread(string $recipientType, int $recipientId): array
    {
        return self::db()->fetchAll(
            "SELECT * FROM notifications 
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0
             ORDER BY created_at DESC LIMIT 20",
            [$recipientType, $recipientId]
        );
    }

    // ============================================================
    // Get all notifications for a user (paginated)
    // ============================================================
    public static function getAll(
        string $recipientType,
        int    $recipientId,
        int    $limit = 20,
        int    $offset = 0
    ): array {
        return self::db()->fetchAll(
            "SELECT * FROM notifications 
             WHERE recipient_type = ? AND recipient_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$recipientType, $recipientId, $limit, $offset]
        );
    }

    // ============================================================
    // Count unread notifications
    // ============================================================
    public static function countUnread(string $recipientType, int $recipientId): int
    {
        return (int)self::db()->fetchColumn(
            "SELECT COUNT(*) FROM notifications 
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0",
            [$recipientType, $recipientId]
        );
    }

    // ============================================================
    // Mark a notification as read
    // ============================================================
    public static function markRead(int $notificationId, int $recipientId): bool
    {
        $rows = self::db()->execute(
            "UPDATE notifications SET is_read = 1, read_at = NOW() 
             WHERE id = ? AND recipient_id = ?",
            [$notificationId, $recipientId]
        );
        return $rows > 0;
    }

    // ============================================================
    // Mark all notifications as read
    // ============================================================
    public static function markAllRead(string $recipientType, int $recipientId): void
    {
        self::db()->execute(
            "UPDATE notifications SET is_read = 1, read_at = NOW() 
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0",
            [$recipientType, $recipientId]
        );
    }

    // ============================================================
    // Delete old read notifications (cleanup)
    // ============================================================
    public static function deleteOld(int $daysOld = 60): int
    {
        return self::db()->execute(
            "DELETE FROM notifications 
             WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysOld]
        );
    }

    // ============================================================
    // Internal: Insert notification record
    // ============================================================
    private static function insert(
        string $recipientType,
        int    $recipientId,
        string $title,
        string $message,
        string $type,
        string $link
    ): bool {
        try {
            self::db()->execute(
                "INSERT INTO notifications 
                 (recipient_type, recipient_id, title, message, type, link, is_read, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 0, NOW())",
                [$recipientType, $recipientId, $title, $message, $type, $link]
            );
            return true;
        } catch (Exception $e) {
            Logger::log('NOTIFICATION_ERROR', $e->getMessage());
            return false;
        }
    }
}