<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id']) && isset($_POST['status'])) {
    $claim_id = $_POST['claim_id'];
    $new_status = $_POST['status'];
    
    try {
        $pdo->beginTransaction();
        
        // Update claim status
        $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE claim_id = ?");
        $stmt->execute([$new_status, $claim_id]);
        
        // Log activity
        $log_stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, item_id, activity_type, activity_date)
            SELECT ?, item_id, 'update_claim_status', NOW()
            FROM claims WHERE claim_id = ?
        ");
        $log_stmt->execute([$_SESSION['user_id'], $claim_id]);
        
        $pdo->commit();
        $success = "Status klaim berhasil diperbarui.";
    } catch (Exception $e) {
        $pdo->rollBack();
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
            i.item_id,
            i.title,
            i.type,
            i.status as item_status,
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
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen">
<head>
    <meta charset="UTF-8">
    <title>Kelola Klaim - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow p-6 w-full">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-extrabold text-center text-pink-700 mb-10">Kelola Klaim</h2>

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
                <div class="text-center text-gray-600 mt-8">
                    <p class="text-lg">Belum ada klaim yang perlu dikelola.</p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengklaim</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Klaim</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($claims as $claim): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($claim['title']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= ucfirst($claim['type']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($claim['reporter_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($claim['claimant_name']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d F Y H:i', strtotime($claim['date_claimed'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                                <?= $claim['claim_status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                    ($claim['claim_status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                                <?= ucfirst($claim['claim_status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if ($claim['claim_status'] === 'pending'): ?>
                                                <div class="flex space-x-3">
                                                    <form method="POST" class="inline-block">
                                                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" 
                                                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition duration-200 flex items-center"
                                                                onclick="return confirm('Setujui klaim ini?')">
                                                            <i class="fas fa-check mr-1"></i> Setujui
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline-block">
                                                        <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" 
                                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition duration-200 flex items-center"
                                                                onclick="return confirm('Tolak klaim ini?')">
                                                            <i class="fas fa-times mr-1"></i> Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">Tidak ada aksi</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="w-full max-w-7xl mx-auto flex justify-center mt-6 mb-10">
        <a href="dashboard.php" 
           class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-200">
            Kembali ke Dashboard
        </a>
    </div>

    <?php include '../includes/footer.php'; ?>

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