<?php
session_start();
include __DIR__ . "/../../config/connect.php";

$id = $_SESSION['id'] ?? '';

$pasien = null;
if ($id !== '') {
    $stmt_pasien = mysqli_prepare($conn, "SELECT nama_lengkap FROM pelanggan WHERE id_pelanggan = ?");
    mysqli_stmt_bind_param($stmt_pasien, "i", $id);
    mysqli_stmt_execute($stmt_pasien);
    $pasien = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pasien));
}

$kategori_aktif = trim($_GET['kategori'] ?? '');

// daftar kategori 
$daftar_kategori = [];
$q_kategori = mysqli_query($conn, "SELECT DISTINCT kategori FROM obat WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
if ($q_kategori) {
    while ($kat_row = mysqli_fetch_assoc($q_kategori)) {
        $daftar_kategori[] = $kat_row['kategori'];
    }
}

// daftar obat berdasarkan kategori
if ($kategori_aktif !== '') {
    $stmt_obat = mysqli_prepare($conn, "SELECT o.*,
               COALESCE((SELECT SUM(s.jumlah_stok) FROM stok_obat s
                         WHERE s.id_obat = o.id_obat AND s.tanggal_kadaluarsa >= CURDATE()), 0) AS total_stok
        FROM obat o WHERE o.kategori = ? ORDER BY o.nama_obat ASC");
    mysqli_stmt_bind_param($stmt_obat, "s", $kategori_aktif);
    mysqli_stmt_execute($stmt_obat);
    $data_obat = mysqli_stmt_get_result($stmt_obat);
} else {
    $stmt_obat = mysqli_prepare($conn, "SELECT o.*,
               COALESCE((SELECT SUM(s.jumlah_stok) FROM stok_obat s
                         WHERE s.id_obat = o.id_obat AND s.tanggal_kadaluarsa >= CURDATE()), 0) AS total_stok
        FROM obat o ORDER BY o.nama_obat ASC");
    mysqli_stmt_execute($stmt_obat);
    $data_obat = mysqli_stmt_get_result($stmt_obat);
}

$jumlah_obat = $data_obat ? mysqli_num_rows($data_obat) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Obat - MediQuick</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../../asset/img/icon.png">
    <style>
        .obat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .obat-item {
            width: 100%;
        }

        .obat-card-button {
            width: 100%;
            height: 100%;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            text-align: inherit;
        }

        .description-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            height: 100%;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .description-box.is-hovered {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(44, 94, 173, 0.14);
            border-color: #4fb0f0;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar" style="background-color: #2C5EAD;">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="user.php"><img src="../../asset/img/logo.png" alt="logo" width="40" height="40">MediQuick</a>

            <!-- Hamburger Menu -->
            <button class="navbar-toggler"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar"
                    aria-controls="offcanvasNavbar"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Isi Offcanvas -->
            <div class="offcanvas offcanvas-end text-bg-light" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu Navigasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link" href="user.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="beli_obat.php">Pesan Obat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">Daftar Obat</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat_pesanan.php">Riwayat Pesanan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../../login/proses/access_logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 20px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="mb-0 fw-bold">Daftar Obat</h2>
                <?php if ($pasien): ?>
                    <small class="text-muted">Pasien: <strong><?= htmlspecialchars($pasien['nama_lengkap']); ?></strong></small>
                <?php endif; ?>
            </div>
            <a href="user.php<?= $id !== '' ? '?id=' . urlencode($id) : '' ?>" class="btn btn-outline-secondary btn-sm fw-semibold">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <hr>

    <!-- Filter Kategori -->
    <div class="container mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-semibold text-muted me-1">Kategori:</span>
            <a href="daftar_obat.php<?= $id !== '' ? '?id=' . urlencode($id) : '' ?>"
               class="btn btn-sm <?= $kategori_aktif === '' ? 'btn-primary fw-semibold' : 'btn-outline-primary' ?>">
                Semua
            </a>
            <?php foreach ($daftar_kategori as $kat){ ?>
                <a href="daftar_obat.php?<?= $id !== '' ? 'id=' . urlencode($id) . '&' : '' ?>kategori=<?= urlencode($kat) ?>"
                   class="btn btn-sm <?= $kategori_aktif === $kat ? 'btn-primary fw-semibold' : 'btn-outline-primary' ?>">
                    <?= htmlspecialchars($kat) ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <!-- Daftar Obat -->
    <div class="container mt-3 mb-5">
        <?php if ($jumlah_obat > 0){ ?>
            <div class="obat-grid">
                <?php while ($obat = mysqli_fetch_assoc($data_obat)){ ?>
                    <form action="detail_obat.php" method="POST" class="obat-item m-0">
                        <input type="hidden" name="id_obat" value="<?= (int) $obat['id_obat'] ?>">
                        <input type="hidden" name="id_pelanggan" value="<?= htmlspecialchars($id) ?>">
                        <input type="hidden" name="keyPage" value="fromDaftar">
                        <button type="submit" class="obat-card-button">
                            <div class="description-box" onmouseover="hoverOn(this)" onmouseout="hoverOff(this)">
                                <?php if (!empty($obat['gambar_obat'])){ ?>
                                    <img src="../../asset/uploads/obat/<?= htmlspecialchars($obat['gambar_obat']) ?>"
                                         alt="<?= htmlspecialchars($obat['nama_obat']) ?>"
                                         class="img-fluid mb-2"
                                         style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                                <?php }else{ ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center mb-2 text-muted"
                                         style="width: 100%; height: 150px; border-radius: 8px;">
                                        <i class="bi bi-capsule fs-1"></i>
                                    </div>
                                <?php } ?>
                                <div class="small text-muted mb-1"><?= htmlspecialchars($obat['kategori']) ?></div>
                                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($obat['nama_obat']) ?></h6>
                                <div class="small fw-semibold text-primary">
                                    Rp <?= number_format((float) $obat['harga'], 0, ',', '.') ?>
                                    <span class="text-muted fw-normal">/ <?= htmlspecialchars($obat['satuan']) ?></span>
                                </div>
                                <div class="small mt-1">
                                    <?php if ((int) $obat['total_stok'] > 0){ ?>
                                        <span class="text-muted">Stok: <?= (int) $obat['total_stok'] ?></span>
                                    <?php }else{ ?>
                                        <span class="badge bg-secondary">Stok Habis</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </button>
                    </form>
                <?php } ?>
            </div>
        <?php }else { ?>
            <div class="alert alert-info text-center">
                Obat Sedang Kosong<?= $kategori_aktif !== '' ? ' untuk kategori "' . htmlspecialchars($kategori_aktif) . '"' : '' ?>.
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function hoverOn(el) {
            el.classList.add('is-hovered');
        }

        function hoverOff(el) {
            el.classList.remove('is-hovered');
        }
    </script>
</body>
</html>