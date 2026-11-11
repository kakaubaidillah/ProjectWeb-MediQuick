<?php 
session_start();
$id = $_GET['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Konsultasi</title>
</head>
<body>
    <div class="container mt-5">
        <h5>Konsultasi</h5>
        <p>Jelaskan Keluhanmu</p>
        <form action="konsul.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $id ?>">
            <div class="mb-3">
                <label class="form-label">Gejala</label>
                <textarea id="gejala" name="gejala" class="form-control" placeholder="Gejala apa yang anda alami?" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Riwayat Alergi</label>
                <input id="alergi" type="text" name="alergi" class="form-control" placeholder="apakah anda punya riwayat alergi?">
            </div>
            <div class="mb-3">
                <label class="form-label">Riwayat Penyakit</label>
                <input id="penyakit" type="text" name="penyakit" class="form-control" placeholder="apakah anda punya riwayat penyakit?">
            </div>
            <div class="mb-3">
                <label class="form-label">Sejak kapan</label>
                <input id="mulai_gejala" type="date" name="mulai_gejala" class="form-control" required>
            </div>
            <button class="btn btn-primary mt-3" type="submit" name="simpan">Kirim</button>
            <a href="user.php?id=<?php echo $id ?>" class="btn btn-secondary mt-3">Batal</a>
        </form>
    </div>
</body>
</html>