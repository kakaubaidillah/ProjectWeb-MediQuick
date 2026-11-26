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

$id_pesanan = $_POST['id_pesanan'] ?? '';

if (empty($id_pesanan)) {
    header("Location: ../dashboard_apoteker.php");
    exit();
}

// ==========================================================
// 1. PROSES TOLAK PESANAN / RESEP (Prepared Statement)
// ==========================================================
if (isset($_POST['tolak_pesanan'])) {
    $catatan_apoteker = trim($_POST['catatan_apoteker'] ?? '');

    if (empty($catatan_apoteker)) {
        $_SESSION['proses_error'] = "Harap isi catatan alasan penolakan sebelum menolak pesanan!";
        header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
        exit();
    }

    $status_tolak = 'ditolak';
    $stmt_tolak = mysqli_prepare($conn, "UPDATE pesanan 
                                         SET status = ?, 
                                             id_apoteker = ?, 
                                             catatan_apoteker = ? 
                                         WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_tolak, "sisi", $status_tolak, $idapoteker, $catatan_apoteker, $id_pesanan);
    $eksekusi_tolak = mysqli_stmt_execute($stmt_tolak);
    mysqli_stmt_close($stmt_tolak);

    if ($eksekusi_tolak) {
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
    $catatan_apoteker = trim($_POST['catatan_apoteker'] ?? '');
    $arr_id_obat      = $_POST['id_obat'] ?? [];
    $arr_jumlah       = $_POST['jumlah'] ?? [];

    if (empty($arr_id_obat)) {
        $_SESSION['proses_error'] = "Minimal harus memilih 1 obat untuk memverifikasi pesanan!";
        header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
        exit();
    }

    $total_harga = 0;
    $item_valid  = [];

    // Validasi ketersediaan stok seluruh item terlebih dahulu menggunakan Prepared Statement
    for ($i = 0; $i < count($arr_id_obat); $i++) {
        $id_obat = $arr_id_obat[$i];
        $jumlah  = (int) $arr_jumlah[$i];

        if ($jumlah <= 0) {
            $_SESSION['proses_error'] = "Jumlah obat harus lebih dari 0.";
            header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
            exit();
        }

        // A. Ambil harga satuan & nama obat
        $stmt_obat = mysqli_prepare($conn, "SELECT nama_obat, harga FROM obat WHERE id_obat = ?");
        mysqli_stmt_bind_param($stmt_obat, "i", $id_obat);
        mysqli_stmt_execute($stmt_obat);
        $res_obat = mysqli_stmt_get_result($stmt_obat);
        $d_obat   = mysqli_fetch_assoc($res_obat);
        mysqli_stmt_close($stmt_obat);

        if (!$d_obat) {
            $_SESSION['proses_error'] = "Data obat tidak ditemukan.";
            header("Location: ../proses_pesanan.php?id=" . urlencode($id_pesanan));
            exit();
        }

        // B. Cek total stok yang tersedia dan belum kedaluwarsa
        $stmt_stok = mysqli_prepare($conn, "SELECT COALESCE(SUM(jumlah_stok), 0) AS total_tersedia 
                                             FROM stok_obat 
                                             WHERE id_obat = ? 
                                               AND jumlah_stok > 0 
                                               AND tanggal_kadaluarsa >= CURDATE()");
        mysqli_stmt_bind_param($stmt_stok, "i", $id_obat);
        mysqli_stmt_execute($stmt_stok);
        $res_stok      = mysqli_stmt_get_result($stmt_stok);
        $d_stok        = mysqli_fetch_assoc($res_stok);
        mysqli_stmt_close($stmt_stok);

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

    // Simpan ke detail_pesanan dan potong stok per batch (FEFO) menggunakan Prepared Statements
    foreach ($item_valid as $item) {
        $id_obt = $item['id_obat'];
        $jml    = $item['jumlah'];
        $hrg    = $item['harga_satuan'];
        $sub    = $item['subtotal'];

        // A. Insert ke tabel `detail_pesanan`
        $stmt_ins_det = mysqli_prepare($conn, "INSERT INTO detail_pesanan (id_pesanan, id_obat, jumlah, harga_satuan, subtotal) 
                                               VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_ins_det, "iiidd", $id_pesanan, $id_obt, $jml, $hrg, $sub);
        mysqli_stmt_execute($stmt_ins_det);
        mysqli_stmt_close($stmt_ins_det);

        // B. Potong stok berdasarkan tanggal kedaluwarsa terdekat (FEFO)
        $stmt_batches = mysqli_prepare($conn, "SELECT id_stok, jumlah_stok 
                                               FROM stok_obat 
                                               WHERE id_obat = ? 
                                                 AND jumlah_stok > 0 
                                                 AND tanggal_kadaluarsa >= CURDATE() 
                                               ORDER BY tanggal_kadaluarsa ASC");
        mysqli_stmt_bind_param($stmt_batches, "i", $id_obt);
        mysqli_stmt_execute($stmt_batches);
        $res_batches = mysqli_stmt_get_result($stmt_batches);

        $sisa_potong = $jml;
        while ($batch = mysqli_fetch_assoc($res_batches)) {
            if ($sisa_potong <= 0) break;

            $id_stok_batch = $batch['id_stok'];
            $stok_batch    = (int) $batch['jumlah_stok'];

            if ($stok_batch >= $sisa_potong) {
                $stok_baru = $stok_batch - $sisa_potong;
                
                $stmt_up_stok = mysqli_prepare($conn, "UPDATE stok_obat SET jumlah_stok = ? WHERE id_stok = ?");
                mysqli_stmt_bind_param($stmt_up_stok, "ii", $stok_baru, $id_stok_batch);
                mysqli_stmt_execute($stmt_up_stok);
                mysqli_stmt_close($stmt_up_stok);

                $sisa_potong = 0;
            } else {
                $stok_nol = 0;
                $stmt_up_stok = mysqli_prepare($conn, "UPDATE stok_obat SET jumlah_stok = ? WHERE id_stok = ?");
                mysqli_stmt_bind_param($stmt_up_stok, "ii", $stok_nol, $id_stok_batch);
                mysqli_stmt_execute($stmt_up_stok);
                mysqli_stmt_close($stmt_up_stok);

                $sisa_potong -= $stok_batch;
            }
        }
        mysqli_stmt_close($stmt_batches);
    }

    // C. Update status tabel `pesanan` menjadi 'belum bayar'
    $status_belum_bayar = 'belum bayar';
    $stmt_verif = mysqli_prepare($conn, "UPDATE pesanan 
                                         SET status = ?, 
                                             id_apoteker = ?, 
                                             total_harga = ?, 
                                             catatan_apoteker = ? 
                                         WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_verif, "sidsi", $status_belum_bayar, $idapoteker, $total_harga, $catatan_apoteker, $id_pesanan);
    $eksekusi_verif = mysqli_stmt_execute($stmt_verif);
    mysqli_stmt_close($stmt_verif);

    if ($eksekusi_verif) {
        echo "<script>
                alert('Pesanan BERHASIL diverifikasi!');
                window.location.href = '../dashboard_apoteker.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Pesanan GAGAL diverifikasi!'); 
                window.location.href='../proses_pesanan.php?id=" . urlencode($id_pesanan) . "';
              </script>";
        exit();
    }
}

// Jika diakses langsung tanpa menekan tombol submit
header("Location: ../dashboard_apoteker.php");
exit();
?>