<?php
session_start();
include __DIR__ . "/../../../config/connect.php";

$id_pelanggan = $_SESSION['id'] ?? "";
if (empty($id_pelanggan) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$id_pesanan = isset($_POST['id_pesanan']) ? (int) $_POST['id_pesanan'] : 0;

$stmt = mysqli_prepare($conn, "
    UPDATE pesanan
    SET status = 'dibatalkan'
    WHERE id_pesanan = ?
      AND id_pelanggan = ?
      AND status IN ('menunggu', 'belum bayar')
");
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_pelanggan);
mysqli_stmt_execute($stmt);

header("Location: ../detail_pesanan.php?id=" . $id_pesanan);
exit();
