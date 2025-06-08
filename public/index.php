<?php
require_once '../includes/auth.php';
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost&Found IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-200 via-white to-blue-200 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold text-pink-600 mb-6">Lost&Found IT</h1>
        <a href="login.php" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded mx-2">Login</a>
        <a href="register.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded mx-2">Register</a>
    </div>
</body>
</html>