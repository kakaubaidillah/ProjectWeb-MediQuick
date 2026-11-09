<?php
    session_start();

    include __DIR__ . "/../connection.php";

    $nama     = $_POST['name'];
    $email    = $_POST['email'];
    $pass     = $_POST['pass'];
    $notelp   = $_POST['tlp'];
    $tgl      = $_POST['date'];
    $gender   = $_POST['gender'];

    // check apakah akun telah ada di dalam table database
    $sqlcheck = "SELECT * FROM pasien WHERE email = '$email'";
    $querycheck = mysqli_query($conn, $sqlcheck);
    if(mysqli_num_rows($querycheck) > 0) {
        $_SESSION['register-error'] = "Email is Already Used";
        header("location: registrasi.php");
        exit();
    }

    // Jika email belum ada di table 'pasien', makan program ini akan berjalan untuk menyimpan akun ke dalam table 'pasien'
    $sql = "INSERT INTO pasien(nama, email, no_telp, password, tanggal_lahir, jenis_kelamin) 
            VALUES('$nama', '$email', '$notelp', '$pass', '$tgl', '$gender')";
    $query = mysqli_query($conn, $sql);

    if($query) {
        header("location: ../../dashboard/user.php");
        exit();
    }
?>