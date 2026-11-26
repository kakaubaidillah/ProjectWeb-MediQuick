<?php
session_start();
include __DIR__ . "/../../config/connect.php";

$id_pelanggan = $_SESSION['id'] ?? "";
if (empty($id_pelanggan)) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../../login/login.php';</script>";
    exit();
}

$id_pesanan = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_pesanan <= 0) {
    header("Location: user.php");
    exit();
}

// Ambil data pesanan milik pelanggan yang sedang login
$stmt = mysqli_prepare($conn, "SELECT *, TIMESTAMPDIFF(SECOND, NOW(), batas_pembayaran) AS sisa_detik FROM pesanan WHERE id_pesanan = ? AND id_pelanggan = ?");
mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_pelanggan);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$pesanan) {
    echo "<script>alert('Data pesanan tidak ditemukan!'); window.location.href='user.php';</script>";
    exit();
}

// Auto-cancel jika status 'belum bayar' dan batas_pembayaran sudah lewat.
// Waktu dihitung oleh MySQL (NOW()) supaya sama dengan waktu yang dipakai saat pesanan dibuat.
$sisa_detik = 0;
if ($pesanan['status'] === 'belum bayar' && $pesanan['sisa_detik'] !== null) {
    if ((int) $pesanan['sisa_detik'] <= 0) {
        $stmt_exp = mysqli_prepare($conn, "UPDATE pesanan SET status = 'dibatalkan' WHERE id_pesanan = ? AND status = 'belum bayar'");
        mysqli_stmt_bind_param($stmt_exp, "i", $id_pesanan);
        mysqli_stmt_execute($stmt_exp);
        $pesanan['status'] = 'dibatalkan';
    } else {
        $sisa_detik = (int) $pesanan['sisa_detik'];
    }
}

