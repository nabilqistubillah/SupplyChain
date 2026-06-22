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
    'Pantai Talang Siring merupakan destinasi wisata alam yang terletak di Pamekasan. Menyajikan panorama pantai pasir putih yang indah berpadu dengan kawasan hutan mangrove yang rindang dan sejuk. Pengunjung dapat menikmati wisata jembatan mangrove, spot foto gardu pandang, dan menikmati kuliner seafood segar.',
    'Pamekasan, Madura', -7.161100, 113.564500, 'assets/uploads/destinasi/talang_siring.jpg'),

(2, 'Pantai Lon Malang', 
    'Pantai Lon Malang terkenal dengan keindahan pantai pasir putih bersih dan deretan pohon cemara udang yang berjajar rapi di sepanjang bibir pantai. Terletak di Sampang, pantai ini menawarkan berbagai wahana air seperti banana boat, jet ski, dan area camping ground yang nyaman untuk berkemah bersama keluarga.',
    'Sampang, Madura', -6.904500, 113.250500, 'assets/uploads/destinasi/lon_malang.jpg'),

(3, 'Pantai Lombang', 
    'Pantai Lombang adalah salah satu pantai legendaris di Sumenep yang terkenal karena pasir putihnya yang halus dan hamparan pohon cemara udang yang langka dan sangat rindang. Suasana tenang dan sejuk menjadikan pantai ini cocok untuk relaksasi, berfoto, dan menikmati sunrise/sunset yang memukau.',
    'Sumenep, Madura', -6.837500, 114.024500, 'assets/uploads/destinasi/lombang.jpg'),

(4, 'Pulau Gili Labak', 
    'Pulau kecil eksotis dengan air laut jernih berwarna biru kehijauan dan terumbu karang yang indah. Cocok untuk snorkeling dan diving. Akses menggunakan perahu dari pelabuhan Kalianget sekitar 1 jam perjalanan laut.',
    'Sumenep, Madura', -7.216389, 113.951944, 'assets/uploads/destinasi/gili_labak.jpg'),

(5, 'Pantai Sembilan', 
    'Pantai dengan karakteristik unik: memiliki 9 bukit kecil (gumuk) yang mengelilingi pantai. Pasir putih yang lembut dan ombak yang tenang menjadikan tempat ini ideal untuk berenang dan bermain air bersama keluarga.',
    'Bangkalan, Madura', -7.0500, 112.7500, 'assets/uploads/destinasi/sembilan.jpg');

-- ============================================================================
-- 4. SEED: paket_wisata (Paket Liburan)
-- ============================================================================
INSERT INTO paket_wisata (id, nama_paket, deskripsi, harga, durasi_hari, foto_path) VALUES
(1, 'Paket Relaksasi Pantai Talang Siring', 
    'Trip harian lengkap dengan agenda berkunjung ke Pantai Talang Siring. Termasuk wisata mangrove tracking, spot foto gardu pandang, makan siang seafood, dan free time bermain di pantai. Cocok untuk keluarga dan rombongan.',
    150000.00, 1, 'assets/uploads/paket/paket_talang_siring.jpg'),

(2, 'Paket Eksplorasi Lon Malang 2 Hari', 
    'Paket 2 hari 1 malam di Pantai Lon Malang. Hari 1: Check-in homestay, wahana banana boat, sunset viewing, BBQ malam. Hari 2: Sunrise tracking, free time pantai, check-out. Termasuk penginapan, makan 3x, dan wahana air.',
    300000.00, 2, 'assets/uploads/paket/paket_lon_malang.jpg'),

(3, 'Paket Lombang Premium 3 Hari', 
    'Paket premium 3 hari 2 malam di Pantai Lombang Sumenep. Fasilitas homestay AC, makan 3x sehari, tour guide, sewa kuda pantai, kunjungan ke Keraton Sumenep, dan oleh-oleh batik tulis Madura.',
    500000.00, 3, 'assets/uploads/paket/paket_lombang.jpg'),

(4, 'Paket Petualangan Gili Labak', 
    'Paket day trip ke Pulau Gili Labak. Penjemputan dari hotel, perjalanan perahu cepat, snorkeling equipment, makan siang di pulau, dokumentasi underwater, pulang sore. Minimal 4 orang.',
    400000.00, 1, 'assets/uploads/paket/paket_gili_labak.jpg'),

(5, 'Paket Madura Coastal Tour 5 Hari', 
    'Paket lengkap 5 hari 4 malam menjelajahi 5 pantai utama Madura: Sembilan, Talang Siring, Lon Malang, Lombang, dan Gili Labak. Termasuk transportasi AC, penginapan hotel bintang 3, tour guide, dan semua tiket masuk.',
    1500000.00, 5, 'assets/uploads/paket/paket_coastal_tour.jpg');

-- ============================================================================
-- 5. SEED: detail_paket_destinasi (Junction M:N Paket & Destinasi)
-- ============================================================================
INSERT INTO detail_paket_destinasi (paket_wisata_id, destinasi_id, hari_ke, urutan) VALUES
-- Paket 1: Relaksasi Talang Siring (1 hari)
(1, 1, 1, 1),

-- Paket 2: Eksplorasi Lon Malang (2 hari)
(2, 2, 1, 1),
(2, 2, 2, 1),

-- Paket 3: Lombang Premium (3 hari)
(3, 3, 1, 1),
(3, 3, 2, 1),
(3, 3, 3, 1),

-- Paket 4: Petualangan Gili Labak (1 hari)
(4, 4, 1, 1),

