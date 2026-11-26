<?php
session_start();
include __DIR__ . "/../../../config/connect.php";

$id_pelanggan = $_SESSION['id'] ?? "";
if (empty($id_pelanggan)) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../../../login/login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../beli_obat.php");
    exit();
}

$alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
if (empty($alamat_pengiriman)) {
    echo "<script>alert('Alamat pengiriman wajib diisi!'); window.history.back();</script>";
    exit();
}

if (!isset($_FILES['resep']) || $_FILES['resep']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('File resep gagal diunggah. Pastikan file sudah dipilih!'); window.history.back();</script>";
    exit();
}

$file = $_FILES['resep'];
$max_size = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $max_size) {
    echo "<script>alert('Ukuran file maksimal 5 MB!'); window.history.back();</script>";
    exit();
}

// Validasi ekstensi dan MIME type asli file
$allowed_ext  = ['jpg', 'jpeg', 'png'];
$allowed_mime = ['image/jpeg', 'image/png'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
    echo "<script>alert('Format file tidak valid! Hanya diperbolehkan JPG, JPEG, atau PNG.'); window.history.back();</script>";
    exit();
}

// Siapkan folder upload
// Lokasi: ProjectWeb-MediQuick/asset/upload/resep/
$upload_dir = __DIR__ . "/../../../asset/uploads/resep/";
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
    echo "<script>alert('Folder upload resep tidak dapat dibuat.'); window.history.back();</script>";
    exit();
}

$nama_file_baru = "resep_" . $id_pelanggan . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
$tujuan_upload  = $upload_dir . $nama_file_baru;

if (!move_uploaded_file($file['tmp_name'], $tujuan_upload)) {
    echo "<script>alert('Terjadi kesalahan saat menyimpan file resep ke server.'); window.history.back();</script>";
    exit();
}

// Simpan pesanan dengan status 'menunggu'
$stmt = mysqli_prepare($conn, "
    INSERT INTO pesanan (id_pelanggan, alamat_pengiriman, tanggal_pesanan, total_harga, resep_obat, status)
    VALUES (?, ?, NOW(), 0, ?, 'menunggu')
");
mysqli_stmt_bind_param($stmt, "iss", $id_pelanggan, $alamat_pengiriman, $nama_file_baru);

if (mysqli_stmt_execute($stmt)) {
    $id_pesanan = mysqli_insert_id($conn);
    header("Location: ../detail_pesanan.php?id=" . $id_pesanan);
    exit();
} else {
    @unlink($tujuan_upload); // Hapus file jika query gagal
    echo "<script>alert('Gagal menyimpan data pesanan resep.'); window.history.back();</script>";
    exit();
}