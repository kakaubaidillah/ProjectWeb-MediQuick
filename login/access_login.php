<?php
    session_start();
    include __DIR__ . "/../backend/connection.php";

    $user = $_POST['email'];
    $pass = $_POST['pass'];

    $sql = "SELECT * FROM admin WHERE email = '$user'";
    $query = mysqli_query($conn, $sql);

    
    $data = mysqli_fetch_assoc($query);
    //cek data 
    if($data) {
        if($pass == $data['password']) {
            $_SESSION['id'] = $data['id_admin'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['notelp'] = $data['no_telp'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['status'] = "Login";

            $idlogin = $_SESSION['id'];

            if($data['role'] == "admin") {
                header("location: ../dashboard/admin/dashboard_admin.php?id=$idlogin");
            } else if($data['role'] == "dokter") {
                header("location: ../dashboard/dokter/dashboard_dokter.php?id=$idlogin");
            } else if($data['role'] == "apoteker") {
                header("location: ../dashboard/apoteker/dashboard_apoteker.php?id=$idlogin");
            }
            exit();
        } else {
            echo "PASSWORD SALAH";
        }
    }
    
    $sql = "SELECT * FROM pasien WHERE email = '$user'";
    $query = mysqli_query($conn, $sql);

    $datauser = mysqli_fetch_assoc($query);

    if($datauser) {
        if($pass == $datauser['password']) {
            $_SESSION['id'] = $datauser['id_pasien'];
            $_SESSION['nama'] = $datauser['nama'];
            $_SESSION['email'] = $datauser['email'];
            $_SESSION['notelp'] = $datauser['no_telp'];
            $_SESSION['date'] = $datauser['tanggal_lahir'];
            $_SESSION['gender'] = $datauser['jenis_kelamin'];
            $_SESSION['role'] = "pasien";
            $_SESSION['status'] = "LOGIN";

            $uid = $_SESSION['id'];

            header("location: ../dashboard/user/user.php?id=$uid");
            exit();
        } else {
            echo "PASSWORD SALAH";
            // header("location: login.php?pesan=failed");
        }
    }
    echo "123";
    // header("location: login.php?pesan=failed")
?>