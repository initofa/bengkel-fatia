<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/pagination.php';
cek_login('Admin');

$id_admin = $_SESSION['user_id'];

// ==================== HANDLE GET: SELESAI ====================
if (isset($_GET['selesai'])) {
    $id = $_GET['selesai'];
    
    $stmtGet = $pdo->prepare("SELECT id_mekanik FROM transaksi WHERE id_transaksi=? AND status='Proses'");
    $stmtGet->execute([$id]);
    $id_mekanik = $stmtGet->fetchColumn();
    
    if ($id_mekanik) {
        $pdo->beginTransaction();
        
        $stmt1 = $pdo->prepare("UPDATE transaksi SET status='Selesai' WHERE id_transaksi=?");
        $stmt1->execute([$id]);
        
        $stmt2 = $pdo->prepare("UPDATE mekanik SET status_aktif='Tersedia' WHERE id_mekanik=?");
        $stmt2->execute([$id_mekanik]);
        
        $pdo->commit();
        header("Location: pembayaran.php?msg=Servis selesai & mekanik kembali tersedia");
    } else {
        header("Location: pembayaran.php?msg=Gagal: Transaksi tidak ditemukan");
    }
    exit;
}

// ==================== SEARCH & PAGINATION ====================
$search = $_GET['q'] ?? '';

