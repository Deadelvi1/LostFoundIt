<?php
$userName = $_SESSION['name'] ?? '';
?>
<nav class="bg-pink-600 text-white p-4 flex justify-between items-center shadow-md">
    <h1 class="text-xl font-bold">Lost&Found IT</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="flex items-center space-x-4">
            <span>Halo, <strong><?= htmlspecialchars($userName) ?></strong></span>

            <a href="logout.php"
               class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition"
               onclick="return confirm('Yakin ingin logout?')">
               Logout
            </a>
        </div>
    <?php else: ?>
        <div class="flex items-center space-x-4">
            <a href="login.php" class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition">Login</a>
            <a href="register.php" class="bg-white text-pink-600 font-semibold px-3 py-1 rounded hover:bg-pink-100 transition">Register</a>
        </div>
    <?php endif; ?>
</nav>