// Ambil rincian item obat jika sudah ada di detail_pesanan
$stmt_detail = mysqli_prepare($conn, "
    SELECT dp.*, o.nama_obat, o.satuan
    FROM detail_pesanan dp
    JOIN obat o ON dp.id_obat = o.id_obat
    WHERE dp.id_pesanan = ?
");
mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
mysqli_stmt_execute($stmt_detail);
$res_detail = mysqli_stmt_get_result($stmt_detail);
$items = mysqli_fetch_all($res_detail, MYSQLI_ASSOC);

$is_resep = !empty($pesanan['resep_obat']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Detail Pesanan #<?= $id_pesanan ?> - MediQuick</title>
</head>
<body class="bg-light">
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand" href="user.php">
                <h3 class="fw-bold text-white mb-0">MediQuick</h3>
            </a>
            <a href="user.php" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-house-door me-1"></i> Beranda
            </a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h2 class="fw-bold mb-0">Detail Pesanan #<?= $pesanan['id_pesanan'] ?></h2>
                <small class="text-muted">Tanggal Pesan: <?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> WIB</small>
            </div>
            <div>
                <?php
                $badge_map = [
                    'menunggu'    => ['bg-warning text-dark', 'Menunggu Verifikasi Apoteker'],
                    'belum bayar' => ['bg-primary', 'Belum Bayar'],
                    'dibayar'     => ['bg-success', 'Sudah Dibayar'],
                    'ditolak'     => ['bg-danger', 'Resep Ditolak'],
                    'dibatalkan'  => ['bg-secondary', 'Dibatalkan']
                ];
                [$badge_class, $badge_label] = $badge_map[$pesanan['status']] ?? ['bg-secondary', $pesanan['status']];
                ?>
                <span class="badge <?= $badge_class ?> fs-6 px-3 py-2"><?= $badge_label ?></span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Kolom Kiri: Info Pesanan & Rincian Obat / Resep -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4 mb-3">
                    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">Informasi Pengiriman</h5>
                    <p class="mb-1 text-muted small">Metode Pemesanan:</p>
                    <p class="fw-semibold"><?= $is_resep ? 'Pembelian Melalui Resep Dokter' : 'Pembelian Obat Bebas (Manual)' ?></p>

                    <p class="mb-1 text-muted small">Alamat Pengiriman:</p>
                    <p class="mb-0"><?= htmlspecialchars($pesanan['alamat_pengiriman']) ?></p>
                </div>

                <?php if ($is_resep){ ?>
                    <div class="card border-0 shadow-sm p-4 mb-3">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">Lampiran Resep Dokter</h5>
                        <?php
                        $file_url = "../../asset/uploads/resep/" . urlencode($pesanan['resep_obat']);
                        $ext_file = strtolower(pathinfo($pesanan['resep_obat'], PATHINFO_EXTENSION));
                        ?>
                        <?php if ($ext_file === 'pdf'){ ?>
                            <a href="<?= $file_url ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Dokumen Resep (PDF)
                            </a>
                        <?php }else{ ?>
                            <img src="<?= $file_url ?>" alt="Resep Dokter" class="img-fluid rounded border" style="max-height: 360px;">
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (!empty($items)){ ?>
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">Daftar Obat</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $it){ ?>
                                        <tr>
                                            <td><?= htmlspecialchars($it['nama_obat']) ?></td>
                                            <td class="text-center"><?= (int)$it['jumlah'] ?> <?= htmlspecialchars($it['satuan']) ?></td>
                                            <td class="text-end">Rp <?= number_format($it['harga_satuan'], 0, ',', '.') ?></td>
                                            <td class="text-end fw-semibold">Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total Tagihan</th>
                                        <th class="text-end text-primary fs-5">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Kolom Kanan: Panel Status / Pembayaran QRIS / Aksi -->
            <div class="col-lg-5">
                <?php if ($pesanan['status'] === 'menunggu'){ ?>
                    <div class="card border-0 shadow-sm p-4 text-center">
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 3.5rem;"></i>
                        <h5 class="fw-bold mt-2">Menunggu Verifikasi Apoteker</h5>
                        <p class="text-muted small">
                            Resep Anda sedang diperiksa oleh apoteker kami. Setelah disetujui, rincian harga dan kode pembayaran QRIS akan muncul di halaman ini.
                        </p>
                        <div class="d-grid gap-2 mt-2">
                            <a href="user.php" class="btn btn-primary fw-semibold">Kembali ke Dashboard</a>
                            <form action="proses/batal_pesanan.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan resep ini?')">
                                <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                <button type="submit" class="btn btn-outline-danger w-100">Batalkan Pengajuan</button>
                            </form>
                        </div>
                    </div>

                <?php }elseif ($pesanan['status'] === 'belum bayar'){ ?>
                    <div class="card border-0 shadow-sm p-4 text-center">
                        <?php if ($sisa_detik > 0){ ?>
                        <div class="alert alert-warning py-2 mb-3">
                            <small class="d-block">Selesaikan pembayaran dalam:</small>
                            <span class="fw-bold fs-4 text-danger" id="countdown-timer">--:--</span>
                        </div>
                        <?php } ?>

                        <h6 class="fw-bold mb-2">Scan QRIS MediQuick</h6>
                        <div class="border rounded p-3 bg-white d-inline-block mx-auto mb-2">
                            <!-- QRIS Gimmick SVG -->
                            <img src="../../asset/img/images.jpg" alt="Qris" width="170" height="170">
                            <div class="small fw-bold text-muted mt-1">NMID: ID102026MEDIQUICK</div>
                        </div>

                        <p class="mb-3">Total Bayar: <strong class="text-primary fs-5">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong></p>

                        <div class="d-grid gap-2">
                            <form action="proses/bayar.php" method="POST" onsubmit="return confirm('Konfirmasi simulasi pembayaran sekarang?')">
                                <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                <button type="submit" class="btn btn-success btn-lg fw-bold w-100">
                                    <i class="bi bi-qr-code-scan me-1"></i> Bayar Sekarang
                                </button>
                            </form>

                            <?php if (!$is_resep){ ?>
                                <a href="beli_obat.php?edit=<?= $id_pesanan ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil-square me-1"></i> Ubah Pesanan
                                </a>
                            <?php } ?>

                            <form action="proses/batal_pesanan.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                <input type="hidden" name="id_pesanan" value="<?= $id_pesanan ?>">
                                <button type="submit" class="btn btn-outline-danger w-100">Batalkan Pesanan</button>
                            </form>
                        </div>
                    </div>

                <?php }elseif ($pesanan['status'] === 'dibayar'){ ?>
                    <div class="card border-0 shadow-sm p-4 text-center">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                        <h5 class="fw-bold mt-2 text-success">Pesanan Berhasil Dibayar!</h5>
                        <p class="text-muted small">
                            Terima kasih. Pembayaran sebesar <strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong> telah diterima dan pesanan Anda segera diproses untuk pengiriman.
                        </p>
                        <a href="user.php" class="btn btn-primary fw-semibold">Kembali ke Beranda</a>
                    </div>

                <?php }elseif ($pesanan['status'] === 'ditolak'){ ?>
                    <div class="card border-0 shadow-sm p-4 text-center">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 3.5rem;"></i>
                        <h5 class="fw-bold mt-2 text-danger">Pesanan Resep Ditolak</h5>
                        <div class="alert alert-danger text-start small mt-2">
                            <strong>Catatan Apoteker:</strong><br>
                            <?= htmlspecialchars($pesanan['catatan_apoteker'] ?: 'Resep tidak memenuhi syarat verifikasi.') ?>
                        </div>
                        <a href="beli_obat.php" class="btn btn-primary fw-semibold">Ajukan Pesanan Baru</a>
                    </div>

                <?php }elseif ($pesanan['status'] === 'dibatalkan'){ ?>
                    <div class="card border-0 shadow-sm p-4 text-center">
                        <i class="bi bi-slash-circle text-secondary" style="font-size: 3.5rem;"></i>
                        <h5 class="fw-bold mt-2 text-secondary">Pesanan Dibatalkan</h5>
                        <p class="text-muted small">Pesanan ini telah dibatalkan atau melewati batas waktu pembayaran.</p>
                        <a href="beli_obat.php" class="btn btn-outline-primary fw-semibold">Buat Pesanan Baru</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php if ($pesanan['status'] === 'belum bayar' && $sisa_detik > 0){ ?>
    <script>
        let sisaDetik = <?= (int) $sisa_detik ?>;
        const timerEl = document.getElementById('countdown-timer');

        function updateTimer() {
            if (sisaDetik <= 0) {
                timerEl.textContent = '00:00';
                // Reload agar backend otomatis mengubah status menjadi 'dibatalkan'
                window.location.reload();
                return;
            }
            const menit = Math.floor(sisaDetik / 60);
            const detik = sisaDetik % 60;
            timerEl.textContent = String(menit).padStart(2, '0') + ':' + String(detik).padStart(2, '0');
            sisaDetik--;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
    <?php } ?>
</body>
</html>