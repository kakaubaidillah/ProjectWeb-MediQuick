-- =========================================================
-- MediQuick - Database Telemedisin dan Manajemen Resep Obat
-- Kompatibilitas: MySQL 8.0.16+ / phpMyAdmin
-- =========================================================

CREATE DATABASE IF NOT EXISTS mediquick
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mediquick;

-- ---------------------------------------------------------
-- 1. Tabel pasien
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS pasien (
    id_pasien INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_telp VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. Tabel admin, dokter, dan apoteker
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telp VARCHAR(20),
    role ENUM('admin', 'dokter', 'apoteker') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. Tabel alamat pasien
-- Satu pasien dapat memiliki beberapa alamat.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS alamat_pasien (
    id_alamat INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    nama_alamat VARCHAR(50),
    provinsi VARCHAR(100),
    kota VARCHAR(100),
    kecamatan VARCHAR(100),
    kelurahan VARCHAR(100),
    kode_pos VARCHAR(10),
    alamat_lengkap TEXT NOT NULL,
    is_utama BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_alamat_pasien
        FOREIGN KEY (id_pasien)
        REFERENCES pasien(id_pasien)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. Tabel konsultasi
-- Foto disimpan di folder server; kolom foto_gejala
-- menyimpan path/nama file, bukan binary gambar.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS konsultasi (
    id_konsultasi INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_dokter INT NULL,

    gejala TEXT NOT NULL,
    sejak_kapan VARCHAR(100),
    riwayat_alergi TEXT,
    riwayat_penyakit TEXT,
    foto_gejala VARCHAR(255),

    diagnosis TEXT,
    catatan_dokter TEXT,

    status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak')
        NOT NULL DEFAULT 'Menunggu',

    tanggal_konsultasi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tanggal_selesai DATETIME NULL,

    CONSTRAINT fk_konsultasi_pasien
        FOREIGN KEY (id_pasien)
        REFERENCES pasien(id_pasien)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_konsultasi_dokter
        FOREIGN KEY (id_dokter)
        REFERENCES admin(id_admin)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    INDEX idx_konsultasi_pasien (id_pasien),
    INDEX idx_konsultasi_dokter (id_dokter),
    INDEX idx_konsultasi_status (status),
    INDEX idx_konsultasi_tanggal (tanggal_konsultasi)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. Tabel resep
-- Satu konsultasi maksimal memiliki satu resep.
-- Konsultasi boleh tidak menghasilkan resep.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS resep (
    id_resep INT AUTO_INCREMENT PRIMARY KEY,
    id_konsultasi INT NOT NULL UNIQUE,
    id_apoteker INT NULL,

    tanggal_resep DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_resep ENUM(
        'Menunggu',
        'Diproses',
        'Siap Diambil',
        'Selesai',
        'Dibatalkan'
    ) NOT NULL DEFAULT 'Menunggu',

    catatan TEXT,

    CONSTRAINT fk_resep_konsultasi
        FOREIGN KEY (id_konsultasi)
        REFERENCES konsultasi(id_konsultasi)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_resep_apoteker
        FOREIGN KEY (id_apoteker)
        REFERENCES admin(id_admin)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. Tabel obat
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS obat (
    id_obat INT AUTO_INCREMENT PRIMARY KEY,
    nama_obat VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(100),
    satuan VARCHAR(30),
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_obat_harga CHECK (harga >= 0)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. Tabel stok obat
-- Stok dicatat per batch agar tanggal kedaluwarsa
-- setiap batch dapat dikelola secara terpisah.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS stok_obat (
    id_stok INT AUTO_INCREMENT PRIMARY KEY,
    id_obat INT NOT NULL,
    nomor_batch VARCHAR(50),
    jumlah_stok INT NOT NULL DEFAULT 0,
    tanggal_masuk DATE,
    tanggal_kadaluarsa DATE NOT NULL,

    CONSTRAINT fk_stok_obat
        FOREIGN KEY (id_obat)
        REFERENCES obat(id_obat)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT chk_jumlah_stok CHECK (jumlah_stok >= 0),
    INDEX idx_stok_obat_kadaluarsa (id_obat, tanggal_kadaluarsa)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 8. Tabel detail resep
-- Satu resep dapat berisi beberapa jenis obat.
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS detail_resep (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_resep INT NOT NULL,
    id_obat INT NOT NULL,

    jumlah INT NOT NULL,
    dosis VARCHAR(100) NOT NULL,
    aturan_pakai VARCHAR(255) NOT NULL,
    catatan TEXT,

    CONSTRAINT fk_detail_resep
        FOREIGN KEY (id_resep)
        REFERENCES resep(id_resep)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_detail_obat
        FOREIGN KEY (id_obat)
        REFERENCES obat(id_obat)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT chk_detail_jumlah CHECK (jumlah > 0),
    INDEX idx_detail_resep_obat (id_obat)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 9. Tabel notifikasi pasien
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifikasi (
    id_notifikasi INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    status_baca BOOLEAN NOT NULL DEFAULT FALSE,
    tanggal_notifikasi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifikasi_pasien
        FOREIGN KEY (id_pasien)
        REFERENCES pasien(id_pasien)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    INDEX idx_notifikasi_pasien (id_pasien, status_baca)
) ENGINE=InnoDB;

-- =========================================================
-- SELESAI
-- Catatan:
-- 1. Isi password harus berupa hash dari PHP password_hash().
-- 2. Isi foto_gejala dengan path file yang diunggah.
-- 3. Dashboard dibuat dari query SELECT, bukan tabel khusus.
-- 4. Tabel admin menampung role admin, dokter, dan apoteker.
-- =========================================================
