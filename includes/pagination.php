<?php
/**
 * File pagination.php
 * Helper pagination untuk semua halaman CRUD
 * 
 * Cara pakai:
 *   1. Hitung total:    $total = $pdo->query("SELECT COUNT(*) FROM tabel")->fetchColumn();
 *   2. Buat pagination: $pg = paginate($total, 9);
 *   3. Query data:      $data = $pdo->query("SELECT * FROM tabel LIMIT {$pg['limit']} OFFSET {$pg['offset']}")->fetchAll();
 *   4. Render tombol:   echo renderPagination($pg, 'halaman.php', ['q' => $search]);
 */

/**
 * Hitung metadata pagination
 * 
 * @param int $totalData  Total baris data
 * @param int $perPage    Jumlah data per halaman (default 9)
 * @return array          Metadata pagination
 */
function paginate($totalData, $perPage = 9) {
    // ==================== VALIDASI INPUT ====================
    $totalData = max(0, (int)$totalData);
    $perPage   = max(1, (int)$perPage);

    // ==================== HITUNG TOTAL HALAMAN ====================
    $totalPages  = max(1, (int)ceil($totalData / $perPage));
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Clamp: minimal 1, maksimal totalPages
    if ($currentPage < 1) $currentPage = 1;
    if ($currentPage > $totalPages) $currentPage = $totalPages;

    // ==================== HITUNG OFFSET ====================
    $offset = ($currentPage - 1) * $perPage;

    return [
        'current'   => $currentPage,
        'total'     => $totalPages,
        'perPage'   => $perPage,
        'offset'    => $offset,
        'limit'     => $perPage,
        'totalData' => $totalData,
        'from'      => $totalData > 0 ? $offset + 1 : 0,
        'to'        => min($offset + $perPage, $totalData),
    ];
}

/**
 * Render HTML tombol pagination
 * 
 * @param array  $pg      Return dari function paginate()
 * @param string $url     URL halaman (tanpa parameter 'page')
 * @param array  $params  Parameter GET tambahan (contoh: ['q' => $search])
 * @return string         HTML pagination
 */
function renderPagination($pg, $url, $params = []) {
    // ==================== GUARD: Tidak perlu tampil kalau 1 halaman ====================
    if ($pg['total'] <= 1) return '';

    // ==================== BERSIHKAN PARAMETER 'page' ====================
    // Biar tidak duplikat kalau $params sudah punya 'page'
    $params = array_filter($params, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY);

    // ==================== BANGUN BASE URL ====================
    $query   = http_build_query($params);
    $baseUrl = $url . ($query ? '?' . $query : '');
    $sep     = $query ? '&' : '?';

    $current = $pg['current'];
    $total   = $pg['total'];

    // ==================== HITUNG RANGE HALAMAN ====================
    // Maks 5 tombol nomor
    $start = max(1, $current - 2);
    $end   = min($total, $start + 4);
    if ($end - $start < 4) $start = max(1, $end - 4);

    ob_start();
    ?>
    <div class="flex justify-between items-center my-2">
        <!-- Info: Menampilkan X-Y dari Z data -->
        <p class="text-slate-400 text-xs">
            Menampilkan 
            <span class="text-lime-400 font-bold"><?= $pg['from'] ?>-<?= $pg['to'] ?></span>
            dari 
            <span class="text-lime-400 font-bold"><?= $pg['totalData'] ?></span> 
            data
        </p>

        <div class="flex items-center gap-1">
            <!-- Tombol First « -->
            <?php if ($current > 1): ?>
                <a href="<?= htmlspecialchars($baseUrl . $sep . 'page=1') ?>" 
                   title="Halaman Pertama"
                   class="w-7 h-7 flex items-center justify-center bg-white/5 hover:bg-white/10 text-slate-300 rounded transition text-xs">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="<?= htmlspecialchars($baseUrl . $sep . 'page=' . ($current - 1)) ?>" 
                   title="Sebelumnya"
                   class="w-7 h-7 flex items-center justify-center bg-white/5 hover:bg-white/10 text-slate-300 rounded transition text-xs">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php else: ?>
                <span class="w-7 h-7 flex items-center justify-center bg-white/5 text-slate-600 rounded text-xs cursor-not-allowed">
                    <i class="fas fa-angle-double-left"></i>
                </span>
                <span class="w-7 h-7 flex items-center justify-center bg-white/5 text-slate-600 rounded text-xs cursor-not-allowed">
                    <i class="fas fa-angle-left"></i>
                </span>
            <?php endif; ?>

            <!-- Tombol Nomor Halaman -->
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?= htmlspecialchars($baseUrl . $sep . 'page=' . $i) ?>"
                   class="w-7 h-7 flex items-center justify-center rounded text-xs font-bold transition
                   <?= $i == $current 
                        ? 'bg-[#a3e635] text-slate-900 shadow-md shadow-lime-500/30' 
                        : 'bg-white/5 hover:bg-white/10 text-slate-300' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Tombol Next › -->
            <?php if ($current < $total): ?>
                <a href="<?= htmlspecialchars($baseUrl . $sep . 'page=' . ($current + 1)) ?>" 
                   title="Selanjutnya"
                   class="w-7 h-7 flex items-center justify-center bg-white/5 hover:bg-white/10 text-slate-300 rounded transition text-xs">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="<?= htmlspecialchars($baseUrl . $sep . 'page=' . $total) ?>" 
                   title="Halaman Terakhir"
                   class="w-7 h-7 flex items-center justify-center bg-white/5 hover:bg-white/10 text-slate-300 rounded transition text-xs">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php else: ?>
                <span class="w-7 h-7 flex items-center justify-center bg-white/5 text-slate-600 rounded text-xs cursor-not-allowed">
                    <i class="fas fa-angle-right"></i>
                </span>
                <span class="w-7 h-7 flex items-center justify-center bg-white/5 text-slate-600 rounded text-xs cursor-not-allowed">
                    <i class="fas fa-angle-double-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>