<?php
session_start();
include __DIR__ . "/../../backend/connection.php";

$sql_form = "SELECT * FROM konten_dashboard WHERE status = 1 ORDER BY id_konten DESC";

$data = mysqli_query($conn, $sql_form);

$jumlah = mysqli_num_rows($data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

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
                </ul>
            </div>
          </div>
        </div> 
    </nav>

    <div class="container" style="margin-top: 50px;">
        <h2>Dashboard Admin</h2>
        <p>Kelola Informasi dan Konten MediQuick</p>
    </div>
    <hr>

    <div class="d-flex justify-content-between align-items-center mx-5">
        <h3>Konten Dashboard</h3>
        <?php if($jumlah < 3) { ?>
        <a href="konten_tambah.php"><button class="btn btn-primary"><i class="bi bi-plus-circle"> Tambah Konten</i></button></a>
        <?php } ?>
    </div>

    <div class="row">
        <?php while($row = mysqli_fetch_assoc($data)) {?>
        <div class="col-md-6 col-lg-4 mx-auto">
            <div class="card h-100 shadow-sm">
                <img>
                <div class="card-body">
                    <h4><?php echo $row['judul']; ?></h4>
                    <p><?php echo $row['deskripsi']; ?></p>
                </div>
                <div class="card-footer">
                    <a href="konten_edit.php?id=<?php echo $row['id_konten'];?>"><button class="btn btn-success"><i class="bi bi-pencil-square"></i></button></a>
                    <a href="/../../backend/hapus_konten.php?id=<?php echo $row['id_konten']; ?>"><button class="btn btn-danger"><i class="bi bi-trash3-fill"></i></button></a>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if($jumlah == 0) {?>
            <div class="col-12">
                <div class="alert alert-info">Belum ada konten dashboard</div>
            </div>
        <?php } ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>