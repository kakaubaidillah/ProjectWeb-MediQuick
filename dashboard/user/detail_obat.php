<?php
session_start();
include __DIR__ . "/../../config/connect.php";
$id_obat = $_POST['id_obat']?? "";
$id_pelanggan = $_POST['id_pelanggan']?? "";
$key = $_POST['keyPage']?? "";

$id_obat = (int) $id_obat;
$stmt = mysqli_prepare($conn, "SELECT o.*, COALESCE((SELECT SUM(s.jumlah_stok) FROM stok_obat s WHERE s.id_obat = o.id_obat AND s.tanggal_kadaluarsa >= CURDATE()), 0) AS total_stok FROM obat o WHERE o.id_obat = ?");
mysqli_stmt_bind_param($stmt, "i", $id_obat);
mysqli_stmt_execute($stmt);
$obat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$obat) {
    header("Location: daftar_obat.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <title>Detail Obat - MediQuick</title>
</head>
<body>
    <nav class="navbar" style="background-color: #2C5EAD">
        <div class="container">
            <a class="navbar-brand">
                <h3 class="fw-bold text-white"><img src="../../asset/img/logo.png" alt="logo" width="40" height="40">MediQuick</h3>
            </a>
            <button type="button" onclick="kembali('<?= $key ?>', '<?= $id_pelanggan ?>')" class="btn btn-outline-secondary" style="background-color: white;"><i class="bi bi-arrow-left" style="color: black;"></i></button>
        </div> 
    </nav>

    <div class="container" style="margin-top: 20px;">
        <h2>Detail Obat</h2>
    </div>
    <hr>

    <div class="container mt-5">
        <div class="row">
            <div class="col-3 mb-4">
                <?php if(!empty($obat['gambar_obat'])) {?>
                    <img src="../../asset/uploads/obat/<?= $obat['gambar_obat'] ?>" alt="image" style="width: -webkit-fill-available;">
                <?php } ?>
            </div>
            <div class="col">
                <div class="card shadow-sm" style="max-width: 800px; max-height: 800px;">
                    <div class="card-body">
                        <div class="align-items-center gap-5 mb-3">
                            <h3 class="mb-3">Nama Obat: <?= htmlspecialchars($obat['nama_obat']); ?></h3>
                            <h3 class="mb-3">Kategori: <?= htmlspecialchars($obat['kategori']); ?></h3>
                            <h3 class="mb-3">Satuan: <?= htmlspecialchars($obat['satuan']); ?></h3>
                            <h3 class="mb-3">Harga: Rp <?= number_format((float) $obat['harga'], 0, ',', '.'); ?></h3>
                            <h3 class="mb-3">Stok: <?= (int) $obat['total_stok'] > 0 ? (int) $obat['total_stok'] . ' ' . htmlspecialchars($obat['satuan']) : 'Habis'; ?></h3>
                        </div>
                        <div class="btn-group mb-3" role="group" aria-label="Color selection">
                            <label class="btn" style="
                                                background-color:#4fb0f0;
                                                outline: #4fb0f0 solid;
                                                color: white;">
                            Ket:</label>
                            <label class="btn" style="
                                                background-color: white;
                                                outline: #4fb0f0 solid;">
                            <?php if($obat['butuh_resep'] == 1){?>
                            <p>Butuh Resep Dokter</p>
                            <?php }else {?>
                            <p>Tanpa Resep Dokter</p>
                            <?php } ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function kembali(key, id_pelanggan){
            const form = document.createElement('form');
            form.method = 'POST';
            if(key == 'fromUser'){
                form.action = 'index.php';
            }else if(key == 'fromDaftar'){
                form.action = 'daftar_obat.php';
            }else{
                alert('Error: key undifined!');
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id_pelanggan';
            input.value = id_pelanggan;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>

</body>
</html>