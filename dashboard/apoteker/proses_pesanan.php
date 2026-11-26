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

$id_pesanan = $_GET['id'] ?? '';

if (empty($id_pesanan)) {
    echo "<script>alert('ID Pesanan tidak valid!'); window.location.href='dashboard_apoteker.php';</script>";
    exit();
}

// Ambil pesan error jika ada redirect gagal dari proses/eksekusi_pesanan.php
$error_msg = $_SESSION['proses_error'] ?? "";
unset($_SESSION['proses_error']);

// 1. Ambil detail pesanan & data pelanggan menggunakan Prepared Statement
$sql_pesanan = "SELECT p.id_pesanan, p.alamat_pengiriman, p.tanggal_pesanan, p.total_harga, 
                       p.resep_obat, p.status, p.catatan_apoteker,
                       pl.nama_lengkap, pl.no_telp, pl.tanggal_lahir, pl.gender
                FROM pesanan p
                INNER JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                WHERE p.id_pesanan = ?";

$stmt_pesanan = mysqli_prepare($conn, $sql_pesanan);
mysqli_stmt_bind_param($stmt_pesanan, "i", $id_pesanan);
mysqli_stmt_execute($stmt_pesanan);
$result_pesanan = mysqli_stmt_get_result($stmt_pesanan);
$data           = mysqli_fetch_assoc($result_pesanan);
mysqli_stmt_close($stmt_pesanan);

if (!$data) {
    echo "<script>alert('Data pesanan tidak ditemukan!'); window.location.href='dashboard_apoteker.php';</script>";
    exit();
}

// 2. Ambil daftar obat & total stok aktif (belum kedaluwarsa) untuk dropdown pilihan obat menggunakan Prepared Statement
$sql_daftar_obat = "SELECT 
                        o.id_obat, 
                        o.nama_obat, 
                        o.satuan, 
                        o.harga,
                        COALESCE(SUM(CASE WHEN so.tanggal_kadaluarsa >= CURDATE() THEN so.jumlah_stok ELSE 0 END), 0) AS total_stok
                    FROM obat o
                    LEFT JOIN stok_obat so ON o.id_obat = so.id_obat
                    GROUP BY o.id_obat, o.nama_obat, o.satuan, o.harga
                    ORDER BY o.nama_obat ASC";

