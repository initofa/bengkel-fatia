<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login();

$data = $pdo->query("SELECT * FROM mekanik ORDER BY id_mekanik ASC")->fetchAll();

$totalMekanik  = count($data);
$totalTersedia = count(array_filter($data, fn($m) => $m['status_aktif'] == 'Tersedia'));
$totalBertugas = count(array_filter($data, fn($m) => $m['status_aktif'] == 'Bertugas'));

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
    <title>Laporan Mekanik - Bengkel Fatia Kamal</title>
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

    <!-- Top Bar -->
    <div class="no-print bg-[#252332] text-white p-4 flex justify-between items-center">
        <a href="mekanik.php" 
           class="bg-slate-600 hover:bg-slate-500 px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400">Preview Laporan Mekanik</span>
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
                👨‍🔧
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase tracking-wide">BENGKEL MOTOR FATIA KAMAL</h1>
                <p class="text-lime-400 font-bold text-sm tracking-wider">LAPORAN DATA MEKANIK</p>
                <p class="text-slate-300 text-xs mt-1">
                    <i class="fas fa-calendar"></i> Dicetak: <?= formatTanggalID(date('Y-m-d')) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="p-6 pb-0">
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-lime-50 border-2 border-lime-500 rounded-lg p-3 text-center">
                <p class="text-xs text-slate-600 font-bold tracking-wider">TOTAL MEKANIK</p>
                <p class="text-2xl font-black text-lime-700"><?= $totalMekanik ?></p>
            </div>
            <div class="bg-green-50 border-2 border-green-500 rounded-lg p-3 text-center">
                <p class="text-xs text-slate-600 font-bold tracking-wider">TERSEDIA</p>
                <p class="text-2xl font-black text-green-700"><?= $totalTersedia ?></p>
            </div>
            <div class="bg-amber-50 border-2 border-amber-500 rounded-lg p-3 text-center">
                <p class="text-xs text-slate-600 font-bold tracking-wider">BERTUGAS</p>
                <p class="text-2xl font-black text-amber-700"><?= $totalBertugas ?></p>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="px-6 pb-6">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-[#252332] text-white">
                    <th class="border border-slate-700 p-2 w-10">No</th>
                    <th class="border border-slate-700 p-2">Kode</th>
                    <th class="border border-slate-700 p-2">Nama Mekanik</th>
                    <th class="border border-slate-700 p-2">Alamat</th>
                    <th class="border border-slate-700 p-2">No Telp</th>
                    <th class="border border-slate-700 p-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="6" class="border border-slate-300 p-6 text-center text-slate-400 italic">
                            Belum ada data mekanik
                        </td>
                    </tr>
                <?php else: foreach ($data as $i => $m): ?>
                    <tr class="<?= $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' ?>">
                        <td class="border border-slate-300 p-2 text-center"><?= $i + 1 ?></td>
                        <td class="border border-slate-300 p-2 font-mono text-xs text-slate-600"><?= htmlspecialchars($m['id_mekanik']) ?></td>
                        <td class="border border-slate-300 p-2 font-semibold"><?= htmlspecialchars($m['nama_mekanik']) ?></td>
                        <td class="border border-slate-300 p-2 text-xs"><?= htmlspecialchars($m['alamat'] ?? '-') ?></td>
                        <td class="border border-slate-300 p-2 font-mono text-xs"><?= htmlspecialchars($m['no_telp']) ?></td>
                        <td class="border border-slate-300 p-2 text-center text-xs font-bold <?= $m['status_aktif']=='Tersedia'?'text-green-600':'text-amber-600' ?>">
                            <?= htmlspecialchars($m['status_aktif']) ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="bg-slate-50 border-t p-4 flex justify-between text-xs text-slate-500">
        <div><i class="fas fa-print"></i> Sistem Informasi Bengkel Fatia Kamal</div>
        <div class="font-semibold text-[#252332]">Total: <?= $totalMekanik ?> mekanik</div>
    </div>

</div>

</body>
</html>