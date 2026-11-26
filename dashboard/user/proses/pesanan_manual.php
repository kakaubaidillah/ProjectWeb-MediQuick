<?php
session_start();
include __DIR__ . "/../../../config/connect.php";
// Supaya error SQL (mis. kolom belum ada) terlihat & ter-rollback, tidak gagal diam-diam
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id_pelanggan = $_SESSION['id'] ?? "";
if (empty($id_pelanggan)) {
    header("Location: ../../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../beli_obat.php");
    exit();
}

$alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
$arr_id_obat       = $_POST['id_obat'] ?? [];
$arr_jumlah        = $_POST['jumlah'] ?? [];
$edit_id           = isset($_POST['edit_id']) ? (int) $_POST['edit_id'] : 0;

if (empty($alamat_pengiriman) || empty($arr_id_obat)) {
    echo "<script>alert('Alamat pengiriman dan minimal 1 obat wajib diisi!'); window.history.back();</script>";
    exit();
}

// Gabungkan jika pelanggan memilih id_obat yang sama di dua baris berbeda
$keranjang = [];
for ($i = 0; $i < count($arr_id_obat); $i++) {
    $id_obt = (int) $arr_id_obat[$i];
    $jml    = (int) $arr_jumlah[$i];
    if ($id_obt > 0 && $jml > 0) {
        $keranjang[$id_obt] = ($keranjang[$id_obt] ?? 0) + $jml;
    }
}

if (empty($keranjang)) {
    echo "<script>alert('Jumlah obat harus lebih dari 0!'); window.history.back();</script>";
    exit();
}

mysqli_begin_transaction($conn);

try {
    $total_harga = 0;
    $item_valid  = [];

    // 1. Validasi setiap obat langsung dari database (butuh_resep, stok aktif, harga asli)
    $stmt_obat = mysqli_prepare($conn, "
        SELECT 
            o.id_obat, o.nama_obat, o.harga, o.butuh_resep,
            COALESCE(SUM(s.jumlah_stok), 0) AS total_stok
        FROM obat o
        LEFT JOIN stok_obat s 
            ON o.id_obat = s.id_obat 
            AND s.tanggal_kadaluarsa >= CURDATE()
        WHERE o.id_obat = ?
        GROUP BY o.id_obat, o.nama_obat, o.harga, o.butuh_resep
    ");

    foreach ($keranjang as $id_obt => $jml) {
        mysqli_stmt_bind_param($stmt_obat, "i", $id_obt);
        mysqli_stmt_execute($stmt_obat);
        $res_obat = mysqli_stmt_get_result($stmt_obat);
        $obat = mysqli_fetch_assoc($res_obat);

        if (!$obat) {
            throw new Exception("Obat dengan ID $id_obt tidak ditemukan.");
        }
        if ((int) $obat['butuh_resep'] !== 0) {
            throw new Exception("Obat '{$obat['nama_obat']}' membutuhkan resep dokter dan tidak dapat dibeli secara bebas.");
        }
        if ((int) $obat['total_stok'] < $jml) {
            throw new Exception("Stok '{$obat['nama_obat']}' tidak mencukupi (Tersedia: {$obat['total_stok']}, Diminta: $jml).");
        }

        $harga_satuan = (float) $obat['harga'];
        $subtotal     = $harga_satuan * $jml;
        $total_harga += $subtotal;

        $item_valid[] = [
            'id_obat'      => $id_obt,
            'jumlah'       => $jml,
            'harga_satuan' => $harga_satuan,
            'subtotal'     => $subtotal
        ];
    }

    // 2. Insert baru atau Update pesanan lama jika sedang mode Ubah Pesanan
    if ($edit_id > 0) {
        $stmt_cek = mysqli_prepare($conn, "SELECT id_pesanan FROM pesanan WHERE id_pesanan = ? AND id_pelanggan = ? AND status = 'belum bayar' AND resep_obat IS NULL AND (batas_pembayaran IS NULL OR batas_pembayaran > NOW())");
        mysqli_stmt_bind_param($stmt_cek, "ii", $edit_id, $id_pelanggan);
        mysqli_stmt_execute($stmt_cek);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek))) {
            throw new Exception("Pesanan tidak dapat diubah atau sudah kedaluwarsa.");
        }

        $stmt_upd = mysqli_prepare($conn, "UPDATE pesanan SET alamat_pengiriman = ?, total_harga = ? WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_upd, "sdi", $alamat_pengiriman, $total_harga, $edit_id);
        mysqli_stmt_execute($stmt_upd);

        // Hapus detail lama sebelum insert detail baru
        $stmt_del = mysqli_prepare($conn, "DELETE FROM detail_pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $edit_id);
        mysqli_stmt_execute($stmt_del);

        $id_pesanan = $edit_id;
    } else {
        // Batas bayar 15 menit dihitung oleh MySQL, sama dengan jam yang dipakai saat pengecekan kadaluarsa
        $stmt_ins = mysqli_prepare($conn, "
            INSERT INTO pesanan (id_pelanggan, alamat_pengiriman, tanggal_pesanan, batas_pembayaran, total_harga, status)
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 15 MINUTE), ?, 'belum bayar')
        ");
        mysqli_stmt_bind_param($stmt_ins, "isd", $id_pelanggan, $alamat_pengiriman, $total_harga);
        mysqli_stmt_execute($stmt_ins);
        $id_pesanan = mysqli_insert_id($conn);
    }

    // 3. Simpan semua baris obat ke detail_pesanan
    $stmt_det = mysqli_prepare($conn, "
        INSERT INTO detail_pesanan (id_pesanan, id_obat, jumlah, harga_satuan, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($item_valid as $item) {
        mysqli_stmt_bind_param($stmt_det, "iiidd", $id_pesanan, $item['id_obat'], $item['jumlah'], $item['harga_satuan'], $item['subtotal']);
        mysqli_stmt_execute($stmt_det);
    }

    mysqli_commit($conn);
    header("Location: ../detail_pesanan.php?id=" . $id_pesanan);
    exit();

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn);
    error_log("pesanan_manual: " . $e->getMessage());
    echo "<script>alert('Terjadi kesalahan database saat menyimpan pesanan.'); window.history.back();</script>";
    exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    $pesan = json_encode('Gagal memproses pesanan: ' . $e->getMessage());
    echo "<script>alert($pesan); window.history.back();</script>";
    exit();
}