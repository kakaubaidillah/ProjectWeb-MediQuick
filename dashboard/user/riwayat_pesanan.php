<?php
session_start();
include __DIR__ . "/../../config/connect.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../../login/login.php");
    exit;
}
$id_pelanggan = (int) $_SESSION['id'];

// Pesanan 'belum bayar' yang waktunya habis -> otomatis dibatalkan
$stmt = mysqli_prepare($conn, "
    UPDATE pesanan SET status = 'dibatalkan'
    WHERE id_pelanggan = ? AND status = 'belum bayar'
      AND batas_pembayaran IS NOT NULL AND batas_pembayaran < NOW()
");
mysqli_stmt_bind_param($stmt, "i", $id_pelanggan);
mysqli_stmt_execute($stmt);

// Ambil semua pesanan milik pelanggan ini
$stmt = mysqli_prepare($conn, "
    SELECT id_pesanan, tanggal_pesanan, total_harga, resep_obat, status
    FROM pesanan
    WHERE id_pelanggan = ?
    ORDER BY tanggal_pesanan DESC
");
mysqli_stmt_bind_param($stmt, "i", $id_pelanggan);
mysqli_stmt_execute($stmt);
$semua = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Pisahkan jadi dua kelompok
$pesanan_aktif   = [];
$pesanan_selesai = [];
foreach ($semua as $p) {
    if (in_array($p['status'], ['menunggu', 'belum bayar'], true)) {
        $pesanan_aktif[] = $p;
    } else {
        $pesanan_selesai[] = $p;
    }
}

$rupiah = fn($n) => "Rp " . number_format((float) $n, 0, ',', '.');

// Warna badge per status
$badge = [
    'menunggu'    => ['bg-warning text-dark', 'Menunggu Verifikasi'],
    'belum bayar' => ['text-white" style="background-color:#1591DC;', 'Belum Bayar'],
    'dibayar'     => ['bg-success', 'Dibayar'],
    'ditolak'     => ['bg-danger', 'Ditolak'],
    'dibatalkan'  => ['bg-secondary', 'Dibatalkan'],
];

// Satu fungsi untuk menggambar kartu pesanan (dipakai kedua kelompok)
function kartu_pesanan($p, $badge, $rupiah)
{
    $is_resep = !empty($p['resep_obat']);
    $b = $badge[$p['status']];
    ?>
    <div class="card shadow-sm mb-3" style="max-width: 850px; border-color:#C4E2F5;">
        <a href="detail_pesanan.php?id=<?= (int) $p['id_pesanan']; ?>" class="text-decoration-none text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">ID Pesanan</h6>
                        <h5>#<?= (int) $p['id_pesanan']; ?></h5>
                        <p class="mb-0">
                            <i class="bi bi-calendar3"></i>
                            <?= date('d M Y, H:i', strtotime($p['tanggal_pesanan'])); ?>
                        </p>
                        <p class="mb-0">
                            Jenis:
                            <strong><?= $is_resep ? "Lewat Resep Dokter" : "Pembelian Manual"; ?></strong>
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge <?= $b[0]; ?>"><?= $b[1]; ?></span>
                        <p class="mb-0 mt-2 fw-bold" style="color:#2C5EAD;">
                            <?php if ($p['status'] === 'menunggu' || ($is_resep && (float) $p['total_harga'] == 0)){ ?>
                                <small class="text-muted fw-normal">Total belum tersedia</small>
                            <?php }else{ ?>
                                <?= $rupiah($p['total_harga']); ?>
                            <?php } ?>
                        </p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <title>Riwayat Pesanan - MediQuick</title>
</head>

<body>
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php"><img src="../../asset/img/logo.png" alt="logo" width="40" height="40">MediQuick</a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar"
                    aria-controls="offcanvasNavbar"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- isi HM -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="beli_obat.php">Pesan Obat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="daftar_obat.php">Daftar Obat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Riwayat Pesanan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../../login/proses/access_logout.php">LOGOUT</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 20px;">
        <h2 style="color:#2C5EAD;">Riwayat Pesanan</h2>
    </div>
    <hr>

    <div class="container mt-3 mb-4">

        <!-- Menunggu / Belum Bayar -->
        <div class="card shadow-sm mb-4" style="max-width: 900px; border-color:#1591DC;">
            <div class="card-header text-white fw-semibold" style="background-color:#1591DC;">
                <i class="bi bi-hourglass-split"></i> Pesanan Berjalan
                <span class="badge bg-light text-dark ms-1"><?= count($pesanan_aktif); ?></span>
            </div>
            <div class="card-body" style="background-color:#C4E2F5;">
                <?php foreach ($pesanan_aktif as $p) { kartu_pesanan($p, $badge, $rupiah); } ?>

                <?php if (!$pesanan_aktif){ ?>
                    <div class="alert alert-light mb-0">Tidak ada pesanan yang sedang berjalan.</div>
                <?php } ?>
            </div>
        </div>

        <!-- Dibayar / Ditolak / Dibatalkan -->
        <div class="card shadow-sm" style="max-width: 900px; border-color:#2C5EAD;">
            <div class="card-header text-white fw-semibold" style="background-color:#2C5EAD;">
                <i class="bi bi-clock-history"></i> Pesanan Selesai
                <span class="badge bg-light text-dark ms-1"><?= count($pesanan_selesai); ?></span>
            </div>
            <div class="card-body" style="background-color:#C4E2F5;">
                <?php foreach ($pesanan_selesai as $p) { kartu_pesanan($p, $badge, $rupiah); } ?>

                <?php if (!$pesanan_selesai){ ?>
                    <div class="alert alert-light mb-0">Belum ada riwayat pesanan.</div>
                <?php } ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>