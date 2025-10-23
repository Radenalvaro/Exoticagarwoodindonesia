-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql210.infinityfree.com
-- Generation Time: Jul 14, 2025 at 10:39 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39126676_agarwood_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `hashpassword` varchar(64) NOT NULL COMMENT 'SHA256 hash',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `hashpassword`, `is_active`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'EsiriaJuita', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 1, '2025-05-30 08:39:38', '2025-05-31 23:44:07', '2025-05-31 23:44:07'),
(2, 'RadenAlvaro', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 1, '2025-05-31 11:01:02', '2025-05-31 14:48:37', '2025-05-31 14:48:37'),
(3, 'Admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 1, '2025-06-05 03:08:13', '2025-06-28 05:03:01', '2025-06-28 05:01:56');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`) VALUES
(1, 'Kayu Gaharu', 'kayu_gaharu', 'Premium Indonesian agarwood products', 1, '2025-05-26 10:46:26'),
(2, 'Minyak Gaharu', 'minyak_gaharu', 'Pure agarwood essential oils', 1, '2025-05-26 10:46:26');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `is_read`, `created_at`) VALUES
(65, 'Muzzhafar makarin', 'radenghazyalvaro@gmail.com', '+6285813661112', 'Lebih baik lagi', 1, '2025-06-28 05:09:58'),
(66, 'belerbaib', 'asdeasd@gsdgfsf.t', '3e12312', 'singkong kucing rebus', 0, '2025-06-30 01:10:54'),
(67, 'belerbaib', 'asdeasd@gsdgfsf.t', '3e12312', 'singkong kucing rebus', 0, '2025-07-01 05:37:14');

-- --------------------------------------------------------

--
-- Table structure for table `loginmaintainance`
--

CREATE TABLE `loginmaintainance` (
  `id` int(11) NOT NULL,
  `username_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of username',
  `password_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of password',
  `namaayah_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of nama ayah',
  `namaibu_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of nama ibu',
  `tanggallahir_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of tanggal lahir',
  `seedphrase_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of 12 kata unik',
  `combined_hash` varchar(64) NOT NULL COMMENT 'SHA256 hash of all data combined',
  `hash_timestamp` int(11) NOT NULL COMMENT 'Timestamp for hash generation (30 second intervals)',
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loginmaintainance`
--

INSERT INTO `loginmaintainance` (`id`, `username_hash`, `password_hash`, `namaayah_hash`, `namaibu_hash`, `tanggallahir_hash`, `seedphrase_hash`, `combined_hash`, `hash_timestamp`, `last_updated`, `is_active`, `created_at`) VALUES
(1, '7bc668b1c97d5f0a1369d5b937a7baff4ff3fbd213b99b82de92f4e973f623f4', '1c7fd337399c0b8a2478a45921b3a145fd5aaa2195ee2b0f58feac00edf4ce64', '3a462179a68052f475cd153a9025163ea51cc823a001dbef1c80964b529608dd', '0c329b9449d4c05e6aeb322d1486245d37d9ad74643584c342b1986ad8f210e2', 'b015789b1b3ca7fe7c600db141a26856f86bd7d8a1cf2d0107747a9933b843bb', '3a94f1c26912bb7502578afc6fc5329746c68b0c6dd56172c117e25d245d930f', 'c5750f15a2776ccad9ba27dc5c422fb80daa887b478c15afdea011fcac46d7e0', 175108686, '2025-06-28 05:01:09', 1, '2025-05-31 01:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('kayu_gaharu','minyak_gaharu') NOT NULL,
  `grade` varchar(50) NOT NULL,
  `origin` varchar(100) DEFAULT 'Indonesia',
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_settings`
--

CREATE TABLE `website_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `website_settings`
--

INSERT INTO `website_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Exotic Agarwood Indonesia', 'text', 'Website name', '2025-05-26 10:46:26'),
(2, 'contact_email', 'info@exoticagarwood.com', 'text', 'Main contact email', '2025-05-26 10:46:26'),
(3, 'contact_phone', '+62 815-5402-0227', 'text', 'Main contact phone', '2025-05-26 10:46:26'),
(4, 'whatsapp_number', '+6281554020227', 'text', 'WhatsApp contact number', '2025-05-26 10:46:26'),
(5, 'company_address', 'Jl. Agarwood Premium No. 123, Jakarta, Indonesia 12345', 'text', 'Company address', '2025-05-26 10:46:26'),
(6, 'currency', 'USD', 'text', 'Default currency', '2025-05-26 10:46:26'),
(7, 'tax_rate', '0.10', 'number', 'Tax rate (10%)', '2025-05-26 10:46:26'),
(8, 'shipping_cost', '50.00', 'number', 'Default shipping cost', '2025-05-26 10:46:26'),
(9, 'min_order_amount', '100.00', 'number', 'Minimum order amount', '2025-05-26 10:46:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `loginmaintainance`
--
ALTER TABLE `loginmaintainance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_grade` (`grade`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_primary` (`is_primary`);

--
-- Indexes for table `website_settings`
--
ALTER TABLE `website_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `loginmaintainance`
--
ALTER TABLE `loginmaintainance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `website_settings`
--
ALTER TABLE `website_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
