<?php
/**
 * CampusLink - Login Log Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class LoginLogModel extends Model
{
    protected string $table = 'login_logs';

    // ============================================================
    // Log a login attempt
    // ============================================================
    public function log(array $data): void
    {
        try {
            $this->create([
                'event'      => $data['event'],
                'identifier' => $data['identifier'],
                'user_type'  => $data['user_type'] ?? 'user',
                'user_id'    => (int)($data['user_id'] ?? 0),
                'success'    => (int)($data['success'] ?? 0),
                'ip_address' => $data['ip_address'] ?? getClientIP(),
                'user_agent' => substr($data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            // Silent fail — never break app due to logging
        }
    }

    // ============================================================
    // Get recent login logs for admin
    // ============================================================
    public function getRecent(int $limit = 100): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM login_logs ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    // ============================================================
    // Get failed login attempts for an IP
    // ============================================================
    public function getFailedByIP(string $ip, int $minutes = 30): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM login_logs 
             WHERE ip_address = ? AND success = 0 
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$ip, $minutes]
        );
    }

    // ============================================================
    // Get paginated logs for admin
    // ============================================================
    public function getPaginated(int $page = 1, int $perPage = 50): array
    {
        $total  = $this->count();
        $offset = ($page - 1) * $perPage;
        $data   = $this->db->fetchAll(
            "SELECT * FROM login_logs ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return [
            'data'         => $data,
            'total'        => $total,
            'current_page' => $page,
            'total_pages'  => (int)ceil($total / $perPage),
        ];
    }

    // ============================================================
    // Clean old logs (keep 90 days)
    // ============================================================
    public function cleanup(int $days = 90): int
    {
        return $this->db->execute(
            "DELETE FROM login_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
    }
}