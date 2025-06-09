<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if (!$title) {
        $error = "Judul barang wajib diisi.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, type, location, date_reported, status) VALUES (?, ?, ?, 'found', ?, CURDATE(), 'available')");
        if ($stmt->execute([$user_id, $title, $description, $location])) {
            $success = "Laporan barang ditemukan berhasil dikirim.";
        } else {
            $error = "Gagal mengirim laporan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8" />
    <title>Laporkan Barang Ditemukan - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow p-6 w-full">
        <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10 animate-fade-in-up">Laporkan Barang Ditemukan</h2>

        <form method="POST" action="" class="bg-white p-8 rounded-lg shadow-md w-full max-w-5xl mx-auto" id="reportForm">
            <div class="mb-4">
                <label class="block mb-2 font-semibold text-gray-700" for="title">Judul Barang</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" 
                       type="text" 
                       id="title" 
                       name="title" 
                       placeholder="Contoh: Dompet Hitam" 
                       required 
                       autofocus />
            </div>
            <div class="mb-4">
                <label class="block mb-2 font-semibold text-gray-700" for="location">Lokasi Ditemukan</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" 
                       type="text" 
                       id="location" 
                       name="location" 
                       placeholder="Contoh: Gedung A" 
                       required />
            </div>
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-gray-700" for="description">Deskripsi Detail</label>
                <textarea class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" 
                          id="description" 
                          name="description" 
                          rows="4"
                          placeholder="Jelaskan detail barang yang ditemukan, seperti ciri-ciri khusus, warna, ukuran, dll."></textarea>
            </div>
        </form>

        <div class="w-full max-w-5xl mx-auto flex justify-end items-center gap-4 mt-6">
            <a href="dashboard.php" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-8 rounded transition duration-200 text-center">Kembali</a>
            <button type="submit" form="reportForm" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-8 rounded transition duration-200">Laporkan Barang Ditemukan</button>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#ec4899'
        });
    </script>
    <?php endif; ?>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($success) ?>',
            confirmButtonColor: '#ec4899'
        });
    </script>
    <?php endif; ?>
</body>
</html>
