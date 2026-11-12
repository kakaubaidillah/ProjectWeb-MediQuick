<?php
session_start();
include __DIR__ . "/../../backend/connection.php";

$id_dokter = $_GET['id'];

$sql_konsultasi = "SELECT * FROM konsultasi WHERE status = 'Menunggu'";


$data = mysqli_query($conn, $sql_konsultasi);

$jumlah = mysqli_num_rows($data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Dashboard Dokter</title>
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
                        <a class="nav-link active" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Riwayat Konsultasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Payment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../auth/login.php" onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
                    </li>
                </ul>
            </div>
          </div>
        </div> 
    </nav>

    <div class="container" style="margin-top: 50px;">
        <h2>Dashboard Dokter</h2>
        <p>Kelola Konsultasi Pasien</p>
    </div>
    <hr>

    <div class="d-flex justify-content-between align-items-center mx-5">
        <h3>Daftar Konsultasi Pasien</h3>
        <br>
    </div>

    <div class="row g-3">
        <?php while($row = mysqli_fetch_assoc($data)) {
            $id_pasien = $row['id_pasien'];
            $sql_pasien = "SELECT * FROM pasien WHERE id_pasien = '$id_pasien'";
            $data_pasien = mysqli_query($conn, $sql_pasien);
            $pasien = mysqli_fetch_assoc($data_pasien);
        ?>
        <div class="col-md-6 col-lg-4 mx-auto">
            <div class="card h-100 w-75 shadow-sm">
                <div class="card-body">
                    <p>Nama:<br><?php echo $pasien['nama'] ?></p>
                    <p>Gejala:<br><?php echo $row['gejala']; ?></p>
                    <p>tanggal konsultasi:<br><?php echo $row['tanggal_konsultasi']; ?></p>
                    <p>status:<br><?php echo $row['status']; ?></p>
                </div>
                <div class="card-footer">
                    <a href="konsultasi_pasien.php?id=<?php echo $row['id_konsultasi'];?>"><button class="btn btn-success"><i class="bi bi-pencil-square"></i></button></a>
                    <a href="/../../backend/hapus_konsultasi.php?id=<?php echo $row['id_konsultasi']; ?>"><button class="btn btn-danger"><i class="bi bi-trash3-fill"></i></button></a>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if($jumlah == 0) {?>
            <div class="col-12">
                <div class="alert alert-info">Belum ada Konsultasi Masuk</div>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


