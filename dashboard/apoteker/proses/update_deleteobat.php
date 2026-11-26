<?php
session_start();
include __DIR__ . "/../../../config/connect.php";

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
    $id_obat            = $_POST['id_obat'] ?? '';
    $id_stok            = $_POST['id_stok'] ?? '';
    $gambar_lama        = $_POST['gambar_lama'] ?? '';

    $nama_obat          = trim($_POST['nama_obat'] ?? '');
    $butuh_resep        = $_POST['butuh_resep'] ?? 0;
    $kategori           = $_POST['kategori'] ?? '';
    $satuan             = $_POST['satuan'] ?? '';
    $harga              = $_POST['harga'] ?? 0;

    $batch              = trim($_POST['batch'] ?? '');
    $jumlah_stok        = (int)($_POST['jumlah_stok'] ?? 0);
    $tanggal_masuk      = $_POST['tanggal_masuk'] ?? '';
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'] ?? '';

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

    // A. Update tabel 'obat' menggunakan Prepared Statement
    $stmt_up_obat = mysqli_prepare($conn, "UPDATE obat SET 
                                           nama_obat   = ?,
                                           kategori    = ?,
                                           satuan      = ?,
                                           harga       = ?,
                                           butuh_resep = ?,
                                           gambar_obat = ?
                                           WHERE id_obat = ?");
    mysqli_stmt_bind_param($stmt_up_obat, "sssdisi", $nama_obat, $kategori, $satuan, $harga, $butuh_resep, $nama_gambar, $id_obat);
    $eksekusi_obat = mysqli_stmt_execute($stmt_up_obat);
    mysqli_stmt_close($stmt_up_obat);

    // B. Update tabel 'stok_obat' menggunakan Prepared Statement
    $stmt_up_stok = mysqli_prepare($conn, "UPDATE stok_obat SET 
                                           batch              = ?,
                                           jumlah_stok        = ?,
                                           tanggal_masuk      = ?,
                                           tanggal_kadaluarsa = ?
                                           WHERE id_stok = ?");
    mysqli_stmt_bind_param($stmt_up_stok, "sissi", $batch, $jumlah_stok, $tanggal_masuk, $tanggal_kadaluarsa, $id_stok);
    $eksekusi_stok = mysqli_stmt_execute($stmt_up_stok);
    mysqli_stmt_close($stmt_up_stok);

    if ($eksekusi_obat && $eksekusi_stok) {
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
    $id_obat     = $_POST['id_obat'] ?? '';
    $id_stok     = $_POST['id_stok'] ?? '';
    $batch       = $_POST['batch'] ?? '';
    $gambar_lama = $_POST['gambar_lama'] ?? '';

    // A. Hapus data batch spesifik di tabel 'stok_obat' menggunakan Prepared Statement
    $stmt_hapus_stok = mysqli_prepare($conn, "DELETE FROM stok_obat WHERE id_stok = ?");
    mysqli_stmt_bind_param($stmt_hapus_stok, "i", $id_stok);
    $eksekusi_hapus_stok = mysqli_stmt_execute($stmt_hapus_stok);
    mysqli_stmt_close($stmt_hapus_stok);

    if ($eksekusi_hapus_stok) {
        // B. Cek apakah obat ini masih punya batch lain di tabel 'stok_obat' menggunakan Prepared Statement
        $stmt_cek_sisa = mysqli_prepare($conn, "SELECT id_stok FROM stok_obat WHERE id_obat = ?");
        mysqli_stmt_bind_param($stmt_cek_sisa, "i", $id_obat);
        mysqli_stmt_execute($stmt_cek_sisa);
        $res_sisa_batch = mysqli_stmt_get_result($stmt_cek_sisa);
        $sisa_count = mysqli_num_rows($res_sisa_batch);
        mysqli_stmt_close($stmt_cek_sisa);

        // Jika sudah tidak ada batch tersisa, hapus juga data induk di tabel 'obat' beserta file gambarnya
        if ($sisa_count == 0) {
            $stmt_hapus_obat = mysqli_prepare($conn, "DELETE FROM obat WHERE id_obat = ?");
            mysqli_stmt_bind_param($stmt_hapus_obat, "i", $id_obat);
            mysqli_stmt_execute($stmt_hapus_obat);
            mysqli_stmt_close($stmt_hapus_obat);

            $folder_tujuan = __DIR__ . "/../../../asset/uploads/obat/";
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