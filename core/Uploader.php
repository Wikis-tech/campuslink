<?php
/**
 * CampusLink - Secure File Upload Handler
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Uploader
{
    private array  $allowedMimes;
    private int    $maxSize;
    private string $uploadDir;
    private string $error = '';

    // ============================================================
    // Constructor
    // ============================================================
    public function __construct(
        string $uploadDir,
        array  $allowedMimes = [],
        int    $maxSize = 0
    ) {
        $this->uploadDir    = rtrim($uploadDir, '/') . '/';
        $this->allowedMimes = !empty($allowedMimes)
            ? $allowedMimes
            : unserialize(ALLOWED_IMAGE_TYPES);
        $this->maxSize      = $maxSize > 0 ? $maxSize : UPLOAD_MAX_SIZE;
    }

    // ============================================================
    // Upload a file
    // Returns filename on success, false on failure
    // ============================================================
    public function upload(array $file, string $prefix = ''): string|false
    {
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->error = $this->getUploadError($file['error'] ?? UPLOAD_ERR_NO_FILE);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->error = 'File size exceeds the maximum allowed size of ' . $this->formatSize($this->maxSize) . '.';
            return false;
        }

        // Validate MIME type using finfo (not just extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedMimes)) {
            $this->error = 'Invalid file type. Allowed types: ' . $this->getMimeLabels() . '.';
            return false;
        }

        // Validate file extension matches MIME
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!$this->validateExtension($ext, $mimeType)) {
            $this->error = 'File extension does not match the file content.';
            return false;
        }

        // Extra check: scan for PHP code in images (image injection)
        if ($this->containsPhpCode($file['tmp_name'])) {
            $this->error = 'Invalid file content detected.';
            return false;
        }

        // Ensure upload directory exists
        if (!$this->ensureDirectory($this->uploadDir)) {
            $this->error = 'Upload directory is not accessible.';
            return false;
        }

        // Generate unique filename
        $newFilename = $this->generateFilename($prefix, $ext);
        $destination = $this->uploadDir . $newFilename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->error = 'Failed to save the uploaded file. Please try again.';
            return false;
        }

        // Set proper file permissions
        chmod($destination, 0644);

        return $newFilename;
    }

    // ============================================================
    // Upload multiple files
    // ============================================================
    public function uploadMultiple(array $files, string $prefix = ''): array
    {
        $results = [];

        foreach ($files['name'] as $index => $name) {
            $file = [
                'name'     => $files['name'][$index],
                'type'     => $files['type'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error'    => $files['error'][$index],
                'size'     => $files['size'][$index],
            ];

            $result = $this->upload($file, $prefix . '_' . $index);
            $results[] = [
                'success'  => $result !== false,
                'filename' => $result ?: null,
                'error'    => $result !== false ? null : $this->error,
            ];
        }

        return $results;
    }

    // ============================================================
    // Delete an uploaded file
    // ============================================================
    public function delete(string $filename): bool
    {
        if (empty($filename)) return false;

        // Security: prevent path traversal
        $filename = basename($filename);
        $filePath = $this->uploadDir . $filename;

        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    // ============================================================
    // Get last error message
    // ============================================================
    public function getError(): string
    {
        return $this->error;
    }

    // ============================================================
    // Check if there was an error
    // ============================================================
    public function hasError(): bool
    {
        return !empty($this->error);
    }

    // ============================================================
    // Generate a unique safe filename
    // ============================================================
    private function generateFilename(string $prefix, string $ext): string
    {
        $prefix = $prefix ? Sanitizer::slug($prefix) . '_' : '';
        $unique = bin2hex(random_bytes(16));
        $timestamp = time();
        return $prefix . $timestamp . '_' . $unique . '.' . $ext;
    }

    // ============================================================
    // Ensure upload directory exists and is writable
    // ============================================================
    private function ensureDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }

            // Create .htaccess to block PHP execution
            $htaccess = $dir . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess,
                    "Options -Indexes\n" .
                    "<FilesMatch \"\\.php$\">\n" .
                    "    Order allow,deny\n" .
                    "    Deny from all\n" .
                    "</FilesMatch>\n"
                );
            }
        }

        return is_writable($dir);
    }

    // ============================================================
    // Validate file extension matches its MIME type
    // ============================================================
    private function validateExtension(string $ext, string $mime): bool
    {
        $mimeExtMap = [
            'image/jpeg'      => ['jpg', 'jpeg'],
            'image/png'       => ['png'],
            'image/webp'      => ['webp'],
            'image/gif'       => ['gif'],
            'application/pdf' => ['pdf'],
        ];

        foreach ($mimeExtMap as $validMime => $validExts) {
            if ($mime === $validMime && in_array($ext, $validExts)) {
                return true;
            }
        }

        return false;
    }

    // ============================================================
    // Check file content for PHP code (image injection protection)
    // ============================================================
    private function containsPhpCode(string $tmpPath): bool
    {
        $content = file_get_contents($tmpPath, false, null, 0, 1024);
        if ($content === false) return true;

        $patterns = [
            '<?php', '<?=', '<script', '<%', 'eval(', 'base64_decode(',
            'exec(', 'system(', 'passthru(', 'shell_exec(',
        ];

        foreach ($patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    // ============================================================
    // Get human-readable upload error
    // ============================================================
    private function getUploadError(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'The file exceeds the server upload size limit.',
            UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the form upload size limit.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was selected for upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
        ];

        return $errors[$errorCode] ?? 'An unknown upload error occurred.';
    }

    // ============================================================
    // Format bytes to readable size
    // ============================================================
    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . 'MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . 'KB';
        return $bytes . 'B';
    }

    // ============================================================
    // Get MIME type labels for error messages
    // ============================================================
    private function getMimeLabels(): string
    {
        $labels = [];
        foreach ($this->allowedMimes as $mime) {
            $labels[] = match($mime) {
                'image/jpeg', 'image/jpg' => 'JPG',
                'image/png'               => 'PNG',
                'image/webp'              => 'WEBP',
                'image/gif'               => 'GIF',
                'application/pdf'         => 'PDF',
                default                   => strtoupper(substr($mime, strpos($mime, '/') + 1)),
            };
        }
        return implode(', ', array_unique($labels));
    }
}