if ($search) {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM transaksi t 
                                JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
                                JOIN mekanik m ON t.id_mekanik = m.id_mekanik 
                                JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
                                WHERE k.no_polisi LIKE ? OR m.nama_mekanik LIKE ? 
                                OR p.nama LIKE ? OR t.id_transaksi LIKE ? OR t.status LIKE ?");
    $stmtCount->execute(["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
    $totalData = $stmtCount->fetchColumn();
} else {
    $totalData = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
}

$pg = paginate($totalData, 9);

$baseSql = "FROM transaksi t 
            JOIN kendaraan k ON t.id_kendaraan = k.id_kendaraan 
            JOIN mekanik m ON t.id_mekanik = m.id_mekanik
            JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan";

if ($search) {
    $stmt = $pdo->prepare("SELECT t.*, k.no_polisi, p.nama AS nama_pelanggan, m.nama_mekanik $baseSql 
                           WHERE k.no_polisi LIKE ? OR m.nama_mekanik LIKE ? 
                           OR p.nama LIKE ? OR t.id_transaksi LIKE ? OR t.status LIKE ?
                           ORDER BY t.tanggal_transaksi DESC 
                           LIMIT {$pg['limit']} OFFSET {$pg['offset']}");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT t.*, k.no_polisi, p.nama AS nama_pelanggan, m.nama_mekanik $baseSql 
                         ORDER BY t.tanggal_transaksi DESC 
                         LIMIT {$pg['limit']} OFFSET {$pg['offset']}");
}
$data = $stmt->fetchAll();

// ==================== STATISTIK ====================
$totalTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
$totalDibayar   = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status='Dibayar'")->fetchColumn();
$totalSelesai   = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status='Selesai'")->fetchColumn();
$totalProses    = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status='Proses'")->fetchColumn();

// ==================== DATA UNTUK DROPDOWN ====================
$kendaraan    = $pdo->query("SELECT k.*, p.nama FROM kendaraan k JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan ORDER BY p.nama ASC")->fetchAll();
$mekanik_list = $pdo->query("SELECT * FROM mekanik WHERE status_aktif='Tersedia' ORDER BY nama_mekanik ASC")->fetchAll();
$sp_list      = $pdo->query("SELECT * FROM sparepart WHERE stok > 0 ORDER BY nama ASC")->fetchAll();

$flash_msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        table tbody tr { height: 34px; }
        table thead tr { height: 32px; }
        .modal-active { display: flex !important; }
        /* Scrollbar custom */
        #listSparepartCepat::-webkit-scrollbar { width: 6px; }
        #listSparepartCepat::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 3px; }
        #listSparepartCepat::-webkit-scrollbar-thumb { background: #a3e635; border-radius: 3px; }
    </style>
</head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">

<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">

    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-5 flex flex-col">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-2">
            <div class="flex items-center gap-3">
                <h1 class="text-white font-black text-lg tracking-wider">PEMBAYARAN</h1>
                <span class="text-[10px] font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/40">
                    <i class="fas fa-user-shield"></i> ADMIN
                </span>
            </div>
            <div class="flex gap-2 text-xs">
                <span class="bg-white/10 text-lime-400 px-2.5 py-1 rounded-full font-bold">
                    <i class="fas fa-receipt"></i> <?= $totalTransaksi ?>
                </span>
                <span class="bg-lime-500/20 text-lime-300 px-2.5 py-1 rounded-full font-bold border border-lime-500/40">
                    <i class="fas fa-check-circle"></i> <?= $totalDibayar ?>
                </span>
                <span class="bg-yellow-500/20 text-yellow-300 px-2.5 py-1 rounded-full font-bold border border-yellow-500/40">
                    <i class="fas fa-check"></i> <?= $totalSelesai ?>
                </span>
                <span class="bg-red-500/20 text-red-300 px-2.5 py-1 rounded-full font-bold border border-red-500/40">
                    <i class="fas fa-clock"></i> <?= $totalProses ?>
                </span>
            </div>
        </div>

        <!-- SEARCH -->
        <form method="GET" class="mb-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Cari no polisi, pelanggan, mekanik, atau status..."
                       autocomplete="off"
                       class="w-full bg-white/5 border-2 border-slate-600 text-white placeholder-slate-500 rounded-xl pl-11 pr-24 py-1.5 text-sm focus:outline-none focus:border-lime-500 transition-all">
                <?php if ($search): ?>
                    <a href="pembayaran.php" class="absolute inset-y-0 right-14 flex items-center text-slate-500 hover:text-red-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
                <button type="submit" class="absolute inset-y-1 right-1 bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold px-3 rounded-lg text-xs">CARI</button>
            </div>
        </form>

        <!-- TABEL -->
        <div class="flex-1 rounded-lg overflow-hidden">
            <table class="w-full text-sm table-fixed">
                <thead class="bg-slate-700 text-white">
                    <tr>
                        <th class="p-2 text-left w-10">No</th>
                        <th class="p-2 text-left w-20">Tanggal</th>
                        <th class="p-2 text-left w-24">No Polisi</th>
                        <th class="p-2 text-left w-28">Pelanggan</th>
                        <th class="p-2 text-left w-24">Mekanik</th>
                        <th class="p-2 text-right w-24">Total</th>
                        <th class="p-2 text-center w-20">Status</th>
                        <th class="p-2 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">Belum ada transaksi</td></tr>
                <?php else: foreach ($data as $i => $t): 
                    if ($t['status'] == 'Dibayar') {
                        $statusBadge = '<span class="inline-flex items-center gap-1 bg-lime-500/20 text-lime-300 px-2 py-0.5 rounded-full border border-lime-500/40 font-bold text-[10px]"><i class="fas fa-check-circle"></i> Dibayar</span>';
                    } elseif ($t['status'] == 'Selesai') {
                        $statusBadge = '<span class="inline-flex items-center gap-1 bg-yellow-500/20 text-yellow-300 px-2 py-0.5 rounded-full border border-yellow-500/40 font-bold text-[10px]"><i class="fas fa-check"></i> Selesai</span>';
                    } else {
                        $statusBadge = '<span class="inline-flex items-center gap-1 bg-red-500/20 text-red-300 px-2 py-0.5 rounded-full border border-red-500/40 font-bold text-[10px]"><i class="fas fa-clock"></i> Proses</span>';
                    }
                    
                    $btnAksi = '';
                    if ($t['status'] == 'Proses') {
                        $btnAksi = '<button onclick="confirmSelesai(\'' . $t['id_transaksi'] . '\')" 
                                    class="inline-flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-slate-900 px-2 py-1 rounded text-[10px] font-bold transition">
                                        <i class="fas fa-check"></i> Selesai</button>';
                    } elseif ($t['status'] == 'Selesai') {
                        $btnAksi = '<button onclick="openBayarModal(\'' . $t['id_transaksi'] . '\', ' . $t['total_biaya'] . ')" 
                                    class="inline-flex items-center gap-1 bg-lime-400 hover:bg-lime-500 text-slate-900 px-2 py-1 rounded text-[10px] font-bold transition">
                                        <i class="fas fa-money-bill-wave"></i> Bayar</button>';
                    } else {
                        $btnAksi = '<a href="struk_pdf.php?id=' . $t['id_transaksi'] . '&print=1" target="_blank" 
                                    class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-[10px] font-bold transition">
                                        <i class="fas fa-print"></i> Struk</a>';
                    }
                    
                    $no = $pg['offset'] + $i + 1;
                ?>
                    <tr class="text-white border-b border-slate-700 hover:bg-slate-700/50 transition">
                        <td class="p-2 text-slate-400"><?= $no ?></td>
                        <td class="p-2 text-xs font-mono"><?= date('d-m-Y', strtotime($t['tanggal_transaksi'])) ?></td>
                        <td class="p-2"><span class="font-mono text-xs font-bold text-lime-400 bg-lime-500/10 px-2 py-0.5 rounded border border-lime-500/30"><?= htmlspecialchars($t['no_polisi']) ?></span></td>
                        <td class="p-2 truncate text-xs"><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                        <td class="p-2 truncate text-xs"><?= htmlspecialchars($t['nama_mekanik']) ?></td>
                        <td class="p-2 text-right font-mono text-xs">Rp <?= number_format($t['total_biaya'],0,',','.') ?></td>
                        <td class="p-2 text-center"><?= $statusBadge ?></td>
                        <td class="p-2 text-center"><?= $btnAksi ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?= renderPagination($pg, 'pembayaran.php', $search ? ['q' => $search] : []) ?>

        <div class="flex gap-2 mt-1">
            <button onclick="openTransaksiModal()" 
                    class="bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold px-3.5 py-1.5 rounded text-xs transition flex items-center gap-2">
                <i class="fas fa-plus"></i> TRANSAKSI BARU
            </button>
        </div>

    </div>

    <?php include 'includes/sidebar.php'; ?>
</div>

<!-- =====================================================
     MODAL 1: TRANSAKSI BARU (SPAREPART INLINE CEPAT)
     ===================================================== -->
<div id="modalTransaksi" class="hidden fixed inset-0 bg-black/60 items-center justify-center z-50 p-4">
    <div class="bg-[#252332] rounded-2xl w-[600px] max-h-[90vh] shadow-2xl border border-lime-500/30 flex flex-col">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#a3e635] rounded-full flex items-center justify-center">
                    <i class="fas fa-cash-register text-slate-900"></i>
                </div>
                <h2 class="text-white font-black text-lg">TRANSAKSI BARU</h2>
            </div>
            <button onclick="closeTransaksiModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <!-- Body (scrollable) -->
        <form id="formTransaksi" onsubmit="return submitTransaksi(event)" class="flex-1 overflow-y-auto p-4 space-y-3">
            
            <!-- Kendaraan + Tombol Tambah Pelanggan -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">KENDARAAN</label>
                <div class="flex gap-2">
                    <select name="id_kendaraan" id="selKendaraan" required 
                            class="flex-1 bg-white rounded-xl px-3 py-2 text-sm font-semibold">
                        <option value="">-- PILIH KENDARAAN --</option>
                        <?php foreach ($kendaraan as $k): ?>
                            <option value="<?= $k['id_kendaraan'] ?>"><?= htmlspecialchars($k['no_polisi'] . ' - ' . $k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="openPelangganModal()" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-3 rounded-xl text-sm transition"
                            title="Tambah Pelanggan + Motor Baru">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mekanik -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">MEKANIK (yang tersedia)</label>
                <select name="id_mekanik" required class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="">-- PILIH MEKANIK --</option>
                    <?php foreach ($mekanik_list as $m): ?>
                        <option value="<?= $m['id_mekanik'] ?>"><?= htmlspecialchars($m['nama_mekanik']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Keluhan -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">KELUHAN</label>
                <input name="catatan_keluhan" placeholder="Contoh: Mesin sulit dinyalakan" required
                       autocomplete="off"
                       class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            
            <!-- ⚡ SPAREPART INLINE CEPAT -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="text-slate-300 text-xs font-bold">SPAREPART YANG DIBELI</label>
                    <span class="text-[10px] text-lime-400 italic">
                        <i class="fas fa-bolt"></i> Isi qty langsung
                    </span>
                </div>
                
                <!-- Search box filter sparepart -->
               <div class="relative mb-2">
                    <!-- Icon search -->
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-3.5 w-3.5 text-lime-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Input -->
                    <input type="text" id="searchSparepart" oninput="filterSparepart()" 
                        placeholder="Cari sparepart..."
                        autocomplete="off"
                        class="w-full bg-slate-800 border border-slate-600 text-white placeholder-slate-500 rounded-lg pl-9 pr-3 py-1.5 text-xs focus:outline-none focus:border-lime-500">
                </div>
                
                <!-- List sparepart scrollable -->
                <div id="listSparepartCepat" class="bg-slate-800/50 rounded-xl p-2 space-y-1 max-h-[220px] overflow-y-auto">
                    <?php foreach ($sp_list as $sp): ?>
                        <div class="flex items-center gap-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-lg px-2 py-1.5 sparepart-item"
                             data-nama="<?= strtolower(htmlspecialchars($sp['nama'])) ?>">
                            
                            <!-- Nama + Harga + Stok -->
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-xs font-bold truncate"><?= htmlspecialchars($sp['nama']) ?></p>
                                <p class="text-slate-400 text-[10px]">
                                    Rp <?= number_format($sp['harga_jual'],0,',','.') ?> · Stok: <span class="text-lime-400"><?= $sp['stok'] ?></span>
                                </p>
                            </div>
                            
                            <!-- Tombol - | Input Qty | Tombol + -->
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        onclick="decrementQty(this)" 
                                        class="w-6 h-6 bg-slate-600 hover:bg-slate-500 rounded text-white text-xs font-bold transition">
                                    −
                                </button>
                                <input type="number" 
                                       min="0" 
                                       max="<?= $sp['stok'] ?>" 
                                       value="0"
                                       data-id="<?= $sp['id_sparepart'] ?>"
                                       data-nama="<?= htmlspecialchars($sp['nama'], ENT_QUOTES) ?>"
                                       data-harga="<?= $sp['harga_jual'] ?>"
                                       data-stok="<?= $sp['stok'] ?>"
                                       oninput="updateQtySparepart(this)"
                                       onfocus="if(this.value=='0')this.value=''"
                                       onblur="if(this.value=='')this.value='0'"
                                       class="w-12 bg-white text-slate-900 font-bold text-xs text-center rounded-lg py-1
                                              [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" 
                                        onclick="incrementQty(this)" 
                                        class="w-6 h-6 bg-lime-500 hover:bg-lime-600 rounded text-slate-900 text-xs font-bold transition">
                                    +
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Ringkasan Sparepart Dipilih -->
                <div class="mt-2 bg-lime-500/10 border border-lime-500/30 rounded-lg p-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-lime-300 font-bold">Total Sparepart:</span>
                        <span id="totalSparepartDisplay" class="text-white font-black">Rp 0</span>
                    </div>
                    <div id="listSparepartSelected" class="text-[10px] text-slate-400 mt-1 max-h-[60px] overflow-y-auto">
                        <span class="italic">Belum ada sparepart dipilih</span>
                    </div>
                </div>
            </div>
            
            <!-- Biaya Jasa -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">BIAYA JASA / PEMASANGAN</label>
                <input name="biaya_jasa" id="inputBiayaJasa" type="number" min="0" value="0"
                       oninput="updateTotal()"
                       class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold
                              [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
            </div>
            
            <!-- Total -->
            <div class="bg-lime-500/20 border-2 border-lime-400 rounded-xl p-3">
                <div class="flex justify-between items-center">
                    <span class="text-lime-300 text-xs font-bold">TOTAL BIAYA</span>
                    <span id="totalBiaya" class="text-white font-black text-xl">Rp 0</span>
                </div>
            </div>
            
        </form>
        
        <!-- Footer -->
        <div class="flex gap-2 p-4 border-t border-slate-700">
            <button type="button" onclick="closeTransaksiModal()" 
                    class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 rounded-xl transition">BATAL</button>
            <button type="submit" form="formTransaksi"
                    class="flex-1 bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-2 rounded-xl transition">
                <i class="fas fa-save mr-1"></i> SIMPAN
            </button>
        </div>
    </div>
</div>

<!-- =====================================================
     MODAL 2: PELANGGAN + MOTOR BARU
     ===================================================== -->
<div id="modalPelanggan" class="hidden fixed inset-0 bg-black/70 items-center justify-center z-[60] p-4">
    <div class="bg-[#252332] rounded-2xl w-[500px] max-h-[90vh] shadow-2xl border border-blue-500/30 flex flex-col">
        
        <div class="flex items-center justify-between p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <h2 class="text-white font-black text-base">TAMBAH PELANGGAN + MOTOR</h2>
            </div>
            <button onclick="closePelangganModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <form id="formPelanggan" onsubmit="return submitPelanggan(event)" class="flex-1 overflow-y-auto p-4 space-y-3">
            
            <p class="text-lime-400 text-xs font-bold mb-2">📋 DATA PELANGGAN</p>
            
            <input name="nama" placeholder="NAMA PELANGGAN" required
                   class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
            
            <input name="alamat" placeholder="ALAMAT" required
                   class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
            
            <input name="no_telp" type="number" placeholder="NO TELP" required min="0"
                   class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold
                          [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
            
            <select name="jenis_kelamin" required
                    class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
                <option value="" disabled selected>-- PILIH JENIS KELAMIN --</option>
                <option value="Laki-laki">LAKI-LAKI</option>
                <option value="Perempuan">PEREMPUAN</option>
            </select>
            
            <p class="text-lime-400 text-xs font-bold mb-2 mt-4">🏍️ DATA MOTOR</p>
            
            <input name="no_polisi" placeholder="NO POLISI (contoh: W 1234 GF)" required
                   style="text-transform: uppercase;"
                   class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
            
            <input name="jenis_motor" placeholder="JENIS MOTOR (contoh: Honda Beat)" required
                   class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold">
            
            <input name="tahun" type="number" min="1990" max="2099" value="<?= date('Y') ?>" required
                   class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold
                          [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
        </form>
        
        <div class="flex gap-2 p-4 border-t border-slate-700">
            <button type="button" onclick="closePelangganModal()" 
                    class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 rounded-xl transition text-sm">BATAL</button>
            <button type="submit" form="formPelanggan"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-xl transition text-sm">
                <i class="fas fa-save mr-1"></i> SIMPAN
            </button>
        </div>
    </div>
</div>

<!-- =====================================================
     MODAL 3: BAYAR + KEMBALIAN
     ===================================================== -->
<div id="modalBayar" class="hidden fixed inset-0 bg-black/70 items-center justify-center z-[60] p-4">
    <div class="bg-[#252332] rounded-2xl w-[450px] shadow-2xl border border-lime-500/30 flex flex-col">
        
        <div class="flex items-center justify-between p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-lime-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-slate-900"></i>
                </div>
                <h2 class="text-white font-black text-base">KONFIRMASI BAYAR</h2>
            </div>
            <button onclick="closeBayarModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <form id="formBayar" onsubmit="return submitBayar(event)" class="p-4 space-y-3">
            <input type="hidden" name="id_transaksi" id="bayarIdTrx">
            
            <div class="bg-red-500/20 border-2 border-red-400 rounded-xl p-3">
                <p class="text-red-300 text-xs font-bold">TOTAL TAGIHAN</p>
                <p id="bayarTotal" class="text-white font-black text-2xl">Rp 0</p>
            </div>
            
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">UANG BAYAR DARI PELANGGAN</label>
                <input name="uang_bayar" id="inputUangBayar" type="number" min="0" required 
                       oninput="hitungKembalian()"
                       class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold
                              [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
            </div>
            
            <div class="flex gap-2 flex-wrap">
                <button type="button" onclick="setUang(50000)" class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1 rounded-lg">50K</button>
                <button type="button" onclick="setUang(100000)" class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1 rounded-lg">100K</button>
                <button type="button" onclick="setUang(200000)" class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1 rounded-lg">200K</button>
                <button type="button" onclick="setUangUangPas()" class="bg-lime-500 hover:bg-lime-600 text-slate-900 text-xs px-3 py-1 rounded-lg font-bold">UANG PAS</button>
            </div>
            
            <div class="bg-lime-500/20 border-2 border-lime-400 rounded-xl p-3">
                <p class="text-lime-300 text-xs font-bold">KEMBALIAN</p>
                <p id="bayarKembali" class="text-white font-black text-2xl">Rp 0</p>
            </div>
        </form>
        
        <div class="flex gap-2 p-4 border-t border-slate-700">
            <button type="button" onclick="closeBayarModal()" 
                    class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 rounded-xl text-sm">BATAL</button>
            <button type="submit" form="formBayar"
                    class="flex-1 bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-2 rounded-xl text-sm">
                <i class="fas fa-check mr-1"></i> KONFIRMASI
            </button>
        </div>
    </div>
</div>

<!-- ==================== FLASH MESSAGE DATA ==================== -->
<?php if (!empty($flash_msg)): ?>
<div id="flashData" 
     data-msg="<?= htmlspecialchars($flash_msg, ENT_QUOTES) ?>"
     data-error="<?= strpos(strtolower($flash_msg), 'gagal') !== false ? '1' : '0' ?>"
     style="display:none;"></div>
<?php endif; ?>

<!-- =====================================================
     JAVASCRIPT
     ===================================================== -->
<script>
let sparepartDipilih = [];   // Array sparepart yang dipilih

// ==================== MODAL CONTROL ====================
function showModal(id) { document.getElementById(id).classList.add('modal-active'); }
function hideModal(id) { document.getElementById(id).classList.remove('modal-active'); }

function openTransaksiModal() {
    resetSparepartInputs();
    document.getElementById('formTransaksi').reset();
    updateTotal();
    showModal('modalTransaksi');
}
function closeTransaksiModal() { hideModal('modalTransaksi'); }

function openPelangganModal() { showModal('modalPelanggan'); }
function closePelangganModal() { hideModal('modalPelanggan'); }

// ==================== PELANGGAN ====================
function submitPelanggan(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    fetch('ajax_pelanggan.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.status === 'success') {
                const sel = document.getElementById('selKendaraan');
                const opt = document.createElement('option');
                opt.value = data.id_kendaraan;
                opt.textContent = data.no_polisi + ' - ' + data.nama;
                opt.selected = true;
                sel.appendChild(opt);
                
                closePelangganModal();
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: data.message,
                    confirmButtonColor: '#a3e635', timer: 2000, showConfirmButton: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#ef4444' });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal koneksi ke server', confirmButtonColor: '#ef4444' });
        });
    return false;
}

// ==================== SPAREPART INLINE CEPAT ====================
function incrementQty(btn) {
    const input = btn.previousElementSibling;
    const stok = parseInt(input.getAttribute('data-stok'));
    let qty = parseInt(input.value) || 0;
    if (qty < stok) {
        input.value = qty + 1;
        updateQtySparepart(input);
    } else {
        Swal.fire({
            icon: 'warning', title: 'Stok maksimal!',
            text: 'Stok tersedia: ' + stok,
            confirmButtonColor: '#facc15', timer: 1500, showConfirmButton: false
        });
    }
}

function decrementQty(btn) {
    const input = btn.nextElementSibling;
    let qty = parseInt(input.value) || 0;
    if (qty > 0) {
        input.value = qty - 1;
        updateQtySparepart(input);
    }
}

function updateQtySparepart(input) {
    const id_sparepart = input.getAttribute('data-id');
    const nama         = input.getAttribute('data-nama');
    const harga        = parseInt(input.getAttribute('data-harga'));
    const stok         = parseInt(input.getAttribute('data-stok'));
    let qty            = parseInt(input.value) || 0;
    
    // Validasi
    if (qty < 0) { qty = 0; input.value = 0; }
    if (qty > stok) {
        qty = stok;
        input.value = stok;
        Swal.fire({
            icon: 'warning', title: 'Stok tidak cukup!',
            text: 'Maksimal ' + stok + ' untuk ' + nama,
            confirmButtonColor: '#facc15', timer: 2000, showConfirmButton: false
        });
    }
    
    const existingIndex = sparepartDipilih.findIndex(s => s.id_sparepart === id_sparepart);
    
    if (qty === 0) {
        if (existingIndex !== -1) {
            sparepartDipilih.splice(existingIndex, 1);
        }
    } else {
        if (existingIndex !== -1) {
            sparepartDipilih[existingIndex].qty = qty;
            sparepartDipilih[existingIndex].subtotal = qty * harga;
        } else {
            sparepartDipilih.push({
                id_sparepart: id_sparepart,
                nama: nama,
                qty: qty,
                harga_satuan: harga,
                subtotal: qty * harga
            });
        }
    }
    
    renderSparepartSelected();
    updateTotal();
}

function renderSparepartSelected() {
    const div = document.getElementById('listSparepartSelected');
    const totalDisplay = document.getElementById('totalSparepartDisplay');
    
    if (sparepartDipilih.length === 0) {
        div.innerHTML = '<span class="italic">Belum ada sparepart dipilih</span>';
        totalDisplay.textContent = 'Rp 0';
        return;
    }
    
    const totalSP = sparepartDipilih.reduce((sum, sp) => sum + sp.subtotal, 0);
    totalDisplay.textContent = 'Rp ' + formatRupiah(totalSP);
    
    let html = '';
    sparepartDipilih.forEach(sp => {
        html += '<span class="inline-block bg-lime-500/20 text-lime-300 px-2 py-0.5 rounded mr-1 mb-1">' +
                sp.nama + ' (' + sp.qty + 'x) = Rp ' + formatRupiah(sp.subtotal) +
                '</span>';
    });
    div.innerHTML = html;
}

function filterSparepart() {
    const keyword = document.getElementById('searchSparepart').value.toLowerCase();
    const items = document.querySelectorAll('.sparepart-item');
    
    items.forEach(item => {
        const nama = item.getAttribute('data-nama');
        if (nama.includes(keyword)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function resetSparepartInputs() {
    document.querySelectorAll('#listSparepartCepat input[type=number]').forEach(input => {
        input.value = 0;
    });
    document.getElementById('searchSparepart').value = '';
    document.querySelectorAll('.sparepart-item').forEach(item => {
        item.style.display = 'flex';
    });
    sparepartDipilih = [];
    renderSparepartSelected();
}

// ==================== UPDATE TOTAL ====================
function updateTotal() {
    const totalSP = sparepartDipilih.reduce((sum, sp) => sum + sp.subtotal, 0);
    const biayaJasa = parseInt(document.getElementById('inputBiayaJasa').value) || 0;
    const total = totalSP + biayaJasa;
    document.getElementById('totalBiaya').textContent = 'Rp ' + formatRupiah(total);
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// ==================== SUBMIT TRANSAKSI ====================
function submitTransaksi(e) {
    e.preventDefault();
    const form = e.target;
    const biayaJasa = parseInt(document.getElementById('inputBiayaJasa').value) || 0;
    
    if (sparepartDipilih.length === 0 && biayaJasa === 0) {
        Swal.fire({ icon: 'warning', title: 'Tidak ada item', text: 'Isi minimal sparepart atau biaya jasa', confirmButtonColor: '#a3e635' });
        return false;
    }
    
    const formData = new FormData(form);
    formData.append('sparepart', JSON.stringify(sparepartDipilih.map(s => ({ id: s.id_sparepart, qty: s.qty }))));
    
    Swal.fire({
        title: 'Simpan Transaksi?',
        html: 'Transaksi akan dibuat dengan status <b>"Proses"</b>',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#a3e635', cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
        cancelButtonText: 'Batal', reverseButtons: true
    }).then(result => {
        if (!result.isConfirmed) return;
        
        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('transaksi_simpan.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.status === 'success') {
                    closeTransaksiModal();
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: data.message,
                        confirmButtonColor: '#a3e635'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#ef4444' });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal koneksi server', confirmButtonColor: '#ef4444' });
            });
    });
    return false;
}

// ==================== SELESAI ====================
function confirmSelesai(id) {
    Swal.fire({
        title: 'Tandai Servis Selesai?',
        html: 'Status → <b class="text-yellow-500">Selesai</b><br>Mekanik akan <b class="text-lime-500">Tersedia</b> kembali',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#facc15', cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-check"></i> Ya, Selesai',
        cancelButtonText: 'Batal', reverseButtons: true
    }).then(r => { if (r.isConfirmed) window.location.href = 'pembayaran.php?selesai=' + id; });
}

// ==================== BAYAR ====================
function openBayarModal(id, total) {
    document.getElementById('bayarIdTrx').value = id;
    document.getElementById('bayarTotal').textContent = 'Rp ' + formatRupiah(total);
    document.getElementById('bayarTotal').setAttribute('data-total', total);
    document.getElementById('inputUangBayar').value = '';
    document.getElementById('bayarKembali').textContent = 'Rp 0';
    showModal('modalBayar');
}
function closeBayarModal() { hideModal('modalBayar'); }

function setUang(nominal) {
    document.getElementById('inputUangBayar').value = nominal;
    hitungKembalian();
}

function setUangUangPas() {
    const total = parseInt(document.getElementById('bayarTotal').getAttribute('data-total'));
    document.getElementById('inputUangBayar').value = total;
    hitungKembalian();
}

function hitungKembalian() {
    const total = parseInt(document.getElementById('bayarTotal').getAttribute('data-total')) || 0;
    const bayar = parseInt(document.getElementById('inputUangBayar').value) || 0;
    const kembali = bayar - total;
    document.getElementById('bayarKembali').textContent = 'Rp ' + formatRupiah(kembali >= 0 ? kembali : 0);
    document.getElementById('bayarKembali').style.color = kembali < 0 ? '#ef4444' : '#fff';
}

function submitBayar(e) {
    e.preventDefault();
    const form = e.target;
    const total = parseInt(document.getElementById('bayarTotal').getAttribute('data-total'));
    const bayar = parseInt(document.getElementById('inputUangBayar').value);
    
    if (bayar < total) {
        Swal.fire({ icon: 'error', title: 'Uang kurang!', text: 'Uang bayar kurang dari total tagihan', confirmButtonColor: '#ef4444' });
        return false;
    }
    
    const formData = new FormData(form);
    
    Swal.fire({
        title: 'Konfirmasi Pembayaran?',
        html: 'Bayar: <b class="text-lime-600">Rp ' + formatRupiah(bayar) + '</b><br>Kembali: <b class="text-blue-600">Rp ' + formatRupiah(bayar - total) + '</b>',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#a3e635', cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-check"></i> Ya, Bayar',
        cancelButtonText: 'Batal', reverseButtons: true
    }).then(r => {
        if (!r.isConfirmed) return;
        
        Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        
        fetch('transaksi_bayar.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.status === 'success') {
                    closeBayarModal();
                    window.open('struk_pdf.php?id=' + data.id_transaksi + '&print=1', '_blank');
                    
                    Swal.fire({
                        icon: 'success', title: 'Pembayaran Berhasil!',
                        text: 'Struk sedang dibuka di tab baru',
                        confirmButtonColor: '#a3e635'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, confirmButtonColor: '#ef4444' });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal koneksi server', confirmButtonColor: '#ef4444' });
            });
    });
    return false;
}

// ==================== FLASH MESSAGE ====================
document.addEventListener('DOMContentLoaded', function() {
    const flashEl = document.getElementById('flashData');
    if (!flashEl) return;
    
    const msg = flashEl.getAttribute('data-msg');
    const isError = flashEl.getAttribute('data-error') === '1';
    
    if (window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.delete('msg');
        window.history.replaceState({}, document.title, url);
    }
    
    if (isError) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: msg,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK',
            position: 'center',
            backdrop: 'rgba(0,0,0,0.5)'
        });
    } else {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: msg,
            confirmButtonColor: '#a3e635',
            confirmButtonText: 'OK',
            position: 'center',
            backdrop: 'rgba(0,0,0,0.5)',
            timer: 2500,
            timerProgressBar: true
        });
    }
});
</script>

</body>
</html>