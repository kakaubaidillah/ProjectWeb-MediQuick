<?php
session_start();
include __DIR__ ."/../../config/connect.php";
$id = $_SESSION['id']?? "";

//data baru
$sql_obat = "SELECT * FROM obat LIMIT 9";
$data_obat = mysqli_query($conn, $sql_obat);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <title>MediQuick</title>
    <style>
        .description-box{
            background-color: whitesmoke;
            color:black;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            height: 250px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .description-box img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .hero-carousel {
            height: 400px;
        }

        .hero-carousel .carousel-inner {
            height: 100%;
        }

        .hero-carousel .carousel-item {
            height: 100%;
        }

        .hero-carousel .carousel-item .container {
            height: 100%;
        }

        .hero-carousel .carousel-item .row {
            height: 100%;
        }

        .obat-slider {
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            padding-bottom: 10px;
        }

        .obat-item {
            flex: 0 0 180px;
            width: 180px;
        }

        .obat-card-button {
            width: 100%;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            text-align: inherit;
        }
    </style>
</head>

<body>
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="user.php"><img src="../../asset/img/logo.png" alt="logo" width="40" height="40">MediQuick</a>

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
                        <a class="nav-link active" href="user.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="beli_obat.php">Pesan Obat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="daftar_obat.php">Daftar Obat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat_pesanan.php">Riwayat Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../../login/proses/access_logout.php">LOGOUT</a>
                    </li>
                </ul>
            </div>
          </div>
        </div> 
    </nav>

    <header class="text-white position-relative" style="background-color: #4fb0f0;">
        <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner py-5">
                <!-- Slide 1: Kampanye Utama -->
                <div class="carousel-item active">
                    <div class="container py-2">
                        <div class="row align-items-center justify-content-center g-4">
                            <div class="col-lg-7 text-center text-lg-start">
                                <span class="badge bg-white mb-2 px-3 py-2 rounded-pill fw-semibold" style="color: #4fb0f0;">
                                    <i class="bi bi-heart-pulse me-1"></i>Layanan Kesehatan Digital
                                </span>
                                <h1 class="display-5 fw-bold mb-3">Kesehatan Lebih Mudah Bersama MediQuick</h1>
                                <p class="lead mb-4">
                                    Temukan berbagai kebutuhan obat dan kesehatan dalam satu tempat. Pesan obat dengan mudah, kapan saja,tanpa harus repot keluar rumah.
                                </p>
                                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-2">
                                    <?php if (!empty($id)) {?>
                                        <a href="beli_obat.php" class="btn btn-warning btn-lg fw-bold text-dark">Buat Pesanan</a>
                                    <?php }else{ ?>
                                        <a href="../../login/regist.php" class="btn btn-warning btn-lg fw-bold text-dark">Daftar Sekarang</a>
                                        <a href="../../login/login.php" class="btn btn-outline-light btn-lg">Masuk Akun</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Edukasi Pilah Sampah -->
                <div class="carousel-item">
                    <div class="container py-2">
                        <div class="row align-items-center justify-content-center g-4">
                            <div class="col-lg-7 text-center text-lg-start">
                                <span class="badge bg-light mb-2 px-3 py-2 rounded-pill fw-semibold" style="color: #4fb0f0;">
                                    <i class="bi bi-capsule me-1"></i>Edukasi Kesehatan
                                </span>
                                <h1 class="display-5 fw-bold mb-3">Gunakan Obat dengan Tepat, Jaga Kesehatan Setiap Saat</h1>
                                <p class="lead mb-4">
                                    Pastikan kamu memahami dosis, aturan pakai, dan cara penyimpanan obat. Gunakan obat sesuai petunjuk untuk membantu menjaga kesehatanmu.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Layanan Jemput & Sembako -->
                <div class="carousel-item">
                    <div class="container py-2">
                        <div class="row align-items-center justify-content-center g-4">
                            <div class="col-lg-7 text-center text-lg-start">
                                <span class="badge bg-light mb-2 px-3 py-2 rounded-pill fw-semibold" style="color: #4fb0f0;">
                                    <i class="bi bi-clipboard2-check me-1"></i>Layanan Apoteker
                                </span>
                                <h1 class="display-5 fw-bold mb-3">Butuh Obat dengan Resep? Apoteker Siap Membantu</h1>
                                <p class="lead mb-4">
                                    Unggah resep dokter melalui MediQuick. Apoteker kami akan memeriksa resep dan membantu menyiapkan pesanan obat sesuai kebutuhanmu.
                                </p>
                                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-2">
                                    <?php if (!empty($id)){ ?>
                                        <a href="beli_obat.php" class="btn btn-warning btn-lg fw-bold text-dark">Kirim Resep</a>
                                    <?php }else{ ?>
                                        <a href="../../login/regist.php" class="btn btn-warning btn-lg fw-bold text-dark">Daftar Sekarang</a>
                                        <a href="../../login/login.php" class="btn btn-outline-light btn-lg">Masuk Akun</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: 5%;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: 5%;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Berikutnya</span>
            </button>
        </div>
    </header>

    <div class="container my-4" style="max-width: 900px;">
        <div class="d-flex justify-content-between align-items-center mb-3" style="max-width: 800px;">
            <h3 class="mb-0">Daftar Obat</h3>
            <a href="daftar_obat.php" class="text-decoration-none" style="color: grey;">
                View All<i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="obat-slider">
            <?php if(mysqli_num_rows($data_obat) > 0) {?>
                <div class="d-flex flex-nowrap gap-3">
                    <?php while($obat = mysqli_fetch_assoc($data_obat)) {?>
                    <form action="detail_obat.php" method="POST" class="obat-item m-0">
                        <input type="hidden" name="id_obat" value="<?= $obat['id_obat']; ?>">
                        <input type="hidden" name="id_pelanggan" value="<?= $id ?>">
                        <input type="hidden" name="keyPage" value="fromUser">
                        <button type="submit" class="obat-card-button">
                            <div class="description-box" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">
                                <?php if(!empty($obat['gambar_obat'])) {?>
                                <img src="../../asset/uploads/<?= $obat['gambar_obat']; ?>" 
                                    alt="Gambar Obat" 
                                    class="img-fluid" 
                                    style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                                <?php } ?>
                                <h5><?= $obat['nama_obat']; ?></h5>
                            </div>
                        </button>
                    </form>
                    <?php } ?>
                </div>
            <?php }else { ?>
            <div class="text-center">Obat Sedang Kosong</div>
            <?php } ?>
        </div>        
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function hoverOn(el) {
            el.style.backgroundColor = "#1591DC";
            el.style.color = "white";
        }
        function hoverOff(el) {
            el.style.backgroundColor = "whitesmoke";
            el.style.color = "black";
        }
    </script>
</body>
</html>