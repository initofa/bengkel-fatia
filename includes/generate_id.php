<?php
/**
 * File generate_id.php
 * Generate ID manual (pengganti AUTO_INCREMENT)
 * 
 * Format: PREFIX + YYMM + 3 digit urut
 * 
 * Contoh penggunaan:
 *   - generateId($pdo, 'sparepart', 'id_sparepart', 'SPR')
 *     → SPR2501010
 *   - generateId($pdo, 'transaksi', 'id_transaksi', 'TRX')
 *     → TRX2501009
 *   - generateId($pdo, 'admin', 'id_admin', 'ADM')
 *     → ADM2501002
 * 
 * Prefix yang tersedia:
 *   ADM  → Admin
 *   OWN  → Owner
 *   SPR  → Sparepart
 *   TRX  → Transaksi
 *   LAP  → Laporan Keuangan
 */

/**
 * Generate ID baru
 * 
 * @param PDO    $pdo     Koneksi database
 * @param string $table   Nama tabel (contoh: 'sparepart')
 * @param string $column  Nama kolom ID (contoh: 'id_sparepart')
 * @param string $prefix  Prefix 2-4 huruf kapital (contoh: 'SPR')
 * @return string         ID baru (contoh: 'SPR2501010')
 * @throws InvalidArgumentException jika prefix tidak valid
 * @throws PDOException jika query gagal
 */
function generateId($pdo, $table, $column, $prefix) {
    // ==================== VALIDASI PREFIX ====================
    if (!preg_match('/^[A-Z]{2,4}$/', $prefix)) {
        throw new InvalidArgumentException(
            "Prefix tidak valid: '$prefix'. Harus 2-4 huruf kapital."
        );
    }

    // ==================== BUAT PREFIX + YYMM ====================
    $yy = date('y');   // 2 digit tahun (contoh: 25 = 2025)
    $mm = date('m');   // 2 digit bulan (01-12)
    $prefixFull = $prefix . $yy . $mm;
    // Contoh: SPR2501

    // ==================== CARI ID TERAKHIR ====================
    try {
        $stmt = $pdo->prepare(
            "SELECT $column FROM $table 
             WHERE $column LIKE ? 
             ORDER BY $column DESC 
             LIMIT 1"
        );
        $stmt->execute([$prefixFull . '%']);
        $last = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("generateId error: " . $e->getMessage());
        throw $e;
    }

    // ==================== HITUNG NOMOR URUT BARU ====================
    if ($last) {
        // Contoh: SPR2501005 → ambil '005' → 5 → +1 = 6
        $lastNum = (int) substr($last, -3);
        $newNum = $lastNum + 1;
    } else {
        // Belum ada data bulan ini → mulai dari 1
        $newNum = 1;
    }

    // ==================== HANDLE OVERFLOW (> 999) ====================
    if ($newNum > 999) {
        // Contoh: SPR25011000 (4 digit)
        return $prefixFull . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    // ==================== FORMAT AKHIR ====================
    // Contoh: SPR + 2501 + 006 = SPR2501006
    return $prefixFull . str_pad($newNum, 3, '0', STR_PAD_LEFT);
}

/**
 * Generate ID unik (anti race condition)
 * Retry maksimal 5 kali kalau ID sudah ada
 * 
 * @param PDO    $pdo     Koneksi database
 * @param string $table   Nama tabel
 * @param string $column  Nama kolom ID
 * @param string $prefix  Prefix
 * @param int    $maxRetry Maksimal percobaan (default 5)
 * @return string
 * @throws Exception jika gagal setelah maxRetry
 */
function generateIdSafe($pdo, $table, $column, $prefix, $maxRetry = 5) {
    for ($i = 0; $i < $maxRetry; $i++) {
        $id = generateId($pdo, $table, $column, $prefix);
        
        // Cek apakah ID sudah ada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$id]);
        
        if ($stmt->fetchColumn() == 0) {
            return $id;   // ID unik, aman
        }
        
        usleep(50000);   // Tunggu 50ms sebelum retry
    }
    
    throw new Exception("Gagal generate ID unik setelah $maxRetry percobaan");
}

/**
 * Validasi format ID
 * 
 * @param string $id          ID yang dicek
 * @param int    $digitCount  Jumlah digit nomor urut (default 3)
 * @return bool
 */
function isValidId($id, $digitCount = 3) {
    $pattern = '/^[A-Z]{2,4}\d{4}\d{' . $digitCount . '}$/';
    return (bool) preg_match($pattern, $id);
}

/**
 * Ambil nomor urut dari ID
 * 
 * @param string $id
 * @return int
 */
function getIdNumber($id) {
    return (int) substr($id, -3);
}

/**
 * Ambil prefix dari ID (contoh: 'SPR' dari 'SPR2501001')
 * 
 * @param string $id
 * @return string
 */
function getIdPrefix($id) {
    return preg_replace('/\d+$/', '', $id);
}

/**
 * Ambil periode YYMM dari ID (contoh: '2501' dari 'SPR2501001')
 * 
 * @param string $id
 * @return string
 */
function getIdPeriode($id) {
    // Buang prefix, buang 3 digit terakhir
    return preg_replace('/^[A-Z]+/', '', substr($id, 0, -3));
}
?>