-- Seeding Data for MBT (Madura Blue Tourism)
-- d:/DailyLearn/myCreated/me/database/seed.sql

-- 1. Seed Users (Password untuk semua user adalah: admin123)
-- Hash: $2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK
INSERT INTO users (id, username, password, email, role) VALUES
(1, 'admin', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'admin@mbt.com', 'admin'),
(2, 'vendor_umkm', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'vendor@mbt.com', 'vendor'),
(3, 'hotel_madura', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'hotel@mbt.com', 'buyer'),
(4, 'budi_wisatawan', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'budi@gmail.com', 'wisatawan');

-- 2. Seed Destinations
INSERT INTO destinations (id, name, description, location, latitude, longitude, image_path) VALUES
(1, 'Pantai Talang Siring', 'Pantai Talang Siring merupakan destinasi wisata alam yang terletak di Pamekasan. Menyajikan panorama pantai pasir putih yang indah berpadu dengan kawasan hutan mangrove yang rindang dan sejuk.', 'Pamekasan, Madura', -7.161100, 113.564500, 'assets/images/talang_siring.jpg'),
(2, 'Pantai Lon Malang', 'Pantai Lon Malang terkenal dengan keindahan pantai pasir putih bersih dan deretan pohon cemara udang yang berjajar rapi di sepanjang bibir pantai. Terletak di Sampang.', 'Sampang, Madura', -6.904500, 113.250500, 'assets/images/lon_malang.jpg'),
(3, 'Pantai Lombang', 'Pantai Lombang adalah salah satu pantai legendaris di Sumenep yang terkenal karena pasir putihnya yang halus dan hamparan pohon cemara udang yang langka dan sangat rindang.', 'Sumenep, Madura', -6.837500, 114.024500, 'assets/images/lombang.jpg');

-- 3. Seed Destination Rides
INSERT INTO destination_rides (destination_id, ride_name, description, price) VALUES
(1, 'Wisata Mangrove', 'Petualangan berjalan menyusuri jembatan kayu di tengah hutan mangrove yang asri.', 10000.00),
(1, 'Spot Foto Gardu Pandang', 'Spot berfoto dengan latar belakang pemandangan laut lepas dari ketinggian.', 5000.00),
(2, 'Banana Boat', 'Wahana air seru mengelilingi pantai Lon Malang dengan perahu pisang berkecepatan tinggi.', 25000.00),
(2, 'Camping Ground', 'Fasilitas sewa area berkemah di bawah rindangnya pohon cemara udang.', 50000.00),
(3, 'Sewa Kuda Pantai', 'Aktivitas menunggangi kuda menyusuri garis pantai Lombang yang membentang luas.', 35000.00);

-- 4. Seed Vendor Profiles
INSERT INTO vendor_profile (id, user_id, destination_id, business_name, address, contact, rating) VALUES
(1, 2, 2, 'CV Madura Souvenir & Transport', 'Jl. Raya Lon Malang No. 12, Sampang', '081234567890', 4.85);

-- 5. Seed Categories
INSERT INTO categories (id, name) VALUES
(1, 'Transportasi'),
(2, 'Akomodasi'),
(3, 'Kuliner & Souvenir');

-- 6. Seed Products
INSERT INTO products (id, vendor_id, category_id, name, price, stock, description, image_path) VALUES
(1, 1, 1, 'Sewa Mobil Avanza + Driver', 450000.00, 5, 'Layanan sewa mobil Avanza harian sudah termasuk sopir lokal yang ramah dan berpengalaman untuk rute wisata Madura.', 'assets/images/sewa_avanza.jpg'),
(2, 1, 2, 'Homestay Cemara Lon Malang', 250000.00, 3, 'Penginapan homestay nyaman dengan fasilitas AC, TV, kamar mandi dalam, tepat berada di dekat area Pantai Lon Malang.', 'assets/images/homestay.jpg'),
(3, 1, 3, 'Camilan Rengginang Lorjuk Khas Madura', 15000.00, 100, 'Kerupuk rengginang gurih dengan taburan lorjuk (kerang kecil khas Madura) yang renyah dan nikmat.', 'assets/images/rengginang.jpg'),
(4, 1, 3, 'Kain Batik Tulis Madura Gentongan', 350000.00, 10, 'Batik tulis tradisional Madura dengan motif Gentongan yang dibuat dengan teknik pewarnaan alami yang tahan lama.', 'assets/images/batik_tulis.jpg');

-- 7. Seed Visitor Statistics (Pamekasan - Talang Siring)
INSERT INTO visitor_statistics (destination_id, visit_date, local_tourists, regional_tourists, foreign_tourists) VALUES
(1, CURDATE() - INTERVAL 6 DAY, 120, 45, 2),
(1, CURDATE() - INTERVAL 5 DAY, 130, 50, 3),
(1, CURDATE() - INTERVAL 4 DAY, 90, 30, 1),
(1, CURDATE() - INTERVAL 3 DAY, 150, 60, 5),
(1, CURDATE() - INTERVAL 2 DAY, 210, 80, 8),
(1, CURDATE() - INTERVAL 1 DAY, 250, 110, 12),
(1, CURDATE(), 180, 75, 4);

-- 8. Seed Visitor Statistics (Sampang - Lon Malang)
INSERT INTO visitor_statistics (destination_id, visit_date, local_tourists, regional_tourists, foreign_tourists) VALUES
(2, CURDATE() - INTERVAL 6 DAY, 150, 70, 4),
(2, CURDATE() - INTERVAL 5 DAY, 160, 85, 5),
(2, CURDATE() - INTERVAL 4 DAY, 110, 45, 2),
(2, CURDATE() - INTERVAL 3 DAY, 180, 90, 6),
(2, CURDATE() - INTERVAL 2 DAY, 240, 130, 10),
(2, CURDATE() - INTERVAL 1 DAY, 300, 160, 15),
(2, CURDATE(), 210, 95, 7);

-- 9. Seed Visitor Statistics (Sumenep - Lombang)
INSERT INTO visitor_statistics (destination_id, visit_date, local_tourists, regional_tourists, foreign_tourists) VALUES
(3, CURDATE() - INTERVAL 6 DAY, 90, 30, 1),
(3, CURDATE() - INTERVAL 5 DAY, 110, 40, 2),
(3, CURDATE() - INTERVAL 4 DAY, 80, 25, 0),
(3, CURDATE() - INTERVAL 3 DAY, 130, 55, 3),
(3, CURDATE() - INTERVAL 2 DAY, 180, 70, 5),
(3, CURDATE() - INTERVAL 1 DAY, 220, 90, 7),
(3, CURDATE(), 140, 50, 2);
