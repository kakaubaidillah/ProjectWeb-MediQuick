<?php
    session_start();

    include "backend/connection.php";

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $pass     = $_POST['pass'];
    $notelp   = $_POST['tlp'];
    $tgl      = $_POST['date'];
    $gender   = $_POST['gender'];

    $sytx = "INSERT INTO pasien(nama, email, no_telp, password, tanggal_lahir, jenis_kelamin) 
            VALUES('$nama', '$email', '$notelp', '$pass', '$tgl', '$gender')";
    $query = mysqli_query($conn, $sytx);

    if($query) {
        header("location: .../dashboard/user.php");
        exit();
    }
?>