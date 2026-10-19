<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/generate_id.php';
cek_login('Admin');

header('Content-Type: application/json');

try {
    // ==================== AMBIL INPUT ====================
    $id_admin         = $_SESSION['user_id'];
    $id_kendaraan     = $_POST['id_kendaraan'] ?? '';
    $id_mekanik       = $_POST['id_mekanik'] ?? '';
    $catatan_keluhan  = trim($_POST['catatan_keluhan'] ?? '');
    $biaya_jasa       = (float)($_POST['biaya_jasa'] ?? 0);
    $sparepart_json   = $_POST['sparepart'] ?? '[]';    // Format JSON: [{id, qty}, ...]
    
    // ==================== VALIDASI ====================
    if (empty($id_kendaraan) || empty($id_mekanik) || empty($catatan_keluhan)) {
        echo json_encode(['status' => 'error', 'message' => 'Kendaraan, mekanik & keluhan wajib diisi!']);
        exit;
    }
    
    $sparepart_list = json_decode($sparepart_json, true);
    if (!is_array($sparepart_list)) $sparepart_list = [];
    
    // ==================== MULAI TRANSACTION ====================
    $pdo->beginTransaction();
    
    // ==================== HITUNG TOTAL SPAREPART ====================
    $total_sparepart = 0;
    $sparepart_detail = [];
    
    foreach ($sparepart_list as $item) {
        $id_sparepart = $item['id'];
        $qty = (int)$item['qty'];
        
        if ($qty <= 0) continue;
        
        // Ambil data sparepart
        $stmtSp = $pdo->prepare("SELECT * FROM sparepart WHERE id_sparepart = ?");
        $stmtSp->execute([$id_sparepart]);
        $sp = $stmtSp->fetch();
        
        if (!$sp) {
            throw new Exception("Sparepart ID '$id_sparepart' tidak ditemukan");
        }
        
        // Cek stok
        if ($sp['stok'] < $qty) {
            throw new Exception("Stok '{$sp['nama']}' tidak cukup! Tersisa: {$sp['stok']}, diminta: $qty");
        }
        
        $harga_satuan = (float)$sp['harga_jual'];
        $subtotal = $harga_satuan * $qty;
        $total_sparepart += $subtotal;
        
        $sparepart_detail[] = [
            'id_sparepart' => $id_sparepart,
            'qty' => $qty,
            'harga_satuan' => $harga_satuan,
            'subtotal' => $subtotal,
            'nama' => $sp['nama']
        ];
    }
    
    // ==================== HITUNG TOTAL BIAYA ====================
    $total_biaya = $total_sparepart + $biaya_jasa;
    
    // ==================== INSERT TRANSAKSI ====================
    $newIdTrx = generateId($pdo, 'transaksi', 'id_transaksi', 'TRX');
    
    $stmt1 = $pdo->prepare("INSERT INTO transaksi 
                            (id_transaksi, id_admin, id_kendaraan, id_mekanik, 
                             catatan_keluhan, biaya_jasa, total_sparepart, 
                             total_biaya, status) 
                            VALUES (?,?,?,?,?,?,?,?, 'Proses')");
    $stmt1->execute([
        $newIdTrx, $id_admin, $id_kendaraan, $id_mekanik,
        $catatan_keluhan, $biaya_jasa, $total_sparepart,
        $total_biaya
    ]);
    
    // ==================== INSERT DETAIL SPAREPART + KURANGI STOK ====================
    $counter = 1;
    foreach ($sparepart_detail as $detail) {
        // Insert ke transaksi_sparepart
        $id_detail = $newIdTrx . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
        $stmt2 = $pdo->prepare("INSERT INTO transaksi_sparepart 
                                (id_detail, id_transaksi, id_sparepart, qty, harga_satuan, subtotal) 
                                VALUES (?,?,?,?,?,?)");
        $stmt2->execute([
            $id_detail, $newIdTrx, $detail['id_sparepart'],
            $detail['qty'], $detail['harga_satuan'], $detail['subtotal']
        ]);
        
        // Kurangi stok
        $stmt3 = $pdo->prepare("UPDATE sparepart SET stok = stok - ? WHERE id_sparepart = ?");
        $stmt3->execute([$detail['qty'], $detail['id_sparepart']]);
        
        $counter++;
    }
    
    // ==================== UPDATE STATUS MEKANIK → BERTUGAS ====================
    $stmt4 = $pdo->prepare("UPDATE mekanik SET status_aktif='Bertugas' WHERE id_mekanik=?");
    $stmt4->execute([$id_mekanik]);
    
    $pdo->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaksi berhasil disimpan!',
        'id_transaksi' => $newIdTrx,
        'total_biaya' => $total_biaya
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>