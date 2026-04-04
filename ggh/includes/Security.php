<?php
declare(strict_types=1);

class Security
{
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
        self::startSecureSession($config['session']);
    }

    private static function startSecureSession(array $cfg): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        session_name($cfg['name']);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $cfg['secure'],
            'httponly' => $cfg['httponly'],
            'samesite' => $cfg['samesite'],
        ]);
        session_start();
        if (empty($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
        $fingerprint = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . ($_SERVER['REMOTE_ADDR'] ?? ''));
        if (!empty($_SESSION['_fingerprint']) && $_SESSION['_fingerprint'] !== $fingerprint) {
            session_destroy(); session_start();
        }
        $_SESSION['_fingerprint'] = $fingerprint;
        $lifetime = $cfg['lifetime'];
        if (!empty($_SESSION['_last_active']) && (time() - $_SESSION['_last_active']) > $lifetime) {
            session_unset(); session_destroy(); session_start();
        }
        $_SESSION['_last_active'] = time();
    }

    public static function generateCSRF(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(self::$config['security']['csrf_token_length'] ?? 32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRF(string $token): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireCSRF(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!self::verifyCSRF($token)) self::jsonError('Invalid or missing CSRF token.', 403);
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function clean(string $input): string
    {
        return trim(strip_tags($input));
    }

    public static function hashPassword(string $password): string
    {
        $cost = self::$config['security']['bcrypt_cost'] ?? 12;
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function generateOTP(): string
    {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function checkRateLimit(string $key, int $maxAttempts, int $windowMinutes): bool
    {
        $attempts    = $_SESSION["rl_{$key}"] ?? 0;
        $windowStart = $_SESSION["rl_{$key}_time"] ?? 0;
        if (time() - $windowStart > ($windowMinutes * 60)) {
            $_SESSION["rl_{$key}"] = 0;
            $_SESSION["rl_{$key}_time"] = time();
        }
        $_SESSION["rl_{$key}"]++;
        return $_SESSION["rl_{$key}"] <= $maxAttempts;
    }

    public static function validateUpload(array $file, array $allowedMime, int $maxSize): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return ['valid' => false, 'error' => 'Upload error code: ' . $file['error']];
        if ($file['size'] > $maxSize) return ['valid' => false, 'error' => 'File exceeds maximum allowed size.'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMime, true)) return ['valid' => false, 'error' => 'File type not allowed.'];
        return ['valid' => true, 'mime' => $mime];
    }

    public static function saveUpload(array $file, string $destDir, string $prefix = ''): string|false
    {
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $destPath = rtrim($destDir, '/') . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;
        return $filename;
    }

    public static function requireAuth(string $role = 'user'): array
    {
        $sessionKey = match($role) {
            'vendor' => 'vendor_id',
            'admin'  => 'admin_id',
            default  => 'user_id',
        };
        if (empty($_SESSION[$sessionKey])) {
            header('Location: /pages/login.html?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        return $_SESSION;
    }

    public static function jsonResponse(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function jsonError(string $message, int $code = 400): never
    {
        self::jsonResponse(['success' => false, 'error' => $message], $code);
    }

    public static function setHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://unpkg.com https://js.paystack.co; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.paystack.co;");
    }
}