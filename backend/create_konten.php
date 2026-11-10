<?php 
session_start();

include "/connection.php";
/** @var mysqli $conn */

$judul = $_POST['judul'];
$deskripsi = $_POST['deskripsi'];
//ambil gambar
$namaGambar = $_FILES['gambar']['name'];
$namaBaru = time(). "_". basename($namaGambar);
$gambar = "/../asset/img/" . $namaBaru; 

$sql_cek_status = "SELECT COUNT(*) AS jumlah FROM konten_dashboard WHERE status = 1";

$cek = mysqli_query($conn, $sql_cek_status);
$hasil = mysqli_fetch_assoc($cek);

if($hasil['jumlah'] >= 3){
    echo "<script>alert('Maksimal hanya 3 konten aktif');</script>";
    header("Location: /../dashboard/admin/admin_form.php");
}

$sql_insert = "INSERT INTO konten_dashboard
                (judul, deskripsi, gambar)
                VALUES ($judul, $deskripsi, $gambar)";

mysqli_query($conn, $sql_insert);

header("Location: /../dashboard/admin/admin_form.php");
exit();


?>