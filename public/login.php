<?php
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($email, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = 'Email atau password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-pink-400 via-white to-blue-400 min-h-screen flex items-center justify-center">
<head>
    <meta charset="UTF-8" />
    <title>Login - Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center mb-6 text-pink-600">Login Lost&Found IT</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label class="block mb-2 font-semibold text-gray-700" for="email">Email</label>
            <input class="w-full border border-gray-300 rounded px-3 py-2 mb-4" type="email" name="email" id="email" required autofocus />

            <label class="block mb-2 font-semibold text-gray-700" for="password">Password</label>
            <input class="w-full border border-gray-300 rounded px-3 py-2 mb-6" type="password" name="password" id="password" required />

            <button type="submit" class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 rounded transition">Login</button>
        </form>
        <p class="mt-4 text-center text-gray-600">Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Daftar di sini</a></p>
    </div>
</body>
</html>
