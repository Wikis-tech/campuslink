<?php
declare(strict_types=1);

class Security
{
    private static array $config = [];
    private static bool  $initialized = false;

    public static function init(array $config): void
    {
        if (self::$initialized) return;
        self::$config = $config;
        self::$initialized = true;
        self::startSecureSession($config['session']);
    }

    private static function startSecureSession(array $cfg): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        session_name($cfg['name']);

        // Cookie params
        $cookieParams = [
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $cfg['secure'],
            'httponly' => $cfg['httponly'],
            'samesite' => $cfg['samesite'],
        ];

        // Only set domain if not localhost
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if ($host !== 'localhost' && !str_starts_with($host, '127.')) {
            $cookieParams['domain'] = $host;
        }

        session_set_cookie_params($cookieParams);

        if (!session_start()) {
            error_log('[CL Security] Failed to start session');
            return;
        }

        // Regenerate ID on first request
        if (empty($_SESSION['_cl_init'])) {
            session_regenerate_id(true);
            $_SESSION['_cl_init'] = true;
        }

        // Session fingerprint
        $fp = hash('sha256',
            ($_SERVER['HTTP_USER_AGENT'] ?? '') .
            ($_SERVER['REMOTE_ADDR'] ?? '')
        );

        if (!empty($_SESSION['_cl_fp']) && $_SESSION['_cl_fp'] !== $fp) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['_cl_fp'] = $fp;

        // Idle timeout
        if (!empty($_SESSION['_cl_la'])) {
            $lifetime = $cfg['lifetime'] ?? 7200;
            if ((time() - $_SESSION['_cl_la']) > $lifetime) {
                session_unset();
                session_destroy();
                session_start();
            }
        }
        $_SESSION['_cl_la'] = time();
    }

    // ===== CSRF =====

    public static function generateCSRF(): string
    {
        $len = self::$config['security']['csrf_token_length'] ?? 32;
        if (empty($_SESSION['_cl_csrf'])) {
            $_SESSION['_cl_csrf'] = bin2hex(random_bytes($len));
        }
        return $_SESSION['_cl_csrf'];
    }

    public static function verifyCSRF(string $token): bool
    {
        return !empty($_SESSION['_cl_csrf']) &&
               hash_equals($_SESSION['_cl_csrf'], $token);
    }

    public static function requireCSRF(): void
    {
        $token = $_POST['csrf_token']
               ?? $_SERVER['HTTP_X_CSRF_TOKEN']
               ?? '';
        if (!self::verifyCSRF($token)) {
            self::jsonError('Invalid or missing CSRF token.', 403);
        }
    }

    // ===== INPUT =====

    public static function escape(mixed $v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function clean(string $s): string
    {
        return trim(strip_tags($s));
    }

    public static function cleanMultiline(string $s): string
    {
        return trim(strip_tags($s, '<br>'));
    }

    public static function int(mixed $v): int
    {
        return (int)$v;
    }

    // ===== PASSWORD =====

    public static function hashPassword(string $password): string
    {
        $cost = self::$config['security']['bcrypt_cost'] ?? 12;
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // ===== TOKENS =====

    public static function generateOTP(): string
    {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    // ===== RATE LIMITING =====

    public static function checkRateLimit(string $key, int $max, int $windowMin): bool
    {
        $sk = "_rl_{$key}";
        $tk = "_rl_{$key}_t";

        $now  = time();
        $wins = $windowMin * 60;

        if (empty($_SESSION[$tk]) || ($now - $_SESSION[$tk]) > $wins) {
            $_SESSION[$sk] = 0;
            $_SESSION[$tk] = $now;
        }

        $_SESSION[$sk] = ($_SESSION[$sk] ?? 0) + 1;
        return $_SESSION[$sk] <= $max;
    }

    // ===== FILE UPLOADS =====

    public static function validateUpload(
        array $file,
        array $allowedMime,
        int $maxSize
    ): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file.',
            ];
            return [
                'valid' => false,
                'error' => $errors[$file['error']] ?? 'Upload failed (code '.$file['error'].').',
            ];
        }

        if ($file['size'] > $maxSize) {
            return [
                'valid' => false,
                'error' => 'File exceeds maximum size of ' .
                           round($maxSize / 1024 / 1024, 1) . 'MB.',
            ];
        }

        if (!file_exists($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Uploaded file not found.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedMime, true)) {
            return [
                'valid' => false,
                'error' => "File type '{$mime}' is not allowed.",
            ];
        }

        return ['valid' => true, 'mime' => $mime];
    }

    public static function saveUpload(
        array $file,
        string $dir,
        string $prefix = ''
    ): string|false {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = rtrim($dir, '/') . '/' . $name;

        return move_uploaded_file($file['tmp_name'], $dest) ? $name : false;
    }

    public static function convertToWebP(
        string $src,
        string $dest,
        int $quality = 80
    ): bool {
        if (!function_exists('imagewebp')) return false;

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($src);
        $img  = match($mime) {
            'image/jpeg' => @imagecreatefromjpeg($src),
            'image/png'  => @imagecreatefrompng($src),
            default      => null,
        };

        if (!$img) return false;
        $result = imagewebp($img, $dest, $quality);
        imagedestroy($img);
        return $result;
    }

    // ===== VALIDATION =====

    public static function validateSchoolEmail(string $email, array $school): bool
    {
        return (bool)preg_match($school['email_pattern'], $email);
    }

    public static function validateMatric(string $matric, array $school): bool
    {
        return (bool)preg_match($school['matric_pattern'], $matric);
    }

    // ===== AUTH GUARDS =====

    public static function requireAuth(string $role = 'user'): array
    {
        $key = match($role) {
            'vendor' => 'vendor_id',
            'admin'  => 'admin_id',
            default  => 'user_id',
        };

        if (empty($_SESSION[$key])) {
            if (IS_AJAX) self::jsonError('Unauthorized. Please log in.', 401);
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header("Location: /pages/login?redirect={$redirect}");
            exit;
        }
        return $_SESSION;
    }

    public static function requireAdmin(): array
    {
        if (empty($_SESSION['admin_id'])) {
            if (IS_AJAX) self::jsonError('Unauthorized.', 401);
            header('Location: /admin/');
            exit;
        }
        return $_SESSION;
    }

    // ===== RESPONSE =====

    public static function jsonResponse(array $data, int $code = 200): never
    {
        http_response_code($code);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function jsonError(string $msg, int $code = 400): never
    {
        self::jsonResponse(['success' => false, 'error' => $msg], $code);
    }

    // ===== SECURITY HEADERS =====

    public static function setHeaders(): void
    {
        if (headers_sent()) return;
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
}