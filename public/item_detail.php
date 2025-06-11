<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$item_id) {
    header("Location: dashboard.php");
    exit;
}

// Ambil detail item
$stmt = $pdo->prepare("
    SELECT i.*, u.username as reporter_name 
    FROM items i 
    JOIN users u ON i.user_id = u.user_id 
    WHERE i.item_id = ?
");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: dashboard.php");
    exit;
}

// Cek apakah user sudah mengklaim barang ini
$stmt = $pdo->prepare("
    SELECT * FROM claims 
    WHERE item_id = ? AND claimant_id = ?
");
$stmt->execute([$item_id, $_SESSION['user_id']]);
$claim = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8">
    <title>Detail Barang - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow p-6 w-full">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10">Detail Barang</h2>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-semibold mb-4">Informasi Barang</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Judul:</p>
                        <p class="font-medium"><?= htmlspecialchars($item['title']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Status:</p>
                        <p class="font-medium"><?= ucfirst(htmlspecialchars($item['status'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Lokasi:</p>
                        <p class="font-medium"><?= htmlspecialchars($item['location']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Dilaporkan oleh:</p>
                        <p class="font-medium"><?= htmlspecialchars($item['reporter_name']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Tanggal Dilaporkan:</p>
                        <p class="font-medium"><?= date('d/m/Y', strtotime($item['date_reported'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Tipe:</p>
                        <p class="font-medium"><?= ucfirst(htmlspecialchars($item['type'])) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-gray-600">Deskripsi:</p>
                    <p class="font-medium"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                </div>
            </div>

            <?php if ($claim): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    <p class="font-medium">Status Klaim Anda:</p>
                    <p><?= ucfirst(htmlspecialchars($claim['status'])) ?></p>
                    <p class="text-sm mt-2">Tanggal Klaim: <?= date('d/m/Y H:i', strtotime($claim['claim_date'])) ?></p>
                </div>
            <?php elseif ($item['status'] === 'available'): ?>
                <div class="flex justify-end">
                    <a href="claim.php?id=<?= $item_id ?>" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded">
                        Klaim Barang
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    Barang ini tidak tersedia untuk diklaim.
                </div>
            <?php endif; ?>

            <div class="mt-6">
                <a href="dashboard.php" class="text-pink-600 hover:text-pink-700">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 