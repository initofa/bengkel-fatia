<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login();
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?")->execute([$id]);
header("Location: pelanggan.php?msg=Data berhasil dihapus");
exit;
?>