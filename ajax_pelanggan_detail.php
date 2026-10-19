<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Admin');

header('Content-Type: application/json');

try {
    $id_kendaraan = $_GET['id_kendaraan'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT k.*, p.nama AS nama_pelanggan, p.alamat, p.no_telp
                           FROM kendaraan k 
                           JOIN pelanggan p ON k.id_pelanggan = p.id_pelanggan
                           WHERE k.id_kendaraan = ?");
    $stmt->execute([$id_kendaraan]);
    $data = $stmt->fetch();
    
    if ($data) {
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kendaraan tidak ditemukan']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>