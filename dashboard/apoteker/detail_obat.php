<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";
// if (empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }

$idobat = $_GET['id'] ?? '';
$batchobat = $_GET['batch'] ?? '';

// if (empty($idobat) || empty($batchobat)) {
//     echo "<script>alert('Parameter obat tidak valid!'); window.location.href='inventory.php';</script>";
//     exit();
// }

// Ambil data obat & batch menggunakan Prepared Statement (Prosedural)
$stmt = mysqli_prepare($conn, "SELECT 
                                o.id_obat,
                                o.nama_obat,
                                o.kategori,
                                o.satuan,
                                o.harga,
                                so.id_stok,
                                so.nomor_batch,
                                so.jumlah_stok,
                                so.tanggal_masuk,
                                so.tanggal_kadaluarsa
                               FROM obat o
                               INNER JOIN stok_obat so ON o.id_obat = so.id_obat
                               WHERE o.id_obat = ? AND so.nomor_batch = ?");

mysqli_stmt_bind_param($stmt, "is", $idobat, $batchobat);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// if (!$data) {
//     echo "<script>alert('Data obat tidak ditemukan!'); window.location.href='inventory.php';</script>";
//     exit();
// }

// Daftar pilihan dropdown agar otomatis terpilih (selected) sesuai data database
$daftar_kategori = [
    "Analgesik",
    "Antibiotik",
    "Antialergi",
    "Antiseptik",
    "Vitamin & Suplemen",
    "Obat batuk dan pilek",
    "Obat saluran pencernaan"
];

$daftar_satuan = [
    "Strip",
    "Tablet",
    "Kapsul",
    "Botol",
    "Tube",
    "Box",
    "Sachet"
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/logo.jpeg">
    <title>Detail Obat - MediQuick</title>
    <style>
        .inv-btn {
            padding: 10px 20px;
            background-color: #4fb0f0;
            color: white;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .inv-btn:hover {
            background-color: #008afc;
            color: white;
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
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
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard_apoteker.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="antrean_prosesobat.php">Proses Resep</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="inventory.php">Inventory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_prosesobat.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../login/proses/access_logout.php" onclick="return confirm('Yakin Untuk Logout?')">Logout</a>
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
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'gagal') : ?>
                        <div class="alert alert-danger">Terjadi kesalahan saat memproses data obat. Silakan coba lagi.</div>
                    <?php endif; ?>

                    <!-- Form diarahkan ke file proses terpisah -->
                    <form method="POST" action="proses/update_deleteobat.php">
                        <!-- Hidden ID Obat & ID Stok untuk acuan update/delete -->
                        <input type="hidden" name="id_obat" value="<?= $data['id_obat'] ?>">
                        <input type="hidden" name="id_stok" value="<?= $data['id_stok'] ?>">

                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Obat</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Obat <span class="text-danger">*</span></label>
                                <input type="text" name="nama_obat" class="form-control" value="<?= $data['nama_obat'] ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="" disabled>-- Pilih Kategori --</option>
                                    <?php foreach ($daftar_kategori as $kat) : ?>
                                        <option value="<?= $kat ?>" <?= ($data['kategori'] == $kat) ? 'selected' : '' ?>>
                                            <?= $kat ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                                <select name="satuan" class="form-select" required>
                                    <option value="" disabled>-- Pilih Satuan --</option>
                                    <?php foreach ($daftar_satuan as $sat) : ?>
                                        <option value="<?= $sat ?>" <?= ($data['satuan'] == $sat) ? 'selected' : '' ?>>
                                            <?= $sat ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="100" name="harga" class="form-control" value="<?= $data['harga'] ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Batch <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_batch" class="form-control" value="<?= $data['nomor_batch'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jumlah Stok <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_stok" class="form-control" value="<?= $data['jumlah_stok'] ?>" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" class="form-control" value="<?= $data['tanggal_masuk'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kadaluarsa" class="form-control" value="<?= $data['tanggal_kadaluarsa'] ?>" required>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3">
                            <button type="submit" name="hapus" class="btn btn-danger fw-bold px-4 py-2" style="border-radius: 10px;" onclick="return confirm('Apakah Anda yakin ingin menghapus data obat ini? Tindakan ini tidak dapat dibatalkan.')"><i class="bi bi-trash"></i> Hapus Obat</button>
                            <button type="submit" name="update" class="inv-btn"><i class="bi bi-save"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.min.js" integrity="sha384-89aa4glufciPv46aKltztWQEmk+Tu9W2RhzXr6VcnUUEub7F8+06RcL2RYLCOHUq" crossorigin="anonymous"></script>
</body>

</html>