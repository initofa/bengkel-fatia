<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Admin');

header('Content-Type: application/json');

try {
    // Hanya mekanik dengan status "Tersedia"
    $stmt = $pdo->query("SELECT * FROM mekanik 
                         WHERE status_aktif = 'Tersedia' 
                         ORDER BY nama_mekanik ASC");
    $data = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $data]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>