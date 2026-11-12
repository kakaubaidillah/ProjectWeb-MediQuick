<?php
session_start();
$id = $_GET['id'];

include __DIR__. "/../../backend/connection.php";
//data konsultasi
$data_konsul = mysqli_query($conn, "SELECT * FROM konsultasi WHERE id_konsultasi = '$id'");
$konsul = mysqli_fetch_assoc($data_konsul);

//data pasien
$id_pasien = $konsul['id_pasien'];
$data_pasien = mysqli_query($conn, "SELECT * FROM pasien WHERE id_pasien = '$id_pasien'");
$pasien =mysqli_fetch_assoc($data_pasien);

//data dokter
$data_dokter = mysqli_query($conn, "SELECT * FROM admin WHERE role = 'dokter'");
$dokter = mysqli_fetch_assoc($data_dokter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>MediQuick-Dokter</title>
</head>
<body>
    <div class="container mt-5">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Nama</th>
                    <th>Tanggal Lahir</th>
                    <th>Gejala</th>
                    <th>Datang Gejala</th>
                    <th>Riwayat Alergi</th>
                    <th>Riwayat Penyakit</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <tr>
                    <td><?php echo $pasien['nama']; ?></td>
                    <td><?php echo $pasien['tanggal_lahir']; ?></td>
                    <td><?php echo $konsul['gejala']; ?></td>
                    <td><?php echo $konsul['sejak_kapan']; ?></td>
                    <td><?php echo $konsul['riwayat_alergi']; ?></td>
                    <td><?php echo $konsul['riwayat_alergi']; ?></td>
                </tr>
            </tbody>
        </table>
        <hr>
        <form action="konsul_pasien.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $id ?>">
            <label>Diagnosis</label>
            <input type="text" name="diagnosa" id="diagnosa" class="form-control" required>
            <label>Catatan</label>
            <textarea id="catatan" name="catatan" class="form-control" required></textarea>
            <button class="btn btn-primary mt-3" type="submit" name="update">Kirim</button>
            <a href="dashboard_dokter.php?id=<?php echo $dokter['id_admin']; ?>" class="btn btn-secondary mt-3">Batal</a>
        </form>
    </div>
</body>
</html>