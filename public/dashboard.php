<?php
require_once '../includes/auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="bg-pink-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Lost&Found IT - Dashboard</h1>
        <div>
            <span class="mr-4">Halo, <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="logout.php" class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition">Logout</a>
        </div>
    </nav>

    <main class="flex-grow p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold mb-6 text-pink-700">Menu</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="report_lost.php" class="block bg-white rounded shadow p-6 text-center text-pink-600 hover:bg-pink-50 transition">
                Laporkan Barang Hilang
            </a>
            <a href="report_found.php" class="block bg-white rounded shadow p-6 text-center text-pink-600 hover:bg-pink-50 transition">
                Laporkan Barang Ditemukan
            </a>
            <a href="claim.php" class="block bg-white rounded shadow p-6 text-center text-pink-600 hover:bg-pink-50 transition">
                Klaim Barang
            </a>
            <a href="#" onclick="alert('Fitur lain menyusul!'); return false;" class="block bg-white rounded shadow p-6 text-center text-pink-600 hover:bg-pink-50 transition">
                Lihat Laporan
            </a>
        </div>
    </main>
</body>
</html>
