<?php
session_start();
include __DIR__ . "/../../config/connect.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass  = $_POST['pass'];

    // 1. Cek data pada tabel 'apoteker' 
    $sql_apoteker  = "SELECT id_apoteker, nama_lengkap, email, password, no_telp FROM apoteker WHERE email = ?";
    $stmt_apoteker = mysqli_prepare($conn, $sql_apoteker);
    mysqli_stmt_bind_param($stmt_apoteker, "s", $email);
    mysqli_stmt_execute($stmt_apoteker);

    $result_apoteker = mysqli_stmt_get_result($stmt_apoteker);
    $data_apoteker   = mysqli_fetch_assoc($result_apoteker);
    mysqli_stmt_close($stmt_apoteker);

    if ($data_apoteker) {
        if (password_verify($pass, $data_apoteker['password'])) {
            $_SESSION['id']     = $data_apoteker['id_apoteker'];
            $_SESSION['nama']   = $data_apoteker['nama_lengkap'];
            $_SESSION['email']  = $data_apoteker['email'];
            $_SESSION['notelp'] = $data_apoteker['no_telp'];
            $_SESSION['role']   = "apoteker";
            $_SESSION['status'] = "Login";

            header("Location: ../../dashboard/apoteker/dashboard_apoteker.php");
            exit();
        } else {
            header("Location: ../login.php?pesan=failed");
            exit();
        }
    }

    // 2. Cek data pada tabel 'pelanggan'
    $sql_pelanggan  = "SELECT id_pelanggan, nama_lengkap, email, password, no_telp, tanggal_lahir, gender FROM pelanggan WHERE email = ?";
    $stmt_pelanggan = mysqli_prepare($conn, $sql_pelanggan);
    mysqli_stmt_bind_param($stmt_pelanggan, "s", $email);
    mysqli_stmt_execute($stmt_pelanggan);

    $result_pelanggan = mysqli_stmt_get_result($stmt_pelanggan);
    $data_pelanggan   = mysqli_fetch_assoc($result_pelanggan);
    mysqli_stmt_close($stmt_pelanggan);

    if ($data_pelanggan) {
        if (password_verify($pass, $data_pelanggan['password'])) {
            $_SESSION['id']     = $data_pelanggan['id_pelanggan'];
            $_SESSION['nama']   = $data_pelanggan['nama_lengkap'];
            $_SESSION['email']  = $data_pelanggan['email'];
            $_SESSION['notelp'] = $data_pelanggan['no_telp'];
            $_SESSION['date']   = $data_pelanggan['tanggal_lahir'];
            $_SESSION['gender'] = $data_pelanggan['gender'];
            $_SESSION['role']   = "pelanggan";
            $_SESSION['status'] = "Login";

            header("Location: ../../dashboard/user/index.php");
            exit();
        } else {
            header("Location: ../login.php?pesan=failed");
            exit();
        }
    }

    // Jika email tidak ditemukan di kedua tabel
    header("Location: ../login.php?pesan=failed");
    exit();
} else {
    // Jika diakses langsung tanpa menekan tombol submit
    header("Location: ../login.php");
    exit();
}
?>