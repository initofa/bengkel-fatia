<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/generate_id.php';
cek_login('Admin');

$id_admin = $_SESSION['user_id'];

// ==================== FUNGSI BULAN INDONESIA ====================
function bulanIndo($angka) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $bulan[(int)$angka] ?? '-';
}

function tanggalIndo($date) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $ts = strtotime($date);
    return $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

// ==================== PROSES SIMPAN ====================
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan = (int)$_POST['bulan'];
    $tahun = (int)$_POST['tahun'];
    // Hapus titik pemisah ribuan sebelum dikonversi ke float
    $uang_keluar = (float)str_replace('.', '', $_POST['uang_keluar']);
    $keterangan  = trim($_POST['keterangan'] ?? '');
    
    if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
        $error = "Periode tidak valid!";
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_biaya), 0) 
                               FROM transaksi 
                               WHERE MONTH(tanggal_transaksi) = ? 
                               AND YEAR(tanggal_transaksi) = ? 
                               AND status='Dibayar'");
        $stmt->execute([$bulan, $tahun]);
        $uang_masuk = $stmt->fetchColumn();
        
        $stmtCek = $pdo->prepare("SELECT id_laporan FROM laporan_keuangan 
                                  WHERE MONTH(tanggal_laporan) = ? 
                                  AND YEAR(tanggal_laporan) = ?");
        $stmtCek->execute([$bulan, $tahun]);
        $existing = $stmtCek->fetchColumn();
        
        $tanggal_laporan = date('Y-m-t', strtotime("$tahun-$bulan-01"));
        
        if ($existing) {
            $stmt = $pdo->prepare("UPDATE laporan_keuangan 
                                   SET uang_masuk = ?, uang_keluar = ?, keterangan = ? 
                                   WHERE id_laporan = ?");
            $stmt->execute([$uang_masuk, $uang_keluar, $keterangan, $existing]);
            header("Location: laporan_keuangan_admin.php?msg=Laporan berhasil diperbarui");
        } else {
            $newId = generateId($pdo, 'laporan_keuangan', 'id_laporan', 'LAP');
            $stmt = $pdo->prepare("INSERT INTO laporan_keuangan 
                                   (id_laporan, id_admin, id_owner, tanggal_laporan, uang_masuk, uang_keluar, keterangan) 
                                   VALUES (?, ?, 'OWN2501001', ?, ?, ?, ?)");
            $stmt->execute([$newId, $id_admin, $tanggal_laporan, $uang_masuk, $uang_keluar, $keterangan]);
            header("Location: laporan_keuangan_admin.php?msg=Laporan berhasil dibuat");
        }
        exit;
    }
}

// ==================== AMBIL DATA LAPORAN ====================
$data = $pdo->query("SELECT lk.*, a.nama AS nama_admin 
                     FROM laporan_keuangan lk 
                     JOIN admin a ON lk.id_admin = a.id_admin 
                     ORDER BY lk.tanggal_laporan DESC")->fetchAll();

$totalLaporan = count($data);
$totalMasuk   = array_sum(array_column($data, 'uang_masuk'));
$totalKeluar  = array_sum(array_column($data, 'uang_keluar'));
$totalLaba    = $totalMasuk - $totalKeluar;

$flash_msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Laporan Keuangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        table tbody tr { height: 34px; }
        table thead tr { height: 32px; }
        .modal-active { display: flex !important; }

        /* Scrollbar custom untuk body modal */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { 
            background: #475569; border-radius: 10px; 
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</head>
<body class="bg-[#e2e4e7] p-6 min-h-screen">

<div class="max-w-7xl mx-auto flex gap-6" style="height: 620px;">

    <div class="flex-1 bg-[#252332] rounded-2xl shadow-2xl p-5 flex flex-col">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-2">
            <div class="flex items-center gap-3">
                <h1 class="text-white font-black text-lg tracking-wider">KELOLA LAPORAN KEUANGAN</h1>
                <span class="text-[10px] font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full border border-blue-500/40">
                    <i class="fas fa-user-shield"></i> ADMIN
                </span>
            </div>
            <div class="flex gap-2 text-xs">
                <span class="bg-white/10 text-lime-400 px-2.5 py-1 rounded-full font-bold">
                    <i class="fas fa-file"></i> <?= $totalLaporan ?> Laporan
                </span>
            </div>
        </div>

        <!-- RINGKASAN GLOBAL -->
        <div class="grid grid-cols-3 gap-2 mb-2">
            <div class="bg-lime-500/20 border border-lime-400 rounded-lg p-2">
                <p class="text-lime-300 text-[10px] font-bold">TOTAL PEMASUKAN</p>
                <p class="text-white font-black text-sm">Rp <?= number_format($totalMasuk,0,',','.') ?></p>
            </div>
            <div class="bg-red-500/20 border border-red-400 rounded-lg p-2">
                <p class="text-red-300 text-[10px] font-bold">TOTAL PENGELUARAN</p>
                <p class="text-white font-black text-sm">Rp <?= number_format($totalKeluar,0,',','.') ?></p>
            </div>
            <div class="bg-blue-500/20 border border-blue-400 rounded-lg p-2">
                <p class="text-blue-300 text-[10px] font-bold">TOTAL LABA</p>
                <p class="text-white font-black text-sm">Rp <?= number_format($totalLaba,0,',','.') ?></p>
            </div>
        </div>

        <!-- TABEL LAPORAN -->
        <div class="flex-1 rounded-lg overflow-hidden">
            <table class="w-full text-sm table-fixed">
                <thead class="bg-slate-700 text-white">
                    <tr>
                        <th class="p-2 text-left w-10">No</th>
                        <th class="p-2 text-left w-40">Periode</th>
                        <th class="p-2 text-right">Uang Masuk</th>
                        <th class="p-2 text-right">Uang Keluar</th>
                        <th class="p-2 text-right">Laba</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-8">
                            <i class="fas fa-inbox text-2xl block mb-2 text-slate-600"></i>
                            Belum ada laporan keuangan
                        </td>
                    </tr>
                <?php else: foreach ($data as $i => $l): 
                    $laba = $l['uang_masuk'] - $l['uang_keluar'];
                    $no = $i + 1;
                ?>
                    <tr class="text-white border-b border-slate-700 hover:bg-slate-700/50 transition">
                        <td class="p-2 text-slate-400"><?= $no ?></td>
                        <td class="p-2 font-bold text-lime-400">
                            <?= tanggalIndo($l['tanggal_laporan']) ?>
                        </td>
                        <td class="p-2 text-right font-mono text-xs text-lime-300">
                            Rp <?= number_format($l['uang_masuk'],0,',','.') ?>
                        </td>
                        <td class="p-2 text-right font-mono text-xs text-red-300">
                            Rp <?= number_format($l['uang_keluar'],0,',','.') ?>
                        </td>
                        <td class="p-2 text-right font-mono text-xs font-bold <?= $laba >= 0 ? 'text-blue-300' : 'text-red-300' ?>">
                            <?= $laba >= 0 ? '+ ' : '- ' ?>Rp <?= number_format(abs($laba),0,',','.') ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TOMBOL BUAT LAPORAN -->
        <div class="flex gap-2 mt-1">
            <button onclick="openModalLaporan()" 
                    class="bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold px-3.5 py-1.5 rounded text-xs transition flex items-center gap-2">
                <i class="fas fa-plus"></i> BUAT LAPORAN BARU
            </button>
        </div>

    </div>

    <?php include 'includes/sidebar.php'; ?>
</div>

<!-- ==================== MODAL BUAT LAPORAN ==================== -->
<div id="modalLaporan" class="hidden fixed inset-0 bg-black/60 items-center justify-center z-50 p-4">
    <div class="bg-[#252332] rounded-2xl w-[560px] max-h-[90vh] shadow-2xl border border-lime-500/30 flex flex-col">
        
        <!-- HEADER MODAL -->
        <div class="flex items-center justify-between p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#a3e635] rounded-full flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-slate-900"></i>
                </div>
                <h2 class="text-white font-black text-lg">BUAT LAPORAN KEUANGAN</h2>
            </div>
            <button onclick="closeModalLaporan()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- BODY (scrollable) -->
        <form method="POST" id="formLaporan" onsubmit="return submitLaporan(event)" 
              class="flex-1 overflow-y-auto p-4 space-y-3 custom-scroll">
            
            <!-- Periode -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">PERIODE LAPORAN</label>
                <div class="grid grid-cols-2 gap-2">
                    <select name="bulan" required 
                            class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold text-slate-900">
                        <option disabled value="">-- PILIH BULAN --</option>
                        <?php for ($b = 1; $b <= 12; $b++): ?>
                            <option value="<?= $b ?>" <?= $b == date('n') ? 'selected' : '' ?>>
                                <?= strtoupper(bulanIndo($b)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="tahun" required 
                            class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold text-slate-900">
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <!-- Info Cara Kerja -->
            <div class="bg-lime-500/10 border border-lime-500/30 rounded-xl p-3">
                <p class="text-lime-300 text-[11px] font-bold flex items-center gap-1.5 mb-1">
                    <i class="fas fa-info-circle"></i> CARA KERJA
                </p>
                <ul class="text-slate-300 text-[11px] space-y-0.5 list-disc list-inside">
                    <li><b class="text-lime-400">Uang Masuk</b> otomatis dari transaksi "Dibayar"</li>
                    <li><b class="text-red-400">Uang Keluar</b> Anda input manual di bawah</li>
                    <li><b class="text-blue-400">Laba</b> dihitung otomatis setelah disimpan</li>
                </ul>
            </div>
            
            <!-- Uang Keluar -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">UANG KELUAR (PENGELUARAN)</label>
                <input name="uang_keluar" id="inputUangKeluar" type="text" inputmode="numeric" required 
                       value="0"
                       placeholder="Contoh: 500.000"
                       oninput="formatRibuan(this)"
                       onfocus="if(this.value=='0') this.value=''"
                       onblur="if(this.value=='') this.value='0'"
                       class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold text-slate-900
                              focus:outline-none focus:ring-2 focus:ring-lime-400">
                <p class="text-slate-400 text-[10px] mt-1">
                    Contoh: gaji mekanik, listrik, sewa, pembelian sparepart, dll
                </p>
            </div>
            
            <!-- Keterangan -->
            <div>
                <label class="text-slate-300 text-xs font-bold mb-1 block">
                    KETERANGAN PENGELUARAN
                    <span class="text-slate-500 italic font-normal text-[10px]">(opsional)</span>
                </label>
                <textarea name="keterangan" rows="3"
                          placeholder="Contoh:&#10;- Gaji mekanik: Rp 1.500.000&#10;- Listrik: Rp 500.000&#10;- Sewa: Rp 1.000.000"
                          class="w-full bg-white rounded-xl px-3 py-2 text-sm font-semibold text-slate-900 resize-none
                                 focus:outline-none focus:ring-2 focus:ring-lime-400"></textarea>
                <p class="text-slate-400 text-[10px] mt-1">
                    <i class="fas fa-lightbulb text-yellow-400"></i> Tulis rincian biar Owner tahu kemana uangnya pergi
                </p>
            </div>
            
        </form>
        
        <!-- FOOTER MODAL -->
        <div class="flex gap-2 p-4 border-t border-slate-700">
            <button type="button" onclick="closeModalLaporan()" 
                    class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 rounded-xl transition text-sm">
                BATAL
            </button>
            <button type="submit" form="formLaporan"
                    class="flex-1 bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-bold py-2 rounded-xl transition text-sm">
                <i class="fas fa-save mr-1"></i> SIMPAN
            </button>
        </div>
    </div>
</div>

<!-- FLASH MESSAGE DATA -->
<?php if (!empty($flash_msg)): ?>
<div id="flashData" 
     data-msg="<?= htmlspecialchars($flash_msg, ENT_QUOTES) ?>"
     data-error="<?= strpos(strtolower($flash_msg), 'gagal') !== false ? '1' : '0' ?>"
     style="display:none;"></div>
<?php endif; ?>

<script>
function openModalLaporan() { 
    document.getElementById('modalLaporan').classList.add('modal-active');
}
function closeModalLaporan() { 
    document.getElementById('modalLaporan').classList.remove('modal-active');
}

// Tutup modal dengan ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModalLaporan();
});

// ==================== FORMAT RIBUAN OTOMATIS ====================
// Ketik 500000 → otomatis jadi 500.000
function formatRibuan(input) {
    // Hapus semua karakter selain angka
    let angka = input.value.replace(/[^0-9]/g, '');
    
    // Kalau kosong, biarkan kosong sementara
    if (angka === '') {
        input.value = '';
        return;
    }
    
    // Format dengan titik pemisah ribuan
    input.value = new Intl.NumberFormat('id-ID').format(angka);
}

// ==================== SUBMIT LAPORAN ====================
function submitLaporan(e) {
    e.preventDefault();
    const form = e.target;
    const bulanText = form.bulan.options[form.bulan.selectedIndex].text;
    const keluarText = document.getElementById('inputUangKeluar').value;
    const keluarAngka = parseInt(keluarText.replace(/\./g, '')) || 0;
    
    Swal.fire({
        title: 'Simpan Laporan?',
        html: 'Laporan periode <b>' + bulanText + ' ' + form.tahun.value + '</b><br>' +
              'dengan pengeluaran <b class="text-red-600">Rp ' + keluarAngka.toLocaleString('id-ID') + '</b>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#a3e635',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then(r => {
        if (r.isConfirmed) form.submit();
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