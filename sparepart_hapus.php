<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
cek_login(['Admin', 'Owner']);

$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM sparepart WHERE id_sparepart = ?")->execute([$id]);
header("Location: sparepart.php?msg=Data berhasil dihapus");
exit;
?>