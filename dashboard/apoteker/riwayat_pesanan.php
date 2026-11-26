<?php
session_start();
include __DIR__ . "/../../config/connect.php";

// Validasi session login & role apoteker
$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    header("Location: ../../login/login.php");
    exit();
}

// 1. Query khusus untuk Pesanan Selesai (status = 'dibayar') menggunakan Prepared Statement
$status_dibayar = 'dibayar';
$stmt_dibayar = mysqli_prepare($conn, "SELECT p.id_pesanan, p.tanggal_pesanan, p.total_harga, p.status, 
                                             pl.nama_lengkap, pl.no_telp
                                      FROM pesanan p
                                      JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                                      WHERE p.status = ?
                                      ORDER BY p.tanggal_pesanan DESC");
mysqli_stmt_bind_param($stmt_dibayar, "s", $status_dibayar);
mysqli_stmt_execute($stmt_dibayar);
$queryDibayar = mysqli_stmt_get_result($stmt_dibayar);

// 2. Query khusus untuk Pesanan Ditolak & Dibatalkan menggunakan Prepared Statement
$status_ditolak = 'ditolak';
$status_dibatalkan = 'dibatalkan';
$stmt_lainnya = mysqli_prepare($conn, "SELECT p.id_pesanan, p.tanggal_pesanan, p.total_harga, p.status, p.catatan_apoteker,
                                             pl.nama_lengkap, pl.no_telp
                                      FROM pesanan p
                                      JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                                      WHERE p.status IN (?, ?)
                                      ORDER BY p.tanggal_pesanan DESC");
mysqli_stmt_bind_param($stmt_lainnya, "ss", $status_ditolak, $status_dibatalkan);
mysqli_stmt_execute($stmt_lainnya);
$queryLainnya = mysqli_stmt_get_result($stmt_lainnya);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <title>Riwayat Pesanan - MediQuick</title>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard_apoteker.php"><img src="../../asset/img/logo.png" alt="logo" width="40" height="40">MediQuick</a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Isi Hamburger Menu -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard_apoteker.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" href="riwayat_pesanan.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="../../login/proses/access_logout.php"
                                onclick="return confirm('Yakin Untuk Logout?')">LOGOUT</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container p-2">
        <div class="container-fluid d-flex flex-column align-items-start" style="margin: 10px 0px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">Riwayat Pesanan</h1>
            <p class="text-muted mb-0">Daftar riwayat pesanan</p>
        </div>

        <!-- PESANAN SELESAI -->
        <div class="container-fluid p-2 mb-4">
            <div class="row g-3 px-2">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-success"><i class="bi bi-check-circle-fill"></i> Pesanan Selesai</h5>
                    </div>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Nama Pelanggan</th>
                                    <th>No. Telepon</th>
                                    <th>Tanggal Pesanan</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryDibayar) == 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-secondary text-center p-4 fw-bold">Belum Ada Pesanan Selesai</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($row = mysqli_fetch_assoc($queryDibayar)) { ?>
                                        <tr>
                                            <td class="fw-bold text-center">#<?= htmlspecialchars($row['id_pesanan']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                            <td><?= htmlspecialchars($row['no_telp']); ?></td>
                                            <td><?= date("d M Y, H:i", strtotime($row['tanggal_pesanan'])); ?></td>
                                            <td class="fw-semibold">Rp <?= number_format($row['total_harga'] ?? 0, 0, ',', '.'); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-success text-uppercase px-2 py-1"><?= htmlspecialchars($row['status']); ?></span>
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

        <!-- PESANAN DITOLAK & DIBATALKAN -->
        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-x-circle-fill"></i> Pesanan Ditolak & Dibatalkan</h5>
                    </div>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Nama Pelanggan</th>
                                    <th>No. Telepon</th>
                                    <th>Tanggal Pesanan</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryLainnya) == 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-secondary text-center p-4 fw-bold">Tidak Ada Pesanan Ditolak atau Dibatalkan</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($row = mysqli_fetch_assoc($queryLainnya)) { 
                                        $status = strtolower($row['status']);
                                        $badge_class = ($status == 'ditolak') ? "bg-danger" : "bg-warning text-dark";
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-center">#<?= htmlspecialchars($row['id_pesanan']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                            <td><?= htmlspecialchars($row['no_telp']) ?></td>
                                            <td><?= date("d M Y, H:i", strtotime($row['tanggal_pesanan'])) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $badge_class; ?> text-uppercase px-2 py-1"><?= htmlspecialchars($row['status']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($row['catatan_apoteker'] ?? '-') ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    mysqli_stmt_close($stmt_dibayar);
    mysqli_stmt_close($stmt_lainnya);
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>