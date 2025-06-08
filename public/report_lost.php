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
        $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, type, location, date_reported, status) VALUES (?, ?, ?, 'lost', ?, CURDATE(), 'available')");
        if ($stmt->execute([$user_id, $title, $description, $location])) {
            $success = "Laporan barang hilang berhasil dikirim.";
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
    <title>Laporkan Barang Hilang - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="bg-pink-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Lost&Found IT</h1>
        <a href="dashboard.php" class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition">Kembali</a>
    </nav>

    <main class="flex-grow p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-semibold mb-6 text-pink-700">Laporkan Barang Hilang</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="bg-white p-6 rounded-lg shadow-md">
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
                <label class="block mb-2 font-semibold text-gray-700" for="location">Lokasi Hilang</label>
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
                          placeholder="Jelaskan detail barang yang hilang, seperti ciri-ciri khusus, warna, ukuran, dll."></textarea>
            </div>

            <button type="submit" 
                    class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded transition duration-200">
                Laporkan Barang Hilang
            </button>
        </form>
    </main>
</body>
</html>
