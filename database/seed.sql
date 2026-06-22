-- ============================================================================
-- SEED DATA: BAHARICHAIN (Platform Reservasi Pariwisata Bahari)
-- Version: 3.0
-- Date: 2026-06-14
-- ============================================================================
-- Password untuk semua user: admin123
-- Bcrypt Hash: $2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK

-- ============================================================================
-- 1. SEED: roles (Peran Pengguna)
-- ============================================================================
INSERT INTO roles (id, nama_role, deskripsi) VALUES
(1, 'admin', 'Administrator sistem dengan akses penuh untuk verifikasi pembayaran dan manajemen platform'),
(2, 'pengelola_wisata', 'Pengelola destinasi, paket wisata, dan armada transportasi'),
(3, 'wisatawan', 'Wisatawan yang melakukan eksplorasi dan reservasi paket wisata');

-- ============================================================================
-- 2. SEED: users (Pengguna Sistem)
-- ============================================================================
INSERT INTO users (id, role_id, username, password, email, nama_lengkap, no_telepon) VALUES
(1, 1, 'admin', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'admin@baharichain.com', 'Administrator BahariChain', '081234567890'),
(2, 2, 'pengelola_madura', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'pengelola@madura.com', 'Pengelola Wisata Madura', '081234567891'),
(3, 2, 'pengelola_sampang', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'sampang@wisata.com', 'Pengelola Sampang Tour', '081234567892'),
(4, 3, 'budi_wisatawan', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'budi@gmail.com', 'Budi Santoso', '081234567893'),
(5, 3, 'siti_tourist', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'siti@gmail.com', 'Siti Nurhaliza', '081234567894');

-- ============================================================================
-- 3. SEED: destinasi (Destinasi Wisata Bahari)
-- ============================================================================
INSERT INTO destinasi (id, nama, deskripsi, lokasi, latitude, longitude, foto_path) VALUES
(1, 'Pantai Talang Siring', 
    'Pantai Talang Siring merupakan destinasi wisata alam yang terletak di Pamekasan. Menyajikan panorama pantai pasir putih yang indah berpadu dengan kawasan hutan mangrove yang rindang dan sejuk.',
    'Pamekasan, Madura', -7.161100, 113.564500, 'assets/uploads/destinasi/talang_siring.jpg'),

(2, 'Pantai Lon Malang', 
    'Pantai Lon Malang terkenal dengan keindahan pantai pasir putih bersih dan deretan pohon cemara udang yang berjajar rapi di sepanjang bibir pantai.',
    'Sampang, Madura', -6.904500, 113.250500, 'assets/uploads/destinasi/lon_malang.jpg'),

(3, 'Pantai Lombang', 
    'Pantai Lombang adalah salah satu pantai legendaris di Sumenep yang terkenal karena pasir putihnya yang halus dan hamparan pohon cemara udang.',
    'Sumenep, Madura', -6.837500, 114.024500, 'assets/uploads/destinasi/lombang.jpg');

-- ============================================================================
-- 4. SEED: paket_wisata (Paket Liburan)
-- ============================================================================
INSERT INTO paket_wisata (id, nama_paket, deskripsi, harga, durasi_hari, foto_path) VALUES
(1, 'Paket Relaksasi Pantai Talang Siring', 'Trip harian menikmati keindahan pantai dan wisata mangrove.', 150000.00, 1, 'assets/uploads/paket/paket_talang_siring.jpg'),
(2, 'Paket Eksplorasi Lon Malang 2 Hari', 'Eksplorasi pantai, wahana air, dan camping ground.', 300000.00, 2, 'assets/uploads/paket/paket_lon_malang.jpg');

-- ============================================================================
-- 5. SEED: detail_paket_destinasi
-- ============================================================================
INSERT INTO detail_paket_destinasi (paket_wisata_id, destinasi_id, hari_ke, urutan) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(2, 2, 2, 1);

-- ============================================================================
-- 6. SEED: transportasi (Armada Transportasi)
-- ============================================================================
INSERT INTO transportasi (id, user_id, destinasi_id, jenis_kendaraan, nama_armada, kapasitas, harga_sewa, status_ketersediaan) VALUES
(1, 2, 2, 'perahu_wisata', 'KM Bahari Sejahtera', 20, 200000.00, 'tersedia'),
(2, 3, 2, 'mobil', 'Avanza 2023', 6, 250000.00, 'tersedia');

-- ============================================================================
-- 7. SEED: pesanan (Sample Reservasi)
-- ============================================================================
INSERT INTO pesanan (id, user_id, paket_wisata_id, transportasi_id, tanggal_perjalanan, jumlah_peserta, total_harga, status) VALUES
(1, 4, 1, 1, '2026-06-20', 4, 800000.00, 'paid'),
(2, 5, 2, NULL, '2026-06-25', 2, 600000.00, 'pending');

-- ============================================================================
-- 8. SEED: pembayaran (Payment Records)
-- ============================================================================
INSERT INTO pembayaran (id, pesanan_id, metode_pembayaran, jumlah, status, bukti_transfer, tanggal_bayar) VALUES
(1, 1, 'Transfer Bank BCA', 800000.00, 'paid', 'assets/uploads/bukti/bukti_001.jpg', '2026-06-14 10:30:00');

-- ============================================================================
-- 9. SEED: review (Ulasan Rating Paket)
-- ============================================================================
INSERT INTO review (id, user_id, paket_wisata_id, rating, komentar) VALUES
(1, 4, 1, 5, 'Paket wisata sangat bagus! Pantai Talang Siring indah sekali.');

-- ============================================================================
-- 10. SEED: notifikasi (Sample Notifications)
-- ============================================================================
INSERT INTO notifikasi (id, user_id, judul, pesan, tipe, is_read) VALUES
(1, 4, 'Pembayaran Dikonfirmasi', 'Pembayaran Anda telah dikonfirmasi.', 'transaksi', TRUE);
