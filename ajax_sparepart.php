<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Admin');

header('Content-Type: application/json');

try {
    // ==================== CEK APAKAH MENCARI SATU SPAREPART ====================
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // Ambil 1 sparepart
        $stmt = $pdo->prepare("SELECT * FROM sparepart WHERE id_sparepart = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sparepart tidak ditemukan']);
        }
        exit;
    }
    
    // ==================== AMBIL SEMUA (untuk dropdown) ====================
    $search = $_GET['q'] ?? '';
    
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM sparepart 
                               WHERE nama LIKE ? AND stok > 0 
                               ORDER BY nama ASC");
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM sparepart 
                             WHERE stok > 0 
                             ORDER BY nama ASC");
    }
    $data = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $data]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>