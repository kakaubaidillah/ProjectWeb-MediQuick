<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Tambah Konten</title>
</head>
<body>

    <div class="container mt-5">
        <h5>Tambah Konten Dasbord</h5>
        <form action="/../../backend/create_konten.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input id="judul" type="text" name="judul" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar</label>
                <input id="gambar" type="file" name="gambar" class="form-control" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <button class="btn btn-primary mt-3" type="submit" name="simpan">Simpan</button>
            <a href="admin_form.php" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
    
</body>
</html>