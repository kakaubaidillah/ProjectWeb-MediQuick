<?php
session_start();
include __DIR__ . "/../../../config/connection.php";


$idapoteker = $_SESSION['id'] ?? "";
$role       = $_SESSION['role'] ?? "";

if (empty($idapoteker) || $role !== "apoteker") {
    echo "<script>alert('Mohon Login terlebih dahulu!'); 
            window.location.href='../../../login/login.php';
          </script>";
    exit();
}

if (isset($_POST['simpan'])) {
    $nama_obat          = $_POST['nama_obat'];
    $kategori           = $_POST['kategori'];
    $satuan             = $_POST['satuan'];
    $harga              = $_POST['harga'];
    $butuh_resep        = $_POST['butuh_resep'];
    $batch              = $_POST['batch'];
    $jumlah_stok        = (int)$_POST['jumlah_stok'];
    $tanggal_masuk      = $_POST['tanggal_masuk'];
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];

    // Upload gambar obat jika ada file yang dipilih
    $nama_gambar = "";
    if (!empty($_FILES['gambar_obat']['name'])) {
        $folder_tujuan = __DIR__ . "/../../../asset/uploads/obat/";
        if (!is_dir($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }
        $nama_gambar = time() . "_" . basename($_FILES['gambar_obat']['name']);
        move_uploaded_file($_FILES['gambar_obat']['tmp_name'], $folder_tujuan . $nama_gambar);
    }
    $nama_gambar_esc = mysqli_real_escape_string($conn, $nama_gambar);

    // Cek nama_obat sudah terdaftar di tabel 'obat' atau belum
    $query_cek = mysqli_query($conn, "SELECT id_obat FROM obat WHERE nama_obat = '$nama_obat' LIMIT 1");

    if (mysqli_num_rows($query_cek) > 0) {
        // Jika obat sudah ada, ambil id_obat-nya
        $data_obat = mysqli_fetch_assoc($query_cek);
        $id_obat   = $data_obat['id_obat'];
    } else {
        // Jika obat belum ada, insert data baru ke tabel 'obat'
        $query_insert_obat = mysqli_query($conn, "INSERT INTO obat (nama_obat, kategori, satuan, harga, butuh_resep, gambar_obat) 
                                                  VALUES ('$nama_obat', '$kategori', '$satuan', '$harga', '$butuh_resep', '$nama_gambar_esc')");
        if ($query_insert_obat) {
            $id_obat = mysqli_insert_id($conn);
        } else {
            echo "<script>
                alert('Data obat GAGAL ditambahkan!'); 
                window.location.href='../inventory.php';
              </script>";
            exit();
        }
    }

    // Insert data batch & stok ke tabel 'stok_obat'
    $query_insert_stok = mysqli_query($conn, "INSERT INTO stok_obat (id_obat, batch, jumlah_stok, tanggal_masuk, tanggal_kadaluarsa) 
                                              VALUES ('$id_obat', '$batch', '$jumlah_stok', '$tanggal_masuk', '$tanggal_kadaluarsa')");

    if ($query_insert_stok) {
        echo "<script>
                alert('Data obat dan stok BERHASIL ditambahkan!'); 
                window.location.href='../inventory.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Data obat dan stok GAGAL ditambahkan!'); 
                window.location.href='../inventory.php';
              </script>";
        exit();
    }
} else {
    header("Location: ../tambah_obat.php");
    exit();
}
