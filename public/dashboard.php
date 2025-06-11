<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$userName = getCurrentUserName();
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-200 via-white to-blue-200 min-h-screen">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #f472b6;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #db2777;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans text-gray-800">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow container mx-auto px-4 py-10">
        <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10 animate-fade-in-up">Dashboard</h2>

        <div class="max-w-2xl mx-auto space-y-6">
            <a href="report_lost.php" class="group bg-white rounded-2xl shadow-lg p-8 flex items-center space-x-6 transform hover:scale-105 transition-all duration-300 hover:shadow-pink-200 animate-fade-in-up">
                <div class="bg-pink-50 p-4 rounded-xl group-hover:bg-pink-100 transition-colors duration-300">
                    <i class="fas fa-search text-pink-600 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Laporkan Barang Hilang</h3>
                    <p class="text-sm text-gray-500 mt-1">Kehilangan sesuatu? Laporkan di sini.</p>
                </div>
            </a>

            <a href="report_found.php" class="group bg-white rounded-2xl shadow-lg p-8 flex items-center space-x-6 transform hover:scale-105 transition-all duration-300 hover:shadow-pink-200 animate-fade-in-up delay-100">
                <div class="bg-pink-50 p-4 rounded-xl group-hover:bg-pink-100 transition-colors duration-300">
                    <i class="fas fa-box-open text-pink-600 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Laporkan Barang Ditemukan</h3>
                    <p class="text-sm text-gray-500 mt-1">Menemukan barang? Bantu kembalikan ke pemiliknya.</p>
                </div>
            </a>

            <a href="claim.php" class="group bg-white rounded-2xl shadow-lg p-8 flex items-center space-x-6 transform hover:scale-105 transition-all duration-300 hover:shadow-pink-200 animate-fade-in-up delay-200">
                <div class="bg-pink-50 p-4 rounded-xl group-hover:bg-pink-100 transition-colors duration-300">
                    <i class="fas fa-hand-holding text-pink-600 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Klaim Barang</h3>
                    <p class="text-sm text-gray-500 mt-1">Ajukan klaim atas barang yang kamu temukan di sistem.</p>
                </div>
            </a>

            <a href="laporan.php" class="group bg-white rounded-2xl shadow-lg p-8 flex items-center space-x-6 transform hover:scale-105 transition-all duration-300 hover:shadow-pink-200 animate-fade-in-up delay-300">
                <div class="bg-pink-50 p-4 rounded-xl group-hover:bg-pink-100 transition-colors duration-300">
                    <i class="fas fa-list text-pink-600 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Laporan Saya</h3>
                    <p class="text-sm text-gray-500 mt-1">Lihat status laporan barang yang telah dibuat.</p>
                </div>
            </a>

            <a href="manage_claims.php" class="group bg-white rounded-2xl shadow-lg p-8 flex items-center space-x-6 transform hover:scale-105 transition-all duration-300 hover:shadow-pink-200 animate-fade-in-up delay-400">
                <div class="bg-pink-50 p-4 rounded-xl group-hover:bg-pink-100 transition-colors duration-300">
                    <i class="fas fa-tasks text-pink-600 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Kelola Klaim</h3>
                    <p class="text-sm text-gray-500 mt-1">Atur status klaim barang yang masuk.</p>
                </div>
            </a>

            <a href="backup_db.php"
               onclick="return confirm('Yakin ingin mengunduh backup database?')"
               class="group bg-white rounded-2xl shadow-lg p-8 flex items-center space-x-6 transform hover:scale-105 transition-all duration-300 hover:shadow-pink-200 animate-fade-in-up delay-400">
                <div class="bg-pink-50 p-4 rounded-xl group-hover:bg-pink-100 transition-colors duration-300">
                    <i class="fas fa-database text-pink-600 text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Download Backup Database</h3>
                    <p class="text-sm text-gray-500 mt-1">Simpan salinan data kamu dalam format SQL.</p>
                </div>
            </a>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
