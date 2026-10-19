<?php
/**
 * File auth.php
 * Session keamanan + Role check ketat
 * 
 * Cara pakai:
 *   cek_login();                     → hanya butuh login
 *   cek_login('Admin');              → hanya Admin
 *   cek_login('Owner');              → hanya Owner
 *   cek_login(['Admin', 'Owner']);   → Admin atau Owner
 */

// ==================== SESSION START DENGAN KEAMANAN ====================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Regenerate session ID untuk mencegah session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// ==================== TIMEOUT SESSION (30 MENIT) ====================
$timeout = 1800; // 30 menit
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: index.php?expired=1");
    exit;
}
$_SESSION['last_activity'] = time();

// ==================== FUNGSI CEK LOGIN + ROLE ====================
function cek_login($roles = null) {
    // 1. Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }

    // 2. Cek role jika parameter diisi
    if ($roles) {
        $rolesArr = is_array($roles) ? $roles : [$roles];

        if (!in_array($_SESSION['role'], $rolesArr)) {
            // Redirect ke dashboard sesuai role-nya
            $redirect = ($_SESSION['role'] == 'Owner') ? 'dashboard_owner.php' : 'dashboard_admin.php';

            // Set flash message untuk ditampilkan di dashboard
            $_SESSION['akses_ditolak'] = true;

            header("Location: $redirect");
            exit;
        }
    }
}

// ==================== HELPER FUNCTION ====================
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function is_owner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Owner';
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_nama() {
    return $_SESSION['nama'] ?? 'Guest';
}

function get_role() {
    return $_SESSION['role'] ?? null;
}
?>