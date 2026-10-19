<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login('Admin');

header('Content-Type: application/json');

try {
    $id_transaksi = $_POST['id_transaksi'] ?? '';
    $uang_bayar   = (float)($_POST['uang_bayar'] ?? 0);
    
    if (empty($id_transaksi) || $uang_bayar <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap!']);
        exit;
    }
    
    // Ambil transaksi
    $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id_transaksi=? AND status='Selesai'");
    $stmt->execute([$id_transaksi]);
    $trx = $stmt->fetch();
    
    if (!$trx) {
        echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan atau belum selesai']);
        exit;
    }
    
    // Cek uang cukup
    if ($uang_bayar < $trx['total_biaya']) {
        echo json_encode(['status' => 'error', 'message' => 'Uang bayar kurang dari total tagihan!']);
        exit;
    }
    
    $uang_kembali = $uang_bayar - $trx['total_biaya'];
    
    // Update transaksi → Dibayar
    $stmt2 = $pdo->prepare("UPDATE transaksi 
                            SET status='Dibayar', uang_bayar=?, uang_kembali=? 
                            WHERE id_transaksi=?");
    $stmt2->execute([$uang_bayar, $uang_kembali, $id_transaksi]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Pembayaran berhasil!',
        'uang_bayar' => $uang_bayar,
        'uang_kembali' => $uang_kembali,
        'id_transaksi' => $id_transaksi
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>