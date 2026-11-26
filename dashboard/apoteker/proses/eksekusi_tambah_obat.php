<?php
session_start();
include __DIR__ . "/../../../config/connect.php";

$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    echo "<script>alert('Mohon Login terlebih dahulu!'); 
            window.location.href='../../../login/login.php';
          </script>";
    exit();
}

if (isset($_POST['simpan'])) {
    $nama_obat          = trim($_POST['nama_obat'] ?? '');
    $kategori           = $_POST['kategori'] ?? '';
    $satuan             = $_POST['satuan'] ?? '';
    $harga              = $_POST['harga'] ?? 0;
    $butuh_resep        = $_POST['butuh_resep'] ?? 0;
    $batch              = trim($_POST['batch'] ?? '');
    $jumlah_stok        = (int)($_POST['jumlah_stok'] ?? 0);
    $tanggal_masuk      = $_POST['tanggal_masuk'] ?? '';
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'] ?? '';

    $nama_gambar = "";
    if (!empty($_FILES['gambar_obat']['name'])) {
        $folder_tujuan = __DIR__ . "/../../../asset/uploads/obat/";
        if (!is_dir($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }
        $nama_gambar = time() . "_" . basename($_FILES['gambar_obat']['name']);
        move_uploaded_file($_FILES['gambar_obat']['tmp_name'], $folder_tujuan . $nama_gambar);
    }

    $stmt_cek = mysqli_prepare($conn, "SELECT id_obat FROM obat WHERE nama_obat = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_cek, "s", $nama_obat);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        $data_obat = mysqli_fetch_assoc($result_cek);
        $id_obat   = $data_obat['id_obat'];
        mysqli_stmt_close($stmt_cek);
    } else {
        mysqli_stmt_close($stmt_cek);

        $stmt_ins_obat = mysqli_prepare($conn, "INSERT INTO obat (nama_obat, kategori, satuan, harga, butuh_resep, gambar_obat) 
                                                VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_ins_obat, "sssdis", $nama_obat, $kategori, $satuan, $harga, $butuh_resep, $nama_gambar);
        $eksekusi_obat = mysqli_stmt_execute($stmt_ins_obat);
        
        if ($eksekusi_obat) {
            $id_obat = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_ins_obat);
        } else {
            mysqli_stmt_close($stmt_ins_obat);
            echo "<script>
                alert('Data obat GAGAL ditambahkan!'); 
                window.location.href='../inventory.php';
              </script>";
            exit();
        }
    }

    $stmt_ins_stok = mysqli_prepare($conn, "INSERT INTO stok_obat (id_obat, batch, jumlah_stok, tanggal_masuk, tanggal_kadaluarsa) 
                                            VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_ins_stok, "isisss", $id_obat, $batch, $jumlah_stok, $tanggal_masuk, $tanggal_kadaluarsa);
    $eksekusi_stok = mysqli_stmt_execute($stmt_ins_stok);
    mysqli_stmt_close($stmt_ins_stok);

    if ($eksekusi_stok) {
        echo "<script>
                alert('Data obat dan stok BERHASIL ditambahkan!'); 
                window.location.href='../inventory.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Data obat dan stok GAGAL ditambahkan!'); 
                window.location.href='../inventory.php';
              </script>";
        exit();
    }
} else {
    header("Location: ../tambah_obat.php");
    exit();
}
?>