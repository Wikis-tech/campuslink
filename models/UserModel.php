<?php
/**
 * CampusLink - User Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class UserModel extends Model
{
    protected string $table = 'users';

    // ============================================================
    // Create new user
    // ============================================================
    public function createUser(array $data): string|false
    {
        return $this->create([
            'full_name'          => $data['full_name'],
            'school_email'       => strtolower($data['school_email']),
            'personal_email'     => strtolower($data['personal_email'] ?? ''),
            'phone'              => $data['phone'],
            'level'              => $data['level'] ?? '',
            'department'         => $data['department'] ?? '',
            'password'           => Auth::hashPassword($data['password']),
            'email_verified'     => 0,
            'phone_verified'     => 0,
            'status'             => 'inactive',
            'email_verify_token' => $data['email_verify_token'] ?? null,
            'token_expires_at'   => $data['token_expires_at'] ?? null,
            'terms_accepted'     => 1,
            'terms_version'      => TERMS_VERSION,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Find user by school email
    // ============================================================
    public function findBySchoolEmail(string $email): ?array
    {
        return $this->findBy('school_email', strtolower($email));
    }

    // ============================================================
    // Find user by personal email
    // ============================================================
    public function findByPersonalEmail(string $email): ?array
    {
        return $this->findBy('personal_email', strtolower($email));
    }

    // ============================================================
    // Find user by either email
    // ============================================================
    public function findByAnyEmail(string $email): ?array
    {
        $email = strtolower($email);
        return $this->db->fetchOne(
            "SELECT * FROM users 
             WHERE school_email = ? OR personal_email = ? 
             LIMIT 1",
            [$email, $email]
        );
    }

    // ============================================================
    // Find user by phone
    // ============================================================
    public function findByPhone(string $phone): ?array
    {
        return $this->findBy('phone', $phone);
    }

    // ============================================================
    // Find user by email verification token
    // ============================================================
    public function findByVerifyToken(string $token): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM users 
             WHERE email_verify_token = ? 
             AND token_expires_at > NOW() 
             LIMIT 1",
            [$token]
        );
    }

    // ============================================================
    // Find user by password reset token
    // ============================================================
    public function findByResetToken(string $token): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM users 
             WHERE reset_token = ? 
             AND reset_token_expires > NOW() 
             LIMIT 1",
            [$token]
        );
    }

    // ============================================================
    // Verify email address
    // ============================================================
    public function verifyEmail(int $userId): bool
    {
        return $this->update($userId, [
            'email_verified'     => 1,
            'status'             => 'active',
            'email_verify_token' => null,
            'token_expires_at'   => null,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Mark phone as verified
    // ============================================================
    public function verifyPhone(int $userId): bool
    {
        return $this->update($userId, [
            'phone_verified' => 1,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Set password reset token
    // ============================================================
    public function setResetToken(int $userId, string $token): bool
    {
        return $this->update($userId, [
            'reset_token'         => $token,
            'reset_token_expires' => date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Update password
    // ============================================================
    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, [
            'password'            => Auth::hashPassword($newPassword),
            'reset_token'         => null,
            'reset_token_expires' => null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Update user profile
    // ============================================================
    public function updateProfile(int $userId, array $data): bool
    {
        $allowed = ['full_name', 'personal_email', 'phone', 'level', 'department'];
        $update  = array_intersect_key($data, array_flip($allowed));
        $update['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($userId, $update);
    }

    // ============================================================
    // Update last login
    // ============================================================
    public function updateLastLogin(int $userId): void
    {
        $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip'    => getClientIP(),
        ]);
    }

    // ============================================================
    // Blacklist a user
    // ============================================================
    public function blacklist(int $userId, string $reason): bool
    {
        return $this->update($userId, [
            'status'             => 'blacklisted',
            'blacklist_reason'   => $reason,
            'blacklisted_at'     => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Check if user is blacklisted
    // ============================================================
    public function isBlacklisted(int $userId): bool
    {
        $user = $this->find($userId);
        return $user && $user['status'] === 'blacklisted';
    }

    // ============================================================
    // Get active users count
    // ============================================================
    public function countActive(): int
    {
        return $this->count("status = 'active'");
    }

    // ============================================================
    // Get all users for admin panel (paginated)
    // ============================================================
    public function getAllPaginated(
        int    $page = 1,
        int    $perPage = 25,
        string $search = '',
        string $status = ''
    ): array {
        $conditions = [];
        $params     = [];

        if (!empty($search)) {
            $conditions[] = "(full_name LIKE ? OR school_email LIKE ? OR phone LIKE ?)";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
        }

        if (!empty($status)) {
            $conditions[] = "status = ?";
            $params[]      = $status;
        }

        $where = $conditions ? implode(' AND ', $conditions) : '';
        return $this->paginate($page, $perPage, $where, $params, 'created_at DESC');
    }

    // ============================================================
    // Check email availability
    // ============================================================
    public function emailAvailable(string $email, int $excludeId = 0): bool
    {
        $email = strtolower($email);
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users 
             WHERE (school_email = ? OR personal_email = ?) 
             AND id != ?",
            [$email, $email, $excludeId]
        );
        return (int)$exists === 0;
    }

    // ============================================================
    // Check phone availability
    // ============================================================
    public function phoneAvailable(string $phone, int $excludeId = 0): bool
    {
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE phone = ? AND id != ?",
            [$phone, $excludeId]
        );
        return (int)$exists === 0;
    }
}