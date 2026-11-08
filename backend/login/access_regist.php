<?php
    // session_start();

    include __DIR__ . "/../connection.php";

    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $pass     = $_POST['pass'];
    $notelp   = $_POST['tlp'];
    $tgl      = $_POST['date'];
    $gender   = $_POST['gender'];

    $sqlcheck = "SELECT * FROM pasien WHERE email = $email";
    $querycheck = mysqli_query($conn, $sqlcheck);
    if($querycheck) {
        echo "Alamat Email Telah Digunakan, Silahkan Login";
    }

    $sql = "INSERT INTO pasien(nama, email, no_telp, password, tanggal_lahir, jenis_kelamin) 
            VALUES('$nama', '$email', '$notelp', '$pass', '$tgl', '$gender')";
    $query = mysqli_query($conn, $sql);

    if($query) {
        header("location: .../dashboard/user.php");
        exit();
    }
?>