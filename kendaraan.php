<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/pagination.php';
cek_login('Admin');

$search = $_GET['q'] ?? '';

// ==================== BASE QUERY JOIN ====================
$baseSql = "FROM kendaraan k JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan";

// ==================== HITUNG TOTAL ====================
if ($search) {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) $baseSql 
                                WHERE k.no_polisi LIKE ? 
                                OR k.jenis_motor LIKE ? 
                                OR p.nama LIKE ? 
                                OR k.id_kendaraan LIKE ?");
    $stmtCount->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $totalData = $stmtCount->fetchColumn();
} else {
    $totalData = $pdo->query("SELECT COUNT(*) $baseSql")->fetchColumn();
}

// ==================== PAGINATION ====================
$pg = paginate($totalData, 9);

// ==================== AMBIL DATA ====================
if ($search) {
    $stmt = $pdo->prepare("SELECT k.*, p.nama AS nama_pelanggan $baseSql 
                           WHERE k.no_polisi LIKE ? 
                           OR k.jenis_motor LIKE ? 
                           OR p.nama LIKE ? 
                           OR k.id_kendaraan LIKE ? 
                           ORDER BY k.id_kendaraan DESC 
                           LIMIT {$pg['limit']} OFFSET {$pg['offset']}");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT k.*, p.nama AS nama_pelanggan $baseSql 
                         ORDER BY k.id_kendaraan DESC 
                         LIMIT {$pg['limit']} OFFSET {$pg['offset']}");
}
$data = $stmt->fetchAll();

// ==================== STATISTIK ====================
$totalKendaraan = $pdo->query("SELECT COUNT(*) FROM kendaraan")->fetchColumn();
$totalPelanggan = $pdo->query("SELECT COUNT(DISTINCT id_pelanggan) FROM kendaraan")->fetchColumn();

// ==================== AMBIL FLASH MESSAGE ====================
$flash_msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kendaraan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        table tbody tr { height: 38px; }
        table thead tr { height: 36px; }
    </style>
</head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">

<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">

    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-5 flex flex-col">

        <!-- ==================== HEADER + BADGE ==================== -->
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-3">
                <h1 class="text-white font-black text-lg tracking-wider">DATA KENDARAAN</h1>
                <span class="text-[10px] font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/40">
                    <i class="fas fa-user-shield"></i> ADMIN
                </span>
            </div>
            <div class="flex gap-2 text-xs">
                <span class="bg-white/10 text-lime-400 px-3 py-1 rounded-full font-bold">
                    <i class="fas fa-motorcycle"></i> <?= $totalKendaraan ?> Kendaraan
                </span>
                <span class="bg-white/10 text-lime-400 px-3 py-1 rounded-full font-bold">
                    <i class="fas fa-users"></i> <?= $totalPelanggan ?> Pemilik
                </span>
            </div>
        </div>

        <!-- ==================== SEARCH BAR ==================== -->
        <form method="GET" class="mb-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-lime-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari no polisi, jenis motor, atau pemilik..."
                       autocomplete="off"
                       class="w-full bg-white/5 border-2 border-slate-600 text-white placeholder-slate-500 rounded-xl pl-11 pr-24 py-2 text-sm focus:outline-none focus:border-lime-500 transition-all">
                <?php if ($search): ?>
                    <a href="kendaraan.php" 
                       class="absolute inset-y-0 right-14 flex items-center text-slate-500 hover:text-red-400 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                <?php endif; ?>
                <button type="submit" 
                        class="absolute inset-y-1 right-1 bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold px-3 rounded-lg text-xs tracking-wider transition">
                    CARI
                </button>
            </div>
            <?php if ($search): ?>
                <p class="text-slate-400 text-xs mt-2 ml-1">
                    <i class="fas fa-info-circle text-lime-400"></i>
                    Ditemukan <span class="text-lime-400 font-bold"><?= $totalData ?></span> hasil untuk 
                    "<span class="text-white font-semibold"><?= htmlspecialchars($search) ?></span>"
                </p>
            <?php endif; ?>
        </form>

        <!-- ==================== TABEL (Tanpa Keluhan) ==================== -->
        <div class="flex-1 rounded-lg overflow-hidden">
            <table class="w-full text-sm table-fixed">
                <thead class="bg-slate-700 text-white">
                    <tr>
                        <th class="p-2 text-left w-10">No</th>
                        <th class="p-2 text-left w-40">Pemilik</th>
                        <th class="p-2 text-left w-32">No Polisi</th>
                        <th class="p-2 text-left">Motor</th>
                        <th class="p-2 text-center w-20">Tahun</th>
                        <th class="p-2 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-8">
                            <i class="fas fa-motorcycle text-2xl block mb-2 text-slate-600"></i>
                            <?= $search ? 'Tidak ada hasil yang cocok' : 'Belum ada data kendaraan' ?>
                        </td>
                    </tr>
                <?php else: foreach ($data as $i => $k): 
                    $motor = strtolower($k['jenis_motor']);
                    if (strpos($motor, 'honda') !== false) {
                        $motorBadge = 'bg-red-500/20 text-red-300 border-red-500/40';
                    } elseif (strpos($motor, 'yamaha') !== false) {
                        $motorBadge = 'bg-blue-500/20 text-blue-300 border-blue-500/40';
                    } elseif (strpos($motor, 'suzuki') !== false) {
                        $motorBadge = 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40';
                    } elseif (strpos($motor, 'kawasaki') !== false) {
                        $motorBadge = 'bg-green-500/20 text-green-300 border-green-500/40';
                    } else {
                        $motorBadge = 'bg-slate-500/20 text-slate-300 border-slate-500/40';
                    }
                    $no = $pg['offset'] + $i + 1;
                ?>
                    <tr class="text-white border-b border-slate-700 hover:bg-slate-700/50 transition">
                        <td class="p-2 text-slate-400"><?= $no ?></td>
                        <td class="p-2 font-semibold truncate"><?= htmlspecialchars($k['nama_pelanggan']) ?></td>
                        <td class="p-2">
                            <span class="font-mono text-xs font-bold text-lime-400 bg-lime-500/10 px-2 py-0.5 rounded border border-lime-500/30">
                                <?= htmlspecialchars($k['no_polisi']) ?>
                            </span>
                        </td>
                        <td class="p-2">
                            <span class="inline-flex items-center gap-1 <?= $motorBadge ?> px-2 py-0.5 rounded-full border font-bold text-[10px]">
                                <i class="fas fa-motorcycle"></i> <?= htmlspecialchars($k['jenis_motor']) ?>
                            </span>
                        </td>
                        <td class="p-2 text-center text-xs font-mono"><?= htmlspecialchars($k['tahun']) ?></td>
                        <td class="p-2 text-center">
                            <div class="flex justify-center gap-1">
                                <a href="kendaraan_form.php?id=<?= $k['id_kendaraan'] ?>" 
                                   class="inline-flex items-center justify-center w-6 h-6 bg-yellow-400 hover:bg-yellow-500 text-slate-900 rounded transition"
                                   title="Edit">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </a>
                                <button onclick="confirmDelete('<?= $k['id_kendaraan'] ?>', '<?= htmlspecialchars(addslashes($k['no_polisi'])) ?>')"
                                        class="inline-flex items-center justify-center w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded transition"
                                        title="Hapus">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ==================== PAGINATION ==================== -->
        <?= renderPagination($pg, 'kendaraan.php', $search ? ['q' => $search] : []) ?>

        <!-- ==================== TOMBOL AKSI BAWAH ==================== -->
        <div class="flex gap-2 mt-1">
            <a href="kendaraan_form.php" 
               class="bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold px-3.5 py-1.5 rounded text-xs transition flex items-center gap-2">
                <i class="fas fa-plus"></i> CREATE DATA
            </a>
            <a href="laporan_kendaraan.php" target="_blank" 
               class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-3.5 py-1.5 rounded text-xs transition flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
        </div>

    </div>

    <!-- ==================== SIDEBAR ==================== -->
    <?php include 'includes/sidebar.php'; ?>

</div>

<!-- ==================== SWEETALERT2 SCRIPT ==================== -->
<script>
// ==================== KONFIRMASI HAPUS ====================
function confirmDelete(id, no_polisi) {
    Swal.fire({
        title: 'Yakin hapus data ini?',
        html: `Kendaraan dengan No Polisi <b>"${no_polisi}"</b> akan dihapus permanen!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'kendaraan_hapus.php?id=' + id;
        }
    });
}

// ==================== FLASH MESSAGE ====================
<?php if (!empty($flash_msg)): ?>
if (window.history.replaceState) {
    const url = new URL(window.location);
    url.searchParams.delete('msg');
    window.history.replaceState({}, document.title, url);
}

<?php 
$msg_lower = strtolower($flash_msg);
$is_error = strpos($msg_lower, 'gagal') !== false || strpos($msg_lower, 'error') !== false;
$is_delete = strpos($msg_lower, 'hapus') !== false;
?>

<?php if ($is_error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= htmlspecialchars($flash_msg) ?>',
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'OK',
        position: 'center',
        backdrop: 'rgba(0,0,0,0.5)'
    });
<?php elseif ($is_delete): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil Dihapus!',
        text: '<?= htmlspecialchars($flash_msg) ?>',
        confirmButtonColor: '#a3e635',
        confirmButtonText: 'OK',
        position: 'center',
        backdrop: 'rgba(0,0,0,0.5)',
        timer: 2500,
        timerProgressBar: true
    });
<?php else: ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= htmlspecialchars($flash_msg) ?>',
        confirmButtonColor: '#a3e635',
        confirmButtonText: 'OK',
        position: 'center',
        backdrop: 'rgba(0,0,0,0.5)',
        timer: 2500,
        timerProgressBar: true
    });
<?php endif; ?>
<?php endif; ?>

// ==================== NOTIFIKASI TIDAK ADA HASIL ====================
<?php if ($search && $totalData == 0): ?>
Swal.fire({
    icon: 'info',
    title: 'Tidak Ditemukan',
    text: 'Tidak ada kendaraan dengan kata kunci "<?= htmlspecialchars($search) ?>"',
    confirmButtonColor: '#a3e635',
    confirmButtonText: 'OK',
    position: 'center'
});
<?php endif; ?>
</script>

</body>
</html>