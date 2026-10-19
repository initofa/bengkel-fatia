<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login(['Admin', 'Owner']);

// ==================== AMBIL DAFTAR TAHUN ====================
$tahunList = $pdo->query("SELECT DISTINCT YEAR(tanggal_transaksi) AS tahun 
                          FROM transaksi 
                          ORDER BY tahun DESC")->fetchAll();

$tahun = $_GET['tahun'] ?? (count($tahunList) > 0 ? $tahunList[0]['tahun'] : date('Y'));

// ==================== AMBIL DATA PER BULAN ====================
$stmt = $pdo->prepare("SELECT 
                        MONTH(tanggal_transaksi) AS bulan,
                        COUNT(*) AS jumlah,
                        COALESCE(SUM(total_biaya), 0) AS total
                       FROM transaksi 
                       WHERE YEAR(tanggal_transaksi) = ?
                       GROUP BY MONTH(tanggal_transaksi)
                       ORDER BY bulan ASC");
$stmt->execute([$tahun]);
$rawData = $stmt->fetchAll();

// ==================== SUSUN DATA PER BULAN ====================
$dataPerBulan = [];
foreach ($rawData as $row) {
    $dataPerBulan[(int)$row['bulan']] = $row;
}

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$totalSetahun = array_sum(array_column($rawData, 'total'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service - Bengkel Fatia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">

<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">

    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-6 flex flex-col">

        <!-- Header + Filter Tahun -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-white font-black text-lg tracking-wider">LAPORAN SERVICE</h1>

            <form method="GET" class="flex gap-2 items-center">
                <label class="text-lime-400 text-xs font-bold tracking-wider">TAHUN:</label>
                <select name="tahun" onchange="this.form.submit()"
                        class="bg-white/10 border border-lime-500 text-white font-bold rounded px-3 py-1.5 text-sm focus:outline-none cursor-pointer">
                    <?php if (empty($tahunList)): ?>
                        <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                    <?php else: foreach ($tahunList as $t): ?>
                        <option value="<?= $t['tahun'] ?>" <?= $tahun == $t['tahun'] ? 'selected' : '' ?>>
                            <?= $t['tahun'] ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </form>
        </div>

        <!-- Ringkasan Total Setahun -->
        <div class="bg-lime-500/20 border-2 border-lime-400 rounded-lg p-3 mb-4 flex justify-between items-center">
            <div>
                <p class="text-lime-300 text-xs font-bold tracking-wider">TOTAL PENDAPATAN TAHUN <?= $tahun ?>:</p>
                <p class="text-white font-black text-xl">Rp <?= number_format($totalSetahun, 0, ',', '.') ?></p>
            </div>
            <a href="laporan_service_detail.php?tahun=<?= $tahun ?>" target="_blank"
               class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-4 py-2 rounded text-sm transition">
                <i class="fas fa-print"></i> CETAK TAHUNAN
            </a>
        </div>

        <!-- Tabel Bulan -->
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-700 text-white z-10">
                    <tr>
                        <th class="p-2 text-left w-12">No</th>
                        <th class="p-2 text-left">Bulan</th>
                        <th class="p-2 text-center">Servis</th>
                        <th class="p-2 text-right">Total Pendapatan</th>
                        <th class="p-2 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                for ($b = 1; $b <= 12; $b++): 
                    $row = $dataPerBulan[$b] ?? null;
                    $jumlah = $row['jumlah'] ?? 0;
                    $total  = $row['total'] ?? 0;
                ?>
                    <tr class="text-white border-b border-slate-600 hover:bg-slate-700 transition">
                        <td class="p-2 text-slate-400"><?= $b ?></td>
                        <td class="p-2 font-bold text-lime-400"><?= $namaBulan[$b] ?></td>
                        <td class="p-2 text-center"><?= $jumlah ?></td>
                        <td class="p-2 text-right font-mono text-xs">Rp <?= number_format($total, 0, ',', '.') ?></td>
                        <td class="p-2 text-center">
                            <?php if ($jumlah > 0): ?>
                                <a href="laporan_service_detail.php?bulan=<?= sprintf('%02d', $b) ?>&tahun=<?= $tahun ?>"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white font-bold px-3 py-1 rounded text-xs transition">
                                    <i class="fas fa-print"></i> PRINT
                                </a>
                            <?php else: ?>
                                <span class="text-slate-500 text-xs">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-lime-500/20 font-bold text-lime-300 border-t-2 border-lime-400">
                        <td colspan="3" class="p-2 text-right">TOTAL TAHUN <?= $tahun ?></td>
                        <td class="p-2 text-right font-mono">Rp <?= number_format($totalSetahun, 0, ',', '.') ?></td>
                        <td class="p-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <?php include 'includes/sidebar.php'; ?>

</div>

</body>
</html>