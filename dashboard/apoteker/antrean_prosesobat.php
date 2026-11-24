<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";

// if(empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }

$QueryCekAntrean = mysqli_query($conn, "SELECT
            k.id_konsultasi,
            p.nama AS nama_pasien,
            d.nama AS nama_dokter,
            k.diagnosis,
            k.catatan_dokter,
            k.tanggal_konsultasi
         FROM konsultasi k
         INNER JOIN pasien p
            ON k.id_pasien = p.id_pasien
         INNER JOIN admin d
            ON k.id_dokter = d.id_admin
         WHERE k.status = 'Diproses'
         ORDER BY k.tanggal_konsultasi ASC");

$jmlantrean = mysqli_num_rows($QueryCekAntrean);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <title>Dashboard Apoteker</title>
</head>

<body>

    <body>
        <nav class="navbar" style="background-color: #4fb0f0;">
            <div class="container">
                <a class="navbar-brand fw-bold text-white" href="#">MediQuick</a>

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
                        <!-- tombol X -->
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_apoteker.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="#">Proses Resep</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="inventory.php">Inventory</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="riwayat_prosesobat.php">Riwayat</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../../login/proses/access_logout.php" onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container p-2">
            <div class="container-fluid d-flex flex-column align-items-start" style="margin: 10px 0px; padding: 12px 12px;">
                <h1 class="fw-bold mb-0 fs-1">Antrean Proses Obat</h1>
            </div>

            <div class="container-fluid p-2">
                <div class="row g-3 px-2">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Daftar Antrean Resep</h5>
                        </div>
                        <div class="table-responsive border rounded-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID Konsultasi</th>
                                        <th>Nama Pasien</th>
                                        <th>Dokter Penangan</th>
                                        <th>Diagnosis</th>
                                        <th>Catatan Dokter (Resep)</th>
                                        <th>Tanggal Konsultasi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($jmlantrean <= 0) { ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-secondary p-3 fw-bold">Tidak Ada Antrean Proses Obat</td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php while ($row = mysqli_fetch_assoc($QueryCekAntrean)) { ?>
                                            <tr>
                                                <td><?= $row['id_konsultasi'] ?></td>
                                                <td><?= $row['nama_pasien'] ?></td>
                                                <td><?= $row['nama_dokter'] ?></td>
                                                <td><?= $row['diagnosis'] ?? '-' ?></td>
                                                <td><?= nl2br(htmlspecialchars($row['catatan_dokter'] ?? '-')) ?></td>
                                                <td><?= date('d-m-Y H:i', strtotime($row['tanggal_konsultasi'])) ?></td>
                                                <td class="text-center">
                                                    <a href="proses_obat.php?id=<?= $row['id_konsultasi'] ?>" class="text-decoration-none fw-semibold">Proses</a>
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
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.min.js" integrity="sha384-89aa4glufciPv46aKltztWQEmk+Tu9W2RhzXr6VcnUUEub7F8+06RcL2RYLCOHUq" crossorigin="anonymous"></script>
    </body>

</html>