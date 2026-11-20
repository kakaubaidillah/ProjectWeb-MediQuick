<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";

$pendkonsul = mysqli_query($conn, "SELECT * FROM konsultasi WHERE status = 'Diproses'");
$finkonsul = mysqli_query($conn, "SELECT * FROM konsultasi WHERE status = 'Selesai'")


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
    <title>Dashboard Apoteker</title>
</head>

<body>
    <nav class="navbar" style="background-color: #4fb0f0;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">MediQuick</a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- isi HM -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar"
                aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <!-- tombol X -->
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="antrean_prosesobat.php">Proses Resep</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_prosesobat.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../auth/login.php"
                                onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="p-2">
        <div class="container d-flex flex-column align-item-start" style="margin: 10px 10px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">DASHBOARD</h1>
            <p class="text-muted mb-0">Ringkasan Aktivitas</p>
        </div>
        <div class="container p-2">
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-left align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-medium" style="font-size: 1 rem;">Antrean Resep</p>
                                <?php
                                $jmlpend = mysqli_num_rows($pendkonsul);
                                ?>
                                <h3 class="fw-bold mb-0" id="konsulmasuk"><?= $jmlpend ?></h3>
                                <div ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-left align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-medium" style="font-size: 1 rem;">Resep selesai</p>
                                <?php
                                $jmlsls = mysqli_num_rows($finkonsul);
                                ?>
                                <h3 class="fw-bold mb-0" id="konsulselesai"><?= $jmlsls ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div >

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.min.js"
        integrity="sha384-89aa4glufciPv46aKltztWQEmk+Tu9W2RhzXr6VcnUUEub7F8+06RcL2RYLCOHUq"
        crossorigin="anonymous"></script>
</body>

</html>