<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$redirect = $_SESSION['redirect_to'] ?? 'dashboard_admin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Loading...</title>
    <meta http-equiv="refresh" content="2;url=<?= $redirect ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* ==================== LOADER BESAR ==================== */
        .loader {
            border: 10px solid rgba(209, 213, 219, 0.6);   /* Abu-abu transparan */
            border-top: 10px solid #a3e635;                 /* Lime hijau */
            border-radius: 50%;
            width: 200px;                                    /* ← Diperbesar dari 80px */
            height: 200px;
            animation: spin 1s linear infinite;
            box-shadow: 0 0 30px rgba(163, 230, 53, 0.4);   /* Efek glow */
        }
        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Background blur */
        .blur-bg {
            filter: blur(5px);
            opacity: 0.45;
        }

        /* Teks loading di bawah lingkaran (opsional) */
        .loading-text {
            color: #252332;
            font-weight: 900;
            letter-spacing: 4px;
            margin-top: 25px;
            font-size: 14px;
            text-transform: uppercase;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50%      { opacity: 1; }
        }
    </style>
</head>
<body class="bg-[#e2e4e7] flex items-center justify-center h-screen">

    <!-- ==================== BACKGROUND BLUR (Login) ==================== -->
    <div class="w-[700px] h-[430px] rounded-2xl shadow-2xl flex overflow-hidden blur-bg">

        <!-- Kiri -->
        <div class="w-1/2 bg-[#252332] relative p-10 flex flex-col justify-center overflow-hidden">
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-[#2d2b3d] rounded-full"></div>
            <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-[#222030] rounded-full opacity-60"></div>
            <div class="relative z-10">
                <h1 class="text-white font-black leading-[1.1]" style="font-size: 52px;">
                    SELAMAT<br>DATANG
                </h1>
                <div class="mt-4 ml-1">
                    <p class="text-white font-bold uppercase" style="font-size: 17px; letter-spacing: 3px;">
                        DI SISTEM<br>BENGKEL FATIA
                    </p>
                </div>
            </div>
        </div>

        <!-- Kanan -->
        <div class="w-1/2 bg-[#e2e4e7] p-10 flex flex-col justify-center">
            <h2 class="text-center text-slate-700 font-bold uppercase mb-6" style="font-size: 15px; letter-spacing: 3px;">MASUK</h2>
            <div class="bg-white rounded-sm px-3 py-3 mb-3 text-sm text-slate-400">USERNAME</div>
            <div class="bg-white rounded-sm px-3 py-3 mb-3 text-sm text-slate-400">PASSWORD</div>
            <div class="bg-[#a3e635] text-center py-2.5 rounded-sm font-black text-slate-900 uppercase tracking-widest text-sm">MASUK</div>
        </div>
    </div>

    <!-- ==================== OVERLAY LOADING ==================== -->
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <div class="loader"></div>
        <div class="loading-text">Memuat...</div>
    </div>

</body>
</html>