<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$error = '';
$success = '';

// Ambil daftar barang found yang belum diklaim
$stmt = $pdo->prepare("SELECT * FROM found_items WHERE status = 'found'");
$stmt->execute();
$found_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'] ?? null;

    if (!$item_id) {
        $error = "Pilih barang yang ingin diklaim.";
    } else {
        try {
            $pdo->beginTransaction();

            // Cek status barang
            $stmt = $pdo->prepare("SELECT status FROM found_items WHERE item_id = ? FOR UPDATE");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch();

            if (!$item) {
                throw new Exception("Barang tidak ditemukan.");
            }
            if ($item['status'] !== 'found') {
                throw new Exception("Barang sudah diklaim.");
            }

            // Update status jadi claimed dan assign klaim ke user
            $stmt = $pdo->prepare("UPDATE found_items SET status = 'claimed', claimed_by = ?, claimed_at = NOW() WHERE item_id = ?");
            $stmt->execute([$user_id, $item_id]);

            $pdo->commit();
            $success = "Berhasil mengklaim barang.";
            // Refresh daftar barang yang bisa diklaim
            $stmt = $pdo->prepare("SELECT * FROM found_items WHERE status = 'found'");
            $stmt->execute();
            $found_items = $stmt->fetchAll();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8" />
    <title>Klaim Barang - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex flex-col">
    <nav class="bg-pink-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Lost&Found IT</h1>
        <a href="dashboard.php" class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition">Kembali</a>
    </nav>

    <main class="flex-grow p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-semibold mb-6 text-pink-700">Klaim Barang</h2>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (count($found_items) === 0): ?>
            <p class="text-gray-700">Tidak ada barang yang bisa diklaim saat ini.</p>
        <?php else: ?>
            <form method="POST" action="">
                <label class="block mb-2 font-semibold text-gray-700" for="item_id">Pilih Barang</label>
                <select id="item_id" name="item_id" required class="w-full border border-gray-300 rounded px-3 py-2 mb-6">
                    <option value="">-- Pilih barang --</option>
                    <?php foreach ($found_items as $item): ?>
                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?> - <?= htmlspecialchars($item['description']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 rounded transition">Klaim</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
