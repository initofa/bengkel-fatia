<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Admin');

$id = $_GET['id'] ?? 0;

// Ambil transaksi + relasi
$stmt = $pdo->prepare("SELECT t.*, 
                              k.no_polisi, k.jenis_motor, k.tahun,
                              p.nama AS nama_pelanggan, p.alamat, p.no_telp,
                              m.nama_mekanik,
                              a.nama AS nama_admin
                       FROM transaksi t 
                       JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                       JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
                       JOIN mekanik m ON t.id_mekanik = m.id_mekanik
                       JOIN admin a ON t.id_admin = a.id_admin
                       WHERE t.id_transaksi = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch();

if (!$trx) { die("Transaksi tidak ditemukan"); }

// Ambil detail sparepart
$stmt2 = $pdo->prepare("SELECT ts.*, s.nama 
                        FROM transaksi_sparepart ts 
                        JOIN sparepart s ON ts.id_sparepart = s.id_sparepart 
                        WHERE ts.id_transaksi = ?");
$stmt2->execute([$id]);
$sparepart_list = $stmt2->fetchAll();

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
    <title>Struk <?= $trx['id_transaksi'] ?> - Bengkel Fatia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { padding: 0; margin: 0; }
            .no-print { display: none !important; }
            @page { size: A5 portrait; margin: 1cm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-box { box-shadow: none !important; }
        }
        body { font-family: 'Courier New', monospace; background: #f1f5f9; }
    </style>
</head>
<body class="p-4">

<div class="print-box max-w-sm mx-auto bg-white shadow-2xl rounded-lg overflow-hidden">

    <!-- Top Bar -->
    <div class="no-print bg-[#252332] text-white p-3 flex justify-between items-center">
        <a href="pembayaran.php" class="bg-slate-600 hover:bg-slate-500 px-3 py-1 rounded text-xs font-bold">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button onclick="window.print()" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-1 rounded text-xs">
            <i class="fas fa-print"></i> CETAK
        </button>
    </div>

    <!-- Header -->
    <div class="text-center p-4 border-b-2 border-dashed border-slate-300">
        <div class="text-2xl font-black">🔧 BENGKEL FATIA KAMAL</div>
        <div class="text-xs text-slate-500 mt-1">Jl. Raya Bangkalan - Kamal</div>
        <div class="text-xs text-slate-500">Telp: 081-111-111-111</div>
    </div>

    <!-- Info Transaksi -->
    <div class="p-4 text-xs border-b-2 border-dashed border-slate-300 space-y-1">
        <div class="flex justify-between"><span>No Transaksi</span><span class="font-bold"><?= $trx['id_transaksi'] ?></span></div>
        <div class="flex justify-between"><span>Tanggal</span><span><?= date('d-m-Y H:i', strtotime($trx['tanggal_transaksi'])) ?></span></div>
        <div class="flex justify-between"><span>Kasir</span><span><?= htmlspecialchars($trx['nama_admin']) ?></span></div>
    </div>

    <!-- Info Pelanggan & Motor -->
    <div class="p-4 text-xs border-b-2 border-dashed border-slate-300 space-y-1">
        <div class="flex justify-between"><span>Pelanggan</span><span class="font-bold"><?= htmlspecialchars($trx['nama_pelanggan']) ?></span></div>
        <div class="flex justify-between"><span>No Telp</span><span><?= htmlspecialchars($trx['no_telp']) ?></span></div>
        <div class="flex justify-between"><span>No Polisi</span><span class="font-bold"><?= htmlspecialchars($trx['no_polisi']) ?></span></div>
        <div class="flex justify-between"><span>Motor</span><span><?= htmlspecialchars($trx['jenis_motor']) ?></span></div>
        <div class="flex justify-between"><span>Mekanik</span><span><?= htmlspecialchars($trx['nama_mekanik']) ?></span></div>
    </div>

    <!-- Keluhan -->
    <div class="p-4 text-xs border-b-2 border-dashed border-slate-300">
        <div class="text-slate-500 mb-1">Keluhan:</div>
        <div class="italic"><?= htmlspecialchars($trx['catatan_keluhan']) ?></div>
    </div>

    <!-- Detail Sparepart -->
    <?php if (!empty($sparepart_list)): ?>
    <div class="p-4 text-xs border-b-2 border-dashed border-slate-300">
        <div class="font-bold mb-2">SPAREPART:</div>
        <?php foreach ($sparepart_list as $sp): ?>
            <div class="flex justify-between mb-1">
                <span><?= htmlspecialchars($sp['nama']) ?> (<?= $sp['qty'] ?>x)</span>
                <span>Rp <?= number_format($sp['subtotal'],0,',','.') ?></span>
            </div>
        <?php endforeach; ?>
        <div class="flex justify-between mt-2 pt-2 border-t border-slate-300">
            <span>Total Sparepart</span>
            <span class="font-bold">Rp <?= number_format($trx['total_sparepart'],0,',','.') ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Biaya Jasa -->
    <div class="p-4 text-xs border-b-2 border-dashed border-slate-300">
        <div class="flex justify-between">
            <span>Biaya Jasa</span>
            <span class="font-bold">Rp <?= number_format($trx['biaya_jasa'],0,',','.') ?></span>
        </div>
    </div>

    <!-- Total -->
    <div class="p-4 text-sm border-b-2 border-dashed border-slate-300 bg-slate-100">
        <div class="flex justify-between font-black text-lg">
            <span>TOTAL</span>
            <span>Rp <?= number_format($trx['total_biaya'],0,',','.') ?></span>
        </div>
    </div>

    <!-- Pembayaran -->
    <div class="p-4 text-xs space-y-1">
        <div class="flex justify-between">
            <span>Uang Bayar</span>
            <span>Rp <?= number_format($trx['uang_bayar'],0,',','.') ?></span>
        </div>
        <div class="flex justify-between font-bold">
            <span>Kembalian</span>
            <span>Rp <?= number_format($trx['uang_kembali'],0,',','.') ?></span>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center p-4 text-xs text-slate-500 border-t-2 border-dashed border-slate-300">
        <div class="font-bold">TERIMA KASIH 🙏</div>
        <div class="mt-1">Semoga motor Anda awet & lancar</div>
        <div class="mt-2 text-[10px]">Dicetak: <?= formatTanggalID(date('Y-m-d')) ?></div>
    </div>

</div>

<script>
// Auto print saat dibuka dengan ?print=1
<?php if (isset($_GET['print'])): ?>
window.onload = function() {
    setTimeout(function() { window.print(); }, 500);
};
<?php endif; ?>
</script>

</body>
</html>