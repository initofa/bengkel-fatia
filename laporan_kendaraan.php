<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Admin');

$data = $pdo->query("SELECT k.*, p.nama AS nama_pelanggan 
                     FROM kendaraan k 
                     JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan 
                     ORDER BY k.id_kendaraan ASC")->fetchAll();

$totalKendaraan = count($data);

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
    <title>Laporan Kendaraan - Bengkel Fatia Kamal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { padding: 0; margin: 0; background: white; }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 1.5cm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-container { box-shadow: none !important; border-radius: 0 !important; max-width: 100% !important; }
        }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; }
    </style>
</head>
<body class="p-6">

<div class="print-container max-w-5xl mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden">

    <!-- Top Bar -->
    <div class="no-print bg-[#252332] text-white p-4 flex justify-between items-center">
        <a href="kendaraan.php" 
           class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Preview Laporan Kendaraan</span>
            <button onclick="window.print()" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2 rounded shadow text-sm transition flex items-center gap-2">
                <i class="fas fa-print"></i> CETAK PDF
            </button>
        </div>
    </div>

    <!-- Header Laporan -->
    <div class="bg-gradient-to-r from-[#252332] to-[#3b3852] p-6 text-white border-b-4 border-[#a3e635]">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-[#a3e635] rounded-full flex items-center justify-center text-3xl flex-shrink-0">
                🏍️
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-wide">BENGKEL MOTOR FATIA KAMAL</h1>
                <p class="text-lime-400 font-bold text-sm tracking-wider">LAPORAN DATA KENDARAAN</p>
                <p class="text-slate-300 text-xs mt-1">
                    <i class="fas fa-calendar"></i> Dicetak: <?= formatTanggalID(date('Y-m-d')) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="p-6 pb-0">
        <div class="bg-lime-50 border-2 border-lime-500 rounded-lg p-3 mb-4 flex justify-between items-center">
            <p class="text-xs text-slate-600 font-bold tracking-wider">TOTAL KENDARAAN TERDAFTAR</p>
            <p class="text-2xl font-black text-lime-700"><?= $totalKendaraan ?> Unit</p>
        </div>
    </div>

    <!-- Tabel (Tanpa Keluhan) -->
    <div class="px-6 pb-6">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-[#252332] text-white">
                    <th class="border border-slate-700 p-2 w-10">No</th>
                    <th class="border border-slate-700 p-2">Kode</th>
                    <th class="border border-slate-700 p-2">Pemilik</th>
                    <th class="border border-slate-700 p-2">No Polisi</th>
                    <th class="border border-slate-700 p-2">Jenis Motor</th>
                    <th class="border border-slate-700 p-2 text-center">Tahun</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="6" class="border border-slate-300 p-6 text-center text-slate-400 italic">
                            Belum ada data kendaraan
                        </td>
                    </tr>
                <?php else: foreach ($data as $i => $k): ?>
                    <tr class="<?= $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' ?>">
                        <td class="border border-slate-300 p-2 text-center"><?= $i + 1 ?></td>
                        <td class="border border-slate-300 p-2 font-mono text-xs text-slate-600"><?= htmlspecialchars($k['id_kendaraan']) ?></td>
                        <td class="border border-slate-300 p-2 font-semibold"><?= htmlspecialchars($k['nama_pelanggan']) ?></td>
                        <td class="border border-slate-300 p-2 font-mono font-bold"><?= htmlspecialchars($k['no_polisi']) ?></td>
                        <td class="border border-slate-300 p-2"><?= htmlspecialchars($k['jenis_motor']) ?></td>
                        <td class="border border-slate-300 p-2 text-center font-mono"><?= htmlspecialchars($k['tahun']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="bg-slate-50 border-t p-4 flex justify-between text-xs text-slate-500">
        <div><i class="fas fa-print"></i> Sistem Informasi Bengkel Fatia Kamal</div>
        <div class="font-semibold text-[#252332]">Total: <?= $totalKendaraan ?> kendaraan</div>
    </div>

</div>

</body>
</html>