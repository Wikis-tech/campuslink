<?php
/**
 * CampusLink - Notification Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class NotificationModel extends Model
{
    protected string $table = 'notifications';

    // ============================================================
    // Create notification
    // ============================================================
    public function createNotification(array $data): string|false
    {
        return $this->create([
            'recipient_type' => $data['recipient_type'],
            'recipient_id'   => (int)$data['recipient_id'],
            'title'          => $data['title'],
            'message'        => $data['message'],
            'type'           => $data['type'] ?? 'info',
            'link'           => $data['link'] ?? '',
            'is_read'        => 0,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Get unread for recipient
    // ============================================================
    public function getUnread(string $type, int $id, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications 
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0
             ORDER BY created_at DESC LIMIT ?",
            [$type, $id, $limit]
        );
    }

    // ============================================================
    // Get all for recipient paginated
    // ============================================================
    public function getAllForRecipient(
        string $type,
        int    $id,
        int    $page = 1,
        int    $perPage = 20
    ): array {
        $total  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE recipient_type = ? AND recipient_id = ?",
            [$type, $id]
        );
        $offset = ($page - 1) * $perPage;
        $data   = $this->db->fetchAll(
            "SELECT * FROM notifications 
             WHERE recipient_type = ? AND recipient_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$type, $id, $perPage, $offset]
        );
        return [
            'data'         => $data,
            'total'        => $total,
            'current_page' => $page,
            'total_pages'  => (int)ceil($total / $perPage),
        ];
    }

    // ============================================================
    // Count unread
    // ============================================================
    public function countUnread(string $type, int $id): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications 
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0",
            [$type, $id]
        );
    }

    // ============================================================
    // Mark single notification read
    // ============================================================
    public function markRead(int $notifId, int $recipientId): bool
    {
        return (bool)$this->db->execute(
            "UPDATE notifications SET is_read = 1, read_at = NOW() 
             WHERE id = ? AND recipient_id = ?",
            [$notifId, $recipientId]
        );
    }

    // ============================================================
    // Mark all read for recipient
    // ============================================================
    public function markAllRead(string $type, int $id): void
    {
        $this->db->execute(
            "UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0",
            [$type, $id]
        );
    }

    // ============================================================
    // Delete old read notifications
    // ============================================================
    public function cleanup(int $daysOld = 60): int
    {
        return $this->db->execute(
            "DELETE FROM notifications 
             WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysOld]
        );
    }
}