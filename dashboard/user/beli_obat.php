<?php 
session_start();
include __DIR__. "/../../config/connect.php";
$id_pelanggan = $_SESSION['id_pelanggan']?? "";

if(empty($id_pelanggan)){
    echo "<script>alert('Silakan login terlebih dahulu!')</script>";
    header("Location: ../../login/login.php");
    exit();
}

//--------Daftar Obat Bebas
$sql_obat = "
    SELECT 
        o.id_obat,
        o.nama_obat,
        o.kategori,
        o.satuan,
        o.harga,
        COALESCE(SUM(s.jumlah_stok), 0) AS total_stok
    FROM obat o
    LEFT JOIN stok_obat s
        ON o.id_obat = s.id_obat
        AND s.tanggal_kadaluarsa >= CURDATE()
    WHERE o.butuh_resep = 0
    GROUP BY 
        o.id_obat,
        o.nama_obat,
        o.kategori,
        o.satuan,
        o.harga
    HAVING total_stok > 0
    ORDER BY o.kategori ASC, o.nama_obat ASC
";

$data_obat = mysqli_query($conn, $sql_obat);
$daftar_obat = [];
$daftar_kategori = [];

if ($data_obat) {
    while ($row = mysqli_fetch_assoc($data_obat)) {
        $daftar_obat[] = $row;
        if(!in_array($row['kategori'], $daftar_kategori)){
            $daftar_kategori[] = $row['kategori'];
        }
    }
}

$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$pesanan_edit = null;
$detail_edit = [];

if($edit_id > 0){
    $stmt_cek = mysqli_prepare($conn, "SELECT * FROM pesanan WHERE id_pesanan = ? AND id_pelanggan = ? AND status = 'belum bayar' AND resep_obat IS NULL");
    mysqli_stmt_bind_param($stmt_cek, "ii", $edit_id, $id_pelanggan);
    mysqli_stmt_execute($stmt_cek);
    $res_cek = mysqli_stmt_get_result($stmt_cek);
    $pesanan_edit = mysqli_fetch_assoc($res_cek);

    if($pesanan_edit){
        $stmt_det = mysqli_prepare($conn, "SELECT dp.id_obat, dp.jumlah, o.kategori, o.harga
                                            FROM detail_pesanan dp
                                            JOIN obat o ON dp.id_obat = o.id_obat
                                            WHERE dp.id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_det, "i", $edit_id);
        mysqli_stmt_execute($stmt_det);
        $res_det = mysqli_stmt_get_result($stmt_det);
        while($d = mysqli_fetch_assoc($res_det)){
            $detail_edit[] = $d;
        }
    }else{
        $edit_id = 0;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>MediQuick</title>
    <style>
        .inv-btn {
            padding: 10px 24px;
            background-color: #2C5EAD;
            color: white;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .inv-btn:hover {
            background-color: #1e4687;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand" href="user.php">
                <h3 class="fw-bold text-white mb-0">MediQuick</h3>
            </a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler border-light"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar"
                    aria-controls="offcanvasNavbar"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <!-- Isi Offcanvas -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item"><a class="nav-link active" href="user.php">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link" href="konsultasi.php">Konsultasi</a></li>
                        <li class="nav-item"><a class="nav-link" href="riwayat_konsultasi.php">Riwayat Konsultasi</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="../../login/proses/access_logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container p-2">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2" style="margin: 10px 0px; padding: 12px;">
            <h1 class="fs-2 fw-bold mb-0">
                <i class="bi bi-cart-plus me-2 text-primary"></i><?= $edit_id > 0 ? 'Ubah Pesanan Obat #' . $edit_id : 'Form Pembelian Obat' ?>
            </h1>
            <div class="d-flex gap-2">
                <?php if ($edit_id === 0): ?>
                    <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modal-resep">
                        <i class="bi bi-file-earmark-medical me-1"></i> Beli dengan Resep Dokter
                    </button>
                <?php endif; ?>
                <a href="<?= $edit_id > 0 ? 'detail_pesanan.php?id=' . $edit_id : 'user.php' ?>" class="btn btn-outline-secondary fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="container-fluid p-2">
            <div class="row g-3 px-2">
                <!-- Card Pembelian Manual -->
                <div class="card p-4 shadow-sm border-0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 border-bottom pb-2 gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 text-primary">Daftar Pembelian Obat Bebas</h5>
                            <small class="text-muted">Pilih kategori terlebih dahulu untuk mempermudah pencarian obat.</small>
                        </div>
                        <button type="button" id="btn-tambah-baris" class="btn btn-sm btn-outline-primary fw-semibold">
                            <i class="bi bi-plus-lg"></i> Tambah Baris Obat
                        </button>
                    </div>

                    <form action="proses/pesanan_manual.php" method="POST" id="form-manual">
                        <?php if ($edit_id > 0): ?>
                            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                        <?php endif; ?>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle" id="tabel-obat">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 22%;">Kategori</th>
                                        <th style="width: 30%;">Obat</th>
                                        <th style="width: 13%;">Jumlah</th>
                                        <th style="width: 15%;">Harga Satuan</th>
                                        <th style="width: 15%;">Subtotal</th>
                                        <th style="width: 5%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-resep">
                                    <?php
                                    // Jika mode edit, render baris sesuai data lama; jika baru, render 1 baris kosong
                                    $rows_to_render = !empty($detail_edit) ? $detail_edit : [['id_obat' => '', 'jumlah' => 1, 'kategori' => '', 'harga' => 0]];
                                    foreach ($rows_to_render as $item):
                                    ?>
                                    <tr class="baris-obat">
                                        <td>
                                            <select class="form-select select-kategori">
                                                <option value="">-- Semua Kategori --</option>
                                                <?php foreach ($daftar_kategori as $kat): ?>
                                                    <option value="<?= htmlspecialchars($kat) ?>" <?= ($item['kategori'] === $kat) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kat) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="id_obat[]" class="form-select select-obat" required>
                                                <option value="" disabled <?= empty($item['id_obat']) ? 'selected' : '' ?>>-- Pilih Obat --</option>
                                                <?php foreach ($daftar_obat as $obt): ?>
                                                    <option value="<?= $obt['id_obat'] ?>"
                                                            data-kategori="<?= htmlspecialchars($obt['kategori']) ?>"
                                                            data-harga="<?= $obt['harga'] ?>"
                                                            data-stok="<?= $obt['total_stok'] ?>"
                                                            <?= ((int)$item['id_obat'] === (int)$obt['id_obat']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($obt['nama_obat']) ?> (Stok: <?= $obt['total_stok'] ?> <?= htmlspecialchars($obt['satuan']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="jumlah[]" class="form-control input-jumlah" min="1" value="<?= (int)$item['jumlah'] ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control bg-light harga-obat" readonly value="Rp 0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control bg-light fw-semibold subtotal-obat" readonly value="Rp 0">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-baris" title="Hapus Baris">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Total Pembayaran:</th>
                                        <th colspan="2" class="text-primary fs-5" id="grand-total">Rp 0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="bi bi-house-door me-1"></i> Alamat Pengiriman</label>
                            <textarea name="alamat_pengiriman" class="form-control" rows="3" placeholder="Masukkan Alamat Lengkap" required><?= htmlspecialchars($pesanan_edit['alamat_pengiriman'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" name="kirim_obat" class="inv-btn" onclick="return confirm('Lanjutkan checkout pesanan obat ini?')">
                                <i class="bi bi-check-circle me-1"></i> <?= $edit_id > 0 ? 'Simpan Perubahan' : 'Checkout' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pembelian dengan Resep Dokter -->
    <div class="modal fade" id="modal-resep" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="proses/pesanan_resep.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary"><i class="bi bi-paperclip me-1"></i>Kirim Resep Dokter</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold">Upload Resep</label>
                        <input type="file" name="resep" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>

                        <label class="form-label fw-semibold mt-3">Alamat Pengiriman</label>
                        <textarea name="alamat_pengiriman" class="form-control" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>

                        <div class="alert alert-warning mt-3 mb-0 small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Pastikan resep terlihat jelas dan merupakan resep dokter yang valid (format: <strong>.jpg, .jpeg, .png, .pdf</strong>, maksimal 5 MB).
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali</button>
                        <button type="submit" name="kirim_resep" class="btn btn-primary">Kirim Resep</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tbodyResep = document.getElementById('tbody-resep');
        const btnTambahBaris = document.getElementById('btn-tambah-baris');
        const grandTotalEl = document.getElementById('grand-total');

        function formatRupiah(angka) {
            return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
        }

        // Filter opsi obat berdasarkan kategori yang dipilih pada baris tersebut
        function filterObatByKategori(baris, resetObat = true) {
            const kategoriDipilih = baris.querySelector('.select-kategori').value;
            const selectObat = baris.querySelector('.select-obat');
            const options = selectObat.querySelectorAll('option');

            options.forEach(opt => {
                if (!opt.value) return; // Abaikan placeholder
                const katOpt = opt.getAttribute('data-kategori');
                const cocok = (kategoriDipilih === '' || katOpt === kategoriDipilih);
                opt.hidden = !cocok;
                opt.disabled = !cocok;
            });

            if (resetObat) {
                selectObat.selectedIndex = 0;
                hitungBaris(baris);
            }
        }

        // Hitung harga satuan, batas max stok, subtotal baris, dan grand total
        function hitungBaris(baris) {
            const selectObat = baris.querySelector('.select-obat');
            const inputJumlah = baris.querySelector('.input-jumlah');
            const inputHarga = baris.querySelector('.harga-obat');
            const inputSubtotal = baris.querySelector('.subtotal-obat');

            const selectedOpt = selectObat.options[selectObat.selectedIndex];
            const harga = selectedOpt && selectedOpt.value ? parseFloat(selectedOpt.getAttribute('data-harga')) : 0;
            const stokMax = selectedOpt && selectedOpt.value ? parseInt(selectedOpt.getAttribute('data-stok')) : 0;

            if (stokMax > 0) {
                inputJumlah.max = stokMax;
                if (parseInt(inputJumlah.value) > stokMax) {
                    inputJumlah.value = stokMax;
                }
            }

            const jumlah = parseInt(inputJumlah.value) || 0;
            const subtotal = harga * jumlah;

            inputHarga.value = formatRupiah(harga);
            inputSubtotal.value = formatRupiah(subtotal);

            hitungGrandTotal();
        }

        function hitungGrandTotal() {
            let total = 0;
            document.querySelectorAll('.baris-obat').forEach(baris => {
                const selectObat = baris.querySelector('.select-obat');
                const selectedOpt = selectObat.options[selectObat.selectedIndex];
                const harga = selectedOpt && selectedOpt.value ? parseFloat(selectedOpt.getAttribute('data-harga')) : 0;
                const jumlah = parseInt(baris.querySelector('.input-jumlah').value) || 0;
                total += (harga * jumlah);
            });
            grandTotalEl.textContent = formatRupiah(total);
        }

        // Event delegation untuk perubahan kategori, obat, dan jumlah
        tbodyResep.addEventListener('change', function (e) {
            const baris = e.target.closest('.baris-obat');
            if (!baris) return;

            if (e.target.classList.contains('select-kategori')) {
                filterObatByKategori(baris, true);
            } else if (e.target.classList.contains('select-obat')) {
                hitungBaris(baris);
            }
        });

        tbodyResep.addEventListener('input', function (e) {
            if (e.target.classList.contains('input-jumlah')) {
                const baris = e.target.closest('.baris-obat');
                hitungBaris(baris);
            }
        });

        // Tambah baris baru
        btnTambahBaris.addEventListener('click', function () {
            const barisPertama = tbodyResep.querySelector('.baris-obat');
            const barisBaru = barisPertama.cloneNode(true);

            barisBaru.querySelector('.select-kategori').selectedIndex = 0;
            const selectObatBaru = barisBaru.querySelector('.select-obat');
            selectObatBaru.selectedIndex = 0;
            selectObatBaru.querySelectorAll('option').forEach(opt => {
                if (opt.value) {
                    opt.hidden = false;
                    opt.disabled = false;
                }
            });

            barisBaru.querySelector('.input-jumlah').value = 1;
            barisBaru.querySelector('.input-jumlah').removeAttribute('max');
            barisBaru.querySelector('.harga-obat').value = 'Rp 0';
            barisBaru.querySelector('.subtotal-obat').value = 'Rp 0';

            tbodyResep.appendChild(barisBaru);
        });

        // Hapus baris
        tbodyResep.addEventListener('click', function (e) {
            const btnHapus = e.target.closest('.btn-hapus-baris');
            if (btnHapus) {
                const semuaBaris = tbodyResep.querySelectorAll('.baris-obat');
                if (semuaBaris.length > 1) {
                    btnHapus.closest('.baris-obat').remove();
                    hitungGrandTotal();
                } else {
                    alert('Minimal harus terdapat 1 baris obat pada pembelian.');
                }
            }
        });

        // Inisialisasi kalkulasi saat halaman dimuat (berguna untuk mode Edit Pesanan)
        document.querySelectorAll('.baris-obat').forEach(baris => {
            filterObatByKategori(baris, false);
            hitungBaris(baris);
        });
    </script>
</body>
</html>