<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

// if (empty($idapoteker) || $role !== "apoteker") {
//     header("Location: ../../login/login.php");
//     exit();
// }

// ringkasan jumlah pesanan selesai dengan status 'dibayar'
$status_selesai = 'dibayar';
$stmt_selesai   = mysqli_prepare($conn, "SELECT COUNT(id_pesanan) AS total_selesai FROM pesanan WHERE status = ?");
mysqli_stmt_bind_param($stmt_selesai, "s", $status_selesai);
mysqli_stmt_execute($stmt_selesai);
$res_selesai    = mysqli_stmt_get_result($stmt_selesai);
$data_selesai   = mysqli_fetch_assoc($res_selesai);
$total_selesai  = $data_selesai['total_selesai'] ?? 0;
mysqli_stmt_close($stmt_selesai);

// ringkasan jumlah jenis obat yang dimiliki apotek 
$stmt_obat  = mysqli_prepare($conn, "SELECT COUNT(id_obat) AS total_obat FROM obat");
mysqli_stmt_execute($stmt_obat);
$res_obat   = mysqli_stmt_get_result($stmt_obat);
$data_obat  = mysqli_fetch_assoc($res_obat);
$total_obat = $data_obat['total_obat'] ?? 0;
mysqli_stmt_close($stmt_obat);

// cek stok dan kadaluarsa
$batas_hari = 30;
$batas_stok = 10;
$stmt_check = mysqli_prepare($conn, "SELECT so.id_stok, so.batch, so.jumlah_stok, so.tanggal_kadaluarsa, o.nama_obat, DATEDIFF(so.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
                                     FROM stok_obat so
                                     JOIN obat o ON so.id_obat = o.id_obat
                                     WHERE so.tanggal_kadaluarsa <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                                        OR so.jumlah_stok <= ?
                                     ORDER BY so.tanggal_kadaluarsa ASC, so.jumlah_stok ASC");
mysqli_stmt_bind_param($stmt_check, "ii", $batas_hari, $batas_stok);
mysqli_stmt_execute($stmt_check);
$queryCheckObat = mysqli_stmt_get_result($stmt_check);

// daftar pesanan dengan status 'menunggu' 
$status_menunggu = 'menunggu';
$stmt_antrean    = mysqli_prepare($conn, "SELECT p.id_pesanan, p.tanggal_pesanan, p.alamat_pengiriman, p.resep_obat, pl.nama_lengkap 
                                          FROM pesanan p 
                                          JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan 
                                          WHERE p.status = ? 
                                            AND p.resep_obat IS NOT NULL 
                                            AND p.resep_obat != '' 
                                          ORDER BY p.tanggal_pesanan ASC");
mysqli_stmt_bind_param($stmt_antrean, "s", $status_menunggu);
mysqli_stmt_execute($stmt_antrean);
$query_antrean = mysqli_stmt_get_result($stmt_antrean);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/logo.jpeg">
    <title>Dashboard Apoteker - MediQuick</title>
</head>

<body>
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard_apoteker.php">MediQuick</a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Isi Hamburger Menu -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" href="dashboard_apoteker.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_pesanan.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="../../login/proses/access_logout.php" onclick="return confirm('Yakin Untuk Logout?')">LOGOUT</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container p-2">
        <div class="container-fluid d-flex flex-column align-items-start" style="margin: 10px 0px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">DASHBOARD</h1>
            <p class="text-muted mb-0">Ringkasan Aktivitas Apoteker</p>
        </div>

        <!-- Card Statistik START -->
        <div class="container-fluid p-2">
            <div class="row g-3 mb-3">
                <!-- Kartu 1: Pesanan Selesai (status = 'dibayar') -->
                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-medium" style="font-size: 1rem;">Pesanan Selesai</p>
                                <h3 class="fw-bold mb-0"><?= $total_selesai ?></h3>
                            </div>
                            <i class="bi bi bi-check2-circle fs-1" style="color: #4fb0f0;"></i>
                        </div>
                    </div>
                </div>

                <!-- Kartu 2: Jenis Obat Tersedia -->
                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 fw-medium" style="font-size: 1rem;">Total Jenis Obat</p>
                                <h3 class="fw-bold mb-0"><?= $total_obat ?></h3>
                            </div>
                            <i class="bi bi-capsule fs-1" style="color: #4fb0f0;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Statistik END -->

        <!-- Card Notifikasi Stok Obat -->
        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">NOTIFIKASI</h5>
                        <button type="button" class="btn btn-primary btn-sm" style="border-radius: 10px;" onclick="window.location.href=('inventory.php')">Lihat Semua</button>
                    </div>
                    <div class="table-responsive border rounded-3 overflow-auto">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Batch</th>
                                    <th>Nama Obat</th>
                                    <th>Stok</th>
                                    <th>Tanggal Kadaluarsa</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryCheckObat) == 0) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary p-4 fw-bold">Persediaan Obat Aman</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php
                                    while ($kondisiobat = mysqli_fetch_assoc($queryCheckObat)) {
                                        $sisahari = (int)$kondisiobat['sisa_hari'];
                                        $stok = (int)$kondisiobat['jumlah_stok'];
                                        $peringatan = [];

                                        if ($sisahari <= 0) {
                                            $peringatan[] = '<span class="badge rounded-pill text-bg-danger">Expired</span>';
                                        } elseif ($sisahari <= 30) {
                                            $peringatan[] = '<span class="badge rounded-pill text-bg-warning">Hampir Expired</span>';
                                        }

                                        if ($stok <= 0) {
                                            $peringatan[] = '<span class="badge rounded-pill text-bg-danger">Stok Habis</span>';
                                        } elseif ($stok <= 10) {
                                            $peringatan[] = '<span class="badge rounded-pill text-bg-warning">Stok Menipis</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($kondisiobat['batch']); ?></td>
                                            <td><?= htmlspecialchars($kondisiobat['nama_obat']); ?></td>
                                            <td><?= $stok; ?></td>
                                            <td><?= date("d M Y", strtotime($kondisiobat['tanggal_kadaluarsa'])); ?></td>
                                            <td><?= implode(" ", $peringatan); ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Notifikasi Stok Obat END -->

        <!-- Card List Antrean Verifikasi Resep START -->
        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Antrean Verifikasi Resep</h5>
                        </div>
                    </div>
                    <div class="table-responsive border rounded-3 overflow-auto">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Tanggal Pesanan</th>
                                    <th>Lampiran Resep</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($query_antrean) == 0) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary p-4 fw-bold">
                                            Tidak Ada Antrean Verifikasi Resep
                                        </td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($row = mysqli_fetch_assoc($query_antrean)) { ?>
                                        <tr>
                                            <td class="fw-bold">#<?= $row['id_pesanan']; ?></td>
                                            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                            <td><?= date("d M Y, H:i", strtotime($row['tanggal_pesanan'])); ?></td>
                                            <td>
                                                <span class="badge bg-info text-dark"><i class="bi bi-file-earmark-image"></i> Terlampir</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="proses_pesanan.php?id=<?= $row['id_pesanan']; ?>" class="btn btn-primary btn-sm fw-bold">Verifikasi</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card List Antrean Verifikasi Resep END -->
    </div>

    <?php
    mysqli_stmt_close($stmt_check);
    mysqli_stmt_close($stmt_antrean);
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>