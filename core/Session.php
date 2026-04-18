<?php
/**
 * CampusLink - Secure Session Handler
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Session
{
    private static bool $started = false;

    // ============================================================
    // Start secure session
    // ============================================================
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $secure   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $httpOnly = true;
        $sameSite = 'Strict';

        session_name(SESSION_NAME);

        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        ini_set('session.use_strict_mode',    '1');
        ini_set('session.use_only_cookies',   '1');
        ini_set('session.use_trans_sid',      '0');
        ini_set('session.cookie_httponly',    '1');
        ini_set('session.gc_maxlifetime',     (string)SESSION_LIFETIME);

        session_start();
        self::$started = true;

        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
            session_regenerate_id(true);
        } elseif (time() - $_SESSION['_created'] > 1800) {
            // Regenerate every 30 min
            $_SESSION['_created'] = time();
            session_regenerate_id(true);
        }

        // Check inactivity timeout
        self::checkInactivity();
    }

    // ============================================================
    // Check for inactivity timeout
    // ============================================================
    private static function checkInactivity(): void
    {
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > SESSION_INACTIVITY) {
                self::destroy();
                return;
            }
        }
        $_SESSION['_last_activity'] = time();
    }

    // ============================================================
    // Set a session value
    // ============================================================
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    // ============================================================
    // Get a session value
    // ============================================================
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    // ============================================================
    // Check if session key exists
    // ============================================================
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    // ============================================================
    // Remove a session value
    // ============================================================
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // ============================================================
    // Set a flash message (shown once)
    // ============================================================
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    // ============================================================
    // Get and clear flash message for a specific type
    // ============================================================
    public static function getFlash(string $type): array
    {
        $messages = $_SESSION['flash'][$type] ?? [];
        unset($_SESSION['flash'][$type]);
        return $messages;
    }

    // ============================================================
    // Get all flash messages and clear them
    // ============================================================
    public static function getAllFlash(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    // ============================================================
    // Check if flash exists
    // ============================================================
    public function hasFlash(string $type): bool
    {
        return isset($_SESSION['flash'][$type]);
    }

    // ============================================================
    // Destroy session (logout)
    // ============================================================
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();
        }

        self::$started = false;
    }

    // ============================================================
    // Regenerate session ID (after login)
    // ============================================================
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_created']       = time();
            $_SESSION['_last_activity'] = time();
        }
    }

    // ============================================================
    // Store user login data in session
    // ============================================================
    public function loginUser(array $user): void
    {
        self::regenerate();
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_name']      = $user['full_name'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['user_role']      = 'user';
        $_SESSION['login_time']     = time();
        $_SESSION['login_ip']       = getClientIP();
    }

    // ============================================================
    // Store vendor login data in session
    // ============================================================
    public function loginVendor(array $vendor): void
    {
        self::regenerate();
        $_SESSION['vendor_logged_in']    = true;
        $_SESSION['vendor_id']           = $vendor['id'];
        $_SESSION['vendor_name']         = $vendor['full_name'];
        $_SESSION['vendor_email']        = $vendor['email'] ?? $vendor['working_email'];
        $_SESSION['vendor_business']     = $vendor['business_name'];
        $_SESSION['vendor_type']         = $vendor['vendor_type'];
        $_SESSION['vendor_plan']         = $vendor['plan_type'] ?? 'basic';
        $_SESSION['vendor_status']       = $vendor['status'];
        $_SESSION['vendor_role']         = 'vendor';
        $_SESSION['login_time']          = time();
        $_SESSION['login_ip']            = getClientIP();
    }

    // ============================================================
    // Store admin login data in session
    // ============================================================
    public function loginAdmin(array $admin): void
    {
        self::regenerate();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        $_SESSION['admin_name']      = $admin['full_name'];
        $_SESSION['admin_email']     = $admin['email'];
        $_SESSION['admin_role']      = $admin['role'];
        $_SESSION['login_time']      = time();
        $_SESSION['login_ip']        = getClientIP();
    }

    // ============================================================
    // Logout user
    // ============================================================
    public function logoutUser(): void
    {
        unset(
            $_SESSION['user_logged_in'],
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_email'],
            $_SESSION['user_role'],
            $_SESSION['login_time'],
            $_SESSION['login_ip']
        );
    }

    // ============================================================
    // Logout vendor
    // ============================================================
    public function logoutVendor(): void
    {
        unset(
            $_SESSION['vendor_logged_in'],
            $_SESSION['vendor_id'],
            $_SESSION['vendor_name'],
            $_SESSION['vendor_email'],
            $_SESSION['vendor_business'],
            $_SESSION['vendor_type'],
            $_SESSION['vendor_plan'],
            $_SESSION['vendor_status'],
            $_SESSION['vendor_role'],
            $_SESSION['login_time'],
            $_SESSION['login_ip']
        );
    }
}