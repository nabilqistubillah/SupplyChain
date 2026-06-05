-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 03:18 PM
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
-- Database: `mbt_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Transportasi'),
(2, 'Akomodasi'),
(3, 'Kuliner & Souvenir');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `description`, `location`, `latitude`, `longitude`, `image_path`, `created_at`) VALUES
(1, 'Pantai Talang Siring', 'Pantai Talang Siring merupakan destinasi wisata alam yang terletak di Pamekasan. Menyajikan panorama pantai pasir putih yang indah berpadu dengan kawasan hutan mangrove yang rindang dan sejuk.', 'Pamekasan, Madura', -7.16110000, 113.56450000, 'assets/images/talang_siring.jpg', '2026-06-02 05:32:22'),
(2, 'Pantai Lon Malang', 'Pantai Lon Malang terkenal dengan keindahan pantai pasir putih bersih dan deretan pohon cemara udang yang berjajar rapi di sepanjang bibir pantai. Terletak di Sampang.', 'Sampang, Madura', -6.90450000, 113.25050000, 'assets/images/lon_malang.jpg', '2026-06-02 05:32:22'),
(3, 'Pantai Lombang', 'Pantai Lombang adalah salah satu pantai legendaris di Sumenep yang terkenal karena pasir putihnya yang halus dan hamparan pohon cemara udang yang langka dan sangat rindang.', 'Sumenep, Madura', -6.83750000, 114.02450000, 'assets/images/lombang.jpg', '2026-06-02 05:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `destination_rides`
--

CREATE TABLE `destination_rides` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `ride_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_rides`
--

INSERT INTO `destination_rides` (`id`, `destination_id`, `ride_name`, `description`, `price`) VALUES
(1, 1, 'Wisata Mangrove', 'Petualangan berjalan menyusuri jembatan kayu di tengah hutan mangrove yang asri.', 10000.00),
(2, 1, 'Spot Foto Gardu Pandang', 'Spot berfoto dengan latar belakang pemandangan laut lepas dari ketinggian.', 5000.00),
(3, 2, 'Banana Boat', 'Wahana air seru mengelilingi pantai Lon Malang dengan perahu pisang berkecepatan tinggi.', 25000.00),
(4, 2, 'Camping Ground', 'Fasilitas sewa area berkemah di bawah rindangnya pohon cemara udang.', 50000.00),
(5, 3, 'Sewa Kuda Pantai', 'Aktivitas menunggangi kuda menyusuri garis pantai Lombang yang membentang luas.', 35000.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid','refunded') DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `vendor_id`, `category_id`, `name`, `price`, `stock`, `description`, `image_path`) VALUES
(1, 1, 1, 'Sewa Mobil Avanza + Driver', 450000.00, 5, 'Layanan sewa mobil Avanza harian sudah termasuk sopir lokal yang ramah dan berpengalaman untuk rute wisata Madura.', 'assets/images/sewa_avanza.jpg'),
(2, 1, 2, 'Homestay Cemara Lon Malang', 250000.00, 3, 'Penginapan homestay nyaman dengan fasilitas AC, TV, kamar mandi dalam, tepat berada di dekat area Pantai Lon Malang.', 'assets/images/homestay.jpg'),
(3, 1, 3, 'Camilan Rengginang Lorjuk Khas Madura', 15000.00, 100, 'Kerupuk rengginang gurih dengan taburan lorjuk (kerang kecil khas Madura) yang renyah dan nikmat.', 'assets/images/rengginang.jpg'),
(4, 1, 3, 'Kain Batik Tulis Madura Gentongan', 350000.00, 10, 'Batik tulis tradisional Madura dengan motif Gentongan yang dibuat dengan teknik pewarnaan alami yang tahan lama.', 'assets/images/batik_tulis.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `courier` varchar(100) NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  `status` enum('processing','shipping','delivered') DEFAULT 'processing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','vendor','buyer','wisatawan') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin123', 'rahasia123', 'admin@mbt.com', 'admin', '2026-06-02 05:32:22'),
(2, 'vendor_umkm', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'vendor@mbt.com', 'vendor', '2026-06-02 05:32:22'),
(3, 'hotel_madura', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'hotel@mbt.com', 'buyer', '2026-06-02 05:32:22'),
(4, 'budi_wisatawan', '$2y$10$rGPGHY40VJsF8FJ1SsssuePXVPb6WFdamwRijUOR3ZU6EAbZ6yLOK', 'budi@gmail.com', 'wisatawan', '2026-06-02 05:32:22'),
(5, 'NBFT', '$2y$10$9pU1J91zWx5R9ADHygY7ReXua3gAdC4r.PjU0FCYB/9oMdZVIe.hy', 'kmlbilqistkmlbilqist@gmail.com', 'buyer', '2026-06-02 05:45:51'),
(6, 'fitri', '$2y$10$5.jYTDdC9Qns3dIhZ0z5qO1TJ8JmIqEiLomBpc3logCIQODvP//cK', 'fitri@gmail.com', 'wisatawan', '2026-06-02 06:04:36');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_profile`
--

