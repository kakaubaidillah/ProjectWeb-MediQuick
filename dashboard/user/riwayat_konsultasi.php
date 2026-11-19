<?php
session_start();
include __DIR__ ."/../../backend/connection.php";
$id = $_GET['id'];

$sql = "SELECT * FROM pasien WHERE id_pasien = '$id'";
$data_pasien = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($data_pasien);

$sql_konsultasi = "SELECT * FROM konsultasi WHERE id_pasien = '$id' ORDER BY tanggal_konsultasi DESC";

$data_konsul = mysqli_query($conn, $sql_konsultasi);

$jumlah = mysqli_num_rows($data_konsul);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDIQUICK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link active" href="user.php?id=<?php echo $row['id_pasien'];?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="konsultasi.php?id=<?php echo $row['id_pasien']; ?>">Konsultasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Riwayat Konsultasi</a>
                    </li>
                </ul>
            </div>
          </div>
        </div> 
    </nav>

    <div class="container" style="margin-top: 20px;">
        <h2>Riwayat Konsultasi Pasien</h2>
    </div>
    <hr>

    <div class="container mt-3">
        <div class="d-flex flex-column g-3">
            <?php while($konsul = mysqli_fetch_assoc($data_konsul)) {?>
            <div class="card shadow-sm" style="max-width: 850px;">
                <a href="riwayat.php?id=<?php echo $konsul['id_konsultasi'] ?>" class="text-decoration-none text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">
                                ID Konsultasi
                            </h6>
                            <h5>
                                <?php echo $konsul['id_konsultasi']; ?>
                            </h5>
                            <p class="mb-0">
                                Nama Pasien: 
                                <strong><?php echo $row['nama']; ?></strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark">
                                <?php echo $konsul['status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                </a>
            </div>
            <?php } ?>

            <?php if($jumlah == 0) {?>
                <div class="col-12">
                    <div class="alert alert-info">Belum ada Konsultasi Masuk</div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>