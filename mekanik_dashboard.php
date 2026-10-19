<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Mekanik');

$data = $pdo->query("SELECT t.*, k.no_polisi, k.jenis_motor, m.nama_mekanik 
                     FROM transaksi t 
                     JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                     JOIN mekanik m ON t.id_mekanik = m.id_mekanik 
                     WHERE t.status IN ('Proses','Selesai')
                     ORDER BY t.tanggal_servis DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Dashboard Mekanik</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">
<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">
    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-6 flex flex-col">
        <h1 class="text-white font-black text-lg tracking-wider mb-3">DAFTAR SERVIS</h1>

        <?php if (isset($_GET['msg'])): ?>
            <div class="bg-lime-500/20 border border-lime-400 text-lime-200 text-xs p-2 rounded mb-3"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-700 text-white">
                    <tr>
                        <th class="p-2 text-left">No</th>
                        <th class="p-2 text-left">Tanggal</th>
                        <th class="p-2 text-left">No Polisi</th>
                        <th class="p-2 text-left">Motor</th>
                        <th class="p-2 text-left">Keluhan</th>
                        <th class="p-2 text-left">Status</th>
                        <th class="p-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="7" class="text-center text-slate-400 p-6">Belum ada servis</td></tr>
                <?php else: foreach ($data as $i => $t): ?>
                    <tr class="text-white border-b border-slate-600 hover:bg-slate-700">
                        <td class="p-2"><?= $i+1 ?></td>
                        <td class="p-2"><?= date('d-m-Y', strtotime($t['tanggal_servis'])) ?></td>
                        <td class="p-2 font-bold text-lime-400"><?= htmlspecialchars($t['no_polisi']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($t['jenis_motor']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($t['keluhan']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($t['status']) ?></td>
                        <td class="p-2 text-center">
                            <?php if ($t['status'] == 'Proses'): ?>
                                <a href="pembayaran_konfirmasi.php?selesai=<?= $t['id_transaksi'] ?>" 
                                   class="bg-lime-400 text-slate-900 px-2 py-1 rounded text-xs font-bold">✅ Selesai</a>
                            <?php else: ?>
                                <span class="text-lime-400 text-xs">✓ Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include 'includes/sidebar.php'; ?>
</div>
</body>
</html>