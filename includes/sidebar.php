<?php
$role = $_SESSION['role'] ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF']);
$dashboard = ($role == 'Owner') ? 'dashboard_owner.php' : 'dashboard_admin.php';
?>
<div class="w-[300px] bg-[#e2e4e7] p-6 flex flex-col">

    <!-- HEADER KOTAK -->
    <div class="bg-[#252332] border-4 border-slate-900 p-3 mb-6 rounded-sm">
        <h2 class="text-center font-black text-white text-sm tracking-widest uppercase">
            <?= $role == 'Owner' ? 'DASHBOARD OWNER' : 'DASHBOARD ADMIN' ?>
        </h2>
    </div>

    <!-- MENU NAVIGASI -->
    <div class="space-y-3 flex-1">
        <?php if ($role == 'Owner'): ?>
            <a href="laporan_service.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Laporan Service</a>
            <a href="laporan_keuangan.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Laporan Keuangan</a>
            <a href="sparepart.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Mengelola Stok</a>
        <?php else: ?>
            <a href="pelanggan.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Data Pelanggan</a>
            <a href="mekanik.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Data Mekanik</a>
            <a href="sparepart.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Data Sparepart</a>
            <a href="kendaraan.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Data Kendaraan</a>
            <a href="pembayaran.php" class="block bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-3 px-4 rounded text-center shadow-md transition">Pembayaran</a>
            <a href="laporan_keuangan_admin.php" class="block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded text-center shadow-md transition">
                <i class="fas fa-file-invoice-dollar"></i> Laporan Keuangan
            </a>
        <?php endif; ?>
    </div>

    <!-- TOMBOL BAWAH -->
    <div class="mt-4 space-y-2">
        <?php if ($currentPage != $dashboard): ?>
            <a href="<?= $dashboard ?>" class="block bg-slate-500 hover:bg-slate-600 text-white font-bold py-2 rounded text-center text-sm transition">Back</a>
        <?php endif; ?>
        <button onclick="confirmLogout()" class="w-full block bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 rounded text-center text-sm tracking-widest transition">
            KELUAR
        </button>
    </div>
</div>

<!-- SWEETALERT2 + LOGOUT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmLogout() {
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: 'Anda akan keluar dari sistem',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#a3e635',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php?logout=1';
        }
    });
}
</script>