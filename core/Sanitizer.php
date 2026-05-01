<?php
/**
 * CampusLink — Sanitizer Class
 * Handles input cleaning and security filtering
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

class Sanitizer {
    /**
     * Clean a string by trimming and stripping tags
     */
    public static function clean(mixed $value): string {
        if ($value === null) return '';
        if (is_array($value)) {
            return ''; 
        }
        return trim(strip_tags((string)$value));
    }

    /**
     * Clean and validate text input with max length
     * Removes all tags and truncates to maxLength
     */
    public static function text(mixed $value, int $maxLength = 255): string {
        if ($value === null) return '';
        if (is_array($value)) {
            return '';
        }
        $cleaned = trim(strip_tags((string)$value));
        return substr($cleaned, 0, $maxLength);
    }

    /**
     * Clean and validate email input
     * Returns empty string if invalid
     */
    public static function email(mixed $value): string {
        if ($value === null) return '';
        if (is_array($value)) {
            return '';
        }
        $email = trim(strtolower((string)$value));
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '';
        }
        return $email;
    }

    /**
     * Clean textarea input, allowing basic formatting
     */
    public static function textarea(mixed $value, int $maxLength = 1000): string {
        if ($value === null) return '';
        if (is_array($value)) {
            return '';
        }
        $cleaned = trim(strip_tags((string)$value, '<p><br><strong><em><u><ol><ul><li><a>'));
        return substr($cleaned, 0, $maxLength);
    }

    /**
     * Clean phone number - removes non-numeric characters
     * Keeps + for international format
     */
    public static function phone(mixed $value): string {
        if ($value === null) return '';
        if (is_array($value)) {
            return '';
        }
        $phone = preg_replace('/[^0-9+\-\s]/', '', (string)$value);
        $phone = trim($phone);
        // Remove spaces and hyphens for storage, but keep format valid
        $phone = str_replace([' ', '-'], '', $phone);
        return substr($phone, 0, 20);
    }

    /**
     * Clean URL - removes dangerous characters
     */
    public static function url(mixed $value): string {
        if ($value === null) return '';
        if (is_array($value)) {
            return '';
        }
        $url = trim((string)$value);
        return filter_var($url, FILTER_SANITIZE_URL) ?: '';
    }

    /**
     * Clean numeric value to integer
     */
    public static function integer(mixed $value): int {
        if ($value === null) return 0;
        return (int)$value;
    }

    /**
     * Clean numeric value to float
     */
    public static function float(mixed $value): float {
        if ($value === null) return 0.0;
        return (float)$value;
    }

    /**
     * Clean boolean value
     */
    public static function boolean(mixed $value): bool {
        if ($value === null) return false;
        if (is_bool($value)) return $value;
        $value = strtolower((string)$value);
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}