<?php
session_start();
require_once 'config/database.php';

// ==================== CEK SESSION (kalau sudah login) ====================
if (isset($_SESSION['user_id'])) {
    $r = $_SESSION['role'];
    if ($r == 'Owner') header("Location: dashboard_owner.php");
    else header("Location: dashboard_admin.php");
    exit;
}

// ==================== PROSES LOGIN ====================
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username atau password tidak boleh kosong!";
    } else {
        // Coba login sebagai ADMIN dulu
        $stmt = $pdo->prepare("SELECT *, 'Admin' AS role FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Kalau tidak ketemu, coba sebagai OWNER
        if (!$user) {
            $stmt = $pdo->prepare("SELECT *, 'Owner' AS role FROM owner WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
        }
        
        // Cek password
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_admin'] ?? $user['id_owner'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['redirect_to'] = ($user['role'] == 'Owner') ? 'dashboard_owner.php' : 'dashboard_admin.php';
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#e2e4e7] flex items-center justify-center h-screen">
    <div class="w-[850px] h-[500px] rounded-2xl shadow-2xl flex overflow-hidden">

        <!-- ==================== KIRI: SAMBUTAN ==================== -->
        <div class="w-1/2 bg-[#252332] p-10 flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-[#1c1a26] rounded-full opacity-70"></div>
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-[#1c1a26] rounded-full opacity-50"></div>
            <div class="absolute top-1/3 -right-8 w-24 h-24 bg-[#1c1a26] rounded-full opacity-40"></div>

            <div class="relative z-10 text-center w-full">
                <h1 class="text-6xl font-black text-white leading-tight uppercase tracking-wider">
                    SELAMAT
                </h1>
                <h1 class="text-6xl font-black text-lime-400 leading-tight uppercase tracking-wider mt-1">
                    DATANG
                </h1>

                <div class="w-24 h-1 bg-lime-400 mx-auto my-6 rounded-full"></div>

                <p class="text-slate-200 font-bold text-xl uppercase tracking-[4px] leading-relaxed">
                    DI SISTEM
                </p>
                <p class="text-lime-400 font-black text-2xl uppercase tracking-[3px] mt-1">
                    BENGKEL FATIA
                </p>
                <p class="text-slate-400 text-xs uppercase tracking-[5px] mt-4 font-semibold">
                    KAMAL — BANGKALAN
                </p>
            </div>
        </div>

        <!-- ==================== KANAN: FORM LOGIN ==================== -->
        <div class="w-1/2 bg-[#e2e4e7] p-12 flex flex-col justify-center">
            <h2 class="text-xl font-black text-slate-800 text-center tracking-[4px] uppercase mb-8">MASUK</h2>

            <form method="POST" class="space-y-4">
                <input type="text" name="username" placeholder="USERNAME" required autofocus
                       class="w-full bg-white border border-slate-300 rounded px-4 py-3 text-sm text-slate-700 font-semibold focus:outline-none focus:border-lime-500 focus:ring-2 focus:ring-lime-300">

                <div class="relative">
                    <input type="password" id="password" name="password" placeholder="PASSWORD" required
                           class="w-full bg-white border border-slate-300 rounded px-4 py-3 pr-12 text-sm text-slate-700 font-semibold focus:outline-none focus:border-lime-500 focus:ring-2 focus:ring-lime-300">

                    <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-lime-600 focus:outline-none transition">
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>

                <button type="submit"
                        class="w-full bg-[#a3e635] hover:bg-[#84cc16] text-slate-950 font-black py-3 rounded uppercase tracking-[3px] text-sm shadow-lg transition">
                    MASUK
                </button>
            </form>
        </div>
    </div>

    <!-- ==================== SCRIPT ==================== -->
    <script>
        // Toggle Password
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // ==================== SWEETALERT2 NOTIFIKASI ====================

        // 1. Error Login (dari PHP)
        <?php if (!empty($error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: '<?= htmlspecialchars($error) ?>',
            confirmButtonColor: '#a3e635',
            confirmButtonText: 'Coba Lagi',
            background: '#ffffff',
            customClass: {
                confirmButton: 'text-slate-900 font-bold px-6 py-2 rounded-lg'
            }
        });
        <?php endif; ?>

        // 2. Session Expired
        <?php if (isset($_GET['expired'])): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Session Expired',
            text: 'Sesi Anda telah berakhir. Silakan login kembali.',
            confirmButtonColor: '#a3e635',
            confirmButtonText: 'OK'
        });
        <?php endif; ?>

        // 3. Akses Ditolak (dari halaman lain)
        <?php if (isset($_GET['denied'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki hak akses ke halaman tersebut.',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK'
        });
        <?php endif; ?>

        // 4. Logout Berhasil
        <?php if (isset($_GET['logout'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Logout',
            text: 'Anda telah keluar dari sistem.',
            confirmButtonColor: '#a3e635',
            confirmButtonText: 'OK',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: true
        });
        <?php endif; ?>
    </script>
</body>
</html>