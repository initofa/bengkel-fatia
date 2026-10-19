<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    $r = $_SESSION['role'];
    if ($r == 'Owner') header("Location: dashboard_owner.php");
    elseif ($r == 'Mekanik') header("Location: mekanik_dashboard.php");
    else header("Location: dashboard_admin.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username atau password tidak boleh kosong!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] == 'Owner') $_SESSION['redirect_to'] = 'dashboard_owner.php';
            elseif ($user['role'] == 'Mekanik') $_SESSION['redirect_to'] = 'mekanik_dashboard.php';
            else $_SESSION['redirect_to'] = 'dashboard_admin.php';

            header("Location: loading.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Bengkel Fatia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#e2e4e7] flex items-center justify-center h-screen">
    <div class="w-[850px] h-[500px] rounded-2xl shadow-2xl flex overflow-hidden">
        <div class="w-1/2 bg-[#252332] p-12 flex flex-col justify-center relative overflow-hidden">
            <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-[#1c1a26] rounded-full opacity-70"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#1c1a26] rounded-full opacity-50"></div>
            <div class="relative z-10">
                <h1 class="text-5xl font-black text-white leading-none uppercase">SELAMAT<br>DATANG</h1>
                <p class="text-slate-300 font-bold text-sm mt-6 uppercase tracking-[3px]">DI SISTEM<br>BENGKEL FATIA</p>
            </div>
        </div>
        <div class="w-1/2 bg-[#e2e4e7] p-12 flex flex-col justify-center">
            <h2 class="text-xl font-black text-slate-800 text-center tracking-[4px] uppercase mb-8">MASUK</h2>
            <?php if (!empty($error)): ?>
                <div class="bg-red-500/15 border border-red-500 text-red-600 text-xs p-2.5 rounded mb-4 text-center font-semibold"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="text" name="username" placeholder="USERNAME" required autofocus
                       class="w-full bg-white border border-slate-300 rounded px-4 py-3 text-sm font-semibold focus:outline-none focus:border-lime-500">
                <input type="password" name="password" placeholder="PASSWORD" required
                       class="w-full bg-white border border-slate-300 rounded px-4 py-3 text-sm font-semibold focus:outline-none focus:border-lime-500">
                <button type="submit" class="w-full bg-[#a3e635] hover:bg-[#84cc16] text-slate-950 font-black py-3 rounded uppercase tracking-[3px] text-sm shadow-lg">MASUK</button>
            </form>
        </div>
    </div>
</body>
</html>