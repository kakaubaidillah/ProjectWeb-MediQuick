<?php
session_start();
include __DIR__ . "/../../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";
// if (empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }


//UPDATE
if (isset($_POST['update'])) {
    $id_obat            = $_POST['id_obat'];
    $id_stok            = $_POST['id_stok'];
    $nama_obat          = $_POST['nama_obat'];
    $kategori           = $_POST['kategori'];
    $satuan             = $_POST['satuan'];
    $harga              = $_POST['harga'];
    $nomor_batch        = $_POST['nomor_batch'];
    $jumlah_stok        = $_POST['jumlah_stok'];
    $tanggal_masuk      = !empty($_POST['tanggal_masuk']) ? "'" . $_POST['tanggal_masuk'] . "'" : "NULL";
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($conn);

    try {
        mysqli_query($conn, "UPDATE obat SET 
                                nama_obat = '$nama_obat', 
                                kategori = '$kategori', 
                                satuan = '$satuan', 
                                harga = '$harga' 
                             WHERE id_obat = '$id_obat'");

        mysqli_query($conn, "UPDATE stok_obat SET 
                                nomor_batch = '$nomor_batch', 
                                jumlah_stok = '$jumlah_stok', 
                                tanggal_masuk = $tanggal_masuk, 
                                tanggal_kadaluarsa = '$tanggal_kadaluarsa' 
                             WHERE id_stok = '$id_stok'");

        mysqli_commit($conn);

        echo "<script>
                alert('Perubahan data obat berhasil disimpan!');
                window.location.href = '../detail_obat.php?id=" . $id_obat . "&batch=" . urlencode($nomor_batch) . "';
              </script>";
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log($e->getMessage());
        echo "<script>
                alert('Gagal memperbarui data');
                window.location.href = '../inventory.php';
              </script>";
        exit();
    }
}

//HAPUS
if (isset($_POST['hapus'])) {
    $id_obat = $_POST['id_obat'];
    $id_stok = $_POST['id_stok'];

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    mysqli_begin_transaction($conn);

    try {
        $query_hapus_stok = "DELETE FROM stok_obat WHERE id_stok = '$id_stok'";
        mysqli_query($conn, $query_hapus_stok);

        $res_cek   = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stok_obat WHERE id_obat = '$id_obat'");
        $row_cek   = mysqli_fetch_assoc($res_cek);

        if ($row_cek['total'] == 0) {
            try {
                $query_hapus_obat = "DELETE FROM obat WHERE id_obat = '$id_obat'";
                mysqli_query($conn, $query_hapus_obat);
            } catch (Exception $fk_error) {
                //
            }
        }

        mysqli_commit($conn);

        echo "<script>
                alert('Data obat berhasil dihapus!');
                window.location.href = '../inventory.php';
              </script>";
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log($e->getMessage());
        echo "<script>
                alert('Gagal menghapus data');
                window.location.href = '../inventory.php';
              </script>";
        exit();
    }
}


header("Location: ../inventory.php");
exit();
?>