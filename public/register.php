<?php
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'Semua field wajib diisi.';
    } elseif ($password !== $password_confirm) {
        $error = 'Password dan konfirmasi password tidak sama.';
    } else {
        if (register($name, $email, $password)) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = 'Email sudah terdaftar.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen flex items-center justify-center">
<head>
    <meta charset="UTF-8" />
    <title>Register - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center mb-6 text-pink-600">Daftar Lost&Found IT</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label class="block mb-2 font-semibold text-gray-700" for="name">Nama Lengkap</label>
            <input class="w-full border border-gray-300 rounded px-3 py-2 mb-4" type="text" name="name" id="name" value="<?= htmlspecialchars($name ?? '') ?>" required autofocus />

            <label class="block mb-2 font-semibold text-gray-700" for="email">Email</label>
            <input class="w-full border border-gray-300 rounded px-3 py-2 mb-4" type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '') ?>" required />

            <label class="block mb-2 font-semibold text-gray-700" for="password">Password</label>
            <input class="w-full border border-gray-300 rounded px-3 py-2 mb-4" type="password" name="password" id="password" required />

            <label class="block mb-2 font-semibold text-gray-700" for="password_confirm">Konfirmasi Password</label>
            <input class="w-full border border-gray-300 rounded px-3 py-2 mb-6" type="password" name="password_confirm" id="password_confirm" required />

            <button type="submit" class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 rounded transition">Daftar</button>
        </form>
        <p class="mt-4 text-center text-gray-600">Sudah punya akun? <a href="login.php" class="text-blue-600 hover:underline">Login di sini</a></p>
    </div>
</body>
</html>
