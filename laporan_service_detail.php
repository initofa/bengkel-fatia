<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login(['Admin', 'Owner']);

$bulan = $_GET['bulan'] ?? null;
$tahun = $_GET['tahun'] ?? date('Y');

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// ==================== QUERY (TAMBAH JOIN PELANGGAN) ====================
if ($bulan) {
    $stmt = $pdo->prepare("SELECT t.*, 
                                  k.no_polisi, k.jenis_motor, 
                                  p.nama AS nama_pelanggan, 
                                  m.nama_mekanik 
                           FROM transaksi t 
                           JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                           JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
                           JOIN mekanik m ON t.id_mekanik = m.id_mekanik 
                           WHERE MONTH(t.tanggal_transaksi) = ? AND YEAR(t.tanggal_transaksi) = ?
                           ORDER BY t.tanggal_transaksi ASC");
    $stmt->execute([$bulan, $tahun]);
    $judulPeriode = 'BULAN ' . strtoupper($namaBulan[(int)$bulan]) . ' ' . $tahun;
} else {
    $stmt = $pdo->prepare("SELECT t.*, 
                                  k.no_polisi, k.jenis_motor, 
                                  p.nama AS nama_pelanggan, 
                                  m.nama_mekanik 
                           FROM transaksi t 
                           JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                           JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
                           JOIN mekanik m ON t.id_mekanik = m.id_mekanik 
                           WHERE YEAR(t.tanggal_transaksi) = ?
                           ORDER BY t.tanggal_transaksi ASC");
    $stmt->execute([$tahun]);
    $judulPeriode = 'TAHUN ' . $tahun;
}

$data = $stmt->fetchAll();
$total = array_sum(array_column($data, 'total_biaya'));

// ==================== FORMAT TANGGAL ====================
function formatTanggalID($tanggal) {
    $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $t = strtotime($tanggal);
    return $hari[date('l', $t)] . ', ' . date('d', $t) . ' ' . $bulan[(int)date('m', $t)] . ' ' . date('Y', $t);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service <?= $judulPeriode ?> - Bengkel Fatia</title>
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
        body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; }
    </style>
</head>
<body class="p-6">

<div class="print-container max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">

    <!-- ==================== TOP BAR (No Print) ==================== -->
    <div class="no-print bg-[#252332] text-white p-4 flex justify-between items-center">
        <a href="laporan_service.php?tahun=<?= $tahun ?>" 
           class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Preview Laporan — <?= $judulPeriode ?></span>
            <button onclick="window.print()" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded shadow text-sm transition flex items-center gap-2">
                <i class="fas fa-print"></i> CETAK PDF
            </button>
        </div>
    </div>

    <!-- ==================== HEADER ==================== -->
    <div class="bg-gradient-to-r from-[#252332] to-[#3b3852] p-6 text-white border-b-4 border-[#a3e635]">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-[#a3e635] rounded-full flex items-center justify-center text-3xl flex-shrink-0">
                🔧
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-wide">BENGKEL MOTOR FATIA KAMAL</h1>
                <p class="text-lime-400 font-bold text-sm tracking-wider">LAPORAN SERVICE <?= $judulPeriode ?></p>
                <p class="text-slate-300 text-xs mt-1">
                    <i class="fas fa-calendar"></i> Dicetak: <?= formatTanggalID(date('Y-m-d')) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ==================== RINGKASAN ==================== -->
    <div class="p-6 pb-0">
        <div class="bg-lime-50 border-2 border-lime-500 rounded-lg p-4 mb-4 flex justify-between items-center">
            <div>
                <p class="text-xs text-slate-600 font-bold tracking-wider">TOTAL PENDAPATAN</p>
                <p class="text-2xl font-black text-lime-700">Rp <?= number_format($total, 0, ',', '.') ?></p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-600 font-bold tracking-wider">JUMLAH TRANSAKSI</p>
                <p class="text-2xl font-black text-slate-800"><?= count($data) ?></p>
            </div>
        </div>
    </div>

    <!-- ==================== TABEL DETAIL ==================== -->
    <div class="px-6 pb-6">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-[#252332] text-white">
                    <th class="border border-slate-700 p-2 w-10">No</th>
                    <th class="border border-slate-700 p-2">ID Transaksi</th>
                    <th class="border border-slate-700 p-2">Tanggal</th>
                    <th class="border border-slate-700 p-2">No Polisi</th>
                    <th class="border border-slate-700 p-2">Pemilik</th>
                    <th class="border border-slate-700 p-2">Mekanik</th>
                    <th class="border border-slate-700 p-2">Keluhan</th>
                    <th class="border border-slate-700 p-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="8" class="border border-slate-300 p-6 text-center text-slate-400 italic">
                            Tidak ada data service untuk periode ini
                        </td>
                    </tr>
                <?php else: foreach ($data as $i => $t): ?>
                    <tr class="<?= $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' ?>">
                        <td class="border border-slate-300 p-2 text-center"><?= $i + 1 ?></td>
                        <td class="border border-slate-300 p-2 font-mono text-xs"><?= htmlspecialchars($t['id_transaksi']) ?></td>
                        <td class="border border-slate-300 p-2"><?= date('d-m-Y', strtotime($t['tanggal_transaksi'])) ?></td>
                        <td class="border border-slate-300 p-2 font-bold"><?= htmlspecialchars($t['no_polisi']) ?></td>
                        <td class="border border-slate-300 p-2"><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                        <td class="border border-slate-300 p-2"><?= htmlspecialchars($t['nama_mekanik']) ?></td>
                        <td class="border border-slate-300 p-2 text-xs"><?= htmlspecialchars($t['catatan_keluhan']) ?></td>
                        <td class="border border-slate-300 p-2 text-right font-semibold">Rp <?= number_format($t['total_biaya'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($data)): ?>
            <tfoot>
                <tr class="bg-[#a3e635] text-slate-900 font-black">
                    <td colspan="7" class="border border-slate-700 p-2 text-right">TOTAL KESELURUHAN</td>
                    <td class="border border-slate-700 p-2 text-right">Rp <?= number_format($total, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- ==================== FOOTER ==================== -->
    <div class="bg-slate-50 border-t p-4 flex justify-between text-xs text-slate-500">
        <div><i class="fas fa-print"></i> Sistem Informasi Bengkel Fatia Kamal</div>
        <div class="font-semibold text-[#252332]">Total: <?= count($data) ?> transaksi</div>
    </div>

</div>

</body>
</html>