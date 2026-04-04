<?php
/**
 * Campuslink — Server-side WebP conversion endpoint
 * GET /api/image-webp?path=relative/to/uploads/file.jpg&w=400&q=80
 * Serves cached WebP version of uploaded images
 */
declare(strict_types=1);
require_once '../includes/bootstrap.php';

$path    = Security::clean($_GET['path'] ?? '');
$width   = max(50, min(1200, (int)($_GET['w']  ?? 600)));
$quality = max(30, min(95, (int)($_GET['q'] ?? 80)));

// Validate path — no traversal
if (!$path || str_contains($path, '..') || str_contains($path, '/') && preg_match('/\.\./', $path)) {
    http_response_code(400); exit;
}

$sourcePath = rtrim(UPLOAD_PATH, '/') . '/' . ltrim($path, '/');
if (!file_exists($sourcePath) || !is_file($sourcePath)) {
    http_response_code(404); exit;
}

// Validate MIME
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($sourcePath);
if (!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'], true)) {
    http_response_code(415); exit;
}

// Cache path
$cacheDir  = rtrim(UPLOAD_PATH, '/') . '/cache/webp/';
$cacheFile = $cacheDir . md5($path . $width . $quality) . '.webp';

if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// Serve from cache
if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($sourcePath)) {
    header('Content-Type: image/webp');
    header('Cache-Control: public, max-age=31536000, immutable');
    header('X-Cache: HIT');
    readfile($cacheFile);
    exit;
}

// Create image from source
$img = match($mime) {
    'image/jpeg' => imagecreatefromjpeg($sourcePath),
    'image/png'  => imagecreatefrompng($sourcePath),
    'image/gif'  => imagecreatefromgif($sourcePath),
    'image/webp' => imagecreatefromwebp($sourcePath),
    default      => null,
};

if (!$img) { http_response_code(500); exit; }

// Resize if needed
[$origW, $origH] = getimagesize($sourcePath);
if ($origW > $width) {
    $newH  = (int)round($origH * ($width / $origW));
    $thumb = imagecreatetruecolor($width, $newH);

    // Preserve transparency for PNG
    if ($mime === 'image/png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefilledrectangle($thumb, 0, 0, $width, $newH, $transparent);
    }

    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $newH, $origW, $origH);
    imagedestroy($img);
    $img = $thumb;
}

// Save as WebP
imagewebp($img, $cacheFile, $quality);
imagedestroy($img);

// Serve
header('Content-Type: image/webp');
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Cache: MISS');
readfile($cacheFile);