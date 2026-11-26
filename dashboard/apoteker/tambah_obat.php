<?php
session_start();
include __DIR__ . "/../../config/connection.php";

// Validasi session login & role apoteker
$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

// if (empty($idapoteker) || $role !== "apoteker") {
//     echo "<script>alert('Mohon Login terlebih dahulu!'); 
//                 window.location.href='../../login/login.php';
//               </script>";
//     exit();
// }


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
    <title>Tambah Obat - MediQuick</title>
    <style>
        .inv-btn {
            padding: 10px 24px;
            background-color: #4fb0f0;
            color: white;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease;
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
            <h1 class="fw-bold mb-0 fs-1">Tambah Obat</h1>
            <a href="inventory.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <div class="card p-4 shadow-sm">
                    <?php if (!empty($pesan_error)) : ?>
                        <div class="alert alert-danger"><?= $pesan_error ?></div>
                    <?php endif; ?>

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
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Kategori --</option>
                                    <option value="Analgesik">Analgesik</option>
                                    <option value="Antibiotik">Antibiotik</option>
                                    <option value="Antialergi">Antialergi</option>
                                    <option value="Antiseptik">Antiseptik</option>
                                    <option value="Vitamin & Suplemen">Vitamin & Suplemen</option>
                                    <option value="Obat batuk dan pilek">Obat batuk dan pilek</option>
                                    <option value="Obat saluran pencernaan">Obat saluran pencernaan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                                <select name="satuan" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Satuan --</option>
                                    <option value="Strip">Strip</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Kapsul">Kapsul</option>
                                    <option value="Botol">Botol</option>
                                    <option value="Tube">Tube</option>
                                    <option value="Box">Box</option>
                                    <option value="Sachet">Sachet</option>
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