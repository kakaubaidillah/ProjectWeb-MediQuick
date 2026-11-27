-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 04:00 PM
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
-- Database: `new_mediquick`
--

-- --------------------------------------------------------

--
-- Table structure for table `apoteker`
--

CREATE TABLE `apoteker` (
  `id_apoteker` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telp` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apoteker`
--

INSERT INTO `apoteker` (`id_apoteker`, `nama_lengkap`, `email`, `password`, `no_telp`) VALUES
(1, 'Hammam Ibad', 'hammam@gmail.com', '$2y$10$N5WiPgTi5jmUiHVltl6KTuT179Jz8C4lCwFc1zKGViuw1m1j7/cCi', '083765676');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_obat` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `id_obat` int(11) NOT NULL,
  `nama_obat` varchar(150) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `butuh_resep` tinyint(1) DEFAULT 0,
  `gambar_obat` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `obat`
--

INSERT INTO `obat` (`id_obat`, `nama_obat`, `kategori`, `satuan`, `harga`, `butuh_resep`, `gambar_obat`) VALUES
(25, 'Paracetamol 500 mg', 'Tablet', 'Tablet', 2500.00, 0, 'obat_01.png'),
(26, 'Ibuprofen 200 mg', 'Analgesik', 'Tablet', 3500.00, 0, 'obat_02.png'),
(27, 'Antasida Doen', 'Antasida', 'Tablet', 3000.00, 0, 'obat_03.png'),
(28, 'Cetirizine 10 mg', 'Antialergi', 'Tablet', 4000.00, 0, 'obat_04.png'),
(29, 'Oralit', 'Rehidrasi', 'Sachet', 1500.00, 0, 'obat_05.png'),
(30, 'Vitamin C 500 mg', 'Vitamin', 'Tablet', 5000.00, 0, 'obat_06.png'),
(31, 'Multivitamin', 'Vitamin', 'Tablet', 8500.00, 0, 'obat_07.png'),
(32, 'Sirup Batuk', 'Obat batuk', 'Botol', 18000.00, 0, 'obat_08.png'),
(33, 'Antiseptik Povidone', 'Antiseptik', 'Botol', 12000.00, 0, 'obat_09.png'),
(34, 'Amoxicillin 500 mg', 'Antibiotik', 'Kapsul', 6500.00, 1, 'obat_10.png'),
(35, 'Metformin 500 mg', 'Antidiabetes', 'Tablet', 4500.00, 1, 'obat_11.png'),
(36, 'Amlodipine 5 mg', 'Antihipertensi', 'Tablet', 5500.00, 1, 'obat_12.png'),
(37, 'Omeprazole 20 mg', 'Lambung', 'Kapsul', 7000.00, 1, 'obat_13.png'),
(38, 'Salbutamol 2 mg', 'Asma', 'Tablet', 4000.00, 1, 'obat_14.png'),
(39, 'Tetes Mata Steril', 'Obat mata', 'Botol', 14000.00, 1, 'obat_15.png');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `gender` enum('L','P') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_lengkap`, `email`, `password`, `no_telp`, `tanggal_lahir`, `gender`) VALUES
(1, 'Agus Kopling', 'agus@gmail.com', '$2y$10$fzyQXY0NJrt8VcLkWgC7.eSC5yX9tOh6KP3iFX350k30iDhscwEbe', '08645678655', '2008-01-01', 'L');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `id_apoteker` int(11) DEFAULT NULL,
  `tanggal_pesanan` datetime DEFAULT current_timestamp(),
  `batas_pembayaran` datetime DEFAULT NULL,
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `resep_obat` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','belum bayar','dibayar','ditolak','dibatalkan') NOT NULL DEFAULT 'belum bayar',
  `catatan_apoteker` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_obat`
--

CREATE TABLE `stok_obat` (
  `id_stok` int(11) NOT NULL,
  `id_obat` int(11) NOT NULL,
  `batch` varchar(50) NOT NULL,
  `jumlah_stok` int(11) NOT NULL DEFAULT 0,
  `tanggal_masuk` date NOT NULL,
  `tanggal_kadaluarsa` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok_obat`
--

INSERT INTO `stok_obat` (`id_stok`, `id_obat`, `batch`, `jumlah_stok`, `tanggal_masuk`, `tanggal_kadaluarsa`) VALUES
(29, 25, 'MQ-B001-2026', 120, '2026-11-01', '2028-12-31'),
(30, 26, 'MQ-B002-2026', 80, '2026-11-01', '2028-12-31'),
(31, 27, 'MQ-B003-2026', 100, '2026-11-01', '2029-06-30'),
(32, 28, 'MQ-B004-2026', 75, '2026-11-01', '2028-12-31'),
(33, 29, 'MQ-B005-2026', 150, '2026-11-01', '2028-12-31'),
(34, 30, 'MQ-B006-2026', 90, '2026-11-01', '2029-06-30'),
(35, 31, 'MQ-B007-2026', 65, '2026-11-01', '2028-12-31'),
(36, 32, 'MQ-B008-2026', 40, '2026-11-01', '2028-12-31'),
(37, 33, 'MQ-B009-2026', 35, '2026-11-01', '2029-06-30'),
(38, 34, 'MQ-B010-2026', 60, '2026-11-01', '2028-12-31'),
(39, 35, 'MQ-B011-2026', 70, '2026-11-01', '2028-12-31'),
(40, 36, 'MQ-B012-2026', 55, '2026-11-01', '2029-06-30'),
(41, 37, 'MQ-B013-2026', 50, '2026-11-01', '2028-12-31'),
(42, 38, 'MQ-B014-2026', 45, '2026-11-01', '2028-12-31'),
(43, 39, 'MQ-B015-2026', 30, '2026-11-01', '2029-06-30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apoteker`
--
ALTER TABLE `apoteker`
  ADD PRIMARY KEY (`id_apoteker`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_obat` (`id_obat`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id_obat`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_apoteker` (`id_apoteker`);

--
-- Indexes for table `stok_obat`
--
ALTER TABLE `stok_obat`
  ADD PRIMARY KEY (`id_stok`),
  ADD KEY `id_obat` (`id_obat`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apoteker`
--
ALTER TABLE `apoteker`
  MODIFY `id_apoteker` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `obat`
--
ALTER TABLE `obat`
  MODIFY `id_obat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stok_obat`
--
ALTER TABLE `stok_obat`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`);

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`id_apoteker`) REFERENCES `apoteker` (`id_apoteker`) ON DELETE SET NULL;

--
-- Constraints for table `stok_obat`
--
ALTER TABLE `stok_obat`
  ADD CONSTRAINT `stok_obat_ibfk_1` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id_obat`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
