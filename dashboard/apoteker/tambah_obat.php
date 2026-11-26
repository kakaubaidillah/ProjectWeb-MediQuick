<?php
session_start();
include __DIR__ . "/../../config/connect.php";

// Validasi session login & role apoteker
$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    header("Location: ../../login/login.php");
    exit();
}

// Ambil pesan error jika ada session error dari proses sebelumnya
if(isset($_SESSION['tambah_error'])) {
    $pesan_error = $_SESSION['tambah_error'] ?? "";
    unset($_SESSION['tambah_error']);
}

// 1. Ambil daftar kategori dinamis dari database + cadangan default menggunakan Prepared Statement
$daftar_kategori = ["Analgesik", "Antibiotik", "Antialergi", "Antiseptik", "Vitamin & Suplemen", "Obat batuk dan pilek", "Obat saluran pencernaan"];
$stmt_kat = mysqli_prepare($conn, "SELECT DISTINCT kategori FROM obat WHERE kategori != '' ORDER BY kategori ASC");
mysqli_stmt_execute($stmt_kat);
$result_kat = mysqli_stmt_get_result($stmt_kat);
while ($row_kat = mysqli_fetch_assoc($result_kat)) {
    $daftar_kategori[] = $row_kat['kategori'];
}
mysqli_stmt_close($stmt_kat);
$daftar_kategori = array_unique($daftar_kategori);

// 2. Ambil daftar satuan dinamis dari database + cadangan default menggunakan Prepared Statement
$daftar_satuan = ["Strip", "Tablet", "Kapsul", "Botol", "Tube", "Box", "Sachet", "Pcs"];
$stmt_sat = mysqli_prepare($conn, "SELECT DISTINCT satuan FROM obat WHERE satuan != '' ORDER BY satuan ASC");
mysqli_stmt_execute($stmt_sat);
$result_sat = mysqli_stmt_get_result($stmt_sat);
while ($row_sat = mysqli_fetch_assoc($result_sat)) {
    $daftar_satuan[] = $row_sat['satuan'];
}
mysqli_stmt_close($stmt_sat);
$daftar_satuan = array_unique($daftar_satuan);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/logo.jpeg">
    <title>Tambah Obat - MediQuick</title>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar" style="background-color: #2C5EAD;">
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
                            <a class="nav-link" href="riwayat_pesanan.php">Riwayat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger fw-bold" href="../../login/proses/access_logout.php"
                                onclick="return confirm('Yakin Untuk Logout?')">LOGOUT</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container p-2">
        <div class="container-fluid d-flex justify-content-between align-items-center" style="margin: 10px 0px; padding: 12px 12px;">
            <h1 class="fw-bold mb-0 fs-1">Tambah Obat</h1>
            <a href="inventory.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4 shadow-sm">
                    <?php if (!empty($pesan_error)) { ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($pesan_error); ?></div>
                    <?php } ?>

                    <form method="POST" action="proses/eksekusi_tambah_obat.php" enctype="multipart/form-data" id="form-tambah-obat">
                        <!-- Bagian 1: Data Induk Obat -->
                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Obat</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama Obat <span class="text-danger">*</span></label>
                                <input type="text" name="nama_obat" class="form-control" placeholder="Contoh: Paracetamol 500mg" required>
                                <div class="form-text">Jika nama obat sudah pernah terdaftar, sistem akan otomatis menambahkan batch baru ke obat tersebut.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status Resep <span class="text-danger">*</span></label>
                                <select name="butuh_resep" class="form-select" required>
                                    <option value="0" selected>Bebas (Tanpa Resep)</option>
                                    <option value="1">Wajib Resep Dokter</option>
                                </select>
                            </div>
                            
                            <!-- Dropdown Kategori Dinamis -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Kategori --</option>
                                    <?php foreach ($daftar_kategori as $kat) { ?>
                                        <option value="<?= htmlspecialchars($kat); ?>"><?= htmlspecialchars($kat); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Dropdown Satuan Dinamis -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                                <select name="satuan" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Satuan --</option>
                                    <?php foreach ($daftar_satuan as $sat) { ?>
                                        <option value="<?= htmlspecialchars($sat); ?>"><?= htmlspecialchars($sat); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga" class="form-control" min="0" step="100" placeholder="Contoh: 15000" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Gambar Obat (Opsional)</label>
                                <input type="file" name="gambar_obat" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <!-- Bagian 2: Data Stok & Batch -->
                        <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Batch & Stok</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Batch <span class="text-danger">*</span></label>
                                <input type="text" name="batch" class="form-control" placeholder="Contoh: BTH-2026-001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jumlah Stok Masuk <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_stok" class="form-control" min="1" placeholder="Contoh: 50" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kadaluarsa" id="tanggal_kadaluarsa" class="form-control" required>
                                <div id="error-kadaluarsa" class="text-danger small mt-1"></div>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <button type="reset" class="btn btn-danger border fw-semibold px-4 py-2" style="border-radius: 10px;">Reset</button>
                            <button type="submit" name="simpan" class="btn btn-primary fw-bold py-2" style="border-radius: 10px;"><i class="bi bi-plus"></i> Simpan Obat</button>
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
        // Validasi client-side untuk memastikan tanggal kadaluarsa lebih dari tanggal masuk (Modul 3)
        const formTambah = document.getElementById("form-tambah-obat");
        const tglMasuk = document.getElementById("tanggal_masuk");
        const tglKadaluarsa = document.getElementById("tanggal_kadaluarsa");
        const errorKadaluarsa = document.getElementById("error-kadaluarsa");

        formTambah.addEventListener("submit", function(e) {
            if (tglKadaluarsa.value <= tglMasuk.value) {
                e.preventDefault();
                errorKadaluarsa.innerHTML = "Tanggal kadaluarsa harus lebih dari tanggal masuk.";
            } else {
                errorKadaluarsa.innerHTML = "";
            }
        });
    </script>
</body>

</html>