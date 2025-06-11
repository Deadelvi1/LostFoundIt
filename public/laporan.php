<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';

try {
    $stmt = $pdo->query("
        SELECT 
            i.item_id,
            i.title,
            i.description,
            i.location,
            i.type,
            i.date_reported,
            i.status,
            u.name as reporter_name
        FROM items i
        JOIN users u ON i.user_id = u.user_id
        ORDER BY i.date_reported DESC
    ");
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Gagal memuat data laporan.";
    $items = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Saya - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow p-6 max-w-5xl mx-auto">
        <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10 animate-fade-in-up">Laporan Barang</h2>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($items)): ?>
                <p class="text-gray-500 text-center py-4">Belum ada laporan barang.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['title']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $item['type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= ucfirst($item['type']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $item['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($item['location']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d F Y', strtotime($item['date_reported'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="item_detail.php?id=<?= $item['item_id'] ?>" 
                                           class="text-pink-600 hover:text-pink-900">Lihat Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="w-full max-w-5xl mx-auto flex justify-center mt-6 mb-10">
        <a href="dashboard.php" 
           class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-8 rounded transition duration-200">
            Kembali ke Dashboard
        </a>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<style>
    .status-badge {
        @apply px-3 py-1 rounded-full text-sm font-semibold;
    }
    .status-available {
        @apply bg-green-100 text-green-800;
    }
    .status-claimed {
        @apply bg-red-100 text-red-800;
    }
    .claim-status {
        @apply px-3 py-1 rounded-full text-sm font-semibold;
    }
    .claim-pending {
        @apply bg-yellow-100 text-yellow-800;
    }
    .claim-approved {
        @apply bg-green-100 text-green-800;
    }
    .claim-rejected {
        @apply bg-red-100 text-red-800;
    }
</style>

<script>
    // Add tooltips for status badges
    document.addEventListener('DOMContentLoaded', function() {
        const statusBadges = document.querySelectorAll('.status-badge');
        statusBadges.forEach(badge => {
            const status = badge.textContent.trim().toLowerCase();
            let tooltip = '';
            switch(status) {
                case 'available':
                    tooltip = 'Barang masih tersedia untuk diklaim';
                    break;
                case 'claimed':
                    tooltip = 'Barang sudah diklaim';
                    break;
            }
            badge.title = tooltip;
        });

        const claimBadges = document.querySelectorAll('.claim-status');
        claimBadges.forEach(badge => {
            const status = badge.textContent.trim().toLowerCase();
            let tooltip = '';
            switch(status) {
                case 'pending':
                    tooltip = 'Klaim sedang diproses';
                    break;
                case 'approved':
                    tooltip = 'Klaim telah disetujui';
                    break;
                case 'rejected':
                    tooltip = 'Klaim ditolak';
                    break;
            }
            badge.title = tooltip;
        });
    });
</script>
