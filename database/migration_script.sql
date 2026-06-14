-- ============================================================================
-- MIGRATION SCRIPT: MBT (E-commerce) → BAHARICHAIN (Tourism Platform)
-- ============================================================================
-- IMPORTANT: Backup database before running this migration!
-- This script will DROP old e-commerce tables and create new tourism tables.

-- Disable foreign key checks for migration
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PHASE 1: DROP OLD E-COMMERCE TABLES (Not in BahariChain design)
-- ============================================================================

-- These tables are from MBT e-commerce system, not in BahariChain design:
DROP TABLE IF EXISTS vendor_profile;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS shipments;
DROP TABLE IF EXISTS destination_rides;
DROP TABLE IF EXISTS visitor_statistics;

-- ============================================================================
-- PHASE 2: TEMPORARILY RENAME EXISTING TABLES FOR DATA PRESERVATION
-- ============================================================================

-- Rename existing tables (if they exist) with _old suffix for backup
RENAME TABLE users TO users_old;
RENAME TABLE destinations TO destinations_old;
RENAME TABLE orders TO orders_old;
RENAME TABLE payments TO payments_old;
RENAME TABLE reviews TO reviews_old;

-- ============================================================================
-- PHASE 3: CREATE NEW BAHARICHAIN TABLES
-- ============================================================================

-- 1. Create roles table
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(30) UNIQUE NOT NULL,
    deskripsi VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create new users table with role_id foreign key
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NULL,
    no_telepon VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create destinasi table (renamed from destinations)