-- Paket 5: Madura Coastal Tour (5 hari - multi destinasi)
(5, 5, 1, 1),  -- Hari 1: Pantai Sembilan
(5, 1, 2, 1),  -- Hari 2: Talang Siring
(5, 2, 3, 1),  -- Hari 3: Lon Malang
(5, 3, 4, 1),  -- Hari 4: Lombang
(5, 4, 5, 1);  -- Hari 5: Gili Labak

-- ============================================================================
-- 6. SEED: transportasi (Armada Transportasi)
-- ============================================================================
INSERT INTO transportasi (id, user_id, destinasi_id, jenis_kendaraan, nama_armada, kapasitas, harga_sewa, deskripsi, status_ketersediaan) VALUES
(1, 2, 2, 'perahu_wisata', 'Perahu Bahari Sejahtera', 20, 200000.00, 
    'Perahu wisata modern dengan life jacket lengkap untuk rute Lon Malang - Gili Labak', 'tersedia'),

(2, 2, 1, 'shuttle_bus', 'Bus Wisata Madura Express', 30, 150000.00, 
    'Shuttle bus ber-AC dengan sound system untuk perjalanan antar destinasi', 'tersedia'),

(3, 3, 2, 'mobil', 'Avanza 2023', 6, 250000.00, 
    'Mobil Avanza tahun 2023 lengkap dengan driver berpengalaman rute wisata Madura', 'tersedia'),

(4, 3, 3, 'mobil', 'Innova Reborn', 7, 300000.00, 
    'Toyota Innova Reborn dengan fasilitas AC, audio system, dan driver profesional', 'habis_dipesan'),

(5, 2, 4, 'perahu_wisata', 'Speed Boat Gili Express', 15, 350000.00, 
    'Speed boat cepat dan aman untuk trip ke Pulau Gili Labak dengan kapten berpengalaman', 'tersedia'),

(6, 3, 5, 'motor', 'Motor Scoopy 2024', 2, 80000.00, 
    'Sewa motor harian Honda Scoopy untuk eksplor sekitar pantai Bangkalan', 'tersedia');

-- ============================================================================
-- 7. SEED: pesanan (Sample Reservasi)
-- ============================================================================
INSERT INTO pesanan (id, user_id, paket_wisata_id, transportasi_id, tanggal_perjalanan, jumlah_peserta, total_harga, status) VALUES
(1, 4, 1, 2, '2026-06-20', 4, 750000.00, 'paid'),      -- Budi pesan Paket 1 + shuttle bus (4 pax × 150k + 150k transport)
(2, 5, 2, NULL, '2026-06-25', 2, 600000.00, 'unpaid'), -- Siti pesan Paket 2 tanpa transport (2 pax × 300k)
(3, 4, 4, 5, '2026-07-01', 5, 2350000.00, 'pending'); -- Budi pesan Paket 4 + speed boat (5 pax × 400k + 350k boat)

-- ============================================================================
-- 8. SEED: pembayaran (Payment Records)
-- ============================================================================
INSERT INTO pembayaran (id, pesanan_id, metode_pembayaran, jumlah, status, bukti_transfer, tanggal_bayar) VALUES
(1, 1, 'Transfer Bank BCA', 750000.00, 'paid', 'assets/uploads/bukti/bukti_001.jpg', '2026-06-14 10:30:00'),
(2, 2, 'Transfer Bank Mandiri', 600000.00, 'unpaid', 'assets/uploads/bukti/bukti_002.jpg', NULL);

-- ============================================================================
-- 9. SEED: review (Ulasan Rating Paket)
-- ============================================================================
INSERT INTO review (id, user_id, paket_wisata_id, rating, komentar) VALUES
(1, 4, 1, 5, 'Paket wisata sangat bagus! Pantai Talang Siring indah sekali, mangrovenya asri dan sejuk. Tour guide ramah dan profesional. Highly recommended!'),
(2, 5, 2, 4, 'Overall bagus, cuma homestay agak kurang bersih. Tapi pantai Lon Malang memang cantik banget, wahana banana boat seru!');

-- ============================================================================
-- 10. SEED: notifikasi (Sample Notifications)
-- ============================================================================
INSERT INTO notifikasi (id, user_id, judul, pesan, tipe, is_read) VALUES
(1, 4, 'Pembayaran Dikonfirmasi', 'Pembayaran Anda untuk Paket Relaksasi Pantai Talang Siring telah dikonfirmasi. Perjalanan Anda dijadwalkan pada 20 Juni 2026. Selamat berlibur!', 'transaksi', TRUE),
(2, 5, 'Menunggu Verifikasi Pembayaran', 'Bukti transfer Anda sedang diverifikasi oleh admin. Mohon tunggu maksimal 1x24 jam untuk konfirmasi pembayaran.', 'transaksi', FALSE),
(3, 4, 'Promo Spesial Akhir Tahun', 'Dapatkan diskon 25% untuk paket wisata 5 hari Madura Coastal Tour! Promo terbatas hingga 31 Desember 2026.', 'info', FALSE),
(4, 2, 'Pesanan Baru Masuk', 'Anda mendapat pesanan baru untuk Paket Eksplorasi Lon Malang dari Siti Nurhaliza. Silakan cek dashboard pengelola.', 'alert', FALSE);

-- ============================================================================
-- SEEDING COMPLETED
-- ============================================================================
SELECT 'Seeding completed successfully!' as status;
SELECT 'Total roles: 3' as info;
SELECT 'Total users: 5 (1 admin, 2 pengelola, 2 wisatawan)' as info;
SELECT 'Total destinasi: 5 pantai' as info;
SELECT 'Total paket_wisata: 5 paket' as info;
SELECT 'Total transportasi: 6 armada' as info;
