<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/generate_id.php';
cek_login('Admin');

header('Content-Type: application/json');

try {
    // ==================== AMBIL INPUT ====================
    $nama         = trim($_POST['nama'] ?? '');
    $alamat       = trim($_POST['alamat'] ?? '');
    $no_telp      = trim($_POST['no_telp'] ?? '');
    $jk           = $_POST['jenis_kelamin'] ?? 'Laki-laki';
    $no_polisi    = strtoupper(trim($_POST['no_polisi'] ?? ''));
    $jenis_motor  = trim($_POST['jenis_motor'] ?? '');
    $tahun        = (int)($_POST['tahun'] ?? date('Y'));
    
    // ==================== VALIDASI ====================
    if (empty($nama) || empty($alamat) || empty($no_telp) || empty($no_polisi) || empty($jenis_motor)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi!']);
        exit;
    }
    
    // Cek no polisi duplikat
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM kendaraan WHERE no_polisi = ?");
    $stmtCheck->execute([$no_polisi]);
    if ($stmtCheck->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => "No polisi '$no_polisi' sudah terdaftar!"]);
        exit;
    }
    
    // ==================== INSERT PELANGGAN ====================
    $pdo->beginTransaction();
    
    $newIdPelanggan = generateId($pdo, 'pelanggan', 'id_pelanggan', 'CUST');
    $stmt1 = $pdo->prepare("INSERT INTO pelanggan 
                            (id_pelanggan, nama, alamat, no_telp, jenis_kelamin) 
                            VALUES (?,?,?,?,?)");
    $stmt1->execute([$newIdPelanggan, $nama, $alamat, $no_telp, $jk]);
    
    // ==================== INSERT KENDARAAN ====================
    $newIdKendaraan = generateId($pdo, 'kendaraan', 'id_kendaraan', 'MTR');
    $stmt2 = $pdo->prepare("INSERT INTO kendaraan 
                            (id_kendaraan, id_pelanggan, no_polisi, jenis_motor, tahun) 
                            VALUES (?,?,?,?,?)");
    $stmt2->execute([$newIdKendaraan, $newIdPelanggan, $no_polisi, $jenis_motor, $tahun]);
    
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => "Pelanggan '$nama' & kendaraan '$no_polisi' berhasil ditambahkan",
        'id_pelanggan' => $newIdPelanggan,
        'id_kendaraan' => $newIdKendaraan,
        'nama' => $nama,
        'no_polisi' => $no_polisi
    ]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>