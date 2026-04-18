<?php
/**
 * CampusLink — Admin Authentication Guard
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

class AdminAuth {

    private static string $sessionKey = 'admin_logged_in';
    private static string $idKey      = 'admin_id';
    private static string $nameKey    = 'admin_name';
    private static string $roleKey    = 'admin_role';

    // ── Login ─────────────────────────────────────────────────────
    public static function login(array $admin): void {
        Session::regenerate();
        $_SESSION[self::$sessionKey] = true;
        $_SESSION[self::$idKey]      = $admin['id'];
        $_SESSION[self::$nameKey]    = $admin['full_name'];
        $_SESSION[self::$roleKey]    = $admin['role'] ?? 'admin';
    }

    // ── Logout ────────────────────────────────────────────────────
    public static function logout(): void {
        unset(
            $_SESSION[self::$sessionKey],
            $_SESSION[self::$idKey],
            $_SESSION[self::$nameKey],
            $_SESSION[self::$roleKey]
        );
        Session::regenerate();
    }

    // ── Checks ────────────────────────────────────────────────────
    public static function isLoggedIn(): bool {
        return !empty($_SESSION[self::$sessionKey]);
    }

    public static function isSuperAdmin(): bool {
        return self::isLoggedIn()
            && ($_SESSION[self::$roleKey] ?? '') === 'super_admin';
    }

    // ── Getters ───────────────────────────────────────────────────
    public static function id(): int {
        return (int)($_SESSION[self::$idKey] ?? 0);
    }

    public static function name(): string {
        return $_SESSION[self::$nameKey] ?? 'Admin';
    }

    public static function role(): string {
        return $_SESSION[self::$roleKey] ?? 'admin';
    }

    // ── Guard ─────────────────────────────────────────────────────
    public static function guard(bool $requireSuperAdmin = false): void {
        if (!self::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to access the admin panel.');
            header('Location: ' . SITE_URL . '/admin/login');
            exit;
        }
        if ($requireSuperAdmin && !self::isSuperAdmin()) {
            http_response_code(403);
            die('Access denied. Super admin only.');
        }
    }

    // ── Rate limiting ─────────────────────────────────────────────
    public static function checkLoginRateLimit(string $ip): bool {
        $key      = 'admin_login_' . md5($ip);
        $attempts = $_SESSION[$key] ?? 0;
        return $attempts < 5;
    }

    public static function recordFailedLogin(string $ip): void {
        $key = 'admin_login_' . md5($ip);
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
    }

    public static function clearLoginAttempts(string $ip): void {
        $key = 'admin_login_' . md5($ip);
        unset($_SESSION[$key]);
    }
}