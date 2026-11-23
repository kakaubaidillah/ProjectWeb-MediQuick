<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";

// if(empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }

//Mengambil data konsultasi untuk ditampilkan
$QueryKonsul = mysqli_query($conn, "SELECT
                    k.id_konsultasi, 
                    p.nama AS nama_pasien, 
                    d.nama AS nama_dokter 
               FROM konsultasi k 
               INNER JOIN pasien p 
                    ON k.id_pasien = p.id_pasien 
               INNER JOIN admin d 
                    ON k.id_dokter = d.id_admin 
               WHERE k.status = 'Diproses' ORDER BY k.tanggal_konsultasi DESC LIMIT 3");
//Menghitung jumlah data dengan status 'Diproses'
$QueryDiproses = mysqli_query($conn, "SELECT COUNT(*) AS total FROM konsultasi WHERE status ='Diproses'");
//Menghitung jumlah data dengan status 'Selesai'
$QuerySelesai = mysqli_query($conn, "SELECT COUNT(*) AS total FROM konsultasi WHERE status='Selesai'");

$QueryCekObat = mysqli_query($conn, "SELECT COUNT(*) AS jumlah_obat FROM stok_obat");

//Mengambil informasi stok obat dan expired
$QueryObat = mysqli_query($conn, "SELECT 
                o.id_obat, 
                so.nomor_batch,
                o.nama_obat, 
                so.jumlah_stok, 
                so.tanggal_kadaluarsa,
                DATEDIFF(so.tanggal_kadaluarsa, CURDATE()) AS selisih_hari
             FROM stok_obat so 
             INNER JOIN obat o 
                ON so.id_obat = o.id_obat
             ORDER BY so.tanggal_kadaluarsa ASC");

$dataObat = mysqli_fetch_all($QueryObat, MYSQLI_ASSOC); //Mengambil langsung semua baris data dalam satu array
$peringatan = [];

foreach ($dataObat as $rowObat) {
    $keterangan = [];
    if ($rowObat['jumlah_stok'] <= 0) {
        $keterangan[] = 'Stok Kosong';
    } elseif ($rowObat['jumlah_stok'] <= 20) {
        $keterangan[] = 'Stok Menipis';
    }

    if ($rowObat['selisih_hari'] <= 0) {
        $keterangan[] = 'Expired';
    } elseif ($rowObat['selisih_hari'] <= 20) {
        $keterangan[] = 'Hampir Expired';
    }

    if (!empty($keterangan)) {
        $rowObat['keterangan'] = implode("/", $keterangan);
        $peringatan[] = $rowObat;
    }

}
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
    <style>
        .dashboard-btn {
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
                            <a class="nav-link" href="../../login/login.php" onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container p-2">
        <div class="container-fluid d-flex flex-column align-items-start" style="margin: 10px 0px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">DASHBOARD</h1>
            <p class="text-muted mb-0">Ringkasan Aktivitas</p>
        </div>

        <!-- Card Statistik Resep START -->
        <div class="container-fluid p-2">
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-start align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-medium" style="font-size: 1rem;">Antrean Resep</p>
                                <?php
                                $dataDiproses = mysqli_fetch_assoc($QueryDiproses);
                                $jmlDiproses = $dataDiproses['total'];
                                ?>
                                <h3 class="fw-bold mb-0" id="konsulmasuk"><?= $jmlDiproses ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card p-3 h-100">
                        <div class="d-flex justify-content-start align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-medium" style="font-size: 1rem;">Resep selesai</p>
                                <?php
                                $dataSelesai = mysqli_fetch_assoc($QuerySelesai);
                                $jmlSelesai = $dataSelesai['total'];
                                ?>
                                <h3 class="fw-bold mb-0" id="konsulselesai"><?= $jmlSelesai ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Statistik Resep END -->

        <!-- Card List Singkat 3 Resep START -->
        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Antrean Resep</h5>
                        <button type="button" id="semua-rsp" class="dashboard-btn" onclick="window.location.href=('antrean_prosesobat.php')">Lihat Semua</button>
                    </div>
                    <div class="table-responsive border rounded-3 overflow-auto">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Konsultasi</th>
                                    <th>Pasien</th>
                                    <th>Dokter</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($jmlDiproses == 0) { ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-secondary p-3 fw-bold">Tidak Ada Antrean</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($rowKonsul = mysqli_fetch_assoc($QueryKonsul)) { ?>
                                        <tr>
                                            <td><?= $rowKonsul['id_konsultasi'] ?></td>
                                            <td><?= $rowKonsul['nama_pasien'] ?></td>
                                            <td><?= $rowKonsul['nama_dokter'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Informasi Obat</h5>
                        <button type="button" id="semua-obt" class="dashboard-btn" onclick="window.location.href=('inventory.php')">Lihat Semua</button>
                    </div>
                    <div class="table-responsive border rounded-3 overflow-auto">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nomor Obat</th>
                                    <th>Batch</th>
                                    <th>Nama Obat</th>
                                    <th>Stock</th>
                                    <th>Expired</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $cekobat = mysqli_fetch_assoc($QueryCekObat);
                                $jmlobat = $cekobat['jumlah_obat'];
                                ?>
                                <?php if($jmlobat == 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary p-3 fw-bold">Penyimpanan Obat Kosong</td>
                                    </tr>
                                <?php } elseif (empty($peringatan)) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary p-3 fw-bold">Persediaan Aman</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php foreach ($peringatan as $rowPeringatan) { ?>
                                        <tr>
                                            <td><?= $rowPeringatan['id_obat'] ?></td>
                                            <td><?= $rowPeringatan['nomor_batch'] ?></td>
                                            <td><?= $rowPeringatan['nama_obat'] ?></td>
                                            <td><?= $rowPeringatan['jumlah_stok'] ?></td>
                                            <td><?= $rowPeringatan['tanggal_kadaluarsa'] ?></td>
                                            <td><?= $rowPeringatan['keterangan'] ?></td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card List Singkat 3 Resep END -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.min.js"
        integrity="sha384-89aa4glufciPv46aKltztWQEmk+Tu9W2RhzXr6VcnUUEub7F8+06RcL2RYLCOHUq"
        crossorigin="anonymous"></script>
    <script>
        const resepbtn = document.getElementById('semua-rsp');
        const obatbtn = document.getElementById('semua-obt');

        resepbtn.onmouseover = () => {
            resepbtn.style.backgroundColor = '#008afc';
        }
        resepbtn.onmouseout = () => {
            resepbtn.style.backgroundColor = '#4fb0f0';
        }
        obatbtn.onmouseover = () => {
            obatbtn.style.backgroundColor = '#008afc';
        }
        obatbtn.onmouseout = () => {
            obatbtn.style.backgroundColor = '#4fb0f0';
        }
    </script>
</body>

</html>