CREATE TABLE `vendor_profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `business_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 5.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_profile`
--

INSERT INTO `vendor_profile` (`id`, `user_id`, `destination_id`, `business_name`, `address`, `contact`, `rating`) VALUES
(1, 2, 2, 'CV Madura Souvenir & Transport', 'Jl. Raya Lon Malang No. 12, Sampang', '081234567890', 4.85);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_statistics`
--

CREATE TABLE `visitor_statistics` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `local_tourists` int(11) DEFAULT 0,
  `regional_tourists` int(11) DEFAULT 0,
  `foreign_tourists` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_statistics`
--

INSERT INTO `visitor_statistics` (`id`, `destination_id`, `visit_date`, `local_tourists`, `regional_tourists`, `foreign_tourists`) VALUES
(1, 1, '2026-05-27', 120, 45, 2),
(2, 1, '2026-05-28', 130, 50, 3),
(3, 1, '2026-05-29', 90, 30, 1),
(4, 1, '2026-05-30', 150, 60, 5),
(5, 1, '2026-05-31', 210, 80, 8),
(6, 1, '2026-06-01', 250, 110, 12),
(7, 1, '2026-06-02', 180, 75, 4),
(8, 2, '2026-05-27', 150, 70, 4),
(9, 2, '2026-05-28', 160, 85, 5),
(10, 2, '2026-05-29', 110, 45, 2),
(11, 2, '2026-05-30', 180, 90, 6),
(12, 2, '2026-05-31', 240, 130, 10),
(13, 2, '2026-06-01', 300, 160, 15),
(14, 2, '2026-06-02', 210, 95, 7),
(15, 3, '2026-05-27', 90, 30, 1),
(16, 3, '2026-05-28', 110, 40, 2),
(17, 3, '2026-05-29', 80, 25, 0),
(18, 3, '2026-05-30', 130, 55, 3),
(19, 3, '2026-05-31', 180, 70, 5),
(20, 3, '2026-06-01', 220, 90, 7),
(21, 3, '2026-06-02', 140, 50, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `destination_rides`
--
ALTER TABLE `destination_rides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendor_profile`
--
ALTER TABLE `vendor_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `visitor_statistics`
--
ALTER TABLE `visitor_statistics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `destination_rides`
--
ALTER TABLE `destination_rides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vendor_profile`
--
ALTER TABLE `vendor_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visitor_statistics`
--
ALTER TABLE `visitor_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `destination_rides`
--
ALTER TABLE `destination_rides`
  ADD CONSTRAINT `destination_rides_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_profile` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_profile` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_profile`
--
ALTER TABLE `vendor_profile`
  ADD CONSTRAINT `vendor_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_profile_ibfk_2` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visitor_statistics`
--
ALTER TABLE `visitor_statistics`
  ADD CONSTRAINT `visitor_statistics_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
