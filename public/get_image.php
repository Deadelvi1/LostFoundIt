<?php
if (isset($_GET['path'])) {
    $relativePath = $_GET['path'];

    // Sanitize path to prevent directory traversal
    $relativePath = str_replace(['../', './'], '', $relativePath);

    $imagePath = __DIR__ . '/../' . $relativePath; // Path to your uploads directory

    if (file_exists($imagePath)) {
        $mime = mime_content_type($imagePath);
        header('Content-Type: ' . $mime);
        readfile($imagePath);
        exit;
    } else {
        // Fallback for missing image (optional, you can show a placeholder)
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