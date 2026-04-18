<?php
/**
 * CampusLink — Global Helper Functions
 * These functions are available everywhere in the app
 */
defined('CAMPUSLINK') or die('Direct access not permitted.');

// ─────────────────────────────────────────────────────────────────────
// STRING HELPERS
// ─────────────────────────────────────────────────────────────────────

/**
 * Safely escape output to prevent XSS
 * Use this on EVERY variable you echo in a view
 *
 * echo e($user['name']);
 */
function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Truncate a string to a max length and append ellipsis
 *
 * echo truncate($description, 100);
 */
function truncate(string $text, int $length = 100, string $append = '…'): string {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $append;
}

/**
 * Convert newlines to <br> tags safely
 *
 * echo nl2br_safe($vendor['description']);
 */
function nl2br_safe(string $text): string {
    return nl2br(e($text));
}

/**
 * Generate a URL-safe slug from a string
 *
 * slug('Hello World! 123') → 'hello-world-123'
 */
function slug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Generate a unique slug by checking the database
 *
 * $slug = uniqueSlug('My Business', 'vendors', 'slug');
 */
function uniqueSlug(string $text, string $table, string $column = 'slug', int $excludeId = 0): string {
    $db   = DB::getInstance();
    $base = slug($text);
    $try  = $base;
    $i    = 2;

    while (true) {
        $where  = $excludeId
            ? "{$column} = ? AND id != ?"
            : "{$column} = ?";
        $params = $excludeId
            ? [$try, $excludeId]
            : [$try];

        if (!$db->exists($table, $where, $params)) {
            return $try;
        }
        $try = $base . '-' . $i++;
    }
}

/**
 * Generate a random alphanumeric string
 *
 * $token = randomString(32);
 */
function randomString(int $length = 32): string {
    return bin2hex(random_bytes((int)ceil($length / 2)));
}

/**
 * Generate a random numeric OTP
 *
 * $otp = generateOtp(6); → '482931'
 */
function generateOtp(int $digits = 6): string {
    $min = (int)pow(10, $digits - 1);
    $max = (int)pow(10, $digits) - 1;
    return (string)random_int($min, $max);
}

/**
 * Generate a unique complaint ticket ID
 *
 * generateTicketId() → 'CL-A1B2C3D4'
 */
function generateTicketId(): string {
    return 'CL-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

// ─────────────────────────────────────────────────────────────────────
// NUMBER & CURRENCY HELPERS
// ─────────────────────────────────────────────────────────────────────

/**
 * Format a number as Nigerian Naira
 * Paystack stores amounts in kobo (100 kobo = ₦1)
 *
 * formatNaira(500000)  → '₦5,000.00'  (kobo)
 * formatNaira(5000, false) → '₦5,000.00'  (naira already)
 */
function formatNaira(int|float $amount, bool $fromKobo = true): string {
    $naira = $fromKobo ? $amount / 100 : $amount;
    return '₦' . number_format($naira, 2);
}

/**
 * Format a large number nicely
 *
 * formatNumber(1500) → '1,500'
 */
function formatNumber(int|float $number): string {
    return number_format($number);
}

// ─────────────────────────────────────────────────────────────────────
// DATE & TIME HELPERS
// ─────────────────────────────────────────────────────────────────────

/**
 * Convert a datetime string to a human-readable "time ago" format
 *
 * timeAgo('2024-01-01 12:00:00') → '3 months ago'
 */
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year'    . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month'   . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'     . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'    . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute'  . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * Format a date for display
 *
 * formatDate('2024-01-15') → '15 Jan 2024'
 */
function formatDate(string $datetime, string $format = 'd M Y'): string {
    return date($format, strtotime($datetime));
}

/**
 * Format a datetime for display
 *
 * formatDateTime('2024-01-15 14:30:00') → '15 Jan 2024 at 2:30pm'
 */
function formatDateTime(string $datetime): string {
    return date('d M Y \a\t g:ia', strtotime($datetime));
}

/**
 * Get days remaining until a future date
 * Returns negative number if date has passed
 *
 * daysUntil('2025-12-31') → 45
 */
function daysUntil(string $futureDate): int {
    $now    = new DateTime('today');
    $target = new DateTime(date('Y-m-d', strtotime($futureDate)));
    $diff   = $now->diff($target);
    return $diff->invert ? -$diff->days : $diff->days;
}

// ─────────────────────────────────────────────────────────────────────
// VALIDATION HELPERS
// ─────────────────────────────────────────────────────────────────────

/**
 * Validate a Nigerian phone number
 * Accepts: 08012345678, +2348012345678, 2348012345678
 */
function isValidPhone(string $phone): bool {
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
    return (bool)preg_match('/^(\+?234|0)[789][01]\d{8}$/', $cleaned);
}

/**
 * Validate an email address
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if a string meets minimum length
 */
function minLength(string $value, int $min): bool {
    return mb_strlen(trim($value)) >= $min;
}

/**
 * Check if a string does not exceed maximum length
 */
function maxLength(string $value, int $max): bool {
    return mb_strlen(trim($value)) <= $max;
}

/**
 * Sanitize a string — trim and strip tags
 */
function clean(string $value): string {
    return trim(strip_tags($value));
}

/**
 * Get a POST value safely, trimmed and cleaned
 *
 * $name = post('full_name');
 * $name = post('full_name', 'Default');
 */
function post(string $key, mixed $default = ''): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

/**
 * Get a GET value safely
 *
 * $page = get('page', 1);
 */
function get(string $key, mixed $default = ''): mixed {
    return $_GET[$key] ?? $default;
}

// ─────────────────────────────────────────────────────────────────────
// FILE HELPERS
// ─────────────────────────────────────────────────────────────────────

/**
 * Handle a file upload safely
 * Returns the saved filename or throws on error
 *
 * $filename = uploadFile($_FILES['logo'], 'logos', ['image/jpeg','image/png']);
 */
function uploadFile(
    array  $file,
    string $subfolder,
    array  $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'],
    int    $maxMb = 2
): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed. Error code: ' . $file['error']);
    }

    $maxBytes = $maxMb * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        throw new Exception("File is too large. Maximum size is {$maxMb}MB.");
    }

    // Verify MIME type from file content, not extension
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedTypes, true)) {
        throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedTypes));
    }

    // Generate a safe filename
    $ext      = match($mimeType) {
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'image/gif'       => 'gif',
        'application/pdf' => 'pdf',
        default           => 'bin',
    };
    $filename = randomString(20) . '_' . time() . '.' . $ext;

    $uploadDir = UPLOADS_PATH . '/' . trim($subfolder, '/');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to save uploaded file.');
    }

    return $filename;
}

/**
 * Delete an uploaded file safely
 */
function deleteUpload(string $filename, string $subfolder): bool {
    if (empty($filename)) return false;
    $path = UPLOADS_PATH . '/' . trim($subfolder, '/') . '/' . $filename;
    if (file_exists($path)) {
        return unlink($path);
    }
    return false;
}

// ─────────────────────────────────────────────────────────────────────
// STAR RATING HELPER
// ─────────────────────────────────────────────────────────────────────

/**
 * Render star rating HTML
 *
 * echo stars(4.5);
 */
function stars(float $rating, bool $showNumber = false): string {
    $full  = floor($rating);
    $half  = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html  = '<span class="stars-display">';

    for ($i = 0; $i < $full;  $i++) $html .= '<span class="star full">★</span>';
    if ($half)                        $html .= '<span class="star half">★</span>';
    for ($i = 0; $i < $empty; $i++) $html .= '<span class="star empty">☆</span>';

    if ($showNumber) {
        $html .= ' <span class="star-number">' . number_format($rating, 1) . '</span>';
    }

    $html .= '</span>';
    return $html;
}

// ─────────────────────────────────────────────────────────────────────
// PAGINATION HELPER
// ─────────────────────────────────────────────────────────────────────

/**
 * Build pagination array from total count
 *
 * $pag = paginate(250, 20);
 * // $pag['current_page'], $pag['total_pages'], $pag['offset'] etc.
 */
function paginate(int $total, int $perPage = 20): array {
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page       = min($page, $totalPages);

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $page,
        'total_pages'  => $totalPages,
        'offset'       => ($page - 1) * $perPage,
        'has_prev'     => $page > 1,
        'has_next'     => $page < $totalPages,
        'prev_page'    => $page - 1,
        'next_page'    => $page + 1,
    ];
}

// ─────────────────────────────────────────────────────────────────────
// NOTIFICATION TYPE ICON HELPER
// ─────────────────────────────────────────────────────────────────────

/**
 * Get the emoji icon for a notification type
 */
function notifIcon(string $type): string {
    return match($type) {
        'payment'  => '💳',
        'review'   => '⭐',
        'complaint'=> '📋',
        'approval' => '✅',
        'reminder' => '⏰',
        'warning'  => '⚠️',
        'error'    => '❌',
        'success'  => '✅',
        'system'   => '⚙️',
        'info'     => 'ℹ️',
        default    => '🔔',
    };
}

// ─────────────────────────────────────────────────────────────────────
// SUBSCRIPTION STATUS HELPER
// ─────────────────────────────────────────────────────────────────────

/**
 * Get subscription status info for a vendor
 * Returns array with status, days_left, badge class
 */
function subscriptionStatus(array $vendor): array {
    $expiry   = $vendor['subscription_expiry'] ?? null;
    $status   = $vendor['subscription_status'] ?? 'none';

    if (!$expiry || $status !== 'active') {
        return [
            'status'     => 'none',
            'days_left'  => 0,
            'badge'      => 'badge-inactive',
            'label'      => 'No Subscription',
        ];
    }

    $daysLeft = daysUntil($expiry);

    if ($daysLeft < 0) {
        return [
            'status'    => 'expired',
            'days_left' => 0,
            'badge'     => 'badge-suspended',
            'label'     => 'Expired',
        ];
    }

    if ($daysLeft <= 7) {
        return [
            'status'    => 'expiring',
            'days_left' => $daysLeft,
            'badge'     => 'badge-pending',
            'label'     => "Expiring in {$daysLeft} days",
        ];
    }

    return [
        'status'    => 'active',
        'days_left' => $daysLeft,
        'badge'     => 'badge-active',
        'label'     => "Active — {$daysLeft} days left",
    ];
}