$stmt_obat = mysqli_prepare($conn, $sql_daftar_obat);
mysqli_stmt_execute($stmt_obat);
$result_obat = mysqli_stmt_get_result($stmt_obat);
$daftar_obat = mysqli_fetch_all($result_obat, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_obat);
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
    <title>Verifikasi Resep & Pesanan - MediQuick</title>
    <style>
        .resep-preview {
            max-height: 450px;
            object-fit: contain;
            cursor: zoom-in;
        }
    </style>
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
                            <a class="nav-link" href="inventory.php">Inventory</a>
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
            <div>
                <h1 class="fw-bold mb-0 fs-1">Verifikasi Resep & Pesanan</h1>
            </div>
            <a href="dashboard_apoteker.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <?php if (!empty($error_msg)) { ?>
                    <div class="alert alert-danger fw-semibold"><?= htmlspecialchars($error_msg); ?></div>
                <?php } ?>

                <!-- KARTU 1: INFORMASI PESANAN & GAMBAR RESEP PELANGGAN -->
                <div class="card p-4 mb-3 ">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h5 class="fw-bold mb-0 text-primary">Pesanan Pelanggan</h5>
                        <span class="badge bg-warning text-dark fs-6 text-uppercase"><?= htmlspecialchars($data['status']); ?></span>
                    </div>

                    <div class="row g-4">
                        <!-- Kolom Kiri: Data Pelanggan & Pengiriman -->
                        <div class="col-lg-6">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">ID Pesanan</label>
                                    <input type="text" class="form-control bg-light fw-bold" value="#<?= htmlspecialchars($data['id_pesanan']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal Pesanan</label>
                                    <input type="text" class="form-control bg-light" value="<?= date('d M Y, H:i', strtotime($data['tanggal_pesanan'])); ?>" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nama Pelanggan</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">No. Telepon</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['no_telp']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Gender / Tgl. Lahir</label>
                                    <input type="text" class="form-control bg-light"
                                        value="<?= ($data['gender'] == 'L' ? 'Laki-laki' : 'Perempuan') . ' / ' . date('d-m-Y', strtotime($data['tanggal_lahir'])); ?>" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Alamat Pengiriman</label>
                                    <textarea class="form-control bg-light" rows="3" readonly><?= htmlspecialchars($data['alamat_pengiriman']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Preview Gambar Resep -->
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold">Foto Resep Dokter dari Pelanggan</label>
                            <div class="border rounded-3 p-3 bg-light text-center">
                                <?php if (!empty($data['resep_obat'])) { ?>
                                    <a href="../../asset/uploads/resep/<?= htmlspecialchars($data['resep_obat']); ?>" target="_blank" title="Klik untuk memperbesar gambar">
                                        <img src="../../asset/uploads/resep/<?= htmlspecialchars($data['resep_obat']); ?>"
                                             alt="Resep Obat Pelanggan"
                                             class="img-fluid rounded border  resep-preview">
                                    </a>
                                    <div class="form-text mt-2">
                                        <i class="bi bi-zoom-in"></i> Klik gambar untuk membuka ukuran penuh di tab baru
                                    </div>
                                <?php } else { ?>
                                    <div class="py-5 text-muted fw-semibold">Pelanggan tidak melampirkan foto resep.</div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: PEMROSESAN RESEP (INPUT OBAT, VERIFIKASI, ATAU TOLAK) -->
                <div class="card p-4 ">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0 text-primary">Pemrosesan Obat Berdasarkan Resep</h5>
                        <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-outline-primary fw-semibold">
                            <i class="bi bi-plus-lg"></i> Tambah Baris Obat
                        </button>
                    </div>

                    <form method="POST" action="proses/eksekusi_pesanan.php" id="form-proses-pesanan">
                        <input type="hidden" name="id_pesanan" value="<?= htmlspecialchars($data['id_pesanan']); ?>">

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle" id="tabel-resep">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45%;">Pilih Obat</th>
                                        <th style="width: 15%;">Jumlah</th>
                                        <th style="width: 18%;">Harga Satuan</th>
                                        <th style="width: 17%;">Subtotal</th>
                                        <th style="width: 5%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-resep">
                                    <tr class="baris-obat">
                                        <td>
                                            <select name="id_obat[]" class="form-select select-obat">
                                                <option value="" data-harga="0" selected disabled>-- Pilih Obat --</option>
                                                <?php foreach ($daftar_obat as $obt) { ?>
                                                    <option value="<?= $obt['id_obat']; ?>"
                                                            data-harga="<?= $obt['harga']; ?>"
                                                            <?= ($obt['total_stok'] <= 0) ? 'disabled' : ''; ?>>
                                                        <?= htmlspecialchars($obt['nama_obat']); ?> (Stok: <?= $obt['total_stok']; ?> <?= htmlspecialchars($obt['satuan']); ?>)
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="jumlah[]" class="form-control input-jumlah" min="1" placeholder="Jml">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control bg-light input-harga" value="Rp 0" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control bg-light fw-semibold input-subtotal" value="Rp 0" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris" title="Hapus Baris">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end fs-5">Total Harga Pesanan:</th>
                                        <th colspan="2" class="fs-5 text-primary" id="tampilan-total">Rp 0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catatan Apoteker (Aturan Pakai / Alasan Penolakan)</label>
                            <textarea name="catatan_apoteker" id="catatan_apoteker" class="form-control" rows="3"
                                placeholder="Tuliskan aturan pakai obat untuk pelanggan (jika diverifikasi) ATAU alasan penolakan resep (wajib diisi jika pesanan ditolak)..."><?= htmlspecialchars($data['catatan_apoteker'] ?? ''); ?></textarea>
                        </div>

                        <!-- TOMBOL AKSI: TOLAK vs VERIFIKASI -->
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
                            <button type="submit" name="tolak_pesanan" id="btn-tolak" class="btn btn-danger fw-bold px-4 py-2" style="border-radius: 10px;">
                                <i class="bi bi-x-circle"></i> Tolak Pesanan
                            </button>
                            <button type="submit" name="verifikasi_pesanan" id="btn-verifikasi" class="btn btn-primary fw-bold py-2" style="border-radius: 10px;">
                                <i class="bi bi-check-circle"></i> Verifikasi
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
        const tbodyResep    = document.getElementById("tbody-resep");
        const btnTambah     = document.getElementById("btn-tambah-baris");
        const tampilanTotal = document.getElementById("tampilan-total");
        const btnTolak      = document.getElementById("btn-tolak");
        const btnVerifikasi = document.getElementById("btn-verifikasi");
        const catatanInput  = document.getElementById("catatan_apoteker");

        function formatRupiah(angka) {
            return "Rp " + Number(angka).toLocaleString("id-ID");
        }

        function hitungTotal() {
            let grandTotal = 0;
            const semuaBaris = tbodyResep.querySelectorAll(".baris-obat");

            semuaBaris.forEach(baris => {
                const selectObat = baris.querySelector(".select-obat");
                const inputJml   = baris.querySelector(".input-jumlah");
                const inputHarga = baris.querySelector(".input-harga");
                const inputSub   = baris.querySelector(".input-subtotal");

                const opsiTerpilih = selectObat.options[selectObat.selectedIndex];
                const harga        = parseFloat(opsiTerpilih?.getAttribute("data-harga") || 0);
                const jumlah       = parseInt(inputJml.value || 0);
                const subtotal     = harga * jumlah;

                inputHarga.value = formatRupiah(harga);
                inputSub.value   = formatRupiah(subtotal);
                grandTotal      += subtotal;
            });

            tampilanTotal.textContent = formatRupiah(grandTotal);
        }

        tbodyResep.addEventListener("change", function (e) {
            if (e.target.classList.contains("select-obat")) {
                hitungTotal();
            }
        });

        tbodyResep.addEventListener("input", function (e) {
            if (e.target.classList.contains("input-jumlah")) {
                hitungTotal();
            }
        });

        btnTambah.addEventListener("click", function () {
            const barisPertama = tbodyResep.querySelector(".baris-obat");
            const barisBaru    = barisPertama.cloneNode(true);

            barisBaru.querySelector(".select-obat").selectedIndex = 0;
            barisBaru.querySelector(".input-jumlah").value        = "";
            barisBaru.querySelector(".input-harga").value         = "Rp 0";
            barisBaru.querySelector(".input-subtotal").value      = "Rp 0";

            tbodyResep.appendChild(barisBaru);
        });

        tbodyResep.addEventListener("click", function (e) {
            const tombolHapus = e.target.closest(".btn-hapus-baris");
            if (tombolHapus) {
                const semuaBaris = tbodyResep.querySelectorAll(".baris-obat");
                if (semuaBaris.length > 1) {
                    tombolHapus.closest(".baris-obat").remove();
                    hitungTotal();
                } else {
                    alert("Minimal harus terdapat 1 baris obat pada tabel.");
                }
            }
        });

        btnTolak.addEventListener("click", function (e) {
            if (catatanInput.value.trim() === "") {
                e.preventDefault();
                alert("Harap isi alasan penolakan pada kolom Catatan Apoteker terlebih dahulu!");
                catatanInput.focus();
                return;
            }
            if (!confirm("Yakin ingin menolak pesanan ini?")) {
                e.preventDefault();
            }
        });

        btnVerifikasi.addEventListener("click", function (e) {
            const semuaSelect = tbodyResep.querySelectorAll(".select-obat");
            const semuaJumlah = tbodyResep.querySelectorAll(".input-jumlah");

            for (let i = 0; i < semuaSelect.length; i++) {
                if (semuaSelect[i].value === "" || Number(semuaJumlah[i].value) <= 0) {
                    e.preventDefault();
                    alert("Pastikan semua baris obat dan jumlahnya telah diisi dengan benar sebelum memverifikasi!");
                    return;
                }
            }

            if (!confirm("Verifikasi pesanan ini? Stok obat akan dikurangi dan tagihan akan dikirim ke pelanggan.")) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>