<?php
session_start();

include __DIR__ . "/../../config/connect.php";

if (isset($_POST['registrasi'])) {
    $nama   = $_POST['name'];
    $email  = $_POST['email'];
    $pass   = $_POST['pass'];
    $notelp = $_POST['tlp'];
    $tgl    = $_POST['date'];
    $gender = $_POST['gender'];

    // Enkripsi password sebelum disimpan ke database (Modul 7)
    $password_hash = password_hash($pass, PASSWORD_DEFAULT);

    // 1. Cek apakah email sudah terdaftar di tabel 'pelanggan' menggunakan Prepared Statement + get_result
    $stmt_check = mysqli_prepare($conn, "SELECT id_pelanggan FROM pelanggan WHERE email = ?");
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        mysqli_stmt_close($stmt_check);
        $_SESSION['register-error'] = "Email Telah Terdaftar";
        header("Location: ../registrasi.php");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // 2. Simpan data ke tabel 'pelanggan' 
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO pelanggan (nama_lengkap, email, password, no_telp, tanggal_lahir, gender) 
                    VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "ssssss", $nama, $email, $password_hash, $notelp, $tgl, $gender);

    if (mysqli_stmt_execute($stmt_insert)) {
        // Mengambil id_pelanggan yang baru saja di-insert
        $getuid = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_insert);

        // Set session auto-login
        $_SESSION['id']     = $getuid;
        $_SESSION['nama']   = $nama;
        $_SESSION['email']  = $email;
        $_SESSION['notelp'] = $notelp;
        $_SESSION['date']   = $tgl;
        $_SESSION['gender'] = $gender;
        $_SESSION['role']   = "pelanggan";
        $_SESSION['status'] = "Login";

        header("Location: ../../dashboard/user/index.php");
        exit();
    } else {
        mysqli_stmt_close($stmt_insert);
        $_SESSION['register-error'] = "Registrasi gagal: " . mysqli_error($conn);
        header("Location: ../registrasi.php");
        exit();
    }
} else {
    header("Location: ../registrasi.php");
    exit();
}
?>