<?php
session_start();
include __DIR__ . "/../../../config/connect.php";
// Error SQL jadi exception supaya transaksi bisa di-rollback dengan benar
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class BayarError extends Exception {}

$id_pelanggan = $_SESSION['id'] ?? "";
if (empty($id_pelanggan) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user.php");
    exit();
}

$id_pesanan = isset($_POST['id_pesanan']) ? (int) $_POST['id_pesanan'] : 0;

// Tampilkan alert lalu kembali ke halaman detail pesanan
function kembali($pesan, $id_pesanan)
{
    echo "<script>alert(" . json_encode($pesan) . "); window.location.href='../detail_pesanan.php?id=" . (int) $id_pesanan . "';</script>";
    exit();
}

try {
    mysqli_begin_transaction($conn);

    // 1. Kunci baris pesanan (FOR UPDATE) agar klik "Bayar" dua kali / dua tab tidak mengurangi stok dua kali.
    //    Kadaluarsa dicek oleh MySQL (NOW()) supaya zona waktunya sama dengan saat pesanan dibuat.
    $stmt = mysqli_prepare($conn, "
        SELECT status,
               (batas_pembayaran IS NOT NULL AND batas_pembayaran < NOW()) AS kadaluarsa
        FROM pesanan
        WHERE id_pesanan = ? AND id_pelanggan = ?
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_pelanggan);
    mysqli_stmt_execute($stmt);
    $pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$pesanan) {
        throw new BayarError("Pesanan tidak ditemukan!");
    }
    if ($pesanan['status'] !== 'belum bayar') {
        throw new BayarError("Status pesanan tidak valid untuk pembayaran.");
    }

    // 2. Waktu habis -> batalkan pesanan
    if ((int) $pesanan['kadaluarsa'] === 1) {
        $stmt_exp = mysqli_prepare($conn, "UPDATE pesanan SET status = 'dibatalkan' WHERE id_pesanan = ? AND status = 'belum bayar'");
        mysqli_stmt_bind_param($stmt_exp, "i", $id_pesanan);
        mysqli_stmt_execute($stmt_exp);
        mysqli_commit($conn);
        kembali("Waktu pembayaran telah habis. Pesanan otomatis dibatalkan.", $id_pesanan);
    }

    // 3. Ambil obat yang dipesan
    $stmt = mysqli_prepare($conn, "
        SELECT dp.id_obat, dp.jumlah, o.nama_obat
        FROM detail_pesanan dp
        JOIN obat o ON o.id_obat = dp.id_obat
        WHERE dp.id_pesanan = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);
    $items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

    if (empty($items)) {
        throw new BayarError("Pesanan ini belum memiliki daftar obat.");
    }

    // 4. Kurangi stok per obat. Batch yang paling cepat kadaluarsa dipakai lebih dulu (FEFO),
    //    batch yang sudah kadaluarsa atau kosong dilewati.
    $stmt_batch = mysqli_prepare($conn, "
        SELECT id_stok, jumlah_stok
        FROM stok_obat
        WHERE id_obat = ? AND tanggal_kadaluarsa >= CURDATE() AND jumlah_stok > 0
        ORDER BY tanggal_kadaluarsa ASC, id_stok ASC
        FOR UPDATE
    ");
    $stmt_kurangi = mysqli_prepare($conn, "
        UPDATE stok_obat
        SET jumlah_stok = jumlah_stok - ?
        WHERE id_stok = ? AND jumlah_stok >= ?
    ");

    foreach ($items as $it) {
        $id_obat = (int) $it['id_obat'];
        $sisa    = (int) $it['jumlah'];

        mysqli_stmt_bind_param($stmt_batch, "i", $id_obat);
        mysqli_stmt_execute($stmt_batch);
        $batches = mysqli_fetch_all(mysqli_stmt_get_result($stmt_batch), MYSQLI_ASSOC);

        foreach ($batches as $b) {
            if ($sisa <= 0) {
                break;
            }
            $id_stok = (int) $b['id_stok'];
            $ambil   = min($sisa, (int) $b['jumlah_stok']);

            mysqli_stmt_bind_param($stmt_kurangi, "iii", $ambil, $id_stok, $ambil);
            mysqli_stmt_execute($stmt_kurangi);
            if (mysqli_stmt_affected_rows($stmt_kurangi) !== 1) {
                throw new BayarError("Stok '" . $it['nama_obat'] . "' berubah saat diproses. Silakan coba lagi.");
            }
            $sisa -= $ambil;
        }

        if ($sisa > 0) {
            // Stok tidak cukup (mis. dibeli pelanggan lain lebih dulu) -> semua pengurangan dibatalkan
            throw new BayarError("Stok '" . $it['nama_obat'] . "' tidak mencukupi. Silakan ubah atau batalkan pesanan.");
        }
    }

    // 5. Stok sudah beres -> tandai dibayar
    $stmt_bayar = mysqli_prepare($conn, "UPDATE pesanan SET status = 'dibayar' WHERE id_pesanan = ? AND id_pelanggan = ? AND status = 'belum bayar'");
    mysqli_stmt_bind_param($stmt_bayar, "ii", $id_pesanan, $id_pelanggan);
    mysqli_stmt_execute($stmt_bayar);

    mysqli_commit($conn);

    header("Location: ../detail_pesanan.php?id=" . $id_pesanan);
    exit();

} catch (BayarError $e) {
    mysqli_rollback($conn);
    kembali($e->getMessage(), $id_pesanan);
} catch (Throwable $e) {
    mysqli_rollback($conn);
    error_log("bayar.php: " . $e->getMessage());
    kembali("Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.", $id_pesanan);
}
