<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap icon CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .description-box{
            background-color: whitesmoke;
            color:black;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        .description-box p {font-size: 0.8rem; margin-bottom: 0;}

        .description-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin: 10px auto;
            display: block;
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

    <div class="container" style="margin-top: 100px;">
        <h1 class="text-center mb-5">WELCOME!</h1>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Impedit sequi sunt molestias ipsa itaque praesentium adipisci hic libero eligendi accusamus numquam officia eum recusandae veritatis modi, iure repellendus explicabo dolor.</p>
    </div>

    <div class="container my-4" style="max-width: 900px;">
        <div class="row g-3" style="max-width: 800px;">
            <div class="col-12 col-md-4">
                <div class="description-box" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">
                    <a><button class="btn btn-success"><i class="bi bi-pencil-square"></i></button></a>
                    <a><button class="btn btn-danger"><i class="bi bi-trash3-fill"></i></button></a>
                    <img src="../asset/img/images.jpg" alt="picture 1" class="flex-shrink-0" style="max-width: 100px;">
                    <h5>Capture your thoughts, one note at a time.</h5>
                    <p>Express how you feel, record your everyday moments, and clear your mind. Happy, sad, or somewhere in between, just put it into words.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="description-box" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">
                    <a>
                        <button class="btn btn-success"><i class="bi bi-pencil-square"></i></button>
                    </a>
                    <a>
                        <button class="btn btn-danger"><i class="bi bi-trash3-fill"></i></button>
                    </a>
                    <img src="../asset/img/images1.jpg" alt="picture 2" class="flex-shrink-0" style="max-width: 100px;">
                    <h5>Capture your thoughts, one note at a time.</h5>
                    <p>Express how you feel, record your everyday moments, and clear your mind. Happy, sad, or somewhere in between, just put it into words.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="description-box" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">
                    <a>
                        <button class="btn btn-success"><i class="bi bi-pencil-square"></i></button>
                    </a>
                    <a>
                        <button class="btn btn-danger"><i class="bi bi-trash3-fill"></i></button>
                    </a>
                    <img src="../asset/img/images2.jpg" alt="picture 3" class="flex-shrink-0" style="max-width: 100px;">
                    <h5>Capture your thoughts, one note at a time.</h5>
                    <p>Express how you feel, record your everyday moments, and clear your mind. Happy, sad, or somewhere in between, just put it into words.</p>
                </div>
            </div>
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