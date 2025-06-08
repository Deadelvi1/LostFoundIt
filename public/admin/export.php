<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireLogin();

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get database configuration
        $host = 'localhost';
        $dbname = 'lostfoundit';
        $username = 'root';
        $password = '';

        // Create backup filename with timestamp
        $backup_file = 'backup_' . date("Y-m-d_H-i-s") . '.sql';

        // Command to create backup
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($dbname),
            escapeshellarg($backup_file)
        );

        // Execute backup command
        system($command, $return_var);

        if ($return_var === 0) {
            // Set headers for download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup_file . '"');
            header('Content-Length: ' . filesize($backup_file));
            
            // Output file content
            readfile($backup_file);
            
            // Delete the temporary file
            unlink($backup_file);
            exit;
        } else {
            $error = "Gagal membuat backup database.";
        }
    } catch (Exception $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ekspor Database - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen p-4">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-3xl font-bold text-center text-pink-600 mb-6">Ekspor Database</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Backup database akan mencakup semua data termasuk pengguna, item, dan klaim.
                        File akan diunduh dalam format SQL.
                    </p>
                </div>
            </div>
        </div>

        <form method="post" class="space-y-4">
            <div class="text-center">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg transition flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Backup Database
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="../dashboard.php" class="text-blue-600 hover:underline">← Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html> 