<?php
session_start();

include __DIR__. "/../../backend/connection.php";

$id = $_POST['id'];

$data_dokter = mysqli_query($conn, "SELECT * FROM admin WHERE role = 'dokter'");
$dokter = mysqli_fetch_assoc($data_dokter);
$id_dokter = $dokter['id_admin'];

$konsul = mysqli_query($conn, "UPDATE konsultasi SET
                                status = 'Ditolak'
                                WHERE id_konsultasi = '$id'");

if($konsul){
    header("Location: dashboard_dokter.php?id=$id_dokter");
    exit();
}

?>