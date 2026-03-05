<?php
/**
 * PHP built-in server router
 * Usage: php -S localhost:8000 router.php  (run from project root)
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files from public/ with correct MIME types
$mimeMap = [
    'css'   => 'text/css; charset=UTF-8',
    'js'    => 'application/javascript; charset=UTF-8',
    'svg'   => 'image/svg+xml',
    'ico'   => 'image/x-icon',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'webp'  => 'image/webp',
];
$ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
if (isset($mimeMap[$ext])) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file)) {
        header('Content-Type: ' . $mimeMap[$ext]);
        header('Cache-Control: public, max-age=3600');
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

// Block sensitive paths
$blocked = ['/src', '/config', '/database'];
foreach ($blocked as $b) {
    if (str_starts_with($uri, $b)) {
        http_response_code(403);
        echo '403 Forbidden'; exit;
    }
}

// Map URI to public file
$path = __DIR__ . '/public' . $uri;

if (is_dir($path)) {
    $path = rtrim($path, '/') . '/index.php';
}

if (file_exists($path) && str_ends_with($path, '.php')) {
    chdir(__DIR__ . '/public');
    require $path;
    return true;
}

// Default: look in public/
$fallback = __DIR__ . '/public' . $uri . '.php';
if (file_exists($fallback)) {
    chdir(__DIR__ . '/public');
    require $fallback;
    return true;
}

http_response_code(404);
echo '404 Not Found';
