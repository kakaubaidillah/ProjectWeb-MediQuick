<?php
    session_start();
    include __DIR__ . "/../connection.php";

    $user = $_POST['email'];
    $pass = $_POST['pass'];

    $sql = "SELECT * FROM admin WHERE email = '$user'";
    $query = mysqli_query($conn, $sql);

    
    $dataadmin = mysqli_fetch_assoc($query);
    
    if($dataadmin) {
        if($pass == $dataadmin['password']) {
            $_SESSION['id'] = $dataadmin['id_admin'];
            $_SESSION['nama'] = $dataadmin['nama'];
            $_SESSION['email'] = $dataadmin['email'];
            $_SESSION['notelp'] = $dataadmin['no_telp'];
            $_SESSION['role'] = $dataadmin['role'];
            $_SESSION['status'] = "Login";

            if($dataadmin['role'] == "admin") {
                header("location: .../dashboard/admin/dashboard_admin.php");
            } else if($dataadmin['role'] == "dokter") {
                header("location: .../dashboard/admin/dashboard_dokter.php");
            } else if($dataadmin['role'] == "apoteker") {
                header("location: .../dashboard/admin/dashboard_apoteker.php");
            }
            exit();
        } else {
            echo "PASSWORD SALAH";
        }
    }
    
    $sql = "SELECT * FROM pasien WHERE email = '$user'";
    $query = mysqli_query($conn, $sql);

    $datapasien = mysqli_fetch_assoc($query);

    if($datapasien) {
        if($pass == $datapasien['password']) {
            $_SESSION['id'] = $datapasien['id_pasien'];
            $_SESSION['nama'] = $datapasien['nama'];
            $_SESSION['email'] = $datapasien['email'];
            $_SESSION['notelp'] = $datapasien['no_telp'];
            $_SESSION['date'] = $datapasien['tanggal_lahir'];
            $_SESSION['gender'] = $datapasien['jenis_kelamin'];
            $_SESSION['status'] = "LOGIN";

            header("location: ../dashboard/user.php?pesan=loginsuccess");
            exit();
        } else {
            echo "PASSWORD SALAH";
            header("location: login.php?pesan=failed");
        }
    }

    header("location: ..\login.php?pesan=failed")
?>