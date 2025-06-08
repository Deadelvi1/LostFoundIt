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
    <nav class="bg-pink-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Lost&Found IT</h1>
        <a href="dashboard.php" class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition">Kembali</a>
    </nav>

    <main class="flex-grow p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-semibold mb-6 text-pink-700">Laporkan Barang Ditemukan</h2>

        <form method="POST" action="" class="bg-white p-6 rounded-lg shadow-md" id="reportForm">
            <div class="mb-4">
                <label class="block mb-2 font-semibold text-gray-700" for="title">Judul Barang</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" 
                       type="text" 
                       id="title" 
                       name="title" 
                       placeholder="Contoh: Payung Merah" 
                       required 
                       autofocus />
            </div>

            <div class="mb-4">
                <label class="block mb-2 font-semibold text-gray-700" for="location">Lokasi Ditemukan</label>
                <input class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500" 
                       type="text" 
                       id="location" 
                       name="location" 
                       placeholder="Contoh: Perpustakaan" 
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

            <button type="submit" 
                    class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded transition duration-200">
                Laporkan Barang Ditemukan
            </button>
        </form>
    </main>

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
        }).then((result) => {
            if (result.isConfirmed) {
                // Clear the form
                document.getElementById('reportForm').reset();
            }
        });
    </script>
    <?php endif; ?>

    <script>
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            if (!title) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Judul barang wajib diisi.',
                    confirmButtonColor: '#ec4899'
                });
            }
        });
    </script>
</body>
</html>
