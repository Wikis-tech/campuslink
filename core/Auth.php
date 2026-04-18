<?php
/**
 * CampusLink - Authentication Helper
 * Static helper to check login states throughout the app.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Auth
{
    // ============================================================
    // Check if a regular user is logged in
    // ============================================================
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_logged_in']) && !empty($_SESSION['user_id']);
    }

    // ============================================================
    // Check if a vendor is logged in
    // ============================================================
    public static function isVendorLoggedIn(): bool
    {
        return !empty($_SESSION['vendor_logged_in']) && !empty($_SESSION['vendor_id']);
    }

    // ============================================================
    // Check if admin is logged in
    // ============================================================
    public static function isAdminLoggedIn(): bool
    {
        return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
    }

    // ============================================================
    // Check if ANY account type is logged in
    // ============================================================
    public static function isAnyLoggedIn(): bool
    {
        return self::isLoggedIn() || self::isVendorLoggedIn() || self::isAdminLoggedIn();
    }

    // ============================================================
    // Alias: isVendor() — checks vendor session
    // ============================================================
    public static function isVendor(): bool
    {
        return self::isVendorLoggedIn();
    }

    // ============================================================
    // Get logged-in user ID
    // ============================================================
    public static function userId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    // ============================================================
    // Alias for backward compatibility
    // ============================================================
    public static function id(): ?int
    {
        return self::userId();
    }

    // ============================================================
    // Get logged-in vendor ID
    // ============================================================
    public static function vendorId(): ?int
    {
        return $_SESSION['vendor_id'] ?? null;
    }

    // ============================================================
    // Get logged-in admin ID
    // ============================================================
    public static function adminId(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    // ============================================================
    // Get logged-in user name
    // ============================================================
    public static function userName(): string
    {
        return $_SESSION['user_name'] ?? $_SESSION['vendor_name'] ?? '';
    }

    // ============================================================
    // Get logged-in user email
    // ============================================================
    public static function userEmail(): string
    {
        return $_SESSION['user_email'] ?? $_SESSION['vendor_email'] ?? '';
    }

    // ============================================================
    // Get vendor type (student/community)
    // ============================================================
    public static function vendorType(): ?string
    {
        return $_SESSION['vendor_type'] ?? null;
    }

    // ============================================================
    // Get vendor plan
    // ============================================================
    public static function vendorPlan(): string
    {
        return $_SESSION['vendor_plan'] ?? 'basic';
    }

    // ============================================================
    // Get admin role
    // ============================================================
    public static function adminRole(): string
    {
        return $_SESSION['admin_role'] ?? 'moderator';
    }

    // ============================================================
    // Check admin has a specific role
    // ============================================================
    public static function adminHasRole(string $role): bool
    {
        $roles = ['superadmin' => 3, 'admin' => 2, 'moderator' => 1];
        $currentLevel = $roles[$_SESSION['admin_role'] ?? 'moderator'] ?? 0;
        $requiredLevel = $roles[$role] ?? 0;
        return $currentLevel >= $requiredLevel;
    }

    // ============================================================
    // Hash a password securely
    // ============================================================
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    // ============================================================
    // Verify a password against its hash
    // ============================================================
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // ============================================================
    // Check if password needs rehashing
    // ============================================================
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    // ============================================================
    // Update vendor plan in session after upgrade/downgrade
    // ============================================================
    public static function updateVendorSession(array $vendor): void
    {
        $_SESSION['vendor_plan']   = $vendor['plan_type'] ?? 'basic';
        $_SESSION['vendor_status'] = $vendor['status'];
    }
}