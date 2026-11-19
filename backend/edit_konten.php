<?php 
session_start();
include __DIR__. "/connection.php";

$id = $_POST['id'];

$sql = "SELECT * FROM konten_dashboard WHERE id_konten = '$id'";
$data = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($data);

if(isset($_POST['update'])){
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    $namabaru = $row['gambar'];
    if(!empty($_FILES['images']['name'])){
        $gambar = $_FILES['images']['name'];
        $namabaru = time(). "_". basename($gambar);
        $tmp = $_FILES['images']['tmp_name'];

        $location = __DIR__."/../asset/img/". $namabaru;
        if(!move_uploaded_file($tmp, $location)){
            echo "Gagal Menyimpan Gambar";
            exit();
        }
    }

    $sql_update = "UPDATE konten_dashboard SET
                    judul = '$judul',
                    deskripsi = '$deskripsi',
                    gambar = '$namabaru'
                    WHERE id_konten = '$id'";
    $query = mysqli_query($conn, $sql_update);
    if($query){
        header("Location: ../dashboard/admin/admin_form.php");
        exit;
    }else{
        echo "Gagal Menyimpan Data:". mysqli_error($conn);
    }
    
}else{
    echo"Gagal Update";
}
?>