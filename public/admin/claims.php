<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireLogin();

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit;
}

$success = '';
$error = '';

// Handle claim status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id']) && isset($_POST['status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE claim_id = ?");
        $stmt->execute([$_POST['status'], $_POST['claim_id']]);
        $success = "Status klaim berhasil diperbarui.";
    } catch (Exception $e) {
        $error = "Gagal memperbarui status klaim.";
    }
}

// Get all claims with item and user details
try {
    $stmt = $pdo->query("
        SELECT 
            c.claim_id,
            c.status as claim_status,
            c.date_claimed,
            i.title as item_title,
            i.type as item_type,
            u1.name as reporter_name,
            u2.name as claimant_name
        FROM claims c
        JOIN items i ON c.item_id = i.item_id
        JOIN users u1 ON i.user_id = u1.user_id
        JOIN users u2 ON c.claimant_id = u2.user_id
        ORDER BY c.date_claimed DESC
    ");
    $claims = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Gagal memuat data klaim.";
    $claims = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Klaim - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen p-4">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-3xl font-bold text-center text-pink-600 mb-6">Kelola Klaim</h2>

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

        <?php if (empty($claims)): ?>
            <p class="text-center text-gray-500">Belum ada klaim yang perlu dikelola.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($claims as $claim): ?>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-blue-700"><?= htmlspecialchars($claim['item_title']) ?></h3>
                                <p class="text-gray-600"><?= ucfirst($claim['item_type']) ?></p>
                            </div>
                            <span class="px-2 py-1 rounded <?= 
                                $claim['claim_status'] === 'approved' ? 'bg-green-200 text-green-800' : 
                                ($claim['claim_status'] === 'rejected' ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800')
                            ?>">
                                <?= $claim['claim_status'] === 'approved' ? 'Disetujui' : 
                                    ($claim['claim_status'] === 'rejected' ? 'Ditolak' : 'Menunggu') ?>
                            </span>
                        </div>

                        <p class="text-gray-700 mb-1">
                            <strong>Dilaporkan oleh:</strong> <?= htmlspecialchars($claim['reporter_name']) ?>
                        </p>
                        <p class="text-gray-700 mb-1">
                            <strong>Diklaim oleh:</strong> <?= htmlspecialchars($claim['claimant_name']) ?>
                        </p>
                        <p class="text-gray-700 mb-4">
                            <strong>Tanggal Klaim:</strong> <?= date('d/m/Y H:i', strtotime($claim['date_claimed'])) ?>
                        </p>

                        <?php if ($claim['claim_status'] === 'pending'): ?>
                            <form method="post" class="flex gap-2">
                                <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                <button type="submit" name="status" value="approved"
                                        class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition">
                                    Setujui
                                </button>
                                <button type="submit" name="status" value="rejected"
                                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition">
                                    Tolak
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center">
            <a href="../dashboard.php" class="text-blue-600 hover:underline">← Kembali ke Dashboard</a>
        </div>
    </div>

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