<?php
/**
 * CampusLink - Rate Limiter (Session & DB based)
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class RateLimiter
{
    // ============================================================
    // Check if IP is locked out from login
    // ============================================================
    public static function isLockedOut(string $identifier): bool
    {
        $key      = '_rate_' . md5($identifier);
        $attempts = $_SESSION[$key]['attempts'] ?? 0;
        $lastTime = $_SESSION[$key]['last_attempt'] ?? 0;

        // Reset if lockout period has passed
        if ($attempts >= LOGIN_MAX_ATTEMPTS) {
            if ((time() - $lastTime) >= LOGIN_LOCKOUT_TIME) {
                unset($_SESSION[$key]);
                return false;
            }
            return true;
        }

        return false;
    }

    // ============================================================
    // Record a failed login attempt
    // ============================================================
    public static function recordFailedAttempt(string $identifier): void
    {
        $key = '_rate_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => 0];
        }

        $_SESSION[$key]['attempts']++;
        $_SESSION[$key]['last_attempt'] = time();
    }

    // ============================================================
    // Reset attempts after successful login
    // ============================================================
    public static function resetAttempts(string $identifier): void
    {
        $key = '_rate_' . md5($identifier);
        unset($_SESSION[$key]);
    }

    // ============================================================
    // Get remaining attempts
    // ============================================================
    public static function remainingAttempts(string $identifier): int
    {
        $key      = '_rate_' . md5($identifier);
        $attempts = $_SESSION[$key]['attempts'] ?? 0;
        return max(0, LOGIN_MAX_ATTEMPTS - $attempts);
    }

    // ============================================================
    // Get lockout time remaining in seconds
    // ============================================================
    public static function lockoutRemaining(string $identifier): int
    {
        $key      = '_rate_' . md5($identifier);
        $lastTime = $_SESSION[$key]['last_attempt'] ?? 0;
        $elapsed  = time() - $lastTime;
        return max(0, LOGIN_LOCKOUT_TIME - $elapsed);
    }

    // ============================================================
    // Format lockout time for display
    // ============================================================
    public static function lockoutRemainingFormatted(string $identifier): string
    {
        $seconds = self::lockoutRemaining($identifier);
        if ($seconds >= 60) {
            return ceil($seconds / 60) . ' minute(s)';
        }
        return $seconds . ' second(s)';
    }

    // ============================================================
    // General rate limit check (for any action)
    // ============================================================
    public static function check(
        string $action,
        string $identifier,
        int    $maxAttempts,
        int    $windowSeconds
    ): bool {
        $key = '_rl_' . $action . '_' . md5($identifier);

        $data = $_SESSION[$key] ?? ['count' => 0, 'window_start' => time()];

        // Reset window if expired
        if ((time() - $data['window_start']) >= $windowSeconds) {
            $data = ['count' => 0, 'window_start' => time()];
        }

        if ($data['count'] >= $maxAttempts) {
            return false; // Rate limited
        }

        // Increment counter
        $data['count']++;
        $_SESSION[$key] = $data;

        return true; // Allowed
    }
}