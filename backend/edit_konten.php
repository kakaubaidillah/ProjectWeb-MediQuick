<?php 
session_start();
include "/connection.php";
/** @var mysqli $conn */

$id = $_POST['id'];

$sql = "SELECT * FROM konten_dashboard WHERE id_konten = '$id'";
$data = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($data);

if(isset($_POST['update'])){
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    
}
?>