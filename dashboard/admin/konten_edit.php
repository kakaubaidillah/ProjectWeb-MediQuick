<?php
session_start();
include __DIR__ . "/../../backend/connection.php";
$id = $_GET['id'];

$sql = "SELECT * FROM konten_dashboard WHERE id_konten = '$id'";

$data = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Edit Konten</title>
</head>
<body>
    <div class="container mt-5">
        <h5>Edit Konten Dasbord</h5>
        <form action="../../backend/edit_konten.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id; ?>">
            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input id="judul" type="text" name="judul" class="form-control" value="<?= $row['judul']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" required><?= $row['deskripsi']; ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar</label>
                <input id="gambar" type="file" name="images" class="form-control">
            </div>
            <button class="btn btn-primary mt-3" type="submit" name="update">Update</button>
            <a href="admin_form.php" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
</body>
</html>