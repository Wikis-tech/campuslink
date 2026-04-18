<?php
/**
 * CampusLink - Blacklist Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class BlacklistModel extends Model
{
    protected string $table = 'blacklist';

    // ============================================================
    // Add to blacklist
    // ============================================================
    public function add(array $data): string|false
    {
        return $this->create([
            'user_id'      => (int)($data['user_id'] ?? 0),
            'vendor_id'    => (int)($data['vendor_id'] ?? 0),
            'type'         => $data['type'],   // 'user' or 'vendor'
            'reason'       => $data['reason'],
            'blacklisted_by'=> (int)$data['blacklisted_by'],
            'email'        => strtolower($data['email'] ?? ''),
            'phone'        => $data['phone'] ?? '',
            'ip_address'   => $data['ip_address'] ?? getClientIP(),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Check if email is blacklisted
    // ============================================================
    public function isEmailBlacklisted(string $email): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM blacklist WHERE email = ? AND is_active = 1",
            [strtolower($email)]
        );
    }

    // ============================================================
    // Check if phone is blacklisted
    // ============================================================
    public function isPhoneBlacklisted(string $phone): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM blacklist WHERE phone = ? AND is_active = 1",
            [$phone]
        );
    }

    // ============================================================
    // Check if IP is blacklisted
    // ============================================================
    public function isIPBlacklisted(string $ip): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM blacklist WHERE ip_address = ? AND is_active = 1",
            [$ip]
        );
    }

    // ============================================================
    // Remove from blacklist
    // ============================================================
    public function remove(int $blacklistId): bool
    {
        return (bool)$this->db->execute(
            "UPDATE blacklist SET is_active = 0 WHERE id = ?",
            [$blacklistId]
        );
    }

    // ============================================================
    // Get all blacklisted entries for admin
    // ============================================================
    public function getAll(string $type = ''): array
    {
        $sql    = "SELECT * FROM blacklist WHERE is_active = 1";
        $params = [];

        if ($type) {
            $sql    .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
}