DROP TABLE IF EXISTS destinasi;
CREATE TABLE destinasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    lokasi VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    foto_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create paket_wisata table (NEW - Tourism Packages)
DROP TABLE IF EXISTS paket_wisata;
CREATE TABLE paket_wisata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_paket VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    harga DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    durasi_hari INT NOT NULL DEFAULT 1,
    foto_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT chk_paket_harga CHECK (harga >= 0),
    CONSTRAINT chk_paket_durasi CHECK (durasi_hari > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create detail_paket_destinasi table (NEW - Package-Destination mapping)
DROP TABLE IF EXISTS detail_paket_destinasi;
CREATE TABLE detail_paket_destinasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paket_wisata_id INT NOT NULL,
    destinasi_id INT NOT NULL,
    hari_ke INT NOT NULL DEFAULT 1,
    urutan INT NOT NULL DEFAULT 1,
    
    CONSTRAINT fk_detpak_paket FOREIGN KEY (paket_wisata_id) REFERENCES paket_wisata(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detpak_dest FOREIGN KEY (destinasi_id) REFERENCES destinasi(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_detpak_hari CHECK (hari_ke > 0),
    CONSTRAINT chk_detpak_urutan CHECK (urutan > 0),
    CONSTRAINT uk_detpak_paket_dest UNIQUE (paket_wisata_id, destinasi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Create transportasi table (NEW - Vehicle fleet management)
DROP TABLE IF EXISTS transportasi;
CREATE TABLE transportasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destinasi_id INT NULL,
    jenis_kendaraan ENUM('motor', 'mobil', 'shuttle_bus', 'perahu_wisata', 'lainnya') NOT NULL,
    nama_armada VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    harga_sewa DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    deskripsi TEXT NULL,
    status_ketersediaan ENUM('tersedia', 'habis_dipesan', 'nonaktif') DEFAULT 'tersedia' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_transport_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_transport_dest FOREIGN KEY (destinasi_id) REFERENCES destinasi(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_transport_kapasitas CHECK (kapasitas > 0),
    CONSTRAINT chk_transport_harga CHECK (harga_sewa >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Create pesanan table (renamed and restructured from orders)
DROP TABLE IF EXISTS pesanan;
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paket_wisata_id INT NOT NULL,
    transportasi_id INT NULL,
    tanggal_perjalanan DATE NOT NULL,
    jumlah_peserta INT NOT NULL DEFAULT 1,
    total_harga DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'unpaid', 'paid', 'completed', 'cancelled') DEFAULT 'pending' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pesanan_paket FOREIGN KEY (paket_wisata_id) REFERENCES paket_wisata(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pesanan_transport FOREIGN KEY (transportasi_id) REFERENCES transportasi(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_pesanan_peserta CHECK (jumlah_peserta > 0),
    CONSTRAINT chk_pesanan_harga CHECK (total_harga >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Create pembayaran table (renamed and restructured from payments)
DROP TABLE IF EXISTS pembayaran;
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT UNIQUE NOT NULL,
    metode_pembayaran VARCHAR(50) NOT NULL,
    jumlah DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid' NOT NULL,
    bukti_transfer VARCHAR(255) NULL,
    tanggal_bayar TIMESTAMP NULL,
    
    CONSTRAINT fk_pembayaran_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_pembayaran_jumlah CHECK (jumlah >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Create review table (renamed and restructured from reviews)
DROP TABLE IF EXISTS review;
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paket_wisata_id INT NOT NULL,
    rating INT NOT NULL,
    komentar TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_review_paket FOREIGN KEY (paket_wisata_id) REFERENCES paket_wisata(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Create notifikasi table (NEW - Notification system)
DROP TABLE IF EXISTS notifikasi;
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'transaksi', 'alert') DEFAULT 'info' NOT NULL,
    is_read BOOLEAN DEFAULT FALSE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    CONSTRAINT fk_notifikasi_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- PHASE 4: CREATE INDEXES FOR PERFORMANCE
-- ============================================================================

-- Users table indexes
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_email ON users(email);

-- Destinasi table indexes
CREATE INDEX idx_destinasi_lokasi ON destinasi(lokasi);

-- Detail paket destinasi indexes
CREATE INDEX idx_detpak_paket ON detail_paket_destinasi(paket_wisata_id);
CREATE INDEX idx_detpak_dest ON detail_paket_destinasi(destinasi_id);

-- Transportasi table indexes
CREATE INDEX idx_transport_user ON transportasi(user_id);
CREATE INDEX idx_transport_dest ON transportasi(destinasi_id);
CREATE INDEX idx_transport_status ON transportasi(status_ketersediaan);

-- Pesanan table indexes
CREATE INDEX idx_pesanan_user ON pesanan(user_id);
CREATE INDEX idx_pesanan_paket ON pesanan(paket_wisata_id);
CREATE INDEX idx_pesanan_status ON pesanan(status);
CREATE INDEX idx_pesanan_tanggal ON pesanan(tanggal_perjalanan);

-- Pembayaran table indexes
CREATE INDEX idx_pembayaran_status ON pembayaran(status);

-- Review table indexes
CREATE INDEX idx_review_user ON review(user_id);
CREATE INDEX idx_review_paket ON review(paket_wisata_id);
CREATE INDEX idx_review_rating ON review(rating);

-- Notifikasi table indexes
CREATE INDEX idx_notif_user ON notifikasi(user_id);
CREATE INDEX idx_notif_read ON notifikasi(is_read);
CREATE INDEX idx_notif_created ON notifikasi(created_at);

-- ============================================================================
-- PHASE 5: SEED INITIAL DATA
-- ============================================================================

-- Insert roles data (MANDATORY - must be inserted first)
INSERT INTO roles (id, nama_role, deskripsi) VALUES
(1, 'admin', 'Administrator Sistem'),
(2, 'pengelola_wisata', 'Pengelola destinasi dan paket wisata'),
(3, 'wisatawan', 'Pengguna yang melakukan reservasi wisata');

-- Insert default admin user (Password: admin123 - bcrypt hash)
INSERT INTO users (id, role_id, username, password, email, nama_lengkap, no_telepon) VALUES
(1, 1, 'admin', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'admin@baharichain.com', 'Administrator Sistem', '081234567890');

-- Insert sample destinations
INSERT INTO destinasi (id, nama, deskripsi, lokasi, latitude, longitude) VALUES
(1, 'Pantai Talang Siring', 'Pantai dengan panorama pasir putih dan hutan mangrove', 'Pamekasan, Madura', -7.161100, 113.564500),
(2, 'Pantai Lon Malang', 'Pantai terkenal dengan pasir putih dan pohon cemara udang', 'Sampang, Madura', -6.904500, 113.250500),
(3, 'Pantai Lombang', 'Pantai legendaris Sumenep dengan pasir halus dan cemara langka', 'Sumenep, Madura', -6.837500, 114.024500);

-- Insert sample tourism packages
INSERT INTO paket_wisata (id, nama_paket, deskripsi, harga, durasi_hari) VALUES
(1, 'Paket Relaksasi Pantai Talang Siring', 'Trip harian menikmati keindahan pantai dan wisata mangrove', 150000.00, 1),
(2, 'Paket Eksplorasi Lon Malang 2 Hari', 'Eksplorasi pantai, wahana air, dan camping ground', 300000.00, 2),
(3, 'Paket Lombang Premium 3 Hari', 'Pengalaman pantai premium dengan fasilitas lengkap', 500000.00, 3);

-- Link destinations to packages (detail_paket_destinasi)
INSERT INTO detail_paket_destinasi (paket_wisata_id, destinasi_id, hari_ke, urutan) VALUES
(1, 1, 1, 1),  -- Paket 1: Destinasi 1 pada hari 1 urutan 1
(2, 2, 1, 1),  -- Paket 2: Destinasi 2 pada hari 1 urutan 1
(2, 2, 2, 1),  -- Paket 2: Destinasi 2 pada hari 2 urutan 1
(3, 3, 1, 1),  -- Paket 3: Destinasi 3 pada hari 1 urutan 1
(3, 3, 2, 1),  -- Paket 3: Destinasi 3 pada hari 2 urutan 1
(3, 3, 3, 1);  -- Paket 3: Destinasi 3 pada hari 3 urutan 1

-- Insert sample transportation
INSERT INTO transportasi (user_id, destinasi_id, jenis_kendaraan, nama_armada, kapasitas, harga_sewa, status_ketersediaan) VALUES
(1, 2, 'perahu_wisata', 'KM Bahari Sejahtera', 20, 200000.00, 'tersedia'),
(1, 1, 'shuttle_bus', 'Shuttle Bus Wisata', 30, 150000.00, 'tersedia'),
(1, 3, 'mobil', 'Mobil Avanza', 6, 250000.00, 'habis_dipesan');

-- ============================================================================
-- PHASE 6: CLEANUP - DROP OLD BACKUP TABLES (Optional - do this only after verifying migration)
-- ============================================================================
-- Uncomment these lines AFTER verifying the migration was successful
-- DROP TABLE IF EXISTS users_old;
-- DROP TABLE IF EXISTS destinations_old;
-- DROP TABLE IF EXISTS orders_old;
-- DROP TABLE IF EXISTS payments_old;
-- DROP TABLE IF EXISTS reviews_old;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- MIGRATION COMPLETED
-- ============================================================================
SELECT 'Migration completed successfully!' as status;
