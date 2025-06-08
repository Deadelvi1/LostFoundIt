<?php
require_once '../includes/auth.php';
requireLogin();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

try {
    $stmt = $pdo->query("SELECT id, title, location FROM items WHERE type = 'found' AND status = 'unclaimed'");
    $available_items = $stmt->fetchAll();
} catch (Exception $e) {
    $available_items = [];
    $error = "Gagal memuat data barang.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE item_id = ? AND claimant_id = ?");
        $check->execute([$item_id, $user_id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Kamu sudah mengklaim barang ini sebelumnya.");
        }

        $stmt = $pdo->prepare("INSERT INTO claims (item_id, claimant_id) VALUES (?, ?)");
        $stmt->execute([$item_id, $user_id]);

        $stmt = $pdo->prepare("UPDATE items SET status = 'claimed' WHERE id = ?");
        $stmt->execute([$item_id]);

        $success = "Klaim berhasil dikirim.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Klaim Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen">
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold text-center text-pink-600 mb-4">Form Klaim Barang</h1>

        <?php if ($error): ?>
            <p class="bg-red-100 text-red-700 p-2 rounded"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="bg-green-100 text-green-700 p-2 rounded"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post" class="mt-4 space-y-4">
            <select name="item_id" required class="w-full border p-2 rounded">
                <option value="">-- Pilih Barang --</option>
                <?php foreach ($available_items as $item): ?>
                    <option value="<?= $item['id'] ?>">
                        <?= htmlspecialchars($item['title']) ?>
                        <?= isset($item['location']) && $item['location'] ? ' - ' . htmlspecialchars($item['location']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="w-full bg-pink-500 text-white py-2 rounded">Klaim</button>
        </form>

        <a href="dashboard.php" class="block text-center mt-4 text-blue-600 hover:underline">Kembali ke Dashboard</a>
    </div>
</body>
</html>