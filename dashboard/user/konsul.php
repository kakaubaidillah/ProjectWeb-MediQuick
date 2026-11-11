<?php
session_start();

include __DIR__. "/../../backend/connection.php";

$id = $_POST['id'];
$gejala = $_POST['gejala'];
$alergi = $_POST['alergi'];
$penyakit = $_POST['penyakit'];
$mulai_gejala = $_POST['mulai_gejala'];

$sql_insert = "INSERT INTO konsultasi
                (id_pasien, gejala, riwayat_alergi, riwayat_penyakit, sejak_kapan)
                VALUES ('$id', '$gejala', '$alergi', '$penyakit', '$mulai_gejala')";

$data = mysqli_query($conn, $sql_insert);

if(!$data){
    echo "Gagal Kirim konsultasi!: ". mysqli_error($conn);
}else{
    header("Location: user.php?id=$id");
    exit();
}

?>