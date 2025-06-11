<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

try {
    $stmt = $pdo->query("
        SELECT i.item_id, i.title, i.description, i.location, i.type, i.date_reported, i.photo, u.name as reporter_name 
        FROM items i 
        JOIN users u ON i.user_id = u.user_id 
        LEFT JOIN claims c ON i.item_id = c.item_id
        WHERE i.status = 'available' AND c.claim_id IS NULL
        ORDER BY i.date_reported DESC
    ");
    $available_items = $stmt->fetchAll();
} catch (Exception $e) {
    $available_items = [];
    $error = "Gagal memuat data barang.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    try {
        // Check if item is claimable using the function
        $check = $pdo->prepare("SELECT fn_isItemClaimable(?) as is_claimable");
        $check->execute([$item_id]);
        $is_claimable = $check->fetchColumn();

        if (!$is_claimable) {
            throw new Exception("Barang tidak tersedia untuk diklaim.");
        }

        // Use the stored procedure to claim the item
        $stmt = $pdo->prepare("CALL sp_claimItem(?, ?)");
        $stmt->execute([$user_id, $item_id]);

        $success = "Klaim berhasil dikirim. Silakan tunggu konfirmasi dari pemilik barang.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8">
    <title>Klaim Barang - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow p-6 w-full">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10">Klaim Barang</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($available_items)): ?>
                <div class="text-center text-gray-600 mt-8">
                    <p class="text-lg">Tidak ada barang yang tersedia untuk diklaim.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($available_items as $item): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                            <!-- Item Photo -->
                            <div class="h-48 bg-gray-200 relative">
                                <?php if ($item['photo']): ?>
                                    <img src="../<?= htmlspecialchars($item['photo']) ?>" 
                                         alt="<?= htmlspecialchars($item['title']) ?>" 
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-500">
                                        <i class="fas fa-image text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute top-2 right-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        <?= $item['type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= ucfirst($item['type']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Item Info -->
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                    <?= htmlspecialchars($item['title']) ?>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <?= htmlspecialchars($item['location']) ?>
                                </p>
                                <p class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-user mr-2"></i>
                                    Dilaporkan oleh: <?= htmlspecialchars($item['reporter_name']) ?>
                                </p>
                                <p class="text-sm text-gray-600 mb-4">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <?= date('d F Y', strtotime($item['date_reported'])) ?>
                                </p>

                                <!-- Claim Button -->
                                <form method="post" class="mt-4">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                    <button type="submit" 
                                            class="block w-full bg-pink-500 hover:bg-pink-600 text-white text-center font-bold py-2 px-4 rounded transition duration-300"
                                            onclick="return confirm('Apakah Anda yakin ingin mengklaim barang ini?')">
                                        Klaim Barang
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
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