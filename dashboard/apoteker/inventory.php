<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";

// if (empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }

$Query = mysqli_query($conn, "SELECT
            o.id_obat,
            so.nomor_batch,
            o.nama_obat,
            o.kategori,
            o.satuan,
            o.harga,
            so.tanggal_kadaluarsa,
            so.jumlah_stok
         FROM obat o
         INNER JOIN stok_obat so
            ON o.id_obat = so.id_obat
         ORDER BY o.nama_obat, so.tanggal_kadaluarsa");

$jmldataobat = mysqli_num_rows($Query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/logo.jpeg">
    <title>Penyimpanan Obat - MediQuick</title>
    <style>
        .inv-btn {
            padding: 10px;
            background-color: #4fb0f0;
            color: white;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

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
                            <a class="nav-link" href="antrean_prosesobat.php">Proses Resep</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_prosesobat.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../login/login.php" onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
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
                        <h5 class="fw-bold mb-0"> Katalog Obat</h5>
                        <div>
                            <button type="button" id="tambahobat-btn" class="inv-btn" onclick="window.location.href = 'tambah_obat.php'"><i class="bi bi-plus"></i>Tambah Obat</button>
                        </div>
                    </div>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Obat</th>
                                    <th>Batch</th>
                                    <th>Nama Obat</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Kadaluarsa</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($jmldataobat <= 0) { ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-secondary p-3 fw-bold">Penyimpanan Obat Kosong</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($row = mysqli_fetch_assoc($Query)) { ?>
                                        <tr>
                                            <td><?= $row['id_obat'] ?></td>
                                            <td><?= $row['nomor_batch'] ?></td>
                                            <td><?= $row['nama_obat'] ?></td>
                                            <td><?= $row['kategori'] ?></td>
                                            <td><?= $row['satuan'] ?></td>
                                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                            <td><?= $row['jumlah_stok'] ?></td>
                                            <td><?= $row['tanggal_kadaluarsa'] ?></td>
                                            <td class="text-center">
                                                <a href="detail_obat.php?id=<?= $row['id_obat'] ?>&batch=<?= urlencode($row['nomor_batch']) ?>"><i class="bi bi-pencil-square"></i> Lihat Detail</a>
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
    <script>
        const addmed_btn = document.getElementById('tambahobat-btn');

        addmed_btn.onmouseover = () => {
            addmed_btn.style.backgroundColor = '#008afc';
        }
        addmed_btn.onmouseout = () => {
            addmed_btn.style.backgroundColor = '#4fb0f0';
        }
    </script>
</body>

</html>