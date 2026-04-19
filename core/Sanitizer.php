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
}