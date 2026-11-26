<?php
session_start();
include __DIR__ . "/../../../config/connection.php";

// Validasi session login & role apoteker
$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    header("Location: ../../../login/login.php");
    exit();
}

$id_pesanan = mysqli_real_escape_string($conn, $_POST['id_pesanan'] ?? '');

if (empty($id_pesanan)) {
    header("Location: ../dashboard_apoteker.php");
    exit();
}

// ==========================================================
// 1. PROSES TOLAK PESANAN / RESEP
// ==========================================================
if (isset($_POST['tolak_pesanan'])) {
    $catatan_apoteker = mysqli_real_escape_string($conn, trim($_POST['catatan_apoteker'] ?? ''));

    if (empty($catatan_apoteker)) {
        $_SESSION['proses_error'] = "Harap isi catatan alasan penolakan sebelum menolak pesanan!";
        header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
        exit();
    }

    $sql_tolak = "UPDATE pesanan 
                  SET status = 'ditolak', 
                      id_apoteker = '$idapoteker', 
                      catatan_apoteker = '$catatan_apoteker' 
                  WHERE id_pesanan = '$id_pesanan'";

    if (mysqli_query($conn, $sql_tolak)) {
        echo "<script>
                alert('Pesanan berhasil ditolak dan catatan telah dikirim ke pelanggan.');
                window.location.href = '../dashboard_apoteker.php';
              </script>";
        exit();
    } else {
        $_SESSION['proses_error'] = "Gagal menolak pesanan: " . mysqli_error($conn);
        header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
        exit();
    }
}

// ==========================================================
// 2. PROSES VERIFIKASI RESEP, SIMPAN DETAIL PESANAN & POTONG STOK
// ==========================================================
if (isset($_POST['verifikasi_pesanan'])) {
    $catatan_apoteker = mysqli_real_escape_string($conn, trim($_POST['catatan_apoteker'] ?? ''));
    $arr_id_obat      = $_POST['id_obat'] ?? [];
    $arr_jumlah       = $_POST['jumlah'] ?? [];

    if (empty($arr_id_obat)) {
        $_SESSION['proses_error'] = "Minimal harus memilih 1 obat untuk memverifikasi pesanan!";
        header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
        exit();
    }

    $total_harga = 0;
    $item_valid  = [];

    // Validasi ketersediaan stok seluruh item terlebih dahulu
    for ($i = 0; $i < count($arr_id_obat); $i++) {
        $id_obat = mysqli_real_escape_string($conn, $arr_id_obat[$i]);
        $jumlah  = (int) $arr_jumlah[$i];

        if ($jumlah <= 0) {
            $_SESSION['proses_error'] = "Jumlah obat harus lebih dari 0.";
            header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
            exit();
        }

        // Ambil harga satuan & nama obat
        $q_obat = mysqli_query($conn, "SELECT nama_obat, harga FROM obat WHERE id_obat = '$id_obat'");
        $d_obat = mysqli_fetch_assoc($q_obat);

        if (!$d_obat) {
            $_SESSION['proses_error'] = "Data obat tidak ditemukan.";
            header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
            exit();
        }

        // Cek total stok yang tersedia dan belum kedaluwarsa
        $q_stok = mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_stok), 0) AS total_tersedia 
                                       FROM stok_obat 
                                       WHERE id_obat = '$id_obat' 
                                         AND jumlah_stok > 0 
                                         AND tanggal_kadaluarsa >= CURDATE()");
        $d_stok        = mysqli_fetch_assoc($q_stok);
        $stok_tersedia = (int) $d_stok['total_tersedia'];

        if ($stok_tersedia < $jumlah) {
            $_SESSION['proses_error'] = "Stok obat '" . $d_obat['nama_obat'] . "' tidak mencukupi (Tersedia: $stok_tersedia, Diminta: $jumlah).";
            header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
            exit();
        }

        $harga_satuan = (float) $d_obat['harga'];
        $subtotal     = $harga_satuan * $jumlah;
        $total_harga += $subtotal;

        $item_valid[] = [
            'id_obat'      => $id_obat,
            'jumlah'       => $jumlah,
            'harga_satuan' => $harga_satuan,
            'subtotal'     => $subtotal
        ];
    }

    // Simpan ke detail_pesanan dan potong stok per batch (FEFO)
    foreach ($item_valid as $item) {
        $id_obt = $item['id_obat'];
        $jml    = $item['jumlah'];
        $hrg    = $item['harga_satuan'];
        $sub    = $item['subtotal'];

        // A. Insert ke tabel `detail_pesanan`
        mysqli_query($conn, "INSERT INTO detail_pesanan (id_pesanan, id_obat, jumlah, harga_satuan, subtotal) 
                             VALUES ('$id_pesanan', '$id_obt', '$jml', '$hrg', '$sub')");

        // B. Potong stok berdasarkan tanggal kedaluwarsa terdekat (FEFO)
        $sisa_potong = $jml;
        $q_batches   = mysqli_query($conn, "SELECT id_stok, jumlah_stok 
                                            FROM stok_obat 
                                            WHERE id_obat = '$id_obt' 
                                              AND jumlah_stok > 0 
                                              AND tanggal_kadaluarsa >= CURDATE() 
                                            ORDER BY tanggal_kadaluarsa ASC");

        while ($batch = mysqli_fetch_assoc($q_batches)) {
            if ($sisa_potong <= 0) break;

            $id_stok_batch = $batch['id_stok'];
            $stok_batch    = (int) $batch['jumlah_stok'];

            if ($stok_batch >= $sisa_potong) {
                $stok_baru = $stok_batch - $sisa_potong;
                mysqli_query($conn, "UPDATE stok_obat SET jumlah_stok = '$stok_baru' WHERE id_stok = '$id_stok_batch'");
                $sisa_potong = 0;
            } else {
                mysqli_query($conn, "UPDATE stok_obat SET jumlah_stok = 0 WHERE id_stok = '$id_stok_batch'");
                $sisa_potong -= $stok_batch;
            }
        }
    }

    // C. Update status tabel `pesanan` menjadi 'belum bayar'
    $sql_verif = "UPDATE pesanan 
                  SET status = 'belum bayar', 
                      id_apoteker = '$idapoteker', 
                      total_harga = '$total_harga', 
                      catatan_apoteker = '$catatan_apoteker' 
                  WHERE id_pesanan = '$id_pesanan'";

    if (mysqli_query($conn, $sql_verif)) {
        echo "<script>
                alert('Pesanan BERHASIL diverifikasi!');
                window.location.href = '../dashboard_apoteker.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Pesanan GAGAL diverifikasi!'); 
                window.location.href='../proses_pesanan.php';
              </script>";
        exit();
    }
}

// Jika diakses langsung tanpa menekan tombol submit
header("Location: ../dashboard_apoteker.php");
exit();
?>