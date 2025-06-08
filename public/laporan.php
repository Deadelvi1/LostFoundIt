<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';

try {
    // Get all items with their claim status and claimant info
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
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-3xl font-bold text-center text-pink-600 mb-6">Laporan Saya</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <p class="text-center text-gray-500">Belum ada laporan yang kamu buat.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-xl font-semibold text-blue-700"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-gray-700 mb-1"><strong>Jenis:</strong> <?= ucfirst($item['type']) ?></p>
                        <p class="text-gray-700 mb-1"><strong>Lokasi:</strong> <?= htmlspecialchars($item['location']) ?></p>
                        <p class="text-gray-700 mb-1"><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <p class="text-gray-700 mb-1"><strong>Status:</strong> 
                            <?php
                            $display_status = '';
                            $badge_class = '';

                            if ($item['claim_id']) {
                                if ($item['type'] === 'lost') {
                                    $display_status = 'Sudah Ditemukan'; 
                                    $badge_class = 'bg-blue-200 text-blue-800'; 
                                } elseif ($item['type'] === 'found') {
                                    $display_status = 'Sudah Diklaim'; 
                                    $badge_class = 'bg-yellow-200 text-yellow-800'; 
                                } else {
                                    $display_status = 'Tidak Diketahui'; // Fallback for unexpected type
                                    $badge_class = 'bg-gray-200 text-gray-800';
                                }
                            } else {
                                $display_status = 'Tersedia'; 
                                $badge_class = 'bg-green-200 text-green-800';
                            }
                            ?>
                            <span class="px-2 py-1 rounded <?= $badge_class ?>">
                                <?= $display_status ?>
                            </span>
                        </p>

                        <?php if ($item['claim_id']): ?>
                            <?php if ($item['type'] === 'lost'): ?>
                                <p class="text-gray-700 mb-1">
                                    <strong>Ditemukan oleh:</strong> <?= htmlspecialchars($item['claimant_name']) ?>
                                </p>
                                <p class="text-gray-700">
                                    <strong>Tanggal ditemukan:</strong> <?= date('d/m/Y H:i', strtotime($item['date_claimed'])) ?>
                                </p>
                            <?php elseif ($item['type'] === 'found'): ?>
                                <p class="text-gray-700 mb-1">
                                    <strong>Diklaim oleh:</strong> <?= htmlspecialchars($item['claimant_name']) ?>
                                </p>
                                <p class="text-gray-700">
                                    <strong>Tanggal Klaim:</strong> <?= date('d/m/Y H:i', strtotime($item['date_claimed'])) ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
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
