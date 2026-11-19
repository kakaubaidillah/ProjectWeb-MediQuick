-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2026 at 03:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mediquick`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `role` enum('admin','dokter','apoteker') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `email`, `password`, `no_telp`, `role`, `created_at`) VALUES
(1, 'Hammam Mukhlisul Ibad', 'hammam@gmail.com', 'hammam123', '081122445566', 'admin', '2026-11-01 09:23:00'),
(2, 'Mukhammad Kaka Ubaidillah', 'kaka@gmail.com', 'kaka123', '087654211120', 'dokter', '2026-11-01 09:23:00'),
(3, 'Ubed', 'ubed@gmail.com', 'ubed098', '084378905567', 'apoteker', '2026-11-01 09:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `alamat_pasien`
--

CREATE TABLE `alamat_pasien` (
  `id_alamat` int(11) NOT NULL,
  `id_pasien` int(11) NOT NULL,
  `nama_alamat` varchar(50) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kelurahan` varchar(100) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `alamat_lengkap` text NOT NULL,
  `is_utama` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_resep`
--

CREATE TABLE `detail_resep` (
  `id_detail` int(11) NOT NULL,
  `id_resep` int(11) NOT NULL,
  `id_obat` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `dosis` varchar(100) NOT NULL,
  `aturan_pakai` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi`
--

CREATE TABLE `konsultasi` (
  `id_konsultasi` int(11) NOT NULL,
  `id_pasien` int(11) NOT NULL,
  `id_dokter` int(11) DEFAULT NULL,
  `gejala` text NOT NULL,
  `sejak_kapan` varchar(100) DEFAULT NULL,
  `riwayat_alergi` text DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `foto_gejala` varchar(255) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `catatan_dokter` text DEFAULT NULL,
  `status` enum('Menunggu','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `tanggal_konsultasi` datetime NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konsultasi`
--

INSERT INTO `konsultasi` (`id_konsultasi`, `id_pasien`, `id_dokter`, `gejala`, `sejak_kapan`, `riwayat_alergi`, `riwayat_penyakit`, `foto_gejala`, `diagnosis`, `catatan_dokter`, `status`, `tanggal_konsultasi`, `tanggal_selesai`) VALUES
(1, 3, NULL, 'Meriang, Pusing, HopelessRomantic, Kunang-kunang, kurang uang', '2000-02-03', 'recehan', 'homeless', NULL, NULL, NULL, 'Menunggu', '2026-11-11 16:09:39', NULL),
(2, 2, NULL, 'Sakit gigi, meriang, panas dalam', '1988-03-14', '', '', NULL, NULL, NULL, 'Menunggu', '2026-11-11 20:36:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `konten_dashboard`
--

CREATE TABLE `konten_dashboard` (
  `id_konten` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_pasien` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `status_baca` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_notifikasi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `id_obat` int(11) NOT NULL,
  `nama_obat` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `satuan` varchar(30) DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`id_pasien`, `nama`, `email`, `no_telp`, `password`, `tanggal_lahir`, `jenis_kelamin`, `created_at`) VALUES
(1, 'Isnan', 'isnan@gmail.com', '085493288810', 'qwerty1234', '1907-07-17', 'Laki-laki', '2026-11-05 12:43:12'),
(2, 'jokowi', 'jokowi@gmail.com', '0894773218', '1234567', '2001-12-12', 'Perempuan', '2026-11-10 10:36:17'),
(3, 'basori', 'bsri@gmail.com', '087676898', 'bsri45', '1999-03-12', 'Laki-laki', '2026-11-11 08:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `resep`
--

CREATE TABLE `resep` (
  `id_resep` int(11) NOT NULL,
  `id_konsultasi` int(11) NOT NULL,
  `id_apoteker` int(11) DEFAULT NULL,
  `tanggal_resep` datetime NOT NULL DEFAULT current_timestamp(),
  `status_resep` enum('Menunggu','Diproses','Siap Diambil','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_obat`
--

CREATE TABLE `stok_obat` (
  `id_stok` int(11) NOT NULL,
  `id_obat` int(11) NOT NULL,
  `nomor_batch` varchar(50) DEFAULT NULL,
  `jumlah_stok` int(11) NOT NULL DEFAULT 0,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_kadaluarsa` date NOT NULL
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `email_3` (`email`);

--
-- Indexes for table `alamat_pasien`
--
ALTER TABLE `alamat_pasien`
  ADD PRIMARY KEY (`id_alamat`),
  ADD KEY `fk_alamat_pasien` (`id_pasien`);

--
-- Indexes for table `detail_resep`
--
ALTER TABLE `detail_resep`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_resep` (`id_resep`),
  ADD KEY `idx_detail_resep_obat` (`id_obat`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id_konsultasi`),
  ADD KEY `idx_konsultasi_pasien` (`id_pasien`),
  ADD KEY `idx_konsultasi_dokter` (`id_dokter`),
  ADD KEY `idx_konsultasi_status` (`status`),
  ADD KEY `idx_konsultasi_tanggal` (`tanggal_konsultasi`);

--
-- Indexes for table `konten_dashboard`
--
ALTER TABLE `konten_dashboard`
  ADD PRIMARY KEY (`id_konten`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `idx_notifikasi_pasien` (`id_pasien`,`status_baca`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id_obat`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`id_resep`),
  ADD UNIQUE KEY `id_konsultasi` (`id_konsultasi`),
  ADD KEY `fk_resep_apoteker` (`id_apoteker`);

--
-- Indexes for table `stok_obat`
--
ALTER TABLE `stok_obat`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `idx_stok_obat_kadaluarsa` (`id_obat`,`tanggal_kadaluarsa`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `alamat_pasien`
--
ALTER TABLE `alamat_pasien`
  MODIFY `id_alamat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_resep`
--
ALTER TABLE `detail_resep`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id_konsultasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `konten_dashboard`
--
ALTER TABLE `konten_dashboard`
  MODIFY `id_konten` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `obat`
--
ALTER TABLE `obat`
  MODIFY `id_obat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id_pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `id_resep` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stok_obat`
--
ALTER TABLE `stok_obat`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alamat_pasien`
--
ALTER TABLE `alamat_pasien`
  ADD CONSTRAINT `fk_alamat_pasien` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_resep`
--
ALTER TABLE `detail_resep`
  ADD CONSTRAINT `fk_detail_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_resep` FOREIGN KEY (`id_resep`) REFERENCES `resep` (`id_resep`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD CONSTRAINT `fk_konsultasi_dokter` FOREIGN KEY (`id_dokter`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_konsultasi_pasien` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON UPDATE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notifikasi_pasien` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resep`
--
ALTER TABLE `resep`
  ADD CONSTRAINT `fk_resep_apoteker` FOREIGN KEY (`id_apoteker`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resep_konsultasi` FOREIGN KEY (`id_konsultasi`) REFERENCES `konsultasi` (`id_konsultasi`) ON UPDATE CASCADE;

--
-- Constraints for table `stok_obat`
--
ALTER TABLE `stok_obat`
  ADD CONSTRAINT `fk_stok_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
