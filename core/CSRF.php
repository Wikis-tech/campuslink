<?php
/**
 * CampusLink - CSRF Token Manager
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class CSRF
{
    private static string $sessionKey = '_csrf_tokens';

    // ============================================================
    // Generate a new CSRF token
    // ============================================================
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
        $expiry = time() + CSRF_TOKEN_EXPIRY;

        if (!isset($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = [];
        }

        // Store token with expiry
        $_SESSION[self::$sessionKey][$token] = $expiry;

        // Clean expired tokens
        self::cleanExpired();

        return $token;
    }

    // ============================================================
    // Validate a CSRF token (one-time use)
    // ============================================================
    public static function validate(string $token): bool
    {
        if (empty($token)) return false;

        $tokens = $_SESSION[self::$sessionKey] ?? [];

        if (!isset($tokens[$token])) return false;

        // Check expiry
        if (time() > $tokens[$token]) {
            unset($_SESSION[self::$sessionKey][$token]);
            return false;
        }

        // One-time use — remove after validation
        unset($_SESSION[self::$sessionKey][$token]);

        return true;
    }

    // ============================================================
    // Get current token or generate one (for forms)
    // ============================================================
    public static function token(): string
    {
        return self::generate();
    }

    // ============================================================
    // Output hidden CSRF input field
    // ============================================================
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    // ============================================================
    // Output CSRF meta tag (for AJAX)
    // ============================================================
    public static function meta(): string
    {
        $token = self::generate();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }

    // ============================================================
    // Clean expired tokens from session
    // ============================================================
    private static function cleanExpired(): void
    {
        if (!isset($_SESSION[self::$sessionKey])) return;

        $now = time();
        foreach ($_SESSION[self::$sessionKey] as $token => $expiry) {
            if ($now > $expiry) {
                unset($_SESSION[self::$sessionKey][$token]);
            }
        }

        // Keep only the last 20 tokens max
        if (count($_SESSION[self::$sessionKey]) > 20) {
            $_SESSION[self::$sessionKey] = array_slice(
                $_SESSION[self::$sessionKey], -20, 20, true
            );
        }
    }
}