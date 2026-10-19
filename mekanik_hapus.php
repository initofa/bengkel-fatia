<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login();
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM mekanik WHERE id_mekanik = ?")->execute([$id]);
header("Location: mekanik.php?msg=Data berhasil dihapus");
exit;
?>