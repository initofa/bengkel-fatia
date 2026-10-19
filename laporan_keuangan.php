<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Owner');

// ==================== AMBIL DAFTAR TAHUN ====================
$tahunList = $pdo->query("SELECT DISTINCT YEAR(tanggal_transaksi) AS tahun 
                          FROM transaksi 
                          WHERE status='Dibayar'
                          UNION 
                          SELECT DISTINCT YEAR(tanggal_laporan) AS tahun 
                          FROM laporan_keuangan
                          ORDER BY tahun DESC")->fetchAll();

$tahun = (int)($_GET['tahun'] ?? (count($tahunList) > 0 ? $tahunList[0]['tahun'] : date('Y')));

// ==================== DATA PENGELUARAN DARI laporan_keuangan ====================
$stmtKeluar = $pdo->prepare("SELECT MONTH(tanggal_laporan) AS bulan, 
                                    SUM(uang_keluar) AS uang_keluar,
                                    MAX(id_laporan) AS id_laporan,
                                    MAX(keterangan) AS keterangan
                             FROM laporan_keuangan 
                             WHERE YEAR(tanggal_laporan) = ?
                             GROUP BY MONTH(tanggal_laporan)");
$stmtKeluar->execute([$tahun]);
$dataKeluar = [];
foreach ($stmtKeluar->fetchAll() as $r) {
    $dataKeluar[(int)$r['bulan']] = $r;
}

// ==================== DATA PEMASUKAN LIVE DARI TRANSAKSI ====================
$stmtMasuk = $pdo->prepare("SELECT MONTH(tanggal_transaksi) AS bulan,
                                   SUM(total_biaya) AS uang_masuk
                            FROM transaksi 
                            WHERE YEAR(tanggal_transaksi) = ?
                            AND status='Dibayar'
                            GROUP BY MONTH(tanggal_transaksi)");
$stmtMasuk->execute([$tahun]);
$dataMasuk = [];
foreach ($stmtMasuk->fetchAll() as $r) {
    $dataMasuk[(int)$r['bulan']] = $r;
}

// ==================== GABUNGKAN DATA PER BULAN ====================
$dataPerBulan = [];
$totalMasuk = 0;
$totalKeluar = 0;

for ($b = 1; $b <= 12; $b++) {
    $masuk  = (float)($dataMasuk[$b]['uang_masuk'] ?? 0);
    $keluar = (float)($dataKeluar[$b]['uang_keluar'] ?? 0);
    
    if ($masuk > 0 || $keluar > 0) {
        $dataPerBulan[$b] = [
            'bulan' => $b,
            'uang_masuk' => $masuk,
            'uang_keluar' => $keluar,
            'laba' => $masuk - $keluar,
            'id_laporan' => $dataKeluar[$b]['id_laporan'] ?? null,
        ];
        $totalMasuk += $masuk;
        $totalKeluar += $keluar;
    }
}

$totalLaba = $totalMasuk - $totalKeluar;

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Bengkel Fatia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">

<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">

    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-6 flex flex-col">

        <!-- Header + Filter Tahun -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-white font-black text-lg tracking-wider">LAPORAN KEUANGAN</h1>

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
        <div class="bg-lime-500/20 border-2 border-lime-400 rounded-lg p-3 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-lime-300 text-xs font-bold tracking-wider">TOTAL LABA BERSIH TAHUN <?= $tahun ?>:</p>
                    <p class="text-white font-black text-xl">Rp <?= number_format($totalLaba, 0, ',', '.') ?></p>
                </div>
                <a href="laporan_keuangan_detail.php?tahun=<?= $tahun ?>" target="_blank"
                   class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-4 py-2 rounded text-sm transition">
                    <i class="fas fa-print"></i> CETAK TAHUNAN
                </a>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-lime-400/30 text-xs">
                <div>
                    <span class="text-slate-400">Pemasukan:</span>
                    <span class="text-lime-300 font-bold">Rp <?= number_format($totalMasuk,0,',','.') ?></span>
                </div>
                <div>
                    <span class="text-slate-400">Pengeluaran:</span>
                    <span class="text-red-300 font-bold">Rp <?= number_format($totalKeluar,0,',','.') ?></span>
                </div>
            </div>
        </div>

        <!-- Tabel Bulan -->
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-700 text-white z-10">
                    <tr>
                        <th class="p-2 text-left w-12">No</th>
                        <th class="p-2 text-left">Bulan</th>
                        <th class="p-2 text-right">Pemasukan</th>
                        <th class="p-2 text-right">Pengeluaran</th>
                        <th class="p-2 text-right">Laba/Rugi</th>
                        <th class="p-2 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                for ($b = 1; $b <= 12; $b++): 
                    $row = $dataPerBulan[$b] ?? null;
                    $masuk  = $row ? (float)$row['uang_masuk'] : 0;
                    $keluar = $row ? (float)$row['uang_keluar'] : 0;
                    $laba   = $row ? (float)$row['laba'] : 0;
                    $isActive = ($row != null);
                ?>
                    <tr class="text-white border-b border-slate-600 hover:bg-slate-700 transition <?= !$isActive ? 'opacity-50' : '' ?>">
                        <td class="p-2 text-slate-400"><?= $b ?></td>
                        <td class="p-2 font-bold text-lime-400"><?= $namaBulan[$b] ?></td>
                        <td class="p-2 text-right font-mono text-xs text-lime-300">
                            <?= $isActive ? 'Rp ' . number_format($masuk, 0, ',', '.') : '—' ?>
                        </td>
                        <td class="p-2 text-right font-mono text-xs text-red-300">
                            <?= $isActive ? 'Rp ' . number_format($keluar, 0, ',', '.') : '—' ?>
                        </td>
                        <td class="p-2 text-right font-mono text-xs font-bold <?= $laba >= 0 ? 'text-blue-300' : 'text-red-300' ?>">
                            <?php if ($isActive): ?>
                                <?= $laba >= 0 ? '+ ' : '- ' ?>Rp <?= number_format(abs($laba), 0, ',', '.') ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="p-2 text-center">
                            <?php if ($isActive && $row['id_laporan']): ?>
                                <a href="laporan_keuangan_detail.php?id=<?= $row['id_laporan'] ?>"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white font-bold px-3 py-1 rounded text-xs transition">
                                    <i class="fas fa-print"></i> PRINT
                                </a>
                            <?php elseif ($isActive): ?>
                                <span class="text-yellow-400 text-[10px]" title="Belum ada laporan pengeluaran">
                                    <i class="fas fa-exclamation-triangle"></i> Belum
                                </span>
                            <?php else: ?>
                                <span class="text-slate-500 text-xs">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-lime-500/20 font-bold text-lime-300 border-t-2 border-lime-400">
                        <td colspan="2" class="p-2 text-right">TOTAL TAHUN <?= $tahun ?></td>
                        <td class="p-2 text-right font-mono">Rp <?= number_format($totalMasuk, 0, ',', '.') ?></td>
                        <td class="p-2 text-right font-mono">Rp <?= number_format($totalKeluar, 0, ',', '.') ?></td>
                        <td class="p-2 text-right font-mono <?= $totalLaba >= 0 ? 'text-blue-300' : 'text-red-300' ?>">
                            <?= $totalLaba >= 0 ? '+ ' : '- ' ?>Rp <?= number_format(abs($totalLaba), 0, ',', '.') ?>
                        </td>
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