<?php
// Skrip Penghubung Storage Bersih untuk Shared Hosting
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$relativePath = preg_replace('#^/storage/#', '', $requestUri);

// Mencegah akses ke direktori induk
if (str_contains($relativePath, '..')) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$targetFile = __DIR__ . '/../../siabdes_app/storage/app/public/' . $relativePath;
if (! file_exists($targetFile)) {
    // Fallback jika di lingkungan lokal Laragon
    $targetFile = __DIR__ . '/../storage/app/public/' . $relativePath;
}

if (file_exists($targetFile) && is_file($targetFile)) {
    $mime = mime_content_type($targetFile) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($targetFile));
    header('Cache-Control: public, max-age=604800, immutable');
    readfile($targetFile);
    exit;
}

http_response_code(404);
header('Content-Type: text/plain');
echo 'Berkas tidak ditemukan: '.htmlspecialchars($relativePath);

