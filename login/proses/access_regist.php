<?php
    session_start();

    include __DIR__ . "/connection.php";

    if(isset($_POST['registrasi'])) {
        $nama     = $_POST['name'];
        $email    = $_POST['email'];
        $pass     = $_POST['pass'];
        $notelp   = $_POST['tlp'];
        $tgl      = $_POST['date'];
        $gender   = $_POST['gender'];
    }
    

    // check apakah akun telah ada di dalam table database
    $sqlcheck = "SELECT * FROM pasien WHERE email = '$email'";
    $querycheck = mysqli_query($conn, $sqlcheck);
    if(mysqli_num_rows($querycheck) > 0) {
        $_SESSION['register-error'] = "Email Telah Terdaftar";
        header("location: ../auth/registrasi.php");
        exit();
    }

    // Jika email belum ada di table 'pasien', makan program ini akan berjalan untuk menyimpan akun ke dalam table 'pasien'
    $sql = "INSERT INTO pasien(nama, email, no_telp, password, tanggal_lahir, jenis_kelamin) 
            VALUES('$nama', '$email', '$notelp', '$pass', '$tgl', '$gender')";
    $query = mysqli_query($conn, $sql);

    //Ketika data berhasil dimasukkan ke database (bisa registrasi) maka akan auto terlogin 
    if($query) {
        $getuid = mysqli_insert_id($conn); //mengambil id yang barusaja terinput dari query terakhir

        $_SESSION['id'] = $getuid;
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        $_SESSION['notelp'] = $notelp;
        $_SESSION['date'] = $tgl;
        $_SESSION['gender'] = $gender; 
        $_SESSION['role'] = "pasien";
        $_SESSION['status'] = "Login";
        
        header("location: ../../dashboard/user.php?id=$getuid");
        exit();
    }
?>