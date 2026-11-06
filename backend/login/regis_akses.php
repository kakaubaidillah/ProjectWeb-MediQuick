<?php 
   session_start();

   //ambil data form
   $nama = $_POST['name'] ?? '';
   $email = $_POST['email'] ?? '';
   $pass = $_POST['pass'] ?? '';
   $tlp = $_POST['tlp'] ?? '';
   $date = $_POST['date'] ?? '';
   $gender = $_POST['gender'] ?? '';

   //koneksi Database
   $conn = mysqli_connect("localhost", "root", "", "mediquick");

   if(!$conn){
    die("Koneksi Gagal: ".mysqli_connect_error());
   }

   $sql = "INSERT INTO pasien
            (nama, email, no_telp, password, tanggal_lahir, jenis_kelamin)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if(!$stmt){
        die("Query Gagal disiapkan: ".mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $nama,
        $email,
        $tlp,
        $pass,
        $date,
        $gender
    );

    $result = mysqli_stmt_execute($stmt);

   if($result){
        header("Location: login.php?status=berhasil");
   }else{
        header("Location: registrasi.php?status=gagal");
   }

   mysqli_stmt_close($stmt);
   mysqli_close($conn);
?>