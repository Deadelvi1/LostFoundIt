<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

try {
    $stmt = $pdo->query("
        SELECT i.item_id, i.title, i.description, i.location, i.type, i.date_reported, u.name as reporter_name 
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
        $check = $pdo->prepare("SELECT status FROM items WHERE item_id = ?");
        $check->execute([$item_id]);
        $item_status = $check->fetchColumn();

        if (!$item_status) {
            throw new Exception("Barang tidak ditemukan.");
        }

        if ($item_status !== 'available') {
            throw new Exception("Barang ini sudah diklaim.");
        }
        $check = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE item_id = ? AND claimant_id = ?");
        $check->execute([$item_id, $user_id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Anda sudah mengklaim barang ini sebelumnya.");
        }

        $stmt = $pdo->prepare("INSERT INTO claims (item_id, claimant_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$item_id, $user_id]);

        $stmt_update_item = $pdo->prepare("UPDATE items SET status = 'claimed' WHERE item_id = ?");
        $stmt_update_item->execute([$item_id]);

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

    <main class="flex-grow p-6 max-w-5xl mx-auto">
        <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10 animate-fade-in-up">Klaim Barang</h2>

        <?php if (empty($available_items)): ?>
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <p class="text-gray-600">Tidak ada barang yang tersedia untuk diklaim saat ini.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($available_items as $item): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md">
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

                        <p class="text-gray-600 mb-4">
                            <span class="font-medium">Dilaporkan oleh:</span> <?= htmlspecialchars($item['reporter_name']) ?>
                        </p>

                        <form method="post" class="mt-4">
                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                            <?php
                                $button_text = '';
                                $button_class = '';
                                if ($item['type'] === 'found') {
                                    $button_text = 'Klaim barang ini';
                                    $button_class = 'bg-blue-500 hover:bg-blue-600';
                                } elseif ($item['type'] === 'lost') {
                                    $button_text = 'Menemukan barang ini';
                                    $button_class = 'bg-green-500 hover:bg-green-600';
                                } else {
                                    $button_text = 'Lakukan Aksi'; // Fallback
                                    $button_class = 'bg-gray-500 hover:bg-gray-600';
                                }
                            ?>
                            <button type="submit" 
                                    class="w-full text-white font-bold py-2 px-4 rounded transition duration-200 <?= $button_class ?>">
                                <?= $button_text ?>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-right">
            <a href="dashboard.php" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded transition duration-200">
                Kembali
            </a>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#ec4899'
        });
    </script>
    <?php endif; ?>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($success) ?>',
            confirmButtonColor: '#ec4899'
        });
    </script>
    <?php endif; ?>
</body>
</html>