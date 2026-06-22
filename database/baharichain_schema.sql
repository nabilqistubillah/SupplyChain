-- ============================================================================
-- DATABASE SCHEMA: BAHARICHAIN (Platform Reservasi Pariwisata Bahari)
-- Version: 3.0 (Final Academic Prototype)
-- Date: 2026-06-14
-- Berdasarkan: Perancangan_BahariChain.md
-- ============================================================================

-- CREATE DATABASE IF NOT EXISTS baharichain_db;
-- USE baharichain_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. TABEL: roles (Peran Pengguna)
-- ============================================================================
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(30) UNIQUE NOT NULL COMMENT 'admin, pengelola_wisata, wisatawan',
    deskripsi VARCHAR(255) NULL COMMENT 'Penjelasan peran',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABEL: users (Pengguna Sistem)
-- ============================================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL COMMENT 'Rujukan ke tabel roles',
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt password',
    email VARCHAR(100) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NULL,
    no_telepon VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_email ON users(email);

-- ============================================================================
-- 3. TABEL: destinasi (Destinasi Wisata Bahari)
-- ============================================================================
DROP TABLE IF EXISTS destinasi;
CREATE TABLE destinasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL COMMENT 'Nama objek wisata bahari',
    deskripsi TEXT NULL COMMENT 'Penjelasan daya tarik destinasi',
    lokasi VARCHAR(255) NOT NULL COMMENT 'Wilayah administrasi destinasi',
    latitude DECIMAL(10, 8) NULL COMMENT 'Koordinat garis lintang',
    longitude DECIMAL(11, 8) NULL COMMENT 'Koordinat garis bujur',
    foto_path VARCHAR(255) NULL COMMENT 'Path foto destinasi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_destinasi_lokasi ON destinasi(lokasi);

-- ============================================================================
-- 4. TABEL: paket_wisata (Paket Liburan)
-- ============================================================================
DROP TABLE IF EXISTS paket_wisata;
CREATE TABLE paket_wisata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_paket VARCHAR(150) NOT NULL COMMENT 'Nama penawaran paket',
    deskripsi TEXT NULL COMMENT 'Itinerary perjalanan dan agenda',
    harga DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Tarif dasar per pax',
    durasi_hari INT NOT NULL DEFAULT 1 COMMENT 'Durasi trip (hari)',
    foto_path VARCHAR(255) NULL COMMENT 'Gambar representasi paket',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT chk_paket_harga CHECK (harga >= 0),
    CONSTRAINT chk_paket_durasi CHECK (durasi_hari > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABEL: detail_paket_destinasi (Junction M:N Paket & Destinasi)
-- ============================================================================
DROP TABLE IF EXISTS detail_paket_destinasi;
CREATE TABLE detail_paket_destinasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paket_wisata_id INT NOT NULL COMMENT 'Rujukan paket wisata',
    destinasi_id INT NOT NULL COMMENT 'Rujukan destinasi',
    hari_ke INT NOT NULL DEFAULT 1 COMMENT 'Kunjungan hari ke-X',
    urutan INT NOT NULL DEFAULT 1 COMMENT 'Urutan rute pada hari tersebut',
    
    CONSTRAINT fk_detpak_paket
        FOREIGN KEY (paket_wisata_id) REFERENCES paket_wisata(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_detpak_dest
        FOREIGN KEY (destinasi_id) REFERENCES destinasi(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    CONSTRAINT chk_detpak_hari CHECK (hari_ke > 0),
    CONSTRAINT chk_detpak_urutan CHECK (urutan > 0),
    CONSTRAINT uk_detpak_paket_dest UNIQUE (paket_wisata_id, destinasi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_detpak_paket ON detail_paket_destinasi(paket_wisata_id);
CREATE INDEX idx_detpak_dest ON detail_paket_destinasi(destinasi_id);

-- ============================================================================
-- 6. TABEL: transportasi (Armada Transportasi)
-- ============================================================================
DROP TABLE IF EXISTS transportasi;
CREATE TABLE transportasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Pengelola wisata yang mendaftarkan',
    destinasi_id INT NULL COMMENT 'Lokasi pos layanan terdekat',
    jenis_kendaraan ENUM('motor', 'mobil', 'shuttle_bus', 'perahu_wisata', 'lainnya') NOT NULL,
    nama_armada VARCHAR(100) NOT NULL COMMENT 'Label plat atau nama kendaraan',
    kapasitas INT NOT NULL COMMENT 'Jumlah maksimal penumpang',
    harga_sewa DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Tarif sewa kendaraan',
    deskripsi TEXT NULL COMMENT 'Deskripsi fasilitas armada',
    status_ketersediaan ENUM('tersedia', 'habis_dipesan', 'nonaktif') DEFAULT 'tersedia' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_transport_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_transport_dest
        FOREIGN KEY (destinasi_id) REFERENCES destinasi(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    
    CONSTRAINT chk_transport_kapasitas CHECK (kapasitas > 0),
    CONSTRAINT chk_transport_harga CHECK (harga_sewa >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_transport_user ON transportasi(user_id);
CREATE INDEX idx_transport_dest ON transportasi(destinasi_id);
CREATE INDEX idx_transport_status ON transportasi(status_ketersediaan);

-- ============================================================================
-- 7. TABEL: pesanan (Data Reservasi Transaksi)
-- ============================================================================
DROP TABLE IF EXISTS pesanan;
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Wisatawan pemesan',
    paket_wisata_id INT NOT NULL COMMENT 'Paket wisata yang dipesan',
    transportasi_id INT NULL COMMENT 'Armada transportasi (opsional)',
    tanggal_perjalanan DATE NOT NULL COMMENT 'Tanggal keberangkatan',
    jumlah_peserta INT NOT NULL DEFAULT 1 COMMENT 'Total peserta',
    total_harga DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Total tagihan',
    status ENUM('pending', 'unpaid', 'paid', 'completed', 'cancelled') DEFAULT 'pending' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_pesanan_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_pesanan_paket
        FOREIGN KEY (paket_wisata_id) REFERENCES paket_wisata(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_pesanan_transport
        FOREIGN KEY (transportasi_id) REFERENCES transportasi(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    
    CONSTRAINT chk_pesanan_peserta CHECK (jumlah_peserta > 0),
    CONSTRAINT chk_pesanan_harga CHECK (total_harga >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_pesanan_user ON pesanan(user_id);
CREATE INDEX idx_pesanan_paket ON pesanan(paket_wisata_id);
CREATE INDEX idx_pesanan_status ON pesanan(status);
CREATE INDEX idx_pesanan_tanggal ON pesanan(tanggal_perjalanan);

-- ============================================================================
-- 8. TABEL: pembayaran (Verifikasi Bukti Transfer)
-- ============================================================================
DROP TABLE IF EXISTS pembayaran;
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT UNIQUE NOT NULL COMMENT 'Relasi 1:1 ke pesanan',
    metode_pembayaran VARCHAR(50) NOT NULL COMMENT 'Transfer bank manual',
    jumlah DECIMAL(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'Nominal transfer',
    status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid' NOT NULL,
    bukti_transfer VARCHAR(255) NULL COMMENT 'Path foto struk transfer',
    tanggal_bayar TIMESTAMP NULL COMMENT 'Tanggal verifikasi admin',
    
    CONSTRAINT fk_pembayaran_pesanan
        FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    CONSTRAINT chk_pembayaran_jumlah CHECK (jumlah >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_pembayaran_status ON pembayaran(status);

-- ============================================================================
-- 9. TABEL: review (Ulasan Rating Paket)
-- ============================================================================
DROP TABLE IF EXISTS review;
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Wisatawan pemberi ulasan',
    paket_wisata_id INT NOT NULL COMMENT 'Paket yang diulas',
    rating INT NOT NULL COMMENT 'Skala bintang 1-5',
    komentar TEXT NULL COMMENT 'Catatan ulasan tertulis',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_review_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_review_paket
        FOREIGN KEY (paket_wisata_id) REFERENCES paket_wisata(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_review_user ON review(user_id);
CREATE INDEX idx_review_paket ON review(paket_wisata_id);
CREATE INDEX idx_review_rating ON review(rating);

-- ============================================================================
-- 10. TABEL: notifikasi (Pesan Pemberitahuan)
-- ============================================================================
DROP TABLE IF EXISTS notifikasi;
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'Penerima notifikasi',
    judul VARCHAR(100) NOT NULL COMMENT 'Judul singkat',
    pesan TEXT NOT NULL COMMENT 'Deskripsi detail',
    tipe ENUM('info', 'transaksi', 'alert') DEFAULT 'info' NOT NULL,
    is_read BOOLEAN DEFAULT FALSE NOT NULL COMMENT 'Sudah dibaca?',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_notifikasi_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_notif_user ON notifikasi(user_id);
CREATE INDEX idx_notif_read ON notifikasi(is_read);
CREATE INDEX idx_notif_created ON notifikasi(created_at);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SCHEMA CREATION COMPLETED
-- ============================================================================
