<?php
/**
 * File setup_users.php
 * 
 * Jalankan SEKALI setelah import SQL untuk:
 * 1. Generate hash password yang valid
 * 2. Insert user admin & owner ke database
 * 
 * Cara pakai:
 *   1. Pastikan SQL sudah di-import
 *   2. Buka http://localhost/bengkel_fatia/setup_users.php
 *   3. Akan muncul tabel berisi username & password
 *   4. HAPUS file ini setelah selesai!
 */

require_once 'config/database.php';

// ==================== DATA USER YANG AKAN DIBUAT ====================
$users = [
    [
        'table'    => 'admin',
        'id_field' => 'id_admin',
        'id'       => 'ADM2501001',
        'nama'     => 'Admin Bengkel Fatia',
        'no_telp'  => '081111111111',
        'alamat'   => 'Bangkalan',
        'jk'       => 'Laki-laki',
        'username' => 'admin',
        'password' => 'admin123',
        'role'     => 'Admin'
    ],
    [
        'table'    => 'owner',
        'id_field' => 'id_owner',
        'id'       => 'OWN2501001',
        'nama'     => 'Bapak Fatia (Owner)',
        'no_telp'  => '083333333333',
        'alamat'   => 'Bangkalan',
        'jk'       => 'Laki-laki',
        'username' => 'owner',
        'password' => 'owner123',
        'role'     => 'Owner'
    ]
];

// ==================== PROSES INSERT ====================
$results = [];
foreach ($users as $u) {
    try {
        // Hapus user lama dengan username sama (kalau ada)
        $stmtDelete = $pdo->prepare("DELETE FROM {$u['table']} WHERE username = ?");
        $stmtDelete->execute([$u['username']]);

        // Generate hash password
        $hash = password_hash($u['password'], PASSWORD_DEFAULT);

        // Insert user baru
        $sql = "INSERT INTO {$u['table']} 
                ({$u['id_field']}, nama, no_telp, alamat, jenis_kelamin, username, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $u['id'],
            $u['nama'],
            $u['no_telp'],
            $u['alamat'],
            $u['jk'],
            $u['username'],
            $hash
        ]);

        $results[] = [
            'status'   => 'success',
            'username' => $u['username'],
            'password' => $u['password'],
            'role'     => $u['role'],
            'nama'     => $u['nama'],
            'message'  => 'User berhasil dibuat'
        ];
    } catch (PDOException $e) {
        $results[] = [
            'status'   => 'error',
            'username' => $u['username'],
            'password' => $u['password'],
            'role'     => $u['role'],
            'nama'     => $u['nama'],
            'message'  => 'Error: ' . $e->getMessage()
        ];
    }
}

// ==================== CEK APAKAH SEMUA BERHASIL ====================
$successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
$allSuccess = $successCount === count($users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup Users - Bengkel Fatia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-[#e2e4e7] flex items-center justify-center min-h-screen p-6">

<div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden">

    <!-- ==================== HEADER ==================== -->
    <div class="bg-gradient-to-r from-[#252332] to-[#3b3852] p-6 text-white border-b-4 border-[#a3e635]">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-[#a3e635] rounded-full flex items-center justify-center text-2xl">
                <i class="fas fa-user-cog text-slate-900"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black uppercase">SETUP USERS</h1>
                <p class="text-slate-300 text-sm">Sistem Informasi Bengkel Fatia Kamal</p>
            </div>
        </div>
    </div>

    <!-- ==================== CONTENT ==================== -->
    <div class="p-6">

        <!-- Status Berhasil / Gagal -->
        <?php if ($allSuccess): ?>
            <div class="bg-lime-500/15 border-2 border-lime-500 text-lime-700 rounded-lg p-4 mb-5 flex items-center gap-3">
                <i class="fas fa-check-circle text-2xl"></i>
                <div>
                    <p class="font-bold">✅ Setup Users Berhasil!</p>
                    <p class="text-xs">Semua user sudah dibuat dengan hash password yang valid.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-red-500/15 border-2 border-red-500 text-red-700 rounded-lg p-4 mb-5 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
                <div>
                    <p class="font-bold">⚠️ Ada Masalah!</p>
                    <p class="text-xs">Cek daftar user di bawah untuk detail error.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabel Hasil -->
        <h2 class="font-black text-slate-800 mb-3 flex items-center gap-2">
            <i class="fas fa-users text-lime-500"></i> Daftar User yang Dibuat
        </h2>

        <div class="border-2 border-slate-200 rounded-lg overflow-hidden mb-5">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-left">Password</th>
                        <th class="p-3 text-left">Role</th>
                        <th class="p-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr class="border-b border-slate-200 <?= $r['status'] === 'success' ? 'bg-lime-50' : 'bg-red-50' ?>">
                            <td class="p-3 font-mono font-bold"><?= htmlspecialchars($r['username']) ?></td>
                            <td class="p-3 font-mono"><?= htmlspecialchars($r['password']) ?></td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-full text-xs font-bold 
                                    <?= $r['role'] === 'Admin' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                    <?= $r['role'] ?>
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <?php if ($r['status'] === 'success'): ?>
                                    <i class="fas fa-check-circle text-lime-600 text-lg" title="Berhasil"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-red-600 text-lg" title="Gagal"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Info Login -->
        <?php if ($allSuccess): ?>
            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-5">
                <h3 class="font-bold text-blue-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Cara Login
                </h3>
                <div class="text-xs text-blue-700 space-y-1">
                    <p><strong>Admin:</strong> username <code class="bg-white px-2 py-0.5 rounded font-mono">admin</code> / password <code class="bg-white px-2 py-0.5 rounded font-mono">admin123</code></p>
                    <p><strong>Owner:</strong> username <code class="bg-white px-2 py-0.5 rounded font-mono">owner</code> / password <code class="bg-white px-2 py-0.5 rounded font-mono">owner123</code></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Warning Hapus File -->
        <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4 mb-5">
            <div class="flex items-start gap-3">
                <i class="fas fa-trash-alt text-red-500 text-xl mt-0.5"></i>
                <div>
                    <p class="font-bold text-red-700">⚠️ PENTING: HAPUS FILE INI!</p>
                    <p class="text-xs text-red-600 mt-1">
                        Setelah setup berhasil, <strong>hapus file <code>setup_users.php</code></strong> 
                        demi keamanan sistem. Kalau tidak dihapus, orang lain bisa menjalankan ulang 
                        dan mereset password Anda!
                    </p>
                </div>
            </div>
        </div>

        <!-- Tombol Ke Login -->
        <a href="index.php" 
           class="block w-full bg-[#a3e635] hover:bg-[#84cc16] text-slate-900 font-black py-3 rounded-lg text-center uppercase tracking-widest transition border-b-4 border-[#65a30d]">
            <i class="fas fa-sign-in-alt mr-2"></i> Ke Halaman Login
        </a>

    </div>

    <!-- ==================== FOOTER ==================== -->
    <div class="bg-slate-50 border-t p-4 text-center text-xs text-slate-500">
        <p>Sistem Informasi Bengkel Fatia Kamal &copy; <?= date('Y') ?></p>
    </div>

</div>

</body>
</html>