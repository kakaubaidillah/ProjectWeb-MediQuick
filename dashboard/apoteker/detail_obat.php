<?php
session_start();
include __DIR__ . "/../../config/connection.php";

// Validasi session login & role apoteker
$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

// if (empty($idapoteker) || $role !== "apoteker") {
//     header("Location: ../../login/login.php");
//     exit();
// }

// Tangkap parameter GET dari inventory.php
$idobat    = $_GET['id'];
$batchobat = $_GET['batch'];

if (empty($idobat) || empty($batchobat)) {
    echo "<script>alert('Parameter obat tidak valid!'); 
          window.location.href='inventory.php';
          </script>";
    exit();
}

// 1. Query data obat & batch spesifik
$query = mysqli_query($conn, "SELECT o.id_obat, o.nama_obat, o.kategori, o.satuan, o.harga, o.butuh_resep, o.gambar_obat, s.id_stok, s.batch, s.jumlah_stok, s.tanggal_masuk, s.tanggal_kadaluarsa
                              FROM obat o
                              JOIN stok_obat s ON o.id_obat = s.id_obat
                              WHERE o.id_obat = '$idobat' AND s.batch = '$batchobat'");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data obat tidak ditemukan!'); window.location.href='inventory.php';</script>";
    exit();
}

// 2. Ambil daftar kategori dinamis dari database + cadangan default
$daftar_kategori = [];
$query_kategori = mysqli_query($conn, "SELECT DISTINCT kategori FROM obat WHERE kategori != '' ORDER BY kategori ASC");
while ($row_kategori = mysqli_fetch_assoc($query_kategori)) {
    $daftar_kategori[] = $row_kategori['kategori'];
}
$daftar_kategori = array_unique($daftar_kategori);

// 3. Ambil daftar satuan dinamis dari database + cadangan default
$daftar_satuan = [];
$query_satuan = mysqli_query($conn, "SELECT DISTINCT satuan FROM obat WHERE satuan != '' ORDER BY satuan ASC");
while ($row_satuan = mysqli_fetch_assoc($query_satuan)) {
    $daftar_satuan[] = $row_satuan['satuan'];
}
$daftar_satuan = array_unique($daftar_satuan);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/logo.jpeg">
    <title>Detail Obat - MediQuick</title>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar" style="background-color: #4fb0f0;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard_apoteker.php">MediQuick</a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Isi Hamburger Menu -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard_apoteker.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_prosesobat.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../../login/proses/access_logout.php"
                                onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container p-2">
        <div class="container-fluid d-flex justify-content-between align-items-center" style="margin: 10px 0px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">Detail Obat</h1>
            <a href="inventory.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4 shadow-sm">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'gagal') { ?>
                        <div class="alert alert-danger">Terjadi kesalahan saat memproses data obat. Silakan coba lagi.</div>
                    <?php }elseif (isset($_GET['status']) && $_GET['status'] == 'sukses') { ?>
                        <div class="alert alert-success">Perubahan data obat berhasil disimpan!</div>
                    <?php } ?>

                    <!-- Form diarahkan ke file proses terpisah -->
                    <form method="POST" action="proses/update_deleteobat.php" enctype="multipart/form-data">
                        <input type="hidden" name="id_obat" value="<?= $data['id_obat'] ?>">
                        <input type="hidden" name="id_stok" value="<?= $data['id_stok'] ?>">
                        <input type="hidden" name="gambar_lama" value="<?= $data['gambar_obat'] ?? '' ?>">

                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Obat</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Obat <span class="text-danger">*</span></label>
                                <input type="text" name="nama_obat" class="form-control" value="<?= $data['nama_obat'] ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status Resep <span class="text-danger">*</span></label>
                                <select name="butuh_resep" class="form-select" required>
                                    <option value="0" <?= ($data['butuh_resep'] == 0) ? 'selected' : ''; ?>>Bebas (Tanpa Resep)</option>
                                    <option value="1" <?= ($data['butuh_resep'] == 1) ? 'selected' : ''; ?>>Wajib Resep Dokter</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="" disabled>-- Pilih Kategori --</option>
                                    <?php foreach ($daftar_kategori as $kat) { ?>
                                        <option value="<?= $kat ?>" <?= ($data['kategori'] == $kat) ? 'selected' : ''; ?>>
                                            <?= $kat ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                                <select name="satuan" class="form-select" required>
                                    <option value="" disabled>-- Pilih Satuan --</option>
                                    <?php foreach ($daftar_satuan as $sat) { ?>
                                        <option value="<?= $sat ?>" <?= ($data['satuan'] == $sat) ? 'selected' : ''; ?>>
                                            <?= $sat; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="100" name="harga" class="form-control" value="<?= $data['harga']; ?>" min="0" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Gambar Obat (Opsional) </label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="file" name="gambar_obat" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Batch & Stok</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Batch <span class="text-danger">*</span></label>
                                <input type="text" name="batch" class="form-control" value="<?= $data['batch'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jumlah Stok <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_stok" class="form-control" value="<?= $data['jumlah_stok'] ?>" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_masuk" class="form-control" value="<?= $data['tanggal_masuk'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kadaluarsa" class="form-control" value="<?= $data['tanggal_kadaluarsa'] ?>" required>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3">
                            <button type="submit" name="hapus" class="btn btn-danger fw-bold px-4 py-2" style="border-radius: 10px;"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus data obat? Tindakan ini tidak dapat dibatalkan.')">
                                <i class="bi bi-trash"></i> Hapus Obat
                            </button>
                            <button type="submit" name="update" class="btn btn-primary fw-bold py-2" style="border-radius: 10px;">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script>

    </script>
</body>

</html>