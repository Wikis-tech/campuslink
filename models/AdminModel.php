<?php
/**
 * CampusLink - Admin User Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class AdminModel extends Model
{
    protected string $table = 'admin_users';

    // ============================================================
    // Find admin by email
    // ============================================================
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', strtolower($email));
    }

    // ============================================================
    // Verify admin password
    // ============================================================
    public function verifyPassword(string $password, string $hash): bool
    {
        return Auth::verifyPassword($password, $hash);
    }

    // ============================================================
    // Create admin account
    // ============================================================
    public function createAdmin(array $data): string|false
    {
        return $this->create([
            'full_name'  => $data['full_name'],
            'email'      => strtolower($data['email']),
            'password'   => Auth::hashPassword($data['password']),
            'role'       => $data['role'] ?? 'moderator',
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Update admin last login
    // ============================================================
    public function updateLastLogin(int $adminId): void
    {
        $this->update($adminId, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip'    => getClientIP(),
        ]);
    }

    // ============================================================
    // Get all admins
    // ============================================================
    public function getAllAdmins(): array
    {
        return $this->db->fetchAll(
            "SELECT id, full_name, email, role, is_active, last_login, created_at
             FROM admin_users ORDER BY created_at ASC"
        );
    }

    // ============================================================
    // Update admin password
    // ============================================================
    public function updatePassword(int $adminId, string $newPassword): bool
    {
        return $this->update($adminId, [
            'password'   => Auth::hashPassword($newPassword),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Deactivate admin
    // ============================================================
    public function deactivate(int $adminId): bool
    {
        return $this->update($adminId, ['is_active' => 0]);
    }

    // ============================================================
    // Check admin is active
    // ============================================================
    public function isActive(int $adminId): bool
    {
        $admin = $this->find($adminId);
        return $admin && (int)$admin['is_active'] === 1;
    }
}