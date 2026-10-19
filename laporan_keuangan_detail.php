<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Owner');

// ==================== FUNGSI BANTU ====================
function formatTanggalID($tanggal) {
    $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $t = strtotime($tanggal);
    return $hari[date('l', $t)] . ', ' . date('d', $t) . ' ' . $bulan[(int)date('m', $t)] . ' ' . date('Y', $t);
}

function namaBulanIndo($date) {
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $t = strtotime($date);
    return strtoupper($bulan[(int)date('n', $t)] . ' ' . date('Y', $t));
}

$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

// ============================================================
// MODE 1: TAHUNAN (?tahun=2026)
// ============================================================
if (isset($_GET['tahun'])) {
    $tahun = (int)$_GET['tahun'];

    // Pemasukan LIVE dari transaksi
    $stmtM = $pdo->prepare("SELECT MONTH(tanggal_transaksi) AS bulan, 
                                   SUM(total_biaya) AS uang_masuk,
                                   COUNT(*) AS jml_transaksi
                            FROM transaksi 
                            WHERE YEAR(tanggal_transaksi) = ?
                            AND status='Dibayar'
                            GROUP BY MONTH(tanggal_transaksi)");
    $stmtM->execute([$tahun]);
    $dataMasuk = [];
    foreach ($stmtM->fetchAll() as $r) {
        $dataMasuk[(int)$r['bulan']] = $r;
    }

    // Pengeluaran dari laporan_keuangan
    $stmtK = $pdo->prepare("SELECT MONTH(tanggal_laporan) AS bulan, 
                                   SUM(uang_keluar) AS uang_keluar
                            FROM laporan_keuangan 
                            WHERE YEAR(tanggal_laporan) = ?
                            GROUP BY MONTH(tanggal_laporan)");
    $stmtK->execute([$tahun]);
    $dataKeluar = [];
    foreach ($stmtK->fetchAll() as $r) {
        $dataKeluar[(int)$r['bulan']] = $r;
    }

    $perBulan = [];
    $totalMasuk = 0; $totalKeluar = 0;
    for ($b = 1; $b <= 12; $b++) {
        $m = (float)($dataMasuk[$b]['uang_masuk'] ?? 0);
        $k = (float)($dataKeluar[$b]['uang_keluar'] ?? 0);
        $perBulan[$b] = [
            'uang_masuk' => $m,
            'uang_keluar' => $k,
            'laba' => $m - $k,
            'jml_transaksi' => (int)($dataMasuk[$b]['jml_transaksi'] ?? 0),
            'isActive' => ($m > 0 || $k > 0)
        ];
        $totalMasuk += $m;
        $totalKeluar += $k;
    }
    $totalLaba = $totalMasuk - $totalKeluar;
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Keuangan Tahunan <?= $tahun ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            @media print {
                body { padding: 0; margin: 0; background: white; }
                .no-print { display: none !important; }
                @page { size: A4 portrait; margin: 1.5cm; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                .print-container { box-shadow: none !important; border-radius: 0 !important; max-width: 100% !important; }
            }
            body { font-family: system-ui, sans-serif; background: #f1f5f9; }
        </style>
    </head>
    <body class="p-6">

    <div class="print-container max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">

        <div class="no-print bg-[#252332] text-white p-4 flex justify-between items-center">
            <a href="laporan_keuangan.php?tahun=<?= $tahun ?>" 
               class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400">Preview Laporan Tahunan</span>
                <button onclick="window.print()" 
                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded shadow text-sm transition flex items-center gap-2">
                    <i class="fas fa-print"></i> CETAK PDF
                </button>
            </div>
        </div>

        <div class="bg-gradient-to-r from-[#252332] to-[#3b3852] p-6 text-white border-b-4 border-[#a3e635]">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-[#a3e635] rounded-full flex items-center justify-center text-3xl flex-shrink-0">
                    💰
                </div>
                <div>
                    <h1 class="text-2xl font-black uppercase tracking-wide">BENGKEL MOTOR FATIA KAMAL</h1>
                    <p class="text-lime-400 font-bold text-sm tracking-wider">LAPORAN KEUANGAN TAHUNAN <?= $tahun ?></p>
                </div>
            </div>
        </div>

        <div class="p-6 pb-0">
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="bg-lime-50 border-2 border-lime-500 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-slate-600 font-bold">TOTAL PEMASUKAN</p>
                    <p class="text-xl font-black text-lime-700">Rp <?= number_format($totalMasuk, 0, ',', '.') ?></p>
                </div>
                <div class="bg-red-50 border-2 border-red-500 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-slate-600 font-bold">TOTAL PENGELUARAN</p>
                    <p class="text-xl font-black text-red-700">Rp <?= number_format($totalKeluar, 0, ',', '.') ?></p>
                </div>
                <div class="<?= $totalLaba >= 0 ? 'bg-blue-50 border-blue-500' : 'bg-red-50 border-red-500' ?> border-2 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-slate-600 font-bold"><?= $totalLaba >= 0 ? 'LABA BERSIH' : 'RUGI' ?></p>
                    <p class="<?= $totalLaba >= 0 ? 'text-blue-700' : 'text-red-700' ?> text-xl font-black">
                        Rp <?= number_format(abs($totalLaba), 0, ',', '.') ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="px-6 pb-6">
            <h2 class="text-sm font-black text-slate-800 mb-2 flex items-center gap-2 border-b-2 border-lime-400 pb-2">
                <i class="fas fa-calendar-alt text-lime-500"></i> RINCIAN PER BULAN
            </h2>
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="bg-[#252332] text-white">
                        <th class="border border-slate-700 p-2 w-8">No</th>
                        <th class="border border-slate-700 p-2 text-left">Bulan</th>
                        <th class="border border-slate-700 p-2 text-center">Servis</th>
                        <th class="border border-slate-700 p-2 text-right">Pemasukan</th>
                        <th class="border border-slate-700 p-2 text-right">Pengeluaran</th>
                        <th class="border border-slate-700 p-2 text-right">Laba/Rugi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($b = 1; $b <= 12; $b++): 
                        $r = $perBulan[$b];
                        $isActive = $r['isActive'];
                    ?>
                        <tr class="<?= $isActive ? ($b % 2 == 0 ? 'bg-slate-50' : 'bg-white') : 'bg-slate-100 opacity-60' ?>">
                            <td class="border border-slate-300 p-2 text-center"><?= $b ?></td>
                            <td class="border border-slate-300 p-2 font-bold <?= $isActive ? 'text-slate-800' : 'text-slate-400' ?>">
                                <?= $namaBulan[$b] ?>
                            </td>
                            <td class="border border-slate-300 p-2 text-center <?= $isActive ? 'text-slate-700' : 'text-slate-400' ?>">
                                <?= $isActive ? $r['jml_transaksi'] : '—' ?>
                            </td>
                            <td class="border border-slate-300 p-2 text-right font-mono <?= $isActive ? 'text-lime-700' : 'text-slate-400' ?>">
                                <?= $isActive ? 'Rp ' . number_format($r['uang_masuk'], 0, ',', '.') : '—' ?>
                            </td>
                            <td class="border border-slate-300 p-2 text-right font-mono <?= $isActive ? 'text-red-700' : 'text-slate-400' ?>">
                                <?= $isActive ? 'Rp ' . number_format($r['uang_keluar'], 0, ',', '.') : '—' ?>
                            </td>
                            <td class="border border-slate-300 p-2 text-right font-mono font-bold <?= !$isActive ? 'text-slate-400' : ($r['laba'] >= 0 ? 'text-blue-700' : 'text-red-700') ?>">
                                <?php if ($isActive): ?>
                                    <?= $r['laba'] >= 0 ? '+ ' : '- ' ?>Rp <?= number_format(abs($r['laba']), 0, ',', '.') ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-lime-500 text-slate-900 font-black">
                        <td colspan="3" class="border border-slate-700 p-2 text-right">TOTAL TAHUN <?= $tahun ?></td>
                        <td class="border border-slate-700 p-2 text-right">Rp <?= number_format($totalMasuk, 0, ',', '.') ?></td>
                        <td class="border border-slate-700 p-2 text-right">Rp <?= number_format($totalKeluar, 0, ',', '.') ?></td>
                        <td class="border border-slate-700 p-2 text-right">
                            <?= $totalLaba >= 0 ? '+ ' : '- ' ?>Rp <?= number_format(abs($totalLaba), 0, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-slate-50 border-t p-4 flex justify-between text-xs text-slate-500">
            <div><i class="fas fa-print"></i> Sistem Informasi Bengkel Fatia Kamal</div>
            <div class="font-semibold text-[#252332]">Dicetak: <?= formatTanggalID(date('Y-m-d')) ?></div>
        </div>

    </div>

    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// MODE 2: BULANAN (?id=LAPxxxx)
// ============================================================
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT lk.*, a.nama AS nama_admin 
                       FROM laporan_keuangan lk 
                       JOIN admin a ON lk.id_admin = a.id_admin 
                       WHERE lk.id_laporan = ?");
$stmt->execute([$id]);
$laporan = $stmt->fetch();

if (!$laporan) {
    header("Location: laporan_keuangan.php");
    exit;
}

$bulanLaporan = (int)date('n', strtotime($laporan['tanggal_laporan']));
$tahunLaporan = (int)date('Y', strtotime($laporan['tanggal_laporan']));

// Hitung jumlah transaksi & pemasukan LIVE dari transaksi
$stmtCount = $pdo->prepare("SELECT COUNT(*) AS jml, COALESCE(SUM(total_biaya), 0) AS total
                            FROM transaksi 
                            WHERE MONTH(tanggal_transaksi) = ? 
                            AND YEAR(tanggal_transaksi) = ? 
                            AND status='Dibayar'");
$stmtCount->execute([$bulanLaporan, $tahunLaporan]);
$rowCount = $stmtCount->fetch();
$jumlahServis = (int)$rowCount['jml'];
$uangMasukLive = (float)$rowCount['total'];

// Pakai nilai live (kalau ada transaksi), fallback ke nilai tersimpan
$uangMasuk = $uangMasukLive > 0 ? $uangMasukLive : (float)$laporan['uang_masuk'];
$laba = $uangMasuk - $laporan['uang_keluar'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan <?= namaBulanIndo($laporan['tanggal_laporan']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { padding: 0; margin: 0; background: white; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 1.5cm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-container { box-shadow: none !important; border-radius: 0 !important; max-width: 100% !important; }
        }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; }
        .keterangan-box { white-space: pre-line; line-height: 1.6; }
    </style>
</head>
<body class="p-6">

<div class="print-container max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">

    <div class="no-print bg-[#252332] text-white p-4 flex justify-between items-center">
        <a href="laporan_keuangan.php?tahun=<?= $tahunLaporan ?>" 
           class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Preview Laporan Bulanan</span>
            <button onclick="window.print()" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded shadow text-sm transition flex items-center gap-2">
                <i class="fas fa-print"></i> CETAK PDF
            </button>
        </div>
    </div>

    <div class="bg-gradient-to-r from-[#252332] to-[#3b3852] p-6 text-white border-b-4 border-[#a3e635]">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-[#a3e635] rounded-full flex items-center justify-center text-3xl flex-shrink-0">
                💰
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-wide">BENGKEL MOTOR FATIA KAMAL</h1>
                <p class="text-lime-400 font-bold text-sm tracking-wider">
                    LAPORAN KEUANGAN <?= namaBulanIndo($laporan['tanggal_laporan']) ?>
                </p>
                <p class="text-slate-300 text-xs mt-1">
                    <i class="fas fa-user"></i> Dibuat oleh: <?= htmlspecialchars($laporan['nama_admin']) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="p-6 pb-0">
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-lime-50 border-2 border-lime-500 rounded-lg p-3 text-center">
                <p class="text-[10px] text-slate-600 font-bold">PEMASUKAN</p>
                <p class="text-xl font-black text-lime-700">Rp <?= number_format($uangMasuk, 0, ',', '.') ?></p>
            </div>
            <div class="bg-red-50 border-2 border-red-500 rounded-lg p-3 text-center">
                <p class="text-[10px] text-slate-600 font-bold">PENGELUARAN</p>
                <p class="text-xl font-black text-red-700">Rp <?= number_format($laporan['uang_keluar'], 0, ',', '.') ?></p>
            </div>
            <div class="<?= $laba >= 0 ? 'bg-blue-50 border-blue-500' : 'bg-red-50 border-red-500' ?> border-2 rounded-lg p-3 text-center">
                <p class="text-[10px] text-slate-600 font-bold"><?= $laba >= 0 ? 'LABA BERSIH' : 'RUGI' ?></p>
                <p class="<?= $laba >= 0 ? 'text-blue-700' : 'text-red-700' ?> text-xl font-black">
                    Rp <?= number_format(abs($laba), 0, ',', '.') ?>
                </p>
            </div>
        </div>
    </div>

    <div class="px-6 pb-6">
        <h2 class="text-sm font-black text-slate-800 mb-2 flex items-center gap-2 border-b-2 border-lime-400 pb-2">
            <i class="fas fa-receipt text-lime-500"></i> RINCIAN PEMASUKAN
        </h2>
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-[#252332] text-white">
                    <th class="border border-slate-700 p-2 w-8">No</th>
                    <th class="border border-slate-700 p-2 w-32">Kategori</th>
                    <th class="border border-slate-700 p-2">Keterangan</th>
                    <th class="border border-slate-700 p-2 text-right w-40">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white">
                    <td class="border border-slate-300 p-3 text-center align-top">1</td>
                    <td class="border border-slate-300 p-3 align-top font-bold text-slate-700">Pendapatan Servis</td>
                    <td class="border border-slate-300 p-3 align-top text-slate-700">
                        Total dari <b><?= number_format($jumlahServis, 0, ',', '.') ?></b> transaksi servis yang sudah dibayar
                    </td>
                    <td class="border border-slate-300 p-3 text-right font-mono font-bold text-lime-700 align-top">
                        Rp <?= number_format($uangMasuk, 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="bg-lime-500 text-slate-900 font-black">
                    <td colspan="3" class="border border-slate-700 p-2 text-right">TOTAL PEMASUKAN</td>
                    <td class="border border-slate-700 p-2 text-right">Rp <?= number_format($uangMasuk, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="px-6 pb-6">
        <h2 class="text-sm font-black text-slate-800 mb-2 flex items-center gap-2 border-b-2 border-red-400 pb-2">
            <i class="fas fa-money-bill-transfer text-red-500"></i> RINCIAN PENGELUARAN
        </h2>
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-[#252332] text-white">
                    <th class="border border-slate-700 p-2 w-8">No</th>
                    <th class="border border-slate-700 p-2 w-32">Kategori</th>
                    <th class="border border-slate-700 p-2">Keterangan</th>
                    <th class="border border-slate-700 p-2 text-right w-40">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan['keterangan']) || trim($laporan['keterangan']) === ''): ?>
                    <tr>
                        <td colspan="4" class="border border-slate-300 p-4 text-center text-slate-400 italic">
                            Tidak ada keterangan pengeluaran
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td class="border border-slate-300 p-3 text-center align-top">1</td>
                        <td class="border border-slate-300 p-3 align-top font-bold text-slate-700">Pengeluaran Operasional</td>
                        <td class="border border-slate-300 p-3 align-top keterangan-box text-slate-700">
                            <?= nl2br(htmlspecialchars($laporan['keterangan'])) ?>
                        </td>
                        <td class="border border-slate-300 p-3 text-right font-mono font-bold text-red-700 align-top">
                            Rp <?= number_format($laporan['uang_keluar'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr class="bg-red-500 text-white font-black">
                    <td colspan="3" class="border border-slate-700 p-2 text-right">TOTAL PENGELUARAN</td>
                    <td class="border border-slate-700 p-2 text-right">Rp <?= number_format($laporan['uang_keluar'], 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="px-6 pb-6">
        <h2 class="text-sm font-black text-slate-800 mb-2 flex items-center gap-2 border-b-2 border-blue-400 pb-2">
            <i class="fas fa-calculator text-blue-500"></i> RINGKASAN LABA / RUGI
        </h2>
        <table class="w-full border-collapse">
            <tbody>
                <tr class="bg-lime-50">
                    <td class="border border-slate-300 p-3 font-bold text-slate-700">Total Pemasukan</td>
                    <td class="border border-slate-300 p-3 text-right font-black text-lime-700 text-lg">
                        Rp <?= number_format($uangMasuk, 0, ',', '.') ?>
                    </td>
                </tr>
                <tr class="bg-red-50">
                    <td class="border border-slate-300 p-3 font-bold text-slate-700">Total Pengeluaran</td>
                    <td class="border border-slate-300 p-3 text-right font-black text-red-700 text-lg">
                        - Rp <?= number_format($laporan['uang_keluar'], 0, ',', '.') ?>
                    </td>
                </tr>
                <tr class="<?= $laba >= 0 ? 'bg-blue-100' : 'bg-red-100' ?>">
                    <td class="border-2 <?= $laba >= 0 ? 'border-blue-500' : 'border-red-500' ?> p-3 font-black text-lg">
                        <?= $laba >= 0 ? '💰 LABA BERSIH' : '⚠️ RUGI' ?>
                    </td>
                    <td class="border-2 <?= $laba >= 0 ? 'border-blue-500' : 'border-red-500' ?> p-3 text-right font-black text-2xl <?= $laba >= 0 ? 'text-blue-700' : 'text-red-700' ?>">
                        Rp <?= number_format(abs($laba), 0, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="bg-slate-50 border-t p-4 flex justify-between text-xs text-slate-500">
        <div><i class="fas fa-print"></i> Sistem Informasi Bengkel Fatia Kamal</div>
        <div class="font-semibold text-[#252332]">Dicetak: <?= formatTanggalID(date('Y-m-d')) ?></div>
    </div>

</div>

</body>
</html>