<?php
/**
 * CampusLink - XSS Sanitizer & Input Cleaner
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Sanitizer
{
    // ============================================================
    // Clean a single string input
    // ============================================================
    public static function clean(mixed $input): string
    {
        if (!is_string($input)) {
            $input = (string)$input;
        }

        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Strip tags
        $input = strip_tags($input);

        // Encode HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Trim whitespace
        $input = trim($input);

        return $input;
    }

    // ============================================================
    // Clean an array of inputs recursively
    // ============================================================
    public static function cleanArray(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            $cleanKey = self::clean((string)$key);
            if (is_array($value)) {
                $cleaned[$cleanKey] = self::cleanArray($value);
            } else {
                $cleaned[$cleanKey] = self::clean($value);
            }
        }
        return $cleaned;
    }

    // ============================================================
    // Clean for use in HTML output (alias of htmlspecialchars)
    // ============================================================
    public static function output(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ============================================================
    // Clean for database storage (without HTML encoding)
    // Use only WITH prepared statements
    // ============================================================
    public static function forDb(string $input): string
    {
        $input = str_replace("\0", '', $input);
        $input = strip_tags($input);
        return trim($input);
    }

    // ============================================================
    // Sanitize filename for uploads
    // ============================================================
    public static function filename(string $filename): string
    {
        // Get extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Remove anything that's not alphanumeric, dot, or dash
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');
        $name = strtolower(substr($name, 0, 50));

        return $name . '.' . $ext;
    }

    // ============================================================
    // Sanitize a slug
    // ============================================================
    public static function slug(string $str): string
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
        $str = preg_replace('/-+/', '-', $str);
        return trim($str, '-');
    }

    // ============================================================
    // Sanitize phone number
    // ============================================================
    public static function phone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Convert 0XXXXXXXXXX to +234XXXXXXXXX
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            $phone = '+234' . substr($phone, 1);
        }

        return $phone;
    }

    // ============================================================
    // Sanitize email
    // ============================================================
    public static function email(string $email): string
    {
        $email = strtolower(trim($email));
        return filter_var($email, FILTER_SANITIZE_EMAIL) ?: '';
    }

    // ============================================================
    // Sanitize integer
    // ============================================================
    public static function int(mixed $value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    // ============================================================
    // Sanitize float
    // ============================================================
    public static function float(mixed $value): float
    {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    // ============================================================
    // Sanitize URL
    // ============================================================
    public static function url(string $url): string
    {
        return filter_var(trim($url), FILTER_SANITIZE_URL) ?: '';
    }

    // ============================================================
    // Strip all HTML tags and encode output
    // ============================================================
    public static function text(string $input, int $maxLength = 0): string
    {
        $input = strip_tags($input);
        $input = trim($input);

        if ($maxLength > 0 && strlen($input) > $maxLength) {
            $input = substr($input, 0, $maxLength);
        }

        return $input;
    }

    // ============================================================
    // Sanitize textarea (allow newlines, strip tags)
    // ============================================================
    public static function textarea(string $input, int $maxLength = 0): string
    {
        $input = strip_tags($input);
        $input = trim($input);

        // Normalize line endings
        $input = str_replace(["\r\n", "\r"], "\n", $input);

        // Remove multiple consecutive blank lines
        $input = preg_replace('/\n{3,}/', "\n\n", $input);

        if ($maxLength > 0 && strlen($input) > $maxLength) {
            $input = substr($input, 0, $maxLength);
        }

        return $input;
    }
}