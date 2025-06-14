<?php
if (isset($_GET['path'])) {
    $relativePath = $_GET['path'];

    $relativePath = str_replace(['../', './'], '', $relativePath);

    $imagePath = __DIR__ . '/../' . $relativePath; // Lokasi folder penyimpanan gambar

    if (file_exists($imagePath)) {
        $mime = mime_content_type($imagePath);
        header('Content-Type: ' . $mime);
        readfile($imagePath);
        exit;
    } else {
        // Tampilkan pesan jika gambar tidak ada
        header('HTTP/1.0 404 Not Found');
        echo 'Image not found.';
        exit;
    }
} else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Path parameter missing.';
    exit;
}
?> 