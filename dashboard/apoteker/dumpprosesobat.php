<?php
session_start();
include __DIR__ . "/../../config/connection.php";

$idapoteker = $_SESSION['id'] ?? "";

// if (empty($idapoteker)) {
//     header("location: ../../login/login.php");
//     exit();
// }

$id_konsultasi = $_GET['id'] ?? '';

if (empty($id_konsultasi)) {
    echo "<script>alert('ID Konsultasi tidak valid!'); window.location.href='antrean_prosesobat.php';</script>";
    exit();
}


// ==========================================================
// PROSES SIMPAN RESEP, DETAIL RESEP, POTONG STOK & UPDATE KONSULTASI
// ==========================================================
if (isset($_POST['simpan_resep'])) {
    $catatan_resep = mysqli_real_escape_string($conn, trim($_POST['catatan_resep'] ?? ''));
    
    // Array input dari baris-baris obat
    $arr_id_obat      = $_POST['id_obat'] ?? [];
    $arr_jumlah       = $_POST['jumlah'] ?? [];
    $arr_dosis        = $_POST['dosis'] ?? [];
    $arr_aturan_pakai = $_POST['aturan_pakai'] ?? [];
    $arr_catatan_item = $_POST['catatan_item'] ?? [];

    if (empty($arr_id_obat)) {
        $error_msg = "Minimal harus memilih 1 obat untuk membuat resep!";
    } else {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($conn);

        try {
            // 1. Insert ke tabel `resep`
            $query_resep = "INSERT INTO resep (id_konsultasi, id_apoteker, tanggal_resep, status_resep, catatan) 
                            VALUES ('$id_konsultasi', '$idapoteker', NOW(), 'Selesai', '$catatan_resep')";
            mysqli_query($conn, $query_resep);
            $id_resep = mysqli_insert_id($conn);

            // 2. Loop setiap obat untuk disimpan ke `detail_resep` dan dikurangi stoknya
            for ($i = 0; $i < count($arr_id_obat); $i++) {
                $id_obat      = $arr_id_obat[$i];
                $jumlah       = (int) $arr_jumlah[$i];
                $dosis        = mysqli_real_escape_string($conn, trim($arr_dosis[$i]));
                $aturan_pakai = mysqli_real_escape_string($conn, trim($arr_aturan_pakai[$i]));
                $catatan_item = mysqli_real_escape_string($conn, trim($arr_catatan_item[$i] ?? ''));

                if ($jumlah <= 0) {
                    throw new Exception("Jumlah obat harus lebih dari 0.");
                }

                // Cek total stok tersedia untuk obat ini (yang belum expired / masih ada stok)
                $q_cek_stok = mysqli_query($conn, "SELECT SUM(jumlah_stok) AS total_tersedia 
                                                   FROM stok_obat 
                                                   WHERE id_obat = '$id_obat' AND jumlah_stok > 0");
                $d_stok = mysqli_fetch_assoc($q_cek_stok);
                $stok_tersedia = (int) ($d_stok['total_tersedia'] ?? 0);

                if ($stok_tersedia < $jumlah) {
                    throw new Exception("Stok salah satu obat tidak mencukupi (Tersedia: $stok_tersedia, Diminta: $jumlah).");
                }

                // Insert ke tabel `detail_resep`
                $query_detail_resep = "INSERT INTO detail_resep (id_resep, id_obat, jumlah, dosis, aturan_pakai, catatan) 
                                       VALUES ('$id_resep', '$id_obat', '$jumlah', '$dosis', '$aturan_pakai', '$catatan_item')";
                mysqli_query($conn, $query_detail_resep);

                // Potong stok dari tabel `stok_obat` urut tanggal kadaluarsa terdekat (FEFO)
                $sisa_potong = $jumlah;
                $q_batches = mysqli_query($conn, "SELECT id_stok, jumlah_stok 
                                                  FROM stok_obat 
                                                  WHERE id_obat = '$id_obat' AND jumlah_stok > 0 
                                                  ORDER BY tanggal_kadaluarsa ASC");
                
                while ($batch = mysqli_fetch_assoc($q_batches)) {
                    if ($sisa_potong <= 0) break;

                    $id_stok_batch = $batch['id_stok'];
                    $stok_batch    = (int) $batch['jumlah_stok'];

                    if ($stok_batch >= $sisa_potong) {
                        $stok_baru = $stok_batch - $sisa_potong;
                        mysqli_query($conn, "UPDATE stok_obat SET jumlah_stok = '$stok_baru' WHERE id_stok = '$id_stok_batch'");
                        $sisa_potong = 0;
                    } else {
                        mysqli_query($conn, "UPDATE stok_obat SET jumlah_stok = 0 WHERE id_stok = '$id_stok_batch'");
                        $sisa_potong -= $stok_batch;
                    }
                }
            }

            // 3. Update status tabel `konsultasi` menjadi 'Selesai'
            $query_update_konsul = "UPDATE konsultasi 
                                    SET status = 'Selesai', tanggal_selesai = NOW() 
                                    WHERE id_konsultasi = '$id_konsultasi'";
            mysqli_query($conn, $query_update_konsul);

            mysqli_commit($conn);

            echo "<script>
                    alert('Resep berhasil dibuat dan stok obat telah diperbarui!');
                    window.location.href = 'antrean_prosesobat.php';
                  </script>";
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_msg = "Gagal memproses resep: " . $e->getMessage();
        }
    }
}

// ==========================================================
// AMBIL DETAIL KONSULTASI
// ==========================================================
$query_detail = mysqli_query($conn, "SELECT 
                                        k.id_konsultasi,
                                        p.nama AS nama_pasien,
                                        d.nama AS nama_dokter,
                                        k.gejala,
                                        k.sejak_kapan,
                                        k.riwayat_alergi,
                                        k.riwayat_penyakit,
                                        k.diagnosis,
                                        k.catatan_dokter,
                                        k.tanggal_konsultasi
                                     FROM konsultasi k
                                     INNER JOIN pasien p ON k.id_pasien = p.id_pasien
                                     INNER JOIN admin d ON k.id_dokter = d.id_admin
                                     WHERE k.id_konsultasi = '$id_konsultasi'");

$data = mysqli_fetch_assoc($query_detail);

if (!$data) {
    echo "<script>alert('Data konsultasi tidak ditemukan!'); window.location.href='antrean_prosesobat.php';</script>";
    exit();
}

// ==========================================================
// AMBIL DAFTAR OBAT & TOTAL STOK UNTUK DROPDOWN PILIHAN OBAT
// ==========================================================
$query_daftar_obat = mysqli_query($conn, "SELECT 
                                            o.id_obat, 
                                            o.nama_obat, 
                                            o.satuan, 
                                            o.harga,
                                            COALESCE(SUM(so.jumlah_stok), 0) AS total_stok
                                          FROM obat o
                                          LEFT JOIN stok_obat so ON o.id_obat = so.id_obat
                                          GROUP BY o.id_obat, o.nama_obat, o.satuan, o.harga
                                          ORDER BY o.nama_obat ASC");
$daftar_obat = mysqli_fetch_all($query_daftar_obat, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <title>Detail Proses Obat - MediQuick</title>
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
            transition: background-color 0.2s;
        }
        .inv-btn:hover {
            background-color: #008afc;
            color: white;
        }
    </style>
</head>

<body>
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
                            <a class="nav-link active" href="antrean_prosesobat.php">Proses Resep</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">Inventory</a>
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
            <h1 class="fw-bold mb-0 fs-1">Detail Konsultasi & Pembuatan Resep</h1>
            <a href="antrean_prosesobat.php" class="btn btn-outline-secondary fw-semibold">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <!-- KARTU 1: DETAIL DATA KONSULTASI (READONLY) -->
                <div class="card p-4 mb-3">
                    <?php if (isset($error_msg)) : ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Informasi Konsultasi</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ID Konsultasi</label>
                            <input type="text" class="form-control bg-light" value="<?= $data['id_konsultasi'] ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tanggal Konsultasi</label>
                            <input type="text" class="form-control bg-light" value="<?= date('d-m-Y H:i', strtotime($data['tanggal_konsultasi'])) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Keluhan Sejak Kapan</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['sejak_kapan'] ?: '-') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Pasien</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['nama_pasien']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dokter yang Menangani</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['nama_dokter']) ?>" readonly>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Keluhan & Riwayat Medis Pasien</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Gejala yang Dialami</label>
                            <textarea class="form-control bg-light" rows="2" readonly><?= htmlspecialchars($data['gejala']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Riwayat Alergi</label>
                            <textarea class="form-control bg-light" rows="2" readonly><?= htmlspecialchars($data['riwayat_alergi'] ?: 'Tidak ada') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Riwayat Penyakit</label>
                            <textarea class="form-control bg-light" rows="2" readonly><?= htmlspecialchars($data['riwayat_penyakit'] ?: 'Tidak ada') ?></textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">Hasil Pemeriksaan & Resep Dokter</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Diagnosis Dokter</label>
                            <textarea class="form-control bg-light" rows="2" readonly><?= htmlspecialchars($data['diagnosis'] ?: '-') ?></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Catatan Dokter (Rujukan Resep Obat)</label>
                            <textarea class="form-control bg-warning-subtle border-warning fw-semibold" rows="3" readonly><?= htmlspecialchars($data['catatan_dokter'] ?: '-') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: FORM INPUT RESEP APOTEKER -->
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0 text-primary">Form Pembuatan Resep Obat</h5>
                        <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-outline-primary fw-semibold">
                            <i class="bi bi-plus-lg"></i> Tambah Baris Obat
                        </button>
                    </div>

                    <form method="POST" action="">
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle" id="tabel-resep">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 28%;">Pilih Obat <span class="text-danger">*</span></th>
                                        <th style="width: 12%;">Jumlah <span class="text-danger">*</span></th>
                                        <th style="width: 18%;">Dosis <span class="text-danger">*</span></th>
                                        <th style="width: 22%;">Aturan Pakai <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">Catatan Item</th>
                                        <th style="width: 5%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-resep">
                                    <tr class="baris-obat">
                                        <td>
                                            <select name="id_obat[]" class="form-select" required>
                                                <option value="" selected disabled>-- Pilih Obat --</option>
                                                <?php foreach ($daftar_obat as $obt) { ?>
                                                    <option value="<?= $obt['id_obat'] ?>" <?= ($obt['total_stok'] <= 0) ? 'disabled' : '' ?>>
                                                        <?= htmlspecialchars($obt['nama_obat']) ?> (Stok: <?= $obt['total_stok'] ?> <?= htmlspecialchars($obt['satuan']) ?>)
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="jumlah[]" class="form-control" min="1" placeholder="Jml" required>
                                        </td>
                                        <td>
                                            <input type="text" name="dosis[]" class="form-control" placeholder="Cth: 500mg / 1 tab" required>
                                        </td>
                                        <td>
                                            <input type="text" name="aturan_pakai[]" class="form-control" placeholder="Cth: 3x1 sesudah makan" required>
                                        </td>
                                        <td>
                                            <input type="text" name="catatan_item[]" class="form-control" placeholder="Opsional">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris" title="Hapus Baris">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catatan Tambahan Resep (Apoteker)</label>
                            <textarea name="catatan_resep" class="form-control" rows="2" placeholder="Catatan umum untuk pasien terkait pengambilan/penggunaan obat (opsional)"></textarea>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" name="simpan_resep" class="inv-btn" onclick="return confirm('Simpan resep dan tandai konsultasi ini sebagai Selesai?')">
                                <i class="bi bi-check-circle"></i> Simpan Resep & Selesaikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.min.js" integrity="sha384-89aa4glufciPv46aKltztWQEmk+Tu9W2RhzXr6VcnUUEub7F8+06RcL2RYLCOHUq" crossorigin="anonymous"></script>
    <script>
        const tbodyResep = document.getElementById("tbody-resep");
        const btnTambahBaris = document.getElementById("btn-tambah-baris");

        // Menambah baris input obat baru dengan menduplikasi baris pertama
        btnTambahBaris.addEventListener("click", function () {
            const barisPertama = tbodyResep.querySelector(".baris-obat");
            const barisBaru = barisPertama.cloneNode(true);

            // Kosongkan seluruh nilai input pada baris baru
            barisBaru.querySelector("select").selectedIndex = 0;
            barisBaru.querySelectorAll("input").forEach(input => input.value = "");

            tbodyResep.appendChild(barisBaru);
        });

        // Menghapus baris input obat (minimal menyisakan 1 baris)
        tbodyResep.addEventListener("click", function (e) {
            const tombolHapus = e.target.closest(".btn-hapus-baris");
            if (tombolHapus) {
                const semuaBaris = tbodyResep.querySelectorAll(".baris-obat");
                if (semuaBaris.length > 1) {
                    tombolHapus.closest(".baris-obat").remove();
                } else {
                    alert("Minimal harus terdapat 1 baris obat pada resep.");
                }
            }
        });
    </script>
</body>

</html>