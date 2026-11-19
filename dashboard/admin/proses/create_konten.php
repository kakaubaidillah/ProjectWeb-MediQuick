<?php 
session_start();

include __DIR__. "/../../../config/connection.php";


$judul = $_POST['judul'];
$deskripsi = $_POST['deskripsi'];
//ambil gambar
$namaGambar = $_FILES['images']['name'];
$namaBaru = time(). "_". basename($namaGambar);
$tmp = $_FILES['images']['tmp_name'];

$sql_cek_status = "SELECT COUNT(*) AS jumlah FROM konten_dashboard WHERE status = 1";

$cek = mysqli_query($conn, $sql_cek_status);
$hasil = mysqli_fetch_assoc($cek);

if($hasil['jumlah'] >= 3){
    echo "<script>alert('Maksimal hanya 3 konten aktif');</script>";
    header("Location: ../admin_form.php");
}

$location = __DIR__."/../../../asset/uploads/". $namaBaru;

if(move_uploaded_file($tmp, $location)){
    $sql_insert = "INSERT INTO konten_dashboard
                (judul, deskripsi, gambar)
                VALUES ('$judul', '$deskripsi', '$namaBaru')";
    $query = mysqli_query($conn, $sql_insert);

    if($query){
        header("Location: ../admin_form.php");
        exit();
    }else{
        echo "Gagal Menyimpan Data:". mysqli_error($conn);
    }
}else{
    echo "Gagal Menyimpan Gambar";
}



?>