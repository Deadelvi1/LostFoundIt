<?php
require_once '../includes/auth.php';
requireLogin();
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

    <!-- Navbar -->
    <nav class="bg-pink-600 text-white p-4 shadow-lg sticky top-0 z-10">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-wide">🎒 Lost&Found IT</h1>
            <div class="flex items-center space-x-4">
                <span>Halo, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></span>
                <a href="logout.php" class="bg-white text-pink-600 font-semibold px-4 py-1.5 rounded-full hover:bg-pink-100 transition duration-300">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto px-4 py-10">
        <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10 animate-fade-in-up">Dashboard</h2>

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Menu Card -->
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
                    <h3 class="text-xl font-semibold text-pink-700 group-hover:text-pink-800 transition-colors duration-300">Lihat Laporan</h3>
                    <p class="text-sm text-gray-500 mt-1">Lihat semua laporan yang telah dibuat.</p>
                </div>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center text-sm text-gray-600 py-6">
        &copy; <?= date("Y") ?> Lost&Found IT. Semua hak dilindungi.
    </footer>

</body>
</html>
