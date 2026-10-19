<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Owner');

// ==================== QUERY STATISTIK ====================
$totalServis     = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
$totalPendapatan = $pdo->query("SELECT COALESCE(SUM(total_biaya),0) FROM transaksi WHERE status='Dibayar'")->fetchColumn();
$stokRendah      = $pdo->query("SELECT COUNT(*) FROM sparepart WHERE stok < 10")->fetchColumn();
$totalPelanggan  = $pdo->query("SELECT COUNT(*) FROM pelanggan")->fetchColumn();
$totalMekanik    = $pdo->query("SELECT COUNT(*) FROM mekanik")->fetchColumn();
$totalSparepart  = $pdo->query("SELECT COUNT(*) FROM sparepart")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Owner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">
<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">

    <!-- ==================== KIRI: PANEL GELAP ==================== -->
    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-8 flex flex-col justify-between relative overflow-hidden">
        <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-[#1c1a26] rounded-full opacity-60"></div>

        <!-- Logo di tengah -->
        <div class="relative z-10 flex justify-center items-center flex-1">
            <img src="gambar/logo.png" alt="Logo Bengkel Fatia" 
                 class="max-h-56 max-w-full object-contain drop-shadow-2xl">
        </div>

        <!-- Info Bawah -->
        <div class="relative z-10">
            <h2 class="text-white font-black text-xl mb-1">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
            <p class="text-lime-400 text-sm font-semibold">Pemilik Bengkel Fatia</p>

            <div class="grid grid-cols-2 gap-2 mt-4">
                <!-- Total Servis -->
                <div class="bg-white/10 rounded p-2 text-center">
                    <p class="text-lime-400 font-black text-lg"><?= $totalServis ?></p>
                    <p class="text-white text-xs">Total Servis</p>
                </div>
                <!-- Total Pelanggan -->
                <div class="bg-white/10 rounded p-2 text-center">
                    <p class="text-lime-400 font-black text-lg"><?= $totalPelanggan ?></p>
                    <p class="text-white text-xs">Pelanggan</p>
                </div>
                <!-- Total Mekanik -->
                <div class="bg-white/10 rounded p-2 text-center">
                    <p class="text-lime-400 font-black text-lg"><?= $totalMekanik ?></p>
                    <p class="text-white text-xs">Mekanik</p>
                </div>
                <!-- Total Sparepart -->
                <div class="bg-white/10 rounded p-2 text-center">
                    <p class="text-lime-400 font-black text-lg"><?= $totalSparepart ?></p>
                    <p class="text-white text-xs">Sparepart</p>
                </div>
                <!-- Total Pendapatan -->
                <div class="bg-white/10 rounded p-2 text-center col-span-2">
                    <p class="text-lime-400 font-black text-sm">Rp <?= number_format($totalPendapatan,0,',','.') ?></p>
                    <p class="text-white text-xs">Total Pendapatan</p>
                </div>
                <!-- Stok Rendah -->
                <div class="bg-white/10 rounded p-2 text-center col-span-2">
                    <p class="text-red-400 font-black text-lg"><?= $stokRendah ?></p>
                    <p class="text-white text-xs">Sparepart Perlu Restock</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== KANAN: SIDEBAR ==================== -->
    <?php include 'includes/sidebar.php'; ?>

</div>

<!-- ==================== SWEETALERT2 SCRIPT ==================== -->
<script>
// Notifikasi kalau ada akses ditolak (dari halaman lain)
<?php if (isset($_SESSION['akses_ditolak'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Akses Ditolak',
    text: 'Anda tidak memiliki hak akses ke halaman tersebut.',
    confirmButtonColor: '#ef4444',
    confirmButtonText: 'OK',
    position: 'center',
    backdrop: 'rgba(0,0,0,0.5)'
});
<?php unset($_SESSION['akses_ditolak']); ?>
<?php endif; ?>
</script>

</body>
</html>