<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login(['Admin', 'Owner']);

$role = $_SESSION['role'];
$is_owner = ($role == 'Owner');

// ==================== AMBIL DATA SPAREPART ====================
$data = $pdo->query("SELECT * FROM sparepart ORDER BY id_sparepart ASC")->fetchAll();

// ==================== STATISTIK ====================
$totalItem      = count($data);
$totalStok      = array_sum(array_column($data, 'stok'));
$totalNilaiJual = array_sum(array_map(fn($s) => $s['stok'] * $s['harga_jual'], $data));
$totalNilaiBeli = array_sum(array_map(fn($s) => $s['stok'] * $s['harga_beli'], $data));
$stokRendah     = array_filter($data, fn($s) => $s['stok'] < 10 && $s['stok'] > 0);
$stokHabis      = array_filter($data, fn($s) => $s['stok'] == 0);

// ==================== FORMAT TANGGAL INDONESIA ====================
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
    <title>Laporan Stok Sparepart - Bengkel Fatia Kamal</title>
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
        <a href="sparepart.php" 
           class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Preview Laporan Stok</span>
            <button onclick="window.print()" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded shadow text-sm transition flex items-center gap-2">
                <i class="fas fa-print"></i> CETAK PDF
            </button>
        </div>
    </div>

    <!-- ==================== HEADER LAPORAN ==================== -->
    <div class="bg-gradient-to-r from-[#252332] to-[#3b3852] p-6 text-white border-b-4 border-[#a3e635]">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-[#a3e635] rounded-full flex items-center justify-center text-3xl flex-shrink-0">
                📦
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-wide">BENGKEL MOTOR FATIA KAMAL</h1>
                <p class="text-lime-400 font-bold text-sm tracking-wider">LAPORAN STOK SPAREPART</p>
                <p class="text-slate-300 text-xs mt-1">
                    <i class="fas fa-calendar"></i> Dicetak: <?= formatTanggalID(date('Y-m-d')) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ==================== RINGKASAN STATISTIK ==================== -->
    <div class="p-6 pb-0">
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-lime-50 border-2 border-lime-500 rounded-lg p-3 text-center">
                <p class="text-xs text-slate-600 font-bold tracking-wider">TOTAL ITEM</p>
                <p class="text-2xl font-black text-lime-700"><?= $totalItem ?></p>
            </div>
            <div class="bg-blue-50 border-2 border-blue-500 rounded-lg p-3 text-center">
                <p class="text-xs text-slate-600 font-bold tracking-wider">TOTAL STOK</p>
                <p class="text-2xl font-black text-blue-700"><?= $totalStok ?></p>
            </div>
            <div class="bg-amber-50 border-2 border-amber-500 rounded-lg p-3 text-center">
                <p class="text-xs text-slate-600 font-bold tracking-wider">NILAI JUAL STOK</p>
                <p class="text-lg font-black text-amber-700">Rp <?= number_format($totalNilaiJual, 0, ',', '.') ?></p>
            </div>
        </div>

        <!-- Info Nilai Beli (khusus Owner) -->
        <?php if ($is_owner): ?>
        <div class="bg-purple-50 border-2 border-purple-400 rounded-lg p-3 mb-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="fas fa-crown text-purple-500"></i>
                <span class="text-xs text-purple-700 font-bold">NILAI BELI STOK (MODAL):</span>
            </div>
            <span class="text-lg font-black text-purple-700">Rp <?= number_format($totalNilaiBeli, 0, ',', '.') ?></span>
        </div>
        <?php endif; ?>

        <!-- Alert jika ada stok rendah -->
        <?php if (count($stokRendah) > 0 || count($stokHabis) > 0): ?>
        <div class="bg-red-50 border-2 border-red-500 rounded-lg p-3 mb-4 flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl mt-0.5"></i>
            <div>
                <?php if (count($stokRendah) > 0): ?>
                    <p class="text-xs text-red-700 font-bold">
                        PERHATIAN: <?= count($stokRendah) ?> item memiliki stok di bawah 10 (perlu restock)
                    </p>
                <?php endif; ?>
                <?php if (count($stokHabis) > 0): ?>
                    <p class="text-xs text-red-600 mt-0.5 font-bold">
                        ⚠️ <?= count($stokHabis) ?> item stoknya HABIS
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ==================== TABEL DETAIL ==================== -->
    <div class="px-6 pb-6">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-[#252332] text-white">
                    <th class="border border-slate-700 p-2 w-10">No</th>
                    <th class="border border-slate-700 p-2">Kode</th>
                    <th class="border border-slate-700 p-2">Nama Sparepart</th>
                    <th class="border border-slate-700 p-2 text-center">Stok</th>
                    <?php if ($is_owner): ?>
                        <th class="border border-slate-700 p-2 text-right">
                            <i class="fas fa-crown text-lime-400 text-[10px]"></i> Harga Beli
                        </th>
                    <?php endif; ?>
                    <th class="border border-slate-700 p-2 text-right">Harga Jual</th>
                    <th class="border border-slate-700 p-2 text-right">Subtotal</th>
                    <th class="border border-slate-700 p-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= $is_owner ? 8 : 7 ?>" class="border border-slate-300 p-6 text-center text-slate-400 italic">
                            Belum ada data sparepart
                        </td>
                    </tr>
                <?php else: foreach ($data as $i => $s): 
                    $subtotal = $s['stok'] * $s['harga_jual'];
                    if ($s['stok'] == 0) {
                        $status = '<span class="text-red-600 font-bold">HABIS</span>';
                        $rowClass = 'bg-red-50';
                    } elseif ($s['stok'] < 10) {
                        $status = '<span class="text-amber-600 font-bold">LOW</span>';
                        $rowClass = 'bg-amber-50';
                    } else {
                        $status = '<span class="text-lime-600 font-bold">TERSEDIA</span>';
                        $rowClass = ($i % 2 == 0) ? 'bg-white' : 'bg-slate-50';
                    }
                ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="border border-slate-300 p-2 text-center"><?= $i + 1 ?></td>
                        <td class="border border-slate-300 p-2 font-mono text-xs text-slate-600"><?= htmlspecialchars($s['id_sparepart']) ?></td>
                        <td class="border border-slate-300 p-2 font-semibold"><?= htmlspecialchars($s['nama']) ?></td>
                        <td class="border border-slate-300 p-2 text-center font-bold"><?= htmlspecialchars($s['stok']) ?></td>
                        <?php if ($is_owner): ?>
                            <td class="border border-slate-300 p-2 text-right font-mono text-xs text-purple-700">
                                Rp <?= number_format($s['harga_beli'], 0, ',', '.') ?>
                            </td>
                        <?php endif; ?>
                        <td class="border border-slate-300 p-2 text-right font-mono text-xs">Rp <?= number_format($s['harga_jual'], 0, ',', '.') ?></td>
                        <td class="border border-slate-300 p-2 text-right font-mono text-xs">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        <td class="border border-slate-300 p-2 text-center text-xs"><?= $status ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($data)): ?>
            <tfoot>
                <tr class="bg-[#a3e635] text-slate-900 font-black">
                    <td colspan="3" class="border border-slate-700 p-2 text-right">TOTAL</td>
                    <td class="border border-slate-700 p-2 text-center"><?= $totalStok ?></td>
                    <?php if ($is_owner): ?>
                        <td class="border border-slate-700 p-2 text-right font-mono text-xs">Rp <?= number_format($totalNilaiBeli, 0, ',', '.') ?></td>
                    <?php endif; ?>
                    <td class="border border-slate-700 p-2"></td>
                    <td class="border border-slate-700 p-2 text-right font-mono text-xs">Rp <?= number_format($totalNilaiJual, 0, ',', '.') ?></td>
                    <td class="border border-slate-700 p-2"></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- ==================== FOOTER ==================== -->
    <div class="bg-slate-50 border-t p-4 flex justify-between text-xs text-slate-500">
        <div><i class="fas fa-print"></i> Sistem Informasi Bengkel Fatia Kamal</div>
        <div class="font-semibold text-[#252332]">Total: <?= $totalItem ?> item sparepart</div>
    </div>

</div>

</body>
</html>