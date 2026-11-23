<?php
session_start();
include __DIR__ . "/../../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";
// if (empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }

if (isset($_POST['simpan'])) {
    $nama_obat          = $_POST['nama_obat'];
    $kategori           = $_POST['kategori'];
    $satuan             = $_POST['satuan'];
    $harga              = $_POST['harga'];
    $nomor_batch        = $_POST['nomor_batch'];
    $jumlah_stok        = $_POST['jumlah_stok'];
    $tanggal_masuk      = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d');
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];

    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($conn);

    try {
        $result_cek = mysqli_query($conn, "SELECT id_obat FROM obat WHERE nama_obat = '$nama_obat'");

        if (mysqli_num_rows($result_cek) > 0) {
            $row_obat = mysqli_fetch_assoc($result_cek);
            $id_obat  = $row_obat['id_obat'];

            mysqli_query($conn, "UPDATE obat SET 
                                    kategori = '$kategori', 
                                    satuan = '$satuan', 
                                    harga = '$harga' 
                                 WHERE id_obat = '$id_obat'");
        } else {
            mysqli_query($conn, "INSERT INTO obat (nama_obat, kategori, satuan, harga) 
                                 VALUES ('$nama_obat', '$kategori', '$satuan', '$harga')");
            $id_obat = mysqli_insert_id($conn);
        }

        mysqli_query($conn, "INSERT INTO stok_obat (id_obat, nomor_batch, jumlah_stok, tanggal_masuk, tanggal_kadaluarsa) 
                             VALUES ('$id_obat', '$nomor_batch', '$jumlah_stok', '$tanggal_masuk', '$tanggal_kadaluarsa')");

        mysqli_commit($conn);

        echo "<script>
                alert('Obat berhasil ditambahkan!');
                window.location.href = '../inventory.php;
              </script>";
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log($e->getMessage());
        echo "<script>
                alert('Gagal Menambahkan Obat');
                window.location.href = '../inventory.php';
              </script>";
        exit();
    }
}


header("location: ../inventory.php");
exit();
?>