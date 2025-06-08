<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY date_reported DESC");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Saya - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-3xl font-bold text-center text-pink-600 mb-6">Laporan Saya</h2>

        <?php if (count($items) === 0): ?>
            <p class="text-center text-gray-500">Belum ada laporan yang kamu buat.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-xl font-semibold text-blue-700"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-gray-700 mb-1"><strong>Jenis:</strong> <?= ucfirst($item['type']) ?></p>
                        <p class="text-gray-700 mb-1"><strong>Lokasi:</strong> <?= htmlspecialchars($item['location']) ?></p>
                        <p class="text-gray-700 mb-1"><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <p class="text-gray-700"><strong>Status:</strong> 
                            <span class="px-2 py-1 rounded <?= $item['status'] === 'claimed' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800' ?>">
                                <?= $item['status'] ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-blue-600 hover:underline">← Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
