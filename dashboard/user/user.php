<?php
session_start();
include __DIR__ ."/../../config/connection.php";
$id = $_GET['id'];

$sql = "SELECT * FROM pasien WHERE id_pasien = '$id'";
$data = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($data);

$sql_konten = "SELECT * FROM konten_dashboard WHERE status = 1 ORDER BY id_konten DESC";
$data_konten = mysqli_query($conn, $sql_konten);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .description-box{
            background-color: whitesmoke;
            color:black;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        .description-box p {font-size: 0.8rem; margin-bottom: 0;}
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
                        <a class="nav-link active" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="konsultasi.php?id=<?php echo $row['id_pasien']; ?>">Konsultasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat_konsultasi.php?id=<?php echo $row['id_pasien'] ?>">Riwayat Konsultasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../auth/login.php" onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
                    </li>
                </ul>
            </div>
          </div>
        </div> 
    </nav>

    <div class="container" style="margin-top: 100px;">
        <h1 class="text-center mb-5">WELCOME!</h1>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Impedit sequi sunt molestias ipsa itaque praesentium adipisci hic libero eligendi accusamus numquam officia eum recusandae veritatis modi, iure repellendus explicabo dolor.</p>
    </div>

    <div class="container my-4" style="max-width: 900px;">
        <div class="row g-3 justify-content-center" style="max-width: 800px;">
            <?php while($konten = mysqli_fetch_assoc($data_konten)) {?>
            <div class="col-12 col-md-4">
                <div class="description-box" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">
                    <?php if(!empty($konten['gambar'])) {?>
                        <img src="../../asset/uploads/<?= $konten['gambar']; ?>" alt="Picture" class="img-top" style="height: 180px; object-fit: cover;">
                    <?php } ?>
                    <h5><?php echo $konten['judul']; ?></h5>
                    <p><?php echo $konten['deskripsi']; ?></p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function hoverOn(el) {
            el.style.backgroundColor = "#4fb0f0";
            el.style.color = "white";
        }
        function hoverOff(el) {
            el.style.backgroundColor = "whitesmoke";
            el.style.color = "black";
        }
    </script>
</body>
</html>