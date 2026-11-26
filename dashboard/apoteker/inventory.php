<?php
session_start();
include __DIR__ . "/../../config/connect.php";

$idapoteker = $_SESSION['id'] ?? "";
$role = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    header("location: ../../login/login.php");
    exit();
}

// Query daftar obat
$sql_inventory = "SELECT o.id_obat, o.nama_obat, o.kategori, so.batch, so.jumlah_stok, so.tanggal_kadaluarsa, DATEDIFF(so.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
                  FROM obat o
                  JOIN stok_obat so ON o.id_obat = so.id_obat
                  ORDER BY so.tanggal_kadaluarsa ASC";

$stmt = mysqli_prepare($conn, $sql_inventory);
mysqli_stmt_execute($stmt);
$queryObat = mysqli_stmt_get_result($stmt);
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
    <title>Penyimpanan Obat - MediQuick</title>
</head>

<body>
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
                            <a class="nav-link active fw-bold" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_pesanan.php">Riwayat</a>
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

    <div class="container p-2">
        <div class="container-fluid d-flex flex-column align-items-start" style="margin: 10px 0px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">Penyimpanan Obat</h1>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Daftar Obat</h5>
                        <div>
                            <button type="button" class="btn btn-primary fw-bold" style="border-radius: 10px;" onclick="window.location.href = 'tambah_obat.php'"><i class="bi bi-plus"></i> Tambah Obat</button>
                        </div>
                    </div>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Batch</th>
                                    <th>Nama Obat</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Tanggal Kadaluarsa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($queryObat) == 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-secondary text-center fw-bold">Tidak Ada Obat</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php
                                    while ($daftar_obat = mysqli_fetch_assoc($queryObat)) {
                                        $sisahari = (int)$daftar_obat['sisa_hari'];
                                        $stok = (int)$daftar_obat['jumlah_stok'];
                                    ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($daftar_obat['batch']); ?></td>
                                            <td><?= htmlspecialchars($daftar_obat['nama_obat']); ?></td>
                                            <td><?= htmlspecialchars($daftar_obat['kategori']); ?></td>
                                            <td class="fw-bold <?= ($stok <= 0) ? 'text-danger' : (($stok <= 10) ? 'text-warning' : '') ?>"><?= $stok ?></td>
                                            <td class="fw-bold <?= ($sisahari <= 0) ? 'text-danger' : (($sisahari <= 30) ? 'text-warning' : '') ?>"><?= date("d M Y", strtotime($daftar_obat['tanggal_kadaluarsa'])); ?></td>
                                            <td class="text-center">
                                                <a href="detail_obat.php?id=<?= $daftar_obat['id_obat'] ?>&batch=<?= urlencode($daftar_obat['batch']) ?>" class="btn btn-sm btn-primary fw-bold">Lihat Detail</a>
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

    <?php
    mysqli_stmt_close($stmt);
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>