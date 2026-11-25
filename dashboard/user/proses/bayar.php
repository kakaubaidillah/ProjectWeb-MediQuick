<?php
session_start();
include __DIR__ . "/../../../config/connect.php";

$id_pelanggan = $_SESSION['id_pelanggan'] ?? "";
if (empty($id_pelanggan) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: user.php");
    exit();
}

$id_pesanan = isset($_POST['id_pesanan']) ? (int) $_POST['id_pesanan'] : 0;

$stmt = mysqli_prepare($conn, "SELECT status, batas_pembayaran FROM pesanan WHERE id_pesanan = ? AND id_pelanggan = ?");
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_pelanggan);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$pesanan) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location.href='user.php';</script>";
    exit();
}

if ($pesanan['status'] !== 'belum bayar') {
    echo "<script>alert('Status pesanan tidak valid untuk pembayaran.'); window.location.href='detail_pesanan.php?id=$id_pesanan';</script>";
    exit();
}

if (!empty($pesanan['batas_pembayaran']) && time() >= strtotime($pesanan['batas_pembayaran'])) {
    $stmt_exp = mysqli_prepare($conn, "UPDATE pesanan SET status = 'dibatalkan' WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_exp, "i", $id_pesanan);
    mysqli_stmt_execute($stmt_exp);

    echo "<script>alert('Waktu pembayaran telah habis. Pesanan otomatis dibatalkan.'); window.location.href='detail_pesanan.php?id=$id_pesanan';</script>";
    exit();
}

// Ubah status menjadi 'dibayar'
$stmt_bayar = mysqli_prepare($conn, "UPDATE pesanan SET status = 'dibayar' WHERE id_pesanan = ? AND id_pelanggan = ? AND status = 'belum bayar'");
mysqli_stmt_bind_param($stmt_bayar, "ii", $id_pesanan, $id_pelanggan);
mysqli_stmt_execute($stmt_bayar);

header("Location: detail_pesanan.php?id=" . $id_pesanan);
exit();