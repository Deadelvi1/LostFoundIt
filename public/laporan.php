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
            u.name as reporter_name,
            c.claim_id,
            c.status as claim_status,
            c.date_claimed,
            u2.name as claimant_name
        FROM items i
        JOIN users u ON i.user_id = u.user_id
        LEFT JOIN claims c ON i.item_id = c.item_id
        LEFT JOIN users u2 ON c.claimant_id = u2.user_id
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
                <div class="text-center py-8">
                    <p class="text-gray-600">Belum ada laporan barang.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($items as $item): ?>
                        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-pink-600"><?= htmlspecialchars($item['title']) ?></h3>
                                <span class="px-2 py-1 text-sm rounded <?= $item['type'] === 'lost' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= $item['type'] === 'lost' ? 'Hilang' : 'Ditemukan' ?>
                                </span>
                            </div>
                            
                            <?php if ($item['location']): ?>
                                <p class="text-gray-600 mb-2">
                                    <span class="font-medium">Lokasi:</span> <?= htmlspecialchars($item['location']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($item['description']): ?>
                                <p class="text-gray-600 mb-2">
                                    <span class="font-medium">Deskripsi:</span> <?= htmlspecialchars($item['description']) ?>
                                </p>
                            <?php endif; ?>

                            <p class="text-gray-600 mb-2">
                                <span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 text-sm rounded <?= $item['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $item['status'] === 'available' ? 'Tersedia' : 'Diklaim' ?>
                                </span>
                            </p>

                            <p class="text-gray-600 mb-2">
                                <span class="font-medium">Tanggal Dilaporkan:</span> 
                                <?= date('d F Y', strtotime($item['date_reported'])) ?>
                            </p>

                            <?php if ($item['status'] === 'claimed'): ?>
                                <p class="text-gray-600">
                                    <span class="font-medium">Diklaim oleh:</span> 
                                    <?= htmlspecialchars($item['claimant_name']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-6 text-right">
            <a href="dashboard.php" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded transition duration-200">
                Kembali
            </a>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
