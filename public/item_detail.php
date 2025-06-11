<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$error = '';
$item = null;
$has_claimed = false;

if (isset($_GET['id'])) {
    $item_id = (int)$_GET['id'];
    
    // Get item details
    $stmt = $pdo->prepare("
        SELECT i.*, u.name as reporter_name 
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
        WHERE i.item_id = ?
    ");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if ($item) {
        // Check if user has claimed this item
        $claim_stmt = $pdo->prepare("
            SELECT * FROM claims 
            WHERE item_id = ? AND claimant_id = ?
        ");
        $claim_stmt->execute([$item_id, $_SESSION['user_id']]);
        $has_claimed = $claim_stmt->fetch() !== false;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8" />
    <title>Detail Barang - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow p-6 w-full">
        <?php if ($item): ?>
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-5xl mx-auto">
                <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10">Detail Barang</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Foto Barang -->
                    <div class="space-y-4">
                        <?php if ($item['photo']): ?>
                            <img src="../<?= htmlspecialchars($item['photo']) ?>" 
                                 alt="<?= htmlspecialchars($item['title']) ?>" 
                                 class="w-full h-64 object-cover rounded-lg shadow-md">
                        <?php else: ?>
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <span class="text-gray-500">Tidak ada foto</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informasi Barang -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($item['title']) ?></h3>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold 
                                <?= $item['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </div>

                        <div class="space-y-2">
                            <p class="text-gray-600">
                                <span class="font-semibold">Lokasi:</span> 
                                <?= htmlspecialchars($item['location']) ?>
                            </p>
                            <p class="text-gray-600">
                                <span class="font-semibold">Dilaporkan oleh:</span> 
                                <?= htmlspecialchars($item['reporter_name']) ?>
                            </p>
                            <p class="text-gray-600">
                                <span class="font-semibold">Tanggal laporan:</span> 
                                <?= date('d F Y', strtotime($item['date_reported'])) ?>
                            </p>
                            <p class="text-gray-600">
                                <span class="font-semibold">Tipe:</span> 
                                <?= ucfirst($item['type']) ?>
                            </p>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-800 mb-2">Deskripsi:</h4>
                            <p class="text-gray-600"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        </div>

                        <?php if ($has_claimed): ?>
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                <p class="text-blue-800">
                                    <span class="font-semibold">Status Klaim:</span> 
                                    Anda telah mengklaim barang ini
                                </p>
                            </div>
                        <?php elseif ($item['status'] === 'available'): ?>
                            <div class="mt-6">
                                <a href="claim.php?id=<?= $item['item_id'] ?>" 
                                   class="block w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded text-center transition duration-200">
                                    Klaim Barang
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <p class="text-gray-600">
                                    Barang ini tidak tersedia untuk diklaim
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <a href="dashboard.php" 
                       class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-200">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-5xl mx-auto text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Barang Tidak Ditemukan</h2>
                <p class="text-gray-600 mb-6">Barang yang Anda cari tidak ditemukan atau telah dihapus.</p>
                <a href="dashboard.php" 
                   class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-200">
                    Kembali ke Dashboard
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 