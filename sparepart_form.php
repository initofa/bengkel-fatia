<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/generate_id.php';
cek_login(['Admin', 'Owner']);

$role = $_SESSION['role'];   // Admin / Owner
$is_owner = ($role == 'Owner');

$id = $_GET['id'] ?? 0;
$edit = false;
$s = ['nama'=>'', 'stok'=>'', 'harga_beli'=>'', 'harga_jual'=>''];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM sparepart WHERE id_sparepart = ?");
    $stmt->execute([$id]);
    $s = $stmt->fetch();
    if (!$s) { header("Location: sparepart.php"); exit; }
    $edit = true;
}

// ==================== PROSES SUBMIT ====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama       = $_POST['nama'];
    $stok       = $_POST['stok'];
    $harga_jual = $_POST['harga_jual'];
    
    // Harga beli hanya bisa diubah oleh Owner
    // Kalau Admin, ambil dari database (jangan ubah)
    if ($is_owner) {
        $harga_beli = $_POST['harga_beli'];
    } else {
        // Admin tidak boleh ubah harga beli — pakai nilai lama
        $harga_beli = $s['harga_beli'] ?? 0;
    }
    
    if ($edit) {
        $stmt = $pdo->prepare("UPDATE sparepart SET nama=?, stok=?, harga_beli=?, harga_jual=? WHERE id_sparepart=?");
        $stmt->execute([$nama, $stok, $harga_beli, $harga_jual, $id]);
    } else {
        // Kalau Admin bikin sparepart baru, harga_beli default 0
        // Owner wajib isi harga_beli
        $newId = generateId($pdo, 'sparepart', 'id_sparepart', 'SPR');
        $stmt = $pdo->prepare("INSERT INTO sparepart (id_sparepart, nama, stok, harga_beli, harga_jual) VALUES (?,?,?,?,?)");
        $stmt->execute([$newId, $nama, $stok, $harga_beli, $harga_jual]);
    }
    header("Location: sparepart.php?msg=Data berhasil disimpan");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $edit?'Edit':'Tambah' ?> Sparepart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#e2e4e7] flex items-center justify-center h-screen">
<div class="w-[900px] bg-[#252332] rounded-2xl shadow-2xl flex overflow-hidden" style="height: 550px;">

    <!-- ==================== KIRI: ILUSTRASI ==================== -->
    <div class="w-1/2 p-10 flex flex-col justify-between relative overflow-hidden">
        <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-[#1c1a26] rounded-full opacity-60"></div>

        <h1 class="text-3xl font-black text-white leading-tight relative z-10">
            <?= $edit ? 'EDIT DATA<br>SPAREPART' : 'TAMBAH DATA<br>SPAREPART' ?>
        </h1>

        <div class="relative z-10 flex justify-center -mt-20">
            <img src="gambar/sparepart.png" alt="" class="max-h-48">
        </div>

        <p class="text-slate-300 text-xs relative z-10">Sistem Informasi Bengkel Fatia Kamal</p>
    </div>

    <!-- ==================== KANAN: FORM ==================== -->
    <div class="w-1/2 p-10 flex flex-col justify-center relative">
        <a href="sparepart.php" 
           class="absolute top-4 right-4 bg-slate-700 hover:bg-slate-600 text-lime-400 font-bold px-4 py-1 rounded text-xs transition">
            BACK
        </a>

        <!-- Info role -->
        <div class="absolute top-4 left-10 text-xs font-bold tracking-wider">
            <?php if ($is_owner): ?>
                <span class="text-lime-400"><i class="fas fa-crown"></i> OWNER MODE</span>
            <?php else: ?>
                <span class="text-blue-400"><i class="fas fa-user-shield"></i> ADMIN MODE</span>
            <?php endif; ?>
        </div>

        <form method="POST" class="space-y-3" id="formSparepart" onsubmit="return confirmSubmit(event)">
            <input name="nama" 
                   value="<?= htmlspecialchars($s['nama']) ?>" 
                   placeholder="NAMA" 
                   required
                   class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400">

            <input name="stok" 
                   type="number" 
                   value="<?= htmlspecialchars($s['stok']) ?>" 
                   placeholder="STOK" 
                   required
                   class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400">

            <?php if ($is_owner): ?>
                <!-- HARGA BELI — HANYA UNTUK OWNER -->
                <div class="relative">
                    <input name="harga_beli" 
                           type="number" 
                           value="<?= htmlspecialchars($s['harga_beli']) ?>" 
                           placeholder="HARGA BELI (modal)" 
                           required
                           class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400 border-2 border-lime-400">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-lime-600 bg-lime-100 px-2 py-0.5 rounded-full">
                        KHUSUS OWNER
                    </span>
                </div>
            <?php else: ?>
                <!-- ADMIN: harga beli disembunyikan (hidden input) -->
                <input type="hidden" name="harga_beli" value="<?= htmlspecialchars($s['harga_beli']) ?>">
                
                <!-- Info untuk Admin -->
                <div class="bg-slate-700/50 border border-slate-600 rounded-full px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-lock text-slate-400 text-sm"></i>
                    <span class="text-slate-400 text-xs italic">Harga beli hanya bisa diubah oleh Owner</span>
                </div>
            <?php endif; ?>

            <input name="harga_jual" 
                   type="number" 
                   value="<?= htmlspecialchars($s['harga_jual']) ?>" 
                   placeholder="HARGA JUAL" 
                   required
                   class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400">

            <div class="pt-2">
                <button type="submit" 
                        class="bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-black py-2.5 px-8 rounded-full uppercase text-sm tracking-widest shadow-lg transition">
                    SUBMIT
                </button>
            </div>
        </form>
    </div>

</div>

<!-- ==================== SWEETALERT2 SCRIPT ==================== -->
<script>
function confirmSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const isEdit = <?= $edit ? 'true' : 'false' ?>;
    const isOwner = <?= $is_owner ? 'true' : 'false' ?>;
    
    Swal.fire({
        title: isEdit ? 'Simpan Perubahan?' : 'Simpan Data Baru?',
        html: isEdit 
            ? 'Data sparepart akan diperbarui' 
            : 'Data sparepart akan ditambahkan ke database' + 
              (isOwner ? '' : '<br><small class="text-slate-500">Harga beli tidak akan diubah</small>'),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#a3e635',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}
</script>

</body>
</html>