<?php
session_start();
include __DIR__ . "/../../config/connection.php";
$id_konsultasi = $_GET['id'];

$sql_konsultasi = "SELECT * FROM konsultasi WHERE id_konsultasi = '$id_konsultasi'";

$data = mysqli_query($conn, $sql_konsultasi);

$jumlah = mysqli_num_rows($data);
$row = mysqli_fetch_assoc($data);

$id_pasien = $row['id_pasien'];
$sql_pasien = "SELECT * FROM pasien WHERE id_pasien = '$id_pasien'";
$data_pasien = mysqli_query($conn, $sql_pasien);
$pasien = mysqli_fetch_assoc($data_pasien);
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
            <a href="riwayat_konsultasi.php?id=<?php echo $id_pasien ?>" class="btn btn-light border" title="kembali"><i class="bi bi-arrow-left"></i></a>
        </div> 
    </nav>

    <div class="container" style="margin-top: 20px;">
        <h2>Riwayat Konsultasi Pasien</h2>
    </div>
    <hr>

    <div class="container mt-5">
        <div class="row">
            <div class="col-3 mb-4">
                <?php if(!empty($row['gambar'])) {?>
                    <img src="../../asset/uploads/<?= $row['gambar'] ?>" alt="image" style="width: -webkit-fill-available;">
                <?php } ?>
            </div>
            <div class="col">
                <div class="card shadow-sm" style="max-width: 800px; max-height: 800px;">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-5 mb-3">
                            <h3>ID  : <?php echo $id_konsultasi; ?></h3>
                            <h3>Nama: <?php echo $pasien['nama']; ?></h3>
                        </div>
                        <div class="btn-group mb-3" role="group" aria-label="Color selection">
                            <label class="btn" style="
                                                background-color:#4fb0f0;
                                                outline: #4fb0f0 solid;
                                                color: white;">
                            Status</label>
                            <label class="btn" style="
                                                background-color: white;
                                                outline: #4fb0f0 solid;">
                            <?php echo $row['status']; ?>
                            </label>
                        </div>
                        <hr>
                        <div>
                            <h4 class="mb-2">Diagnosis: <?php echo $row['diagnosis']; ?></h4>
                            <pre class="mb-4" style="white-space:pre-wrap; font-size: 18px;">Catatan: <?php echo $row['catatan_dokter']; ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>