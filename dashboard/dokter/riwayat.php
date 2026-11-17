<?php
session_start();
include __DIR__ . "/../../backend/connection.php";
$id_konsultasi = $_GET['id'];

$sql_konsultasi = "SELECT * FROM konsultasi WHERE id_konsultasi = '$id_konsultasi'";

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
    <title>Document</title>
</head>
<body>
    <nav class="navbar" style="background-color: #4fb0f0;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">MediQuick</a>
            <a href="rk_dokter.php" class="btn btn-light border" title="kembali"><i class="bi bi-arrow-left"></i></a>
        </div> 
    </nav>

    <div class="container" style="margin-top: 20px;">
        <h2>Riwayat Konsultasi Pasien</h2>
    </div>
    <hr>

    <div class="container mt-3">
        <div class="d-flex flex-column g-3">
            <?php while($row = mysqli_fetch_assoc($data)) {
                $id_pasien = $row['id_pasien'];
                $sql_pasien = "SELECT * FROM pasien WHERE id_pasien = '$id_pasien'";
                $data_pasien = mysqli_query($conn, $sql_pasien);
                $pasien = mysqli_fetch_assoc($data_pasien);
            ?>
            <div class="card shadow-sm" style="max-width: 850px;">
                <a href="#" class="text-decoration-none text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">
                                ID Konsultasi
                            </h6>
                            <h5>
                                <?php echo $row['id_konsultasi']; ?>
                            </h5>
                            <p class="mb-0">
                                Nama Pasien: 
                                <strong><?php echo $pasien['nama']; ?></strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark">
                                <?php echo $row['status']; ?>
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

</body>
</html>