<?php
session_start();
include __DIR__ . "/../../../config/connection.php";

// Validasi session login & role apoteker
$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    echo "<script>alert('Mohon Login terlebih dahulu!'); 
                window.location.href='../../../login/login.php';
              </script>";
    exit();
}

// ==========================================
// 1. PROSES UPDATE DATA OBAT & BATCH STOK
// ==========================================
if (isset($_POST['update'])) {
    $id_obat            = $_POST['id_obat'];
    $id_stok            = $_POST['id_stok'];
    $gambar_lama        = $_POST['gambar_lama'];

    $nama_obat          = $_POST['nama_obat'];
    $butuh_resep        = $_POST['butuh_resep'];
    $kategori           = $_POST['kategori'];
    $satuan             = $_POST['satuan'];
    $harga              = $_POST['harga'];

    $batch              = $_POST['batch'];
    $jumlah_stok        = (int)$_POST['jumlah_stok'];
    $tanggal_masuk      = $_POST['tanggal_masuk'];
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];

    // Cek apakah apoteker mengunggah gambar baru
    $nama_gambar = $gambar_lama;
    if (!empty($_FILES['gambar_obat']['name'])) {
        $folder_tujuan = __DIR__ . "/../../../asset/uploads/obat/";
        if (!is_dir($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }

        $nama_file_baru = time() . "_" . basename($_FILES['gambar_obat']['name']);
        if (move_uploaded_file($_FILES['gambar_obat']['tmp_name'], $folder_tujuan . $nama_file_baru)) {
            // Hapus file gambar lama jika ada di folder
            if (!empty($gambar_lama) && file_exists($folder_tujuan . $gambar_lama)) {
                unlink($folder_tujuan . $gambar_lama);
            }
            $nama_gambar = $nama_file_baru;
        }
    }
    $nama_gambar_esc = mysqli_real_escape_string($conn, $nama_gambar);

    
    // Update tabel 'obat'
    $query_obat = mysqli_query($conn, "UPDATE obat SET 
                                       nama_obat   = '$nama_obat',
                                       kategori    = '$kategori',
                                       satuan      = '$satuan',
                                       harga       = '$harga',
                                       butuh_resep = '$butuh_resep',
                                       gambar_obat = '$nama_gambar_esc'
                                       WHERE id_obat = '$id_obat'");
    // Update tabel 'stok_obat'
    $query_stok = mysqli_query($conn, "UPDATE stok_obat SET 
                                       batch              = '$batch',
                                       jumlah_stok        = '$jumlah_stok',
                                       tanggal_masuk      = '$tanggal_masuk',
                                       tanggal_kadaluarsa = '$tanggal_kadaluarsa'
                                       WHERE id_stok = '$id_stok'");

    if ($query_obat && $query_stok) {
        header("Location: ../detail_obat.php?id=" . $id_obat . "&batch=" . urlencode($batch) . "&status=sukses");
        exit();
    } else {
        header("Location: ../detail_obat.php?id=" . $id_obat . "&batch=" . urlencode($batch) . "&status=gagal");
        exit();
    }
}

// ==========================================
// 2. PROSES HAPUS DATA BATCH / OBAT
// ==========================================
if (isset($_POST['hapus'])) {
    $id_obat     = $_POST['id_obat'];
    $id_stok     = $_POST['id_stok'];
    $batch       = $_POST['batch'];
    $gambar_lama = $_POST['gambar_lama'];

    // Hapus data batch spesifik di tabel 'stok_obat'
    $query_hapus_stok = mysqli_query($conn, "DELETE FROM stok_obat WHERE id_stok = '$id_stok'");

    if ($query_hapus_stok) {
        // Cek apakah obat ini masih punya batch lain di tabel 'stok_obat'
        $cek_sisa_batch = mysqli_query($conn, "SELECT id_stok FROM stok_obat WHERE id_obat = '$id_obat'");

        // Jika sudah tidak ada batch tersisa, hapus juga data induk di tabel 'obat' beserta file gambarnya
        if (mysqli_num_rows($cek_sisa_batch) == 0) {
            mysqli_query($conn, "DELETE FROM obat WHERE id_obat = '$id_obat'");

            $folder_tujuan = __DIR__ . "/../../../asset/img/obat/";
            if (!empty($gambar_lama) && file_exists($folder_tujuan . $gambar_lama)) {
                unlink($folder_tujuan . $gambar_lama);
            }
        }

        echo "<script>alert('Data obat BERHASIL dihapus!'); 
                window.location.href='../inventory.php';
              </script>";
        exit();
    } else {
        header("Location: ../detail_obat.php?id=" . urlencode($id_obat) . "&batch=" . urlencode($batch) . "&status=gagal");
        exit();
    }
}

header("Location: ../inventory.php");
exit();
?>