<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/generate_id.php';
cek_login('Admin');

$id = $_GET['id'] ?? 0;
$edit = false;
$p = ['nama'=>'', 'alamat'=>'', 'no_telp'=>'', 'jenis_kelamin'=>''];

// ==================== JIKA EDIT, AMBIL DATA ====================
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) { header("Location: pelanggan.php"); exit; }
    $edit = true;
}

// ==================== PROSES SUBMIT ====================
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama      = trim($_POST['nama']);
    $alamat    = trim($_POST['alamat']);
    $no_telp   = trim($_POST['no_telp']);
    $jk        = $_POST['jenis_kelamin'] ?? '';
    
    if (empty($nama) || empty($alamat) || empty($no_telp)) {
        $error = "Semua field harus diisi!";
    } elseif (empty($jk)) {
        $error = "Pilih jenis kelamin terlebih dahulu!";
    } else {
        if ($edit) {
            // ============ UPDATE ============
            $stmt = $pdo->prepare("UPDATE pelanggan SET nama=?, alamat=?, no_telp=?, jenis_kelamin=? WHERE id_pelanggan=?");
            $stmt->execute([$nama, $alamat, $no_telp, $jk, $id]);
        } else {
            // ============ INSERT ============
            $newId = generateId($pdo, 'pelanggan', 'id_pelanggan', 'CUST');
            $stmt = $pdo->prepare("INSERT INTO pelanggan (id_pelanggan, nama, alamat, no_telp, jenis_kelamin) VALUES (?,?,?,?,?)");
            $stmt->execute([$newId, $nama, $alamat, $no_telp, $jk]);
        }
        header("Location: pelanggan.php?msg=" . ($edit ? "Data berhasil diperbarui" : "Data berhasil ditambahkan"));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $edit ? 'Edit' : 'Tambah' ?> Pelanggan</title>
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
            <?= $edit ? 'EDIT DATA<br>PELANGGAN' : 'TAMBAH DATA<br>PELANGGAN' ?>
        </h1>

        <div class="relative z-10 flex justify-center -mt-20">
            <img src="gambar/pelanggan.png" alt="" class="max-h-48">
        </div>

        <p class="text-slate-300 text-xs relative z-10">Sistem Informasi Bengkel Fatia Kamal</p>
    </div>

    <!-- ==================== KANAN: FORM ==================== -->
    <div class="w-1/2 p-10 flex flex-col justify-center relative">
        <a href="pelanggan.php" 
           class="absolute top-4 right-4 bg-slate-700 hover:bg-slate-600 text-lime-400 font-bold px-4 py-1 rounded text-xs transition">
            BACK
        </a>

        <form method="POST" class="space-y-3" id="formPelanggan" onsubmit="return confirmSubmit(event)">
            
            <!-- Nama -->
            <input name="nama" 
                   value="<?= htmlspecialchars($p['nama']) ?>" 
                   placeholder="NAMA" 
                   required
                   autocomplete="off"
                   class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400 transition">

            <!-- Alamat -->
            <input name="alamat" 
                   value="<?= htmlspecialchars($p['alamat']) ?>" 
                   placeholder="ALAMAT" 
                   required
                   autocomplete="off"
                   class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400 transition">

            <!-- No Telp (NUMBER) -->
            <input name="no_telp" 
                   type="number" 
                   value="<?= htmlspecialchars($p['no_telp']) ?>" 
                   placeholder="NO TELP" 
                   required
                   autocomplete="off"
                   min="0"
                   class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400 transition
                          [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

            <!-- Jenis Kelamin (dengan placeholder disabled) -->
            <select name="jenis_kelamin" 
                    required
                    class="w-full bg-white rounded-full px-5 py-3 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-lime-400 transition">
                <option value="" disabled <?= empty($p['jenis_kelamin']) ? 'selected' : '' ?>>
                    -- PILIH JENIS KELAMIN --
                </option>
                <option value="Laki-laki" <?= $p['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>LAKI-LAKI</option>
                <option value="Perempuan" <?= $p['jenis_kelamin']=='Perempuan'?'selected':'' ?>>PEREMPUAN</option>
            </select>

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
    
    Swal.fire({
        title: isEdit ? 'Simpan Perubahan?' : 'Simpan Data Baru?',
        text: isEdit 
            ? 'Data pelanggan akan diperbarui' 
            : 'Data pelanggan akan ditambahkan ke database',
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

// ==================== NOTIFIKASI ERROR ====================
<?php if (!empty($error)): ?>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: '<?= htmlspecialchars($error) ?>',
    confirmButtonColor: '#ef4444'
});
<?php endif; ?>
</script>

</body>
</html>