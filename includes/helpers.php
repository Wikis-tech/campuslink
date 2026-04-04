<?php
declare(strict_types=1);

/**
 * Campuslink — Global Helper Functions
 */

/**
 * Redirect to a URL
 */
function redirect(string $url, int $code = 302): never
{
    http_response_code($code);
    header("Location: {$url}");
    exit;
}

/**
 * Return JSON and exit
 */
function jsonOut(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Sanitize output for HTML
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Format naira amount
 */
function formatNaira(int $kobo): string
{
    return '₦' . number_format($kobo / 100, 0, '.', ',');
}

/**
 * Format date
 */
function formatDate(string $datetime, string $format = 'M j, Y'): string
{
    return date($format, strtotime($datetime));
}

/**
 * Time ago
 */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    return match(true) {
        $diff < 60      => 'Just now',
        $diff < 3600    => floor($diff / 60) . 'm ago',
        $diff < 86400   => floor($diff / 3600) . 'h ago',
        $diff < 604800  => floor($diff / 86400) . 'd ago',
        $diff < 2592000 => floor($diff / 604800) . 'w ago',
        default         => date('M j, Y', strtotime($datetime)),
    };
}

/**
 * Generate star HTML
 */
function stars(float $rating, bool $returnCount = false): string
{
    $full  = floor($rating);
    $html  = str_repeat('★', (int)$full);
    $html .= str_repeat('☆', 5 - (int)$full);
    return $returnCount ? $html . " ({$rating})" : $html;
}

/**
 * Get current logged-in user session data
 */
function currentUser(): array
{
    return [
        'id'   => $_SESSION['user_id']   ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['role']      ?? null,
    ];
}

/**
 * Check if user is logged in
 */
function isLoggedIn(string $role = 'user'): bool
{
    $key = match($role) {
        'vendor' => 'vendor_id',
        'admin'  => 'admin_id',
        default  => 'user_id',
    };
    return !empty($_SESSION[$key]);
}

/**
 * Get vendor WebP image URL
 */
function vendorImg(string|null $path, int $width = 400, int $quality = 80): string
{
    if (!$path) return '/assets/images/placeholder-vendor.webp';
    return "/api/image-webp?path=" . urlencode($path) . "&w={$width}&q={$quality}";
}

/**
 * Get base URL
 */
function baseUrl(string $path = ''): string
{
    return rtrim(APP_CONFIG['app']['url'], '/') . '/' . ltrim($path, '/');
}

/**
 * Flash message system
 */
function flash(string $key, string $message = '', string $type = 'info'): array|null
{
    if ($message) {
        $_SESSION['_flash'][$key] = ['msg' => $message, 'type' => $type];
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

/**
 * Render a PHP partial/component
 */
function partial(string $name, array $vars = []): void
{
    extract($vars);
    $file = __DIR__ . "/../partials/{$name}.php";
    if (file_exists($file)) include $file;
}

/**
 * Get pagination data
 */
function paginate(int $total, int $perPage = 12, string $pageParam = 'page'): array
{
    $current  = max(1, (int)($_GET[$pageParam] ?? 1));
    $pages    = (int)ceil($total / $perPage);
    $offset   = ($current - 1) * $perPage;
    return [
        'current' => $current,
        'pages'   => $pages,
        'offset'  => $offset,
        'per'     => $perPage,
        'total'   => $total,
        'prev'    => $current > 1 ? $current - 1 : null,
        'next'    => $current < $pages ? $current + 1 : null,
    ];
}

/**
 * Validate matric number format: UAT23/03/04/2782
 */
function validateMatric(string $matric): bool
{
    return (bool)preg_match('/^[Uu][Aa][Tt]\d{2}\/\d{2}\/\d{2}\/\d{4}$/', $matric);
}

/**
 * Parse matric number into components
 */
function parseMatric(string $matric): array
{
    preg_match('/^[Uu][Aa][Tt](\d{2})\/(\d{2})\/(\d{2})\/(\d{4})$/', $matric, $m);
    return [
        'year'       => $m[1] ?? null,
        'faculty'    => $m[2] ?? null,
        'department' => $m[3] ?? null,
        'number'     => $m[4] ?? null,
    ];
}

/**
 * Get CSRF token HTML input
 */
function csrfField(): string
{
    $token = Security::generateCSRF();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 120): string
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '…';
}

/**
 * Generate random reference number
 */
function generateRef(string $prefix = 'CL'): string
{
    return $prefix . '_' . date('YmdHis') . '_' . strtoupper(bin2hex(random_bytes(4)));
}