<?php
    session_start();

    //ambil Data Form
    $user = $_POST['email'] ?? '';
    $pass = $_POST['pass'] ?? '';

    //koneksi DataBase
    $conn = mysqli_connect("localhost", "root", "", "mediquick");

    if(!$conn){
        die("Koneksi Gagal: ".mysqli_connect_error());
    }

    //Cek Admin
    $sql = "SELECT * FROM admin WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, 's', $user);
    mysqli_stmt_execute($stmt);

    $data = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($data);

    if($row){
        //verifikasi Password
        if($pass == $row['password']){
            session_regenerate_id(true);

            $_SESSION['id'] = $row['id_admin'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['status'] = "LOGIN";

            if($row['role'] == 'admin'){
                header("Location: ../dashboard/admin/dashboard_admin.php");
            }elseif($row['role'] == 'dokter'){
                header("Location: ../dashboard/admin/dashboard_dokter.php");
            }elseif($row['role'] == 'apoteker'){
                header("Location: ../dashboard/admin/dashboard_apoteker.php");
            }

            exit();
        }
    }

    //cek akun pasien
    $sql = "SELECT id_pasien, nama, email, password FROM pasien WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, 's', $user);
    mysqli_stmt_execute($stmt);

    $data = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($data);

    if($row){
        if($pass == $row['password']){
            session_regenerate_id(true);

            $_SESSION['id'] = $row['id_pasien'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = "pasien";
            $_SESSION['status'] = "LOGIN";

            header("Location: ../dashboard/user.php");
            exit();
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: login.php?pesan=failed");
    exit();
?>