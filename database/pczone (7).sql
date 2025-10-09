-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 08, 2025 at 02:34 PM
-- Server version: 8.3.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pczone`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id` int NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL,
  `category_id` int DEFAULT NULL,
  `slug` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`brand_id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `category_id`, `slug`) VALUES
(46, 'Lian Li', 8, 'lian-li-cooler'),
(45, 'Corsair', 8, 'corsair-cooler'),
(44, 'Thermaltake', 8, 'thermaltake-cooler'),
(43, 'Arctic', 8, 'arctic'),
(42, 'be quiet!', 8, 'be-quiet'),
(41, 'NZXT', 8, 'nzxt-cooler'),
(40, 'DeepCool', 8, 'deepcool-cooler'),
(39, 'Cooler Master', 8, 'cooler-master-cooler'),
(38, 'Ant Esports', 7, 'ant-esports-case'),
(37, 'DeepCool', 7, 'deepcool-case'),
(36, 'Antec', 7, 'antec-case'),
(35, 'Thermaltake', 7, 'thermaltake-case'),
(34, 'Cooler Master', 7, 'cooler-master-case'),
(33, 'Lian Li', 7, 'lian-li-case'),
(32, 'NZXT', 7, 'nzxt-case'),
(31, 'NZXT', 6, 'nzxt-psu'),
(30, 'Thermaltake', 6, 'thermaltake-psu'),
(29, 'EVGA', 6, 'evga-psu'),
(28, 'Cooler Master', 6, 'cooler-master-psu'),
(27, 'Antec', 6, 'antec'),
(26, 'Corsair', 6, 'corsair-psu'),
(25, 'Kingston', 5, 'kingston-storage'),
(24, 'ADATA', 5, 'adata-storage'),
(23, 'Crucial', 5, 'crucial-storage'),
(22, 'Seagate', 5, 'seagate'),
(21, 'WD', 5, 'wd'),
(20, 'Samsung', 5, 'samsung-storage'),
(19, 'ADATA', 4, 'adata'),
(18, 'Crucial', 4, 'crucial-ram'),
(17, 'Kingston', 4, 'kingston'),
(16, 'G.Skill', 4, 'gskill'),
(15, 'Corsair', 4, 'corsair-ram'),
(14, 'EVGA', 3, 'evga-mb'),
(13, 'Gigabyte', 3, 'gigabyte-mb'),
(12, 'MSI', 3, 'msi-mb'),
(11, 'ASRock', 3, 'asrock'),
(10, 'ASUS', 3, 'asus-mb'),
(9, 'EVGA', 2, 'evga'),
(8, 'Inno3D', 2, 'inno3d'),
(7, 'ZOTAC', 2, 'zotac'),
(6, 'Gigabyte', 2, 'gigabyte-gpu'),
(5, 'MSI', 2, 'msi-gpu'),
(4, 'ASUS', 2, 'asus-gpu'),
(3, 'NVIDIA', 2, 'nvidia'),
(2, 'AMD', 1, 'amd'),
(1, 'Intel', 1, 'intel'),
(53, 'Logitech', 10, 'logitech-keyboard'),
(54, 'Razer', 10, 'razer-keyboard'),
(55, 'Corsair', 10, 'corsair-keyboard'),
(56, 'HyperX', 10, 'hyperx'),
(57, 'Zebronics', 10, 'zebronics'),
(58, 'Logitech', 11, 'logitech-mouse'),
(59, 'Razer', 11, 'razer-mouse'),
(60, 'Redragon', 11, 'redragon-mouse'),
(61, 'Corsair', 11, 'corsair-mouse'),
(62, 'Zebronics', 11, 'zebronics-mouse'),
(63, 'SteelSeries', 12, 'steelseries-mousepad'),
(64, 'Corsair', 12, 'corsair-mousepad'),
(65, 'Razer', 12, 'razer-mousepad'),
(66, 'Ant Esports', 6, 'ant-esports'),
(67, 'Ant Esports', 10, 'ant-esports-1');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `cart_item_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `product_name` varchar(255) NOT NULL,
  `price` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_item_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_item_id`, `user_id`, `product_id`, `quantity`, `product_name`, `price`, `created_at`) VALUES
(11, 5, 16, 1, 'Intel® Core™ i9-14900K New Gaming Desktop Processor 24 cores (8 P-cores + 16 E-cores) with Integrated Graphics - Unlocked', 55999, '2025-10-04 13:00:14'),
(12, 5, 3, 1, 'Cooler Master RR-212S-20PC-R1 Hyper 212 RGB Black Edition CPU Air Cooler 4 Direct Contact Heat Pipes 120mm RGB Fan', 12000, '2025-10-04 13:00:32'),
(13, 5, 4, 1, 'Ant Esports ICE-C612 V2 ARGB CPU Cooler| Support Intel LGA1200, LGA115X, LGA20XX, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 3499, '2025-10-04 13:00:33'),
(14, 5, 19, 1, 'AMD Ryzen 9 9950X3D Desktop Processor with Integrated Radeon Graphics, 16 cores 32 Threads 128MB Cache Base Clock 4.3 GHz Up to 5.7GHz AM5 Socket System Memory DDR5 Up to 5600 MT/s - 100-100000719WOF', 119000, '2025-10-04 13:00:43'),
(31, 6, 16, 1, 'Intel® Core™ i9-14900K New Gaming Desktop Processor 24 cores (8 P-cores + 16 E-cores) with Integrated Graphics - Unlocked', 86000, '2025-10-05 12:55:47');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `parent_id` int DEFAULT NULL,
  `icon_image` varchar(255) DEFAULT NULL,
  `level` int DEFAULT '0',
  `slug` varchar(250) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int DEFAULT '9999',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `parent_id`, `icon_image`, `level`, `slug`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(12, 'Mousepad', NULL, 'mousepad-icon.webp', 0, 'mousepad', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(11, 'Mouse', NULL, 'mouse-icon.webp', 0, 'mouse', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(10, 'Keyboard', NULL, 'keyboard-icon.webp', 0, 'keyboard', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(8, 'Cooling System', NULL, 'liquid-cooler-icon.webp', 0, 'cooling-system', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(7, 'Cabinet', NULL, 'cabinet-icon.webp', 0, 'cabinet', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(6, 'Power Supply', NULL, 'psu-icon.webp', 0, 'power-supply', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(5, 'Storage', NULL, 'ssd-icon.webp', 0, 'storage', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(4, 'RAM', NULL, 'RAM-icon.webp', 0, 'ram', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(3, 'Motherboard', NULL, 'motherboard-icon.webp', 0, 'motherboard', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(2, 'Graphics Card', NULL, 'graphics-card-icon.webp', 0, 'graphics-card', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(1, 'Processor', NULL, 'Processor-Icon.webp', 0, 'processor', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(13, 'Intel', 1, NULL, 1, 'intel', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(14, 'AMD', 1, NULL, 1, 'amd', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(15, 'SSD', 5, NULL, 1, 'ssd', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(16, 'HDD', 5, NULL, 1, 'hdd', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(17, 'NVMe', 5, NULL, 1, 'nvme', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(18, 'Air Cooler', 8, NULL, 1, 'air-cooler', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(19, 'Liquid Cooler', 8, NULL, 1, 'liquid-cooler', 'active', 9999, '2025-10-02 06:54:51', '2025-10-02 06:54:51'),
(21, 'Razer', 20, NULL, 0, 'razer', 'active', 9999, '2025-10-03 08:35:01', '2025-10-03 08:35:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(6,2) DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `product_id`, `quantity`, `unit_price`, `discount`, `total_price`, `created_at`, `updated_at`) VALUES
(1, 6, 16, 1, 36399.35, 35.00, 36399.35, '2025-10-04 18:32:39', '2025-10-04 18:32:39'),
(2, 6, 2, 1, 23559.24, 24.00, 23559.24, '2025-10-04 18:32:39', '2025-10-04 18:32:39'),
(3, 6, 19, 1, 84490.00, 29.00, 84490.00, '2025-10-04 18:32:39', '2025-10-04 18:32:39'),
(4, 6, 4, 1, 1259.64, 64.00, 1259.64, '2025-10-04 18:32:39', '2025-10-04 18:32:39'),
(5, 6, 2, 1, 23559.24, 24.00, 23559.24, '2025-10-04 19:08:23', '2025-10-04 19:08:23'),
(6, 6, 19, 1, 84490.00, 29.00, 84490.00, '2025-10-04 19:08:23', '2025-10-04 19:08:23'),
(7, 6, 1, 1, 10779.23, 23.00, 10779.23, '2025-10-04 19:08:23', '2025-10-04 19:08:23'),
(8, 6, 17, 1, 33720.00, 20.00, 33720.00, '2025-10-04 19:55:21', '2025-10-04 19:55:21'),
(9, 6, 21, 1, 42699.39, 39.00, 42699.39, '2025-10-04 19:55:21', '2025-10-04 19:55:21'),
(10, 1, 2, 1, 23559.24, 24.00, 23559.24, '2025-10-05 11:40:16', '2025-10-05 11:40:16'),
(11, 1, 16, 1, 36399.35, 35.00, 36399.35, '2025-10-05 11:40:16', '2025-10-05 11:40:16'),
(12, 1, 1, 1, 10779.23, 23.00, 10779.23, '2025-10-05 11:40:16', '2025-10-05 11:40:16'),
(13, 6, 16, 1, 55900.00, 35.00, 55900.00, '2025-10-05 17:57:55', '2025-10-05 17:57:55'),
(14, 6, 19, 1, 84490.00, 29.00, 84490.00, '2025-10-05 17:57:55', '2025-10-05 17:57:55'),
(15, 6, 16, 1, 55900.00, 35.00, 55900.00, '2025-10-05 18:18:07', '2025-10-05 18:18:07'),
(16, 6, 26, 2, 68145.00, 41.00, 136290.00, '2025-10-05 18:18:07', '2025-10-05 18:18:07'),
(17, 6, 27, 1, 121360.00, 26.00, 121360.00, '2025-10-05 18:18:07', '2025-10-05 18:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('cash_on_delivery','credit_card','debit_card','upi') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `payment_method`, `amount`, `currency`, `payment_status`, `created_at`) VALUES
(1, 1, 'credit_card', 36399.35, 'INR', 'Pending', '2025-10-04 18:32:39'),
(2, 2, 'credit_card', 23559.24, 'INR', 'Pending', '2025-10-04 18:32:39'),
(3, 3, 'credit_card', 84490.00, 'INR', 'Pending', '2025-10-04 18:32:39'),
(4, 4, 'credit_card', 1259.64, 'INR', 'Pending', '2025-10-04 18:32:39'),
(5, 5, 'debit_card', 23559.24, 'INR', 'Pending', '2025-10-04 19:08:23'),
(6, 6, 'debit_card', 84490.00, 'INR', 'Pending', '2025-10-04 19:08:23'),
(7, 7, 'debit_card', 10779.23, 'INR', 'Pending', '2025-10-04 19:08:23'),
(8, 8, 'upi', 33720.00, 'INR', 'Pending', '2025-10-04 19:55:21'),
(9, 9, 'upi', 42699.39, 'INR', 'Pending', '2025-10-04 19:55:21'),
(10, 10, 'upi', 23559.24, 'INR', 'Pending', '2025-10-05 11:40:16'),
(11, 11, 'upi', 36399.35, 'INR', 'Pending', '2025-10-05 11:40:16'),
(12, 12, 'upi', 10779.23, 'INR', 'Pending', '2025-10-05 11:40:16'),
(13, 13, 'upi', 55900.00, 'INR', 'Pending', '2025-10-05 17:57:55'),
(14, 14, 'upi', 84490.00, 'INR', 'Pending', '2025-10-05 17:57:55'),
(15, 15, 'cash_on_delivery', 55900.00, 'INR', 'Pending', '2025-10-05 18:18:07'),
(16, 16, 'cash_on_delivery', 136290.00, 'INR', 'Pending', '2025-10-05 18:18:07'),
(17, 17, 'cash_on_delivery', 121360.00, 'INR', 'Pending', '2025-10-05 18:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(250) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `slug` varchar(250) DEFAULT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(6,2) DEFAULT '0.00',
  `stock` int DEFAULT '0',
  `weight` decimal(6,2) DEFAULT NULL,
  `rating` float DEFAULT '0',
  `brand_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `main_image` varchar(255) NOT NULL,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `platform` enum('intel','amd','both','none') NOT NULL DEFAULT 'none',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `sku`, `slug`, `description`, `price`, `discount`, `stock`, `weight`, `rating`, `brand_id`, `category_id`, `main_image`, `image_1`, `image_2`, `image_3`, `platform`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Lian Li O11 Dynamic EVO XL Full-Tower Compter Case/Gaming Cabinet - White | Support EATX/ATX/Micro-ATX/MINI-ITX - G99.O11DEXL-W.in', 'B0CGM6RKV8', 'lian-li-o11-dynamic-evo-xl-full-tower-computer-case-gaming-cabinet-white', 'White, Full-Tower, 522 x 304 x 531.9 mm , 4.0mm Tempered Glass Aluminum 8 Expansion Slots, Storage : Behind MB Tray: 3 X 2.5ʹʹ SSD Hard Drive Cage: 4 X 3.5ʹʹ HDD or 2.5ʹʹ SSD I/O Panel : Power Button , Reset Button , USB 3.0 x 4 , Audio x 1 , USB Type C , Color Button , Mode Button Fan Support : Top - 120mm x3 / 140mm x3, Side- 120mm x3 / 140mm x3, Bottom- 120mm x3/ 140mm x3, Rear- 120 mm x1 or 2 GPU Length Clearance : 460mm(Max) ; CPU Cooler Height Clearance : 167mm(Max)', 30999.00, 24.00, 9, 0.65, 4.9, 33, 7, '610tNgEZ6LL._SX679_.jpg', '61zXV1X5zTL._SX679_.jpg', '712etNmCVRL._SX679_.jpg', '71O8DnFAk5L._SX679_.jpg', 'both', 0, 1, '2025-10-02 01:24:52', '2025-10-06 05:06:21'),
(1, 'NZXT H6 Flow | CC-H61FB-01 | Compact Dual-Chamber Mid-Tower Airflow Case | Panoramic Glass Panels | High-Performance Airflow Panels | Includes 3 x 120mm Fans | Cable Management | Black', 'B0C89FCDFP', 'nzxt-h6-flow', 'Wraparound glass panels with a seamless edge provides an unobstructed view of the inside to highlight key components. Compact dual-chamber design improves overall thermal performance and creates a clean, uncrowded aesthetic. Includes three pre-installed 120mm fans positioned at an ideal angle for superb out-of-the-box cooling. The top and side panels feature an airflow-optimized perforation pattern to enhance overall performance and filter dust. An intuitive cable management system simplifies the build process by using wide channels and straps.', 13999.00, 23.00, 10, 0.65, 4.7, 32, 7, '71x+i8yRgrL._SY450_.jpg', '71YDILR+QnL._SY450_.jpg', '71vtU8bv48L._SY450_.jpg', '71u5IWhR-aL._SY450_.jpg', 'both', 0, 1, '2025-10-02 01:24:52', '2025-10-06 05:06:28'),
(12, 'Intel Core I7-13700F Desktop Processor 16 Cores (8 P-Cores + 8 E-Cores) 30Mb Cache,Up to 5.2 Ghz,LGA 1151', 'B0BQ6CSY9C', 'intel-core-i7-13700f-desktop-processor-16-cores-8-pcores-8-ecores-30mb-cacheup-to-52-ghzlga-1151', '16 cores (8 P-cores + 8 E-cores) and 24 threads\r\nPerformance hybrid architecture integrates two core microarchitectures, prioritizing and distributing workloads to optimize performance\r\nUp to 5.2 GHz. 30M Cache\r\nCompatible with Intel 600 series and 700 series chipset-based motherboards\r\nTurbo Boost Max Technology 3.0, and PCIe 5.0 & 4.0 support. Intel Optane Memory support. Intel Laminar RH1 Cooler included. Discrete graphics required', 51000.00, 43.00, 16, 0.00, 0, 1, 1, '1759555129_image1.jpg', '1759555129_image2.jpg', '1759555129_image3.jpg', '1759555129_image4.jpg', 'intel', 0, 1, '2025-10-03 18:02:31', '2025-10-05 06:14:25'),
(13, 'Intel Core i7-11700K LGA1200 Desktop Processor 8 Cores up to 5GHz 16MB Cache with Integrated Intel UHD 750 Graphics', 'B08X6ND3WP', 'intel-core-i7-11700k-lga1200-desktop-processor-8-cores-up-to-5ghz-16mb-cache-with-integrated-intel-uhd-750-graphics', 'Introducing the newest and fastest 11th Gen Intel Core i7 desktop processor, built based on 14 nm lithography supporting Socket type LGA 1200. The Processors features 8 Core which allow the processor to run multiple programs simultaneously without slowing down the system, while the 16 threads allow instructions to be handled by a single CPU core along with Hyper Threading Technology.\r\nWith 3.60 GHz Base frequency, the Intel Turbo Boost 3.0 technology cranks maximum turbo frequency up to blazing 5.00 GHz. The processor is desirable for a gamer looking for a fantastic in-game experience and a creator that is ready to do more creating and sharing alike.\r\nAll this paired with 16MB of Intel Smart Cache. It has a TDP rating of 125W with max memory size of 128GB dual-channel DDR4 support for up-to 3200Mhz with Intel top notch security features.\r\nThis processor is designed for users who value fast responsiveness and comes with built-in Intel UHD Graphics 750 and 4K support at 60Hz, with the cutting-edge processor architecture. The graphics processor is bundled with DirectX support, OpenGL support and supports up to 3 displays offering you a never like gaming experience.\r\nPlay, record and stream simultaneously with high FPS and effortlessly switch to heavy multitasking workloads.', 58500.00, 39.00, 12, 0.00, 0, 1, 1, '1759555450_image1.jpg', '1759555450_image2.jpg', '1759555450_image3.jpg', '1759555450_image4.jpg', 'intel', 0, 1, '2025-10-03 18:02:31', '2025-10-05 06:14:42'),
(4, 'Ant Esports ICE-C612 V2 ARGB CPU Cooler| Support Intel LGA1200, LGA115X, LGA20XX, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 'B084G3MJPZ', 'ant-esports-ice-c612-v2-argb-cpu-cooler', 'Efficient Heat Dissipation: The Ant Esports ICE-C612 V2 CPU air cooler is designed for optimal heat dissipation, featuring a 153mm tall aluminum heatsink and six 6mm thick copper heatpipes. This advanced cooling solution ensures efficient heat transfer from the CPU to the heatsink, effectively reducing temperatures and maintaining peak performance even during demanding tasks.\r\n   Enhanced Cooling Performance: Equipped with a high-performance PWM 120mm ARGB fan, the ICE-C612 V2 cooler offers not only excellent cooling efficiency but also adds a vibrant visual flair to your system. The fans adjustable speed through pulse-width modulation (PWM) ensures a fine balance between cooling power and noise levels, keeping your CPU operating at an ideal temperature while maintaining a quiet environment\r\n   Optimized Surface Area: The interlocked aluminum heatsink design of the ICE-C612 V2 is engineered to provide a larger surface area for heat dissipation. This design maximizes the contact area between the heatsink and the surrounding air, allowing for quicker and more effective heat dispersion. Whether you are running intensive applications or engaging in heavy gaming sessions, this cooler helps maintain stable and consistent performance.\r\n   Wide Compatibility: The Ant Esports ICE-C612 V2 CPU air cooler offers broad compatibility with major Intel and AMD platforms, including the latest LGA 1700 and AM5 sockets. This versatility makes it an ideal choice for both current and future system builds, allowing you to upgrade your CPU without worrying about changing cooling solutions.\r\n   Easy Installation: Installing the ICE-C612 V2 cooler is a hassle-free process thanks to its user-friendly design. The included mounting hardware and easy-to-follow instructions ensure a smooth installation experience, even for users with minimal technical expertise. With its secure mounting mechanism, you can trust that your cooler will be properly seated for optimal thermal performance.\r\n   Support Intel LGA1200, LGA1150, LGA1151, LGA1155, LGA1156, LGA2066, LGA2011-v3, LGA2011, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 3499.00, 64.00, 6, 0.65, 4.1, 38, 8, '51pCa994ysL._SY450_.jpg', '61PedtDNzIL._SY450_.jpg', '71kG6EFIMwL._SY450_.jpg', '61Yb+64vAkL._SY450_.jpg', 'both', 1, 1, '2025-10-02 01:24:52', '2025-10-06 05:07:56'),
(11, 'Intel Core i7-11700 Desktop Processor 8 Cores up to 4.9 GHz LGA1200 (Intel 500 Series & Select 400 Series Chipset) 65W', 'B08X6QHYDL', 'intel-core-i7-11700-desktop-processor-8-cores-up-to-49-ghz-lga1200-intel-500-series-select-400-series-chipset-65w', 'Compatible with Intel 500 series & select Intel 400 series chipset based motherboards\r\nIntel Turbo Boost Max Technology 3.0 Support\r\nIntel Optane Memory Support\r\nPCIe Gen 4.0 Support\r\nThermal solution included', 45000.00, 44.00, 32, 0.00, 0, 1, 1, '1759554881_image1.jpg', '1759554881_image2.jpg', '1759554881_image3.jpg', '1759554881_image4.jpg', 'intel', 0, 1, '2025-10-03 18:02:31', '2025-10-05 06:14:05'),
(5, 'Intel® Core™ i3-12100 Processor 12M Cache, up to 4.30 GHz', 'B09NPHJLPT', 'intel-core-i3-12100-processor-12m-cache-up-to-430-ghz', 'Intel Core i3-12100 Processor 12M Cache, up to 4.30 GHz\r\nIt ensures a hassle-free usage\r\nIt is durable and long lasting.', 18600.00, 30.00, 20, 0.00, 0, 1, 1, '1759496274_image1.jpg', '17594961701.jpg', '17594961702.jpg', '17594961703.jpg', 'intel', 1, 1, '2025-10-03 01:56:10', '2025-10-05 06:13:26'),
(6, 'Intel® Core™ i5-13600KF Processor 24M Cache, up to 5.10 GHz', 'B0BG64N549', 'intel-core-i5-13600kf-processor-24m-cache-up-to-510-ghz', '24M Cache, up to 5.10 GHz', 36000.00, 20.00, 15, 0.00, 0, 1, 1, '1759497517.jpg', '17594975171.jpg', '17594975172.jpg', '17594975173.jpg', 'intel', 0, 1, '2025-10-03 02:18:37', '2025-10-05 06:13:45'),
(3, 'Cooler Master RR-212S-20PC-R1 Hyper 212 RGB Black Edition CPU Air Cooler 4 Direct Contact Heat Pipes 120mm RGB Fan', 'B07H22TC1N', 'cooler-master-rr-212s-20pc-r1-hyper-212-rgb-black-edition-cpu-air-cooler-4-direct-contact-heat-pipes-120mm-rgb-fan', 'Cooler Master Hyper 212 RGB Black Edition Cooling Fan Heatsink - 57.3 CFM - 30 dB(A) Noise - 4-pin PWM Fan - Socket R4 LGA-2066, Socket LGA 2011-v3, Socket R LGA-2011, Socket H4 LGA-1151, Socket H3 LGA-1150, Socket H2 LGA-1155, Socket H LGA-1156, Socket B LGA-1366, Socket AM4, Socket AM3+, Socket AM3 PGA-941, ... Compatible Processor Socket - RGB LED - Aluminum - 18.3 Year Life', 12000.00, 16.00, 4, 0.65, 4, 34, 8, '81B-HuW8ydL._SY450_.jpg', '81jR4Io8OwL._SY450_.jpg', '71Q3El-2flL._SY450_.jpg', '71+9-o7dIwL._SY450_.jpg', 'both', 1, 1, '2025-10-02 01:24:52', '2025-10-06 05:07:46'),
(14, 'Intel Core i9-11900 LGA1200 Desktop Processor 8 Cores up to 5.1GHz 16MB Cache with Integrated Intel UHD 750 Graphics', 'B08X5XVLL9', 'intel-core-i9-11900-lga1200-desktop-processor-8-cores-up-to-51ghz-16mb-cache-with-integrated-intel-uhd-750-graphics', 'Introducing the 11th Gen Intel Core i9 desktop processor, this processor is 14 nm processor which supports LGA 1200. The Unlocked processors features 8 Core which allow the processor to run multiple programs simultaneously without slowing down the system, while the 16 threads allow instructions to be handled by a single CPU core.\r\nWith 2.5GHz Base frequency, Intel\'s Turbo Boost 3.0 technology cranks maximum turbo frequency up to blazing 5.1 GHz. The processor is desirable for a gamer looking for a fantastic in-game experience and a creator that is ready to do more creating and sharing alike.\r\nAll this paired with 16MB of Intel Smart Cache. It has a TDP rating of 65W with max memory size of 128GB dual-channel DDR4 support for up to 3200Mhz with Intel top notch security features.\r\nThis processor is designed for users who value fast responsiveness, comes with The i9-11900 features integrated Intel UHD 750 Graphics driven by the powerful Xe architecture.\r\nA thermal solution is included to help maintain optimal temperatures and the processor is backed by a 3-year warranty.', 63000.00, 39.00, 10, 0.00, 0, 1, 1, '1759558551.jpg', '17595585511.jpg', '17595585512.jpg', '17595585513.jpg', 'intel', 0, 1, '2025-10-04 00:45:51', '2025-10-05 06:15:06'),
(15, 'Intel® Core™ i9-13900K Processor 36M Cache, up to 5.80 GHz', 'B0BG67ZG5R', 'intel-core-i9-13900k-processor-36m-cache-up-to-580-ghz', '36M Cache, up to 5.80 GHz', 94000.00, 16.00, 8, 0.00, 0, 1, 1, '1759558853.jpg', '17595588531.jpg', '17595588532.jpg', '17595588533.jpg', 'intel', 0, 1, '2025-10-04 00:50:53', '2025-10-05 06:16:03'),
(16, 'Intel® Core™ i9-14900K New Gaming Desktop Processor 24 cores (8 P-cores + 16 E-cores) with Integrated Graphics - Unlocked', 'B0CGJDKLB8', 'intel-core-i9-14900k-new-gaming-desktop-processor-24-cores-8-p-cores-16-e-cores-with-integrated-graphics---unlocked', 'Game without compromise. Play harder and work smarter with Intel Core 14th Gen processors\r\n24 cores (8 P-cores + 16 E-cores) and 32 threads. Integrated Intel UHD Graphics 770 included\r\nLeading max clock speed of up to 6.0 GHz gives you smoother game play, higher frame rates, and rapid responsiveness\r\nCompatible with Intel 600-series (with potential BIOS update) or 700-series chipset-based motherboards\r\nDDR4 and DDR5 platform support cuts your load times and gives you the space to run the most demanding games', 86000.00, 35.00, 6, 0.00, 0, 1, 1, '1759559486.jpg', '17595594861.jpg', '17595594862.jpg', '17595594863.jpg', 'intel', 1, 1, '2025-10-04 01:01:26', '2025-10-05 06:15:25'),
(17, 'AMD 7000 Series Ryzen 5 7600X Desktop Processor 6 cores 12 Threads 38 MB Cache 4.7 GHz Upto 5.3 GHz AM5 Socket (100-100000593WOF)', 'B0BBJDS62N', 'amd-7000-series-ryzen-5-7600x-desktop-processor-6-cores-12-threads-38-mb-cache-47-ghz-upto-53-ghz-am5-socket-100-100000593wof', '6 Cores & 12 Threads, 38 MB Cache\r\nBase Clock: 4.7 GHz, Max Boost Clock: up to 5.3 GHz\r\nMemory Support: DDR5 5200MHz, Memory Channels: 2, TDP: 65W, PCI Express Generation : PCIe Gen 5\r\nCompatible with Motherboards based on 600 Series Chipset, Socket AM5\r\nOn Chip Graphic Card , Included Heatsink Fan: No', 42150.00, 20.00, 11, 0.00, 0, 2, 1, '1759559853.jpg', '17595598531.jpg', '17595598532.jpg', '17595598533.jpg', 'amd', 0, 1, '2025-10-04 01:07:33', '2025-10-04 14:24:30'),
(18, 'AMD 7000 Series Ryzen 7 7800X 3D Desktop Processor 8 cores 16 Threads 104 MB Cache 4.2 GHz Upto 5.6 GHz AM5 Socket (100-100000910WOF)', 'B0BTZB7F88', 'amd-7000-series-ryzen-7-7800x-3d-desktop-processor-8-cores-16-threads-104-mb-cache-42-ghz-upto-56-ghz-am5-socket-100-100000910wof', '8 Cores & 16 Threads, 104 MB Cache\r\nBase Clock: 4.2 GHz, Max Boost Clock: up to 5.6 GHz\r\nMemory Support: DDR5 5200MHz, Memory Channels: 2, TDP: 120W, PCI Express Generation : PCIe Gen 5\r\nCompatible with Motherboards based on 600 Series Chipset, Socket AM5\r\nOn Chip Graphic Card', 70000.00, 45.00, 12, 0.00, 0, 2, 1, '1759560629.jpg', '17595606291.jpg', '17595606292.jpg', '17595606293.jpg', 'amd', 0, 1, '2025-10-04 01:20:29', '2025-10-04 14:24:36'),
(19, 'AMD Ryzen 9 9950X3D Desktop Processor with Integrated Radeon Graphics, 16 cores 32 Threads 128MB Cache Base Clock 4.3 GHz Up to 5.7GHz AM5 Socket System Memory DDR5 Up to 5600 MT/s - 100-100000719WOF', 'B0DVZSG8D5', 'amd-ryzen-9-9950x3d-desktop-processor-with-integrated-radeon-graphics-16-cores-32-threads-128mb-cache-base-clock-43-ghz-up-to-57ghz-am5-socket-system-memory-ddr5-up-to-5600-mts---100-100000719wof', 'Ultimate 16-Core Powerhouse: Featuring 16 cores and 32 threads, this CPU delivers unparalleled performance for the most demanding gaming and content creation task\r\n2nd Gen AMD 3D V-Cache for Extreme Performance: Leverages the advanced 2nd generation of 3D V-Cache technology, significantly boosting gaming frame rates and accelerating content creation workflows.\r\nZen 5 Architecture with Blazing Fast Boost Clocks: Built on the cutting-edge Zen 5 architecture, achieving boost clocks up to 5.7 GHz for exceptional responsiveness and speed.\r\nAdvanced DDR5 and PCIe 5.0 Support: Fully supports DDR5 memory with AMD EXPO technology and PCIe 5.0, enabling next-generation connectivity and memory performance.\r\nRobust Overclocking and Tuning Capabilities: Unlocked for overclocking, with Precision Boost Overdrive and Curve Optimizer Voltage Offsets, providing extensive customization options for enthusiasts.\r\nComprehensive Connectivity and Integrated Graphics: Offers a wide range of connectivity options, including USB 3.2 Gen 2, and features integrated AMD Radeon Graphics for basic display needs.\r\nSupporting Chipsets:A620 , X670E , X670 , B650E , B650 , X870E , X870 , B840 , B850', 119000.00, 29.00, 4, 0.00, 0, 2, 1, '1759560884.jpg', '17595608841.jpg', '17595608842.jpg', '17595608843.jpg', 'amd', 1, 1, '2025-10-04 01:24:44', '2025-10-04 14:24:48'),
(20, 'AMD Ryzen 9 9950X Desktop Processor Zen 5 Architecture with Integrated Radeon Graphics, 16 cores 32 Threads 64MB Cache, Base Clock 4.3GHz Upto 5.7GHz AM5 Socket, System Memory DDR5-100-100001277WOF', 'B0D6NNRBGP', 'amd-ryzen-9-9950x-desktop-processor-zen-5-architecture-with-integrated-radeon-graphics-16-cores-32-threads-64mb-cache-base-clock-43ghz-upto-57ghz-am5-socket-system-memory-ddr5-100-100001277wof', 'Core and Thread Count: 16 cores, 32 threads for exceptional multitasking and heavy workloads. Advanced Architecture: Built on Zen 5 architecture for optimized efficiency and power consumption.\r\nClock Speed/Cache : Up to 5.7 GHz boost clock for lightning-fast performance. Large 64MB L3 cache for optimized data access and reduced latency.\r\nMemory Support/PCIe : DDR5 memory support for high bandwidth and low latency. PCIe 5.0 for blazing-fast data transfer speeds to support the latest storage and graphics cards\r\nDDR5 Memory Support: Compatible with high-speed DDR5 memory for optimal system performance.\r\nSupporting Chipsets : A620 , X670E , X670 , B650E , B650 , X870E , X870', 94000.00, 16.00, 16, 0.00, 0, 2, 1, '1759561089.jpg', '17595610891.jpg', '17595610892.jpg', '17595610893.jpg', 'amd', 0, 1, '2025-10-04 01:28:09', '2025-10-04 14:24:53'),
(21, 'GIGABYTE nVidia GeForce RTX 2060 D6 6GB v2.0 Video Card, PCI-E 3.0, 1680 MHz Core Clock, 3x DIsplayPort 1.4, 1x HDMI 2.0', 'B095SWPGVR', 'gigabyte-nvidia-geforce-rtx-2060-d6-6gb-v20-video-card-pci-e-30-1680-mhz-core-clock-3x-displayport-14-1x-hdmi-20', 'RT Cores: Dedicated ray tracing hardware enables fast real-time ray tracing with physically accurate shadows, reflections, refractions, and global illumination.\r\nTensor Cores: Artificial intelligence is driving the greatest technology advancement in history, and Turing is bringing it to computer graphics. Experience AI-processing horsepower that accelerates gaming performance with NVIDIA DLSS 2.0.\r\nNext-Gen Shading: Variable Rate Shading focuses processing power on areas of rich detail, boosting overall performance without affecting perceived image quality. Mesh Shaders advanced geometry processing supports an order of magnitude more objects per-scene, allowing the creation of rich complex worlds.\r\nCore Clock: 1680MHz\r\nWINDFORCE 2X Cooler', 69999.00, 39.00, 13, 0.00, 0, 6, 2, '1759561566.jpg', '17595615661.jpg', '17595615662.jpg', '17595615663.jpg', 'none', 0, 1, '2025-10-04 01:36:06', '2025-10-04 01:36:06'),
(24, 'MSI Geforce RTX 3050 Ventus 2X 6G Oc pci_e Graphic Card - Nvidia Geforce RTX 3050 Gpu, 6Gb Gddr6 96-Bit Memory, 14 Gbps, Express 4 Interface, Up to 1492 Mhz, Dual Fan - Pcie', 'B0CSPNYB42', 'msi-geforce-rtx-3050-ventus-2x-6g-oc-pcie-graphic-card---nvidia-geforce-rtx-3050-gpu-6gb-gddr6-96-bit-memory-14-gbps-express-4-interface-up-to-1492-mhz-dual-fan---pcie', 'GeForce RTX 3050 VENTUS 2X 6G OC, 2304 CUDA Cores, 6GB GDDR6, 96-bit Memory Bus, Chipset: GeForce RTX 3050\r\n1492 MHz Boost, 14Gbps Memory Clock\r\nDisplayPort x 1 (v1.4a) HDMI x 2 (Supports 4K@120Hz as specified in HDMI 2.1)\r\nMinimum System Power Requirement (W) : 300\r\nGraphics Card Dimensions: 189 x 109 x 42 mm', 28000.00, 16.00, 25, 0.00, 0, 5, 2, '1759646255.jpg', '', '', '', 'both', 0, 1, '2025-10-05 06:37:35', '2025-10-05 06:37:35'),
(25, 'GIGABYTE NVIDIA Geforce RTX 4060 Windforce OC Graphics Card - 8GB GDDR6, 128-Bit, pci_e_x16 4.0, 2475Mhz Core Clock, 2X DP 1.4, 2X HDMI 2.1A, NVIDIA DLSS 3 - GV-N4060WF2OC-8GD', 'B0C8ZQTRD7', 'gigabyte-nvidia-geforce-rtx-4060-windforce-oc-graphics-card---8gb-gddr6-128-bit-pciex16-40-2475mhz-core-clock-2x-dp-14-2x-hdmi-21a-nvidia-dlss-3---gv-n4060wf2oc-8gd', '4.71933E+12\r\nCore & Clocks: 2475MHz Boost Clock, 3072 CUDA Cores, 8GB GDDR6 Memory, 128-bit Memory Bus, 17Gbps Memory Speed\r\nI/O & Connectivity: PCIE 4.0, 2 x DisplayPort 1.4a, 2 x HDMI 2.1a, DirectX 12 Ultimate, OpenGL 4.6\r\n3rd Generation RT Cores: Up to 2X ray tracing performance.\r\nRecommended Power Supply: 450W.Cooling: The WINDFORCE cooling system features two 80mm unique alternately spinning 3D active blade fans with a composite copper heat pipe, which directly touch the GPU and screen cooling to provide high efficiency heat dissipation..Integrated with 8GB GDDR6 128bit memory interface.', 84999.00, 26.00, 15, 0.00, 0, 6, 2, '1759646562.jpg', '17596465621.jpg', '17596465622.jpg', '17596465623.jpg', 'both', 0, 1, '2025-10-05 06:42:42', '2025-10-05 06:42:42'),
(26, 'ASUS Dual RX 7700 XT OC Edition 12GB GDDR6 Pci_E 4.0, HDMI 2.1, Displayport 2.1 Graphics Card', 'B0CRHQS1RV', 'asus-dual-rx-7700-xt-oc-edition-12gb-gddr6-pcie-40-hdmi-21-displayport-21-graphics-card', 'OC Mode: up to 2599 MHz (Boost Clock)/ up to 2239 MHz (Game Clock).\r\nDual ball fan bearings can last up to twice as long as sleeve bearing designs.\r\nAuto-extreme technology uses automation to enhance reliability.\r\nA protective backplate prevents PCB flex and trace damage.\r\nGPU Tweak III software provides intuitive performance tweaking, thermal controls, and system monitoring', 115500.00, 41.00, 12, 0.00, 0, 4, 2, '1759646879.jpg', '17596468791.jpg', '17596468792.jpg', '17596468793.jpg', 'both', 0, 1, '2025-10-05 06:47:59', '2025-10-05 06:55:06'),
(27, 'ASUS Prime GeForce RTX™ 5070 Ti OC Edition 16GB GDDR7 Graphics Card (PCIe® 5.0, 16GB GDDR7, HDMI®/DP 2.1, 2.5-Slot, Axial-tech Fans, Dual BIOS)', 'B0DVGVZZYY', 'asus-prime-geforce-rtx-5070-ti-oc-edition-16gb-gddr7-graphics-card-pcie-50-16gb-gddr7-hdmidp-21-25-slot-axial-tech-fans-dual-bios', 'Powered by the NVIDIA Blackwell architecture and DLSS 4\r\nSFF-Ready enthusiast GeForce card compatible with small-form-factor builds\r\nAxial-tech fans feature a smaller fan hub that facilitates longer blades and a barrier ring that increases downward air pressure\r\nPhase-change GPU thermal pad helps ensure optimal heat transfer, lowering GPU temperatures for enhanced performance and reliability\r\n2.5-slot design allows for greater build compatibility while maintaining cooling performance\r\nDual-ball fan bearings last up to twice as long as standard sleeve bearings\r\n0dB technology lets you enjoy light gaming in relative silence', 164000.00, 26.00, 22, 0.00, 0, 4, 2, '1759647298.jpg', '17596472981.jpg', '', '17596472983.jpg', 'both', 0, 1, '2025-10-05 06:54:58', '2025-10-05 06:54:58'),
(28, 'ASUS Prime B760M-A WiFi Intel LGA 1700 mATX Motherboard with PCIe 4.0, DDR5, Two M.2 Slots, 2.5Gb Ethernet, Wi-Fi 6, DisplayPort, HDMI, SATA 6Gbps, Rear USB 3.2 Gen 2, Front USB 3.2 Gen 1 Type-C', 'B0C3W1MXGX', 'asus-prime-b760m-a-wifi-intel-lga-1700-matx-motherboard-with-pcie-40-ddr5-two-m2-slots-25gb-ethernet-wi-fi-6-displayport-hdmi-sata-6gbps-rear-usb-32-gen-2-front-usb-32-gen-1-type-c', 'Intel LGA 1700 Socket: Ready for 13th and 12th Gen Intel processors\r\nUltrafast Connectivity: PCIe 4.0, two M.2 slots, Realtek 2.5Gb Ethernet, Wi-Fi 6, rear USB 3.2 Gen 2, front USB 3.2 Gen 1 Type-C\r\nComprehensive Cooling: VRM heatsinks, M.2 heatsink, PCH heatsink, hybrid fan headers and Fan Xpert 2+\r\nExclusive Memory Technology: ASUS Enhanced Memory Profile II and ASUS OptiMem II\r\nAura Sync RGB Lighting: Onboard Addressable Gen 2 headers and Aura RGB header for RGB LED strips, easily synced with Aura Sync-capable hardware', 25800.00, 29.00, 14, 0.00, 0, 10, 3, '1759927993.jpg', '17599279931.jpg', '17599279932.jpg', '17599279933.jpg', 'both', 0, 1, '2025-10-08 12:53:13', '2025-10-08 12:53:13'),
(29, 'MSI MEG Z890 GODLIKE, E-ATX - Supports Intel Core Ultra Processors (Series 2), LGA 1851 - Dynamic Dashboard III, EZ Slide M.2, DDR5 (9200+ MT/s OC), M.2 & PCIe 5.0, Thunderbolt 5, Wi-Fi 7, 10G LAN', 'B0DM45SDVW', 'msi-meg-z890-godlike-e-atx---supports-intel-core-ultra-processors-series-2-lga-1851---dynamic-dashboard-iii-ez-slide-m2-ddr5-9200-mts-oc-m2-pcie-50-thunderbolt-5-wi-fi-7-10g-lan', 'ULTRA POWER+ - SUPPORTS THE LATEST INTEL CORE ULTRA 9 PROCESSORS IN HIGH PERFORMANCE - The MEG Z890 GODLIKE employs a 26 DRPS (110A, SPS) for Core Ultra Processors (Series 2) with Core Boost, 3.99\" LCD for hardware monitoring and personalization\r\nFROZR GUARD - Premium cooling features such as Wavy fin design, Direct-touch Cross Heat-pipe, 9W/mK MOSFET thermal pads, extra choke thermal pads, double-sided EZ M.2 Shield Frozr II, and Combo-fan (for pump & system) header (3A)\r\nEZ DIY DESIGN - Multiple DIY-friendly features, such as EZ Link with the EZ Bridge and EZ Control Hub, simplify the PC building process. PCIe Release, EZ Magnetic M.2 Shield Frozr II, and EZ M.2 Clip II simplify assembly for SSD and GPU swaps.\r\nOCTUPLE M.2 CONNECTORS and DDR5 MEMORY - Storage include onboard 2 x M.2 Gen5 x4 and 4 x M.2 Gen4 x4 slots with double-sided Shield Frozr. M.2 XPANDER-Z SLIDER GEN5 provides 2 additional M.2 Gen5 slots. 4 x DDR5 DIMM SMT slots (1DPC 1R, 9200+ MT/s OC)\r\nULTRA CONNECT - Network hardware includes a full-speed Wi-Fi 7 with Bluetooth 5.4 & 10Gbps plus 5Gbps LAN; Rear ports include Thunderbolt 4 Type-C and THUNDERBOLTM5 accessory card. 7.1 USB High Performance Audio with Audio Boost 5 HD (supports S/PDIF)', 220000.00, 31.00, 8, 0.00, 0, 12, 3, '1759928291.jpg', '17599282911.jpg', '17599282912.jpg', '17599282913.jpg', 'both', 1, 1, '2025-10-08 12:58:11', '2025-10-08 12:58:11'),
(30, 'ASUS ROG Strix X870E-E ATX DDR5 Gaming Wi-Fi Motherboard, AMD Socket AM5 for AMD Ryzen 9000 & 8000 & 7000 Series Desktop Processors', 'B0DGQBQC3B', 'asus-rog-strix-x870e-e-atx-ddr5-gaming-wi-fi-motherboard-amd-socket-am5-for-amd-ryzen-9000-8000-7000-series-desktop-processors', 'Ready for Advanced AI PCs: Designed for the future of AI computing, with the power and connectivity needed for demanding AI applications\r\nRenowned Software: ASUS DriverHub, ASUS GlideX, HWiNFO, Norton 360 for Gamers (60-day free trial), bundled 60-day AIDA64 Extreme trial subscription and intuitive UEFI BIOS dashboard\r\nIntelligent Control: ASUS-exclusive AI Overclocking, AI Cooling II, AI Networking II and AEMP to simplify setup and improve performance\r\nROG Strix Overclocking technologies: Dynamic OC Switcher, Core Flex, Asynchronous Clock and PBO Enhancement\r\nRobust Power Solution: 18+2+2 power solution rated for 110A per stage with dual ProCool II power connectors, high-quality alloy chokes and durable capacitors to support multi-core processors\r\nOptimized Thermal Design: Massive heatsinks with integrated I/O cover, high-conductivity thermal pads, and connected with an L-shaped heatpipe\r\nLatest M.2 Support: Three onboard PCIe 5.0 M.2 slots, and two PCIe 4.0 M.2 slots, all with substantial cooling solutions', 79999.00, 26.00, 21, 0.00, 0, 10, 3, '1759928587.jpg', '17599285871.jpg', '17599285872.jpg', '17599285873.jpg', 'both', 0, 1, '2025-10-08 13:03:07', '2025-10-08 13:03:07'),
(31, 'Corsair Vengeance LPX 8GB (1x8GB) DDR4 3200MHZ C16 Desktop RAM (Black)', 'B07PNW4Q3F', 'corsair-vengeance-lpx-8gb-1x8gb-ddr4-3200mhz-c16-desktop-ram-black', 'XMP 2.0 SUPPORT: One setting is all it takes to automatically adjust to the fastest safe speed for your VENGEANCE LPX. Tested Voltage 1.35V\r\nALUMINUM HEAT SPREADER: The unique design of the VENGEANCE LPX heat spreader optimally pulls heat away from the ICs and into your system’s cooling path, so you can push it harder.\r\nDESIGNED FOR HIGH-PERFORMANCE OVERCLOCKING: Each VENGEANCE LPX module is built from an custom performance PCB and highly-screened memory ICs.\r\nLOW-PROFILE DESIGN: The small form factor makes it ideal for smaller cases or any system where internal space is at a premium. SPD Latency 15-15-15-36', 3500.00, 20.00, 26, 0.00, 0, 15, 4, '1759928891.jpg', '17599288911.jpg', '', '17599288913.jpg', 'both', 0, 1, '2025-10-08 13:08:11', '2025-10-08 13:08:11'),
(32, 'G.SKILL Trident Z RGB 16GB (2 * 8GB) DDR4 3200 MHz CL16-18-18-38 1.35V Desktop Memory RAM - F4-3200C16D-16GTZR', 'B01MTDEYHU', 'gskill-trident-z-rgb-16gb-2-8gb-ddr4-3200-mhz-cl16-18-18-38-135v-desktop-memory-ram---f4-3200c16d-16gtzr', 'DDR4 Memory available in various speeds and capacities\r\nRGB LED Lighting\r\nTrident Z Heat Spreader', 35799.00, 51.00, 36, 0.00, 0, 16, 4, '1759929281.jpg', '17599292811.jpg', '17599292812.jpg', '17599292813.jpg', 'both', 0, 1, '2025-10-08 13:14:41', '2025-10-08 13:14:41'),
(33, 'G.SKILL Ripjaws V 64GB (2 * 32GB) DDR4 4000MHz CL18-22-22-42 1.40V Desktop Memory RAM - F4-4000C18D-64GVK', 'B08TXR9FSN', 'gskill-ripjaws-v-64gb-2-32gb-ddr4-4000mhz-cl18-22-22-42-140v-desktop-memory-ram---f4-4000c18d-64gvk', '64GB (2*32GB) DDR4\r\nDDR4 4000MHz CL18-22-22-42\r\nDIMM Desktop RAM\r\nSPD Voltage - 1.40V', 50000.00, 31.00, 32, 0.00, 0, 16, 4, '1759929811.jpg', '17599298111.jpg', '17599298112.jpg', '', 'both', 0, 1, '2025-10-08 13:23:31', '2025-10-08 13:23:31'),
(34, 'Seagate Expansion 2TB External HDD - USB 3.0 for Windows and Mac with 3 yr Data Recovery Services, Portable Hard Drive (STKM2000400)', 'B08ZJG6TVT', 'seagate-expansion-2tb-external-hdd---usb-30-for-windows-and-mac-with-3-yr-data-recovery-services-portable-hard-drive-stkm2000400', 'Get an extra layer of protection for your data with the included 3 year Rescue Data Recovery Services.\r\nSleek and simple portable drive design for taking photos, movies, music, and more on-the-go\r\nAutomatic recognition of Windows and Mac computers for simple setup (Reformatting required for use with Time Machine)\r\nDrag-and-drop file saving\r\nUSB 3.0 powered', 8699.00, 17.00, 36, 0.00, 0, 21, 5, '1759930418.jpg', '17599304181.jpg', '17599304182.jpg', '17599304183.jpg', 'both', 0, 1, '2025-10-08 13:33:38', '2025-10-08 13:33:38'),
(35, 'Seagate Barracuda 4 TB Internal SATA Hard Drive HDD 6Gb/s 256MB Cache 3.5 Inches (8.8 cm) for Computer Desktop PC (ST4000DM004)', 'B071WLPRHN', 'seagate-barracuda-4-tb-internal-sata-hard-drive-hdd-6gbs-256mb-cache-35-inches-88-cm-for-computer-desktop-pc-st4000dm004', 'Cost-effective storage upgrade for laptop or desktop computers – store all of your games, music, movies and more\r\nSATA 6Gb/s interface optimizes burst performance. Seagate Secure models for hardware-based data security, Trusted Dependability\r\nBest-fit applications: Desktop or all-in-one PCs, home servers, entry-level direct-attached storage devices (DAS)\r\nInstant Secure Erase allows safe and easy drive retirement, plus protect data with Self-Encrypting Drive (SED) models\r\nActual storage capacity may vary due to differences between decimal and binary calculations.', 13000.00, 31.00, 38, 0.00, 0, 22, 5, '1759930793.jpg', '17599307931.jpg', '17599307932.jpg', '17599307933.jpg', 'both', 0, 1, '2025-10-08 13:39:53', '2025-10-08 13:39:53'),
(36, 'Samsung 990 PRO SSD 4TB PCIe 4.0 M.2 2280 Internal Solid State Hard Drive, Seq. Read Speeds Up to 7,450 MB/s for High End Computing, Gaming, and Heavy Duty Workstations, MZ-V9P4T0B, Black', 'B0CHGT1KFJ', 'samsung-990-pro-ssd-4tb-pcie-40-m2-2280-internal-solid-state-hard-drive-seq-read-speeds-up-to-7450-mbs-for-high-end-computing-gaming-and-heavy-duty-workstations-mz-v9p4t0b-black', 'MEET THE NEXT GEN: Consider this a cheat code; Our Samsung 990 PRO Gen4 SSD helps you reach near max performance* with lightning-fast speeds; Whether you’re a hardcore gamer or a tech guru, you’ll get power efficiency built for the final boss\r\nREACH THE NEXT LEVEL: Gen4 steps up with faster transfer speeds and high-performance bandwidth; With a more than 55% improvement in random performance compared to 980 PRO, it’s here for heavy computing and faster loading\r\nTHE FASTEST SSD FROM THE WORLD\'S #1 FLASH MEMORY BRAND**: The speed you need for any occasion; With read and write speeds up to 7450/6900 MB/s* you’ll reach near max performance of PCIe 4.0*** powering through for any use\r\nPLAY WITHOUT LIMITS: Give yourself some space with storage capacities from 1TB to 4TB; Sync all your saves and reign supreme in gaming, video editing, data analysis and more\r\nIT’S A POWER MOVE: Save the power for your performance; Get power efficiency all while experiencing up to 50% improved performance per watt over the 980 PRO****; It makes every move more effective with less consumption', 78999.00, 51.00, 41, 0.00, 0, 20, 5, '1759931144.jpg', '17599311441.jpg', '17599311442.jpg', '17599311443.jpg', 'both', 0, 1, '2025-10-08 13:45:44', '2025-10-08 13:45:44'),
(37, 'Cooler Master MWE 750 Gold V2 Power Supply - Fully Modular | 80 Plus Gold Certified | 120mm HDB Fan | DC-to-DC Circuit Design | 2 EPS Connectors | 750 Watt', 'B08H2LTN3Q', 'cooler-master-mwe-750-gold-v2-power-supply---fully-modular-80-plus-gold-certified-120mm-hdb-fan-dc-to-dc-circuit-design-2-eps-connectors-750-watt', 'The MWE Series comes with a standard limited manufacturing warranty of 5 years from the date of purchase for complete peace of mind.\r\nCompliance with latest ATX 12V V2.52 specification ensures power requirement for new age multi-core processors and Graphics card are properly met.\r\nAll MWE Gold models have 2 EPS connectors for universal current generation motherboard compatibility.\r\nATX 24 PIN x1, EPS 4+4 PIN x1, EPS 8 PIN x1, PCI-E 6+2 PIN x4, SATA x12, Peripheral 4 pin x4\r\nAll flat modular cables take up less space and are easier to manipulate within the chassis improving ease of build and air airflow.', 14199.00, 31.00, 23, 0.00, 0, 28, 6, '1759931434.jpg', '17599314341.jpg', '17599314342.jpg', '17599314343.jpg', 'both', 0, 1, '2025-10-08 13:50:34', '2025-10-08 13:50:34'),
(38, 'Ant Esports VS600L Non-Modular High Efficiency Gaming Power Supply/PSU with 1 x PCIe and 120mm Silent Fan', 'B09173G65X', 'ant-esports-vs600l-non-modular-high-efficiency-gaming-power-supplypsu-with-1-x-pcie-and-120mm-silent-fan', 'Single 12v Rail – A single 12V promises reliable and consistent power delivery\r\nSilent 120mm fan – The 120mm fan spins consistently yet quietly to keep the unit cool even under full load & is rated for 100,000 hours of lifespan!\r\nModern Connectors – Comes with all the cables for a modern high end system including a dual 4 pin CPU connector.\r\nHigh Quality Components – The VS600L is made with high quality components for reliable and consistent performance\r\nATX 24-Pin Connectors- 1, EPS 4+4 Pin Connectors-1, PCIe (6+2) Connectors-1, SATA Connectors-4, Peripheral 4-Pin Connectors-2\r\nThe PSU comes with 2 years warranty.', 3699.00, 26.00, 36, 0.00, 0, 66, 6, '1759931760.jpg', '17599317601.jpg', '17599317602.jpg', '17599317603.jpg', 'both', 0, 1, '2025-10-08 13:56:00', '2025-10-08 13:56:00'),
(39, 'Thermaltake Toughpower GF3 Snow Edition 1200W 80+ Gold Full Modular SLI/Crossfire Ready ATX 3.0 Power Supply PCIe Gen.5 600W 12VHPWR Connector Included PS-TPD-1200FNFAGU-N 10 Year Warranty', 'B0CKS5P7KF', 'thermaltake-toughpower-gf3-snow-edition-1200w-80-gold-full-modular-slicrossfire-ready-atx-30-power-supply-pcie-gen5-600w-12vhpwr-connector-included-ps-tpd-1200fnfagu-n-10-year-warranty', 'Fully Compatible with Intel ATX 3.0 Standards\r\nPCIe Gen 5.0 Ready\r\nMade to Comply with the Latest Graphics Cards\r\nFully Modular Low-Profile Cables\r\nBuilt-in industrial grade protection', 50083.00, 0.00, 15, 0.00, 0, 30, 6, '1759932070.jpg', '17599320701.jpg', '17599320702.jpg', '17599320703.jpg', 'both', 0, 1, '2025-10-08 14:01:10', '2025-10-08 14:01:10'),
(40, 'Ant Esports MK 1700 Wired Membrane Gaming Keyboard –with Backlit RGB LED, USB-A Connection, Quiet Keystrokes, 12 Multimedia Function Keys - for Computer, PC, Desktop, Gamer– 1 Year Warranty–Mercury', 'B0CLJHP3RF', 'ant-esports-mk-1700-wired-membrane-gaming-keyboard-with-backlit-rgb-led-usb-a-connection-quiet-keystrokes-12-multimedia-function-keys---for-computer-pc-desktop-gamer-1-year-warrantymercury', ' Modern Gaming Style: Modern gaming style keyboard with essential multimedia controls to help improve speed - ideal for upgrade applications\r\n· 12 Multimedia keys: 12 multimedia-function keys for quick and easy access to frequent multimedia commands. Also has Low-force key switches that provide silent keystrokes and tactile response.\r\n· RGB Backlit: Pulsating and sustained LED backlighting that clearly and subtly highlight the keyboard in low-light situations. Backlight brightness that can be adjusted to 3 level modes or turned off\r\n· Easy to use: Just Plug and play…… Has 10,00,000-keystroke switch life. Cable is 4.5 ft\r\n· Warranty: 1 year manufacturer warranty. Product instruction guide included.', 1199.00, 21.00, 40, 0.00, 0, 67, 10, '1759933472.jpg', '17599334721.jpg', '17599334722.jpg', '17599334723.jpg', 'both', 0, 1, '2025-10-08 14:24:32', '2025-10-08 14:24:32'),
(41, 'Logitech G502 X Wired Gaming Mouse - LIGHTFORCE Hybrid Optical-Mechanical Primary switches, Hero 25K Gaming Sensor, Compatible with PC/macOS/Windows - Black', 'B0BCJRPHMY', 'logitech-g502-x-wired-gaming-mouse---lightforce-hybrid-optical-mechanical-primary-switches-hero-25k-gaming-sensor-compatible-with-pcmacoswindows---black', 'Icon reinvented: From the legacy of Logitech\'s most popular G502 design, the G502 X wired gaming mouse is reimagined and redesigned with the latest innovations in gaming technology\r\nLIGHTFORCE switches: All-new hybrid optical-mechanical switch technology for incredible speed and reliability, as well as precise actuation with crisp response, for hours of performance gaming\r\nHERO 25K gaming sensor: Incredibly precise down to the sub-micron for high-precision accuracy with zero smoothing/filtering/acceleration for high gaming performance every time on the computer\r\nRedesigned DPI-shift button: This wired optical gaming mouse features a reversible and removable DPI-shift button for precise customisation depending on your grip and preference\r\nRedesigned hyperfast scroll wheel: Switch between hyper-fast free spin and precise ratcheting mode, and tilt left and right for two additional personalisable controls\r\nMake your choice: Available in black and white colourways', 9999.00, 24.00, 29, 0.00, 0, 58, 11, '1759933745.jpg', '17599337451.jpg', '17599337452.jpg', '17599337453.jpg', 'both', 0, 1, '2025-10-08 14:29:05', '2025-10-08 14:29:05');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
CREATE TABLE IF NOT EXISTS `product_reviews` (
  `product_review_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_review_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`)
) ;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`product_review_id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, NULL, 1, 5, 'Great processor!', '2025-10-04 12:54:59'),
(2, NULL, 2, 4, 'Good processor', '2025-10-04 12:54:59'),
(3, NULL, 3, 3, 'Average processor', '2025-10-04 12:54:59'),
(4, NULL, 4, 2, 'Bad processor', '2025-10-04 12:54:59'),
(5, NULL, 5, 1, 'Terrible processor', '2025-10-04 12:54:59');

-- --------------------------------------------------------

--
-- Table structure for table `product_specs`
--

DROP TABLE IF EXISTS `product_specs`;
CREATE TABLE IF NOT EXISTS `product_specs` (
  `product_spec_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `spec_name` varchar(100) DEFAULT NULL,
  `spec_value` varchar(255) DEFAULT NULL,
  `spec_group` varchar(80) DEFAULT NULL,
  `display_order` int DEFAULT '0',
  PRIMARY KEY (`product_spec_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=780 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_specs`
--

INSERT INTO `product_specs` (`product_spec_id`, `product_id`, `spec_name`, `spec_value`, `spec_group`, `display_order`) VALUES
(83, NULL, 'Cores', '16 (8P+8E)', 'Key Specs', 1),
(84, NULL, 'Threads', '24', 'Key Specs', 2),
(85, NULL, 'Base Clock', '3.4 GHz', 'Key Specs', 3),
(86, NULL, 'Boost Clock', '5.4 GHz', 'Key Specs', 4),
(87, NULL, 'TDP', '125W', 'Key Specs', 5),
(88, NULL, 'Lithography', 'Intel 7', 'Detailed Specs', 1),
(89, NULL, 'Socket Type', 'LGA1700', 'Detailed Specs', 2),
(90, NULL, 'Cache', '30MB Intel Smart Cache', 'Detailed Specs', 3),
(91, NULL, 'Memory Support', 'DDR4-3200 / DDR5-5600', 'Detailed Specs', 4),
(92, NULL, 'PCIe Version', 'PCIe 5.0 / 4.0', 'Detailed Specs', 5),
(93, NULL, 'Integrated Graphics', 'Intel UHD 770', 'Detailed Specs', 6),
(318, 5, 'CPU Socket', 'LGA 1700', 'Key Specs', 4),
(349, 13, 'CPU Speed', '5 GHz', 'Key Specs', 4),
(316, 5, 'CPU Model', 'Core i3', 'Key Specs', 2),
(317, 5, 'CPU Speed', '4.3 GHz', 'Key Specs', 3),
(313, 5, 'Processor Count', '4', 'Detailed Specs', 6),
(314, 5, 'Compatible Devices', 'Laptops, Monitors, PC', 'Detailed Specs', 7),
(315, 5, 'CPU Manufacturer', 'Intel', 'Key Specs', 1),
(312, 5, 'Manufacturer', 'CNA2 Intel Products Chengdu Ltd NO.8-1 Kexin Road Chengdu High-tech Zone Chengdu Sichuang 611731 China, Intel', 'Detailed Specs', 5),
(131, NULL, 'asdfasdf', 'awfsdf', 'adfasdf', 10),
(132, NULL, 'adsfasdf', 'asdfas', 'adfasdf', 10),
(133, NULL, 'asdf', 'asdfa', 'adfasdf', 12),
(134, NULL, '12122', '2121212121', 'dsfasdasdfafafdfa', 12),
(135, NULL, 'sdfasdfa', 'sdfasdfa', 'General', 10),
(136, NULL, 'asdfa', 'sdfa', 'General', 10),
(137, NULL, 'Brand', '32 (8P+8E)', 'Key Specs', 10),
(138, NULL, 'Brand', 'LGA 1700', 'Key Specs', 10),
(139, NULL, 'Processor Count', '4', 'Detailed Specs', 10),
(140, NULL, 'CPU Manufacturer', '‎INTEL, INTEL PRODUCTS VIETNAM CO. LTD. LOT I2 D1 RD SAIGON HIGH TECH PARK TAN PHU WARD, THU DUC CITY 70000 HO CHI MINH CITY VIETNAM', 'Detailed Specs', 10),
(327, 11, 'CPU Model', 'Core i7-10700', 'Key Specs', 3),
(328, 11, 'CPU Speed', '4.9 GHz', 'Key Specs', 4),
(329, 11, 'CPU Socket', 'LGA 1700', 'Key Specs', 5),
(326, 11, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(324, 11, 'Wattage', '65 Watts', 'Detailed Specs', 12),
(325, 11, 'Brand', 'Intel', 'Key Specs', 1),
(323, 11, 'Compatible Devices', 'Personal Computer', 'Detailed Specs', 11),
(322, 11, 'model number', 'BX8070811700', 'Detailed Specs', 10),
(321, 11, 'Product Dimensions', '‎3.81 x 3.81 x 0.51 cm; 200 g', 'Detailed Specs', 9),
(320, 11, 'Model Name', 'i7-11700', 'Detailed Specs', 8),
(319, 11, 'Manufacturer', '‎INTEL PRODUCTS VIETNAM CO. LTD., NO. 8-1, KEXIN ROAD CHENGDU HIGH-TECH ZONE (WEST) 611731 CHENGDU CHINA, Intel', 'Detailed Specs', 6),
(348, 13, 'CPU Model', 'Core i7-10700K', 'Key Specs', 3),
(338, 12, 'CPU Socket', 'LGA 1151', 'Key Specs', 5),
(337, 12, 'CPU Speed', '13700 GHz', 'Key Specs', 4),
(336, 12, 'CPU Model', 'Core i7', 'Key Specs', 3),
(335, 12, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(334, 12, 'Brand', 'Intel', 'Key Specs', 1),
(332, 12, 'model number', 'BX8071513700F', 'Detailed Specs', 8),
(333, 12, 'Wattage', '65 Watts', 'Detailed Specs', 9),
(331, 12, 'Product Dimensions', '12.7 x 10.16 x 0.25 cm; 453.59 g', 'Detailed Specs', 7),
(330, 12, 'Manufacturer', 'Intel', 'Detailed Specs', 6),
(347, 13, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(346, 13, 'Wattage', '125 Watts', 'Detailed Specs', 13),
(345, 13, 'Ram Memory Technology', 'DDR4', 'Detailed Specs', 12),
(344, 13, 'model number', 'BX8070811700K', 'Detailed Specs', 11),
(342, 13, 'Model Name', 'i7-11700K', 'Detailed Specs', 9),
(343, 13, 'Product Dimensions', '‎11.6 x 4.4 x 10.1 cm; 87 g', 'Detailed Specs', 10),
(341, 13, 'Model', 'BX8070811700K', 'Detailed Specs', 8),
(340, 13, 'Manufacturer', '‎INTEL PRODUCTS VIETNAM CO. LTD., NO. 8-1, KEXIN ROAD CHENGDU HIGH-TECH ZONE (WEST) 611731 CHENGDU CHINA, Intel', 'Detailed Specs', 7),
(339, 13, 'CPU Manufacturer', 'Intel', 'Detailed Specs', 6),
(357, 14, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(356, 14, 'Brand', 'Intel', 'Key Specs', 1),
(355, 14, 'Wattage', '65 Watts', 'Detailed Specs', 10),
(354, 14, 'model number', 'BX8070811900', 'Detailed Specs', 9),
(353, 14, 'Product Dimensions', '3.81 x 3.81 x 0.51 cm; 370 g', 'Detailed Specs', 8),
(352, 14, 'Series', '‎i9-11900', 'Detailed Specs', 7),
(351, 14, 'Manufacturer', 'Intel, Intel Semiconductor US LTD', 'Detailed Specs', 6),
(383, 15, 'Are Batteries Included', 'No', 'Detailed Specs', 13),
(382, 15, 'Wattage', '125 Watts', 'Detailed Specs', 12),
(379, 15, 'Processor Count', '‎24', 'Detailed Specs', 9),
(380, 15, 'Computer Memory Type', '‎GDDR4', 'Detailed Specs', 10),
(381, 15, 'Graphics Card Interface', '‎PCI-Express x8', 'Detailed Specs', 11),
(378, 15, 'model number', 'BX8071513900K', 'Detailed Specs', 8),
(377, 15, 'Product Dimensions', '‎6 x 4 x 0.1 cm; 90 g', 'Detailed Specs', 7),
(376, 15, 'Manufacturer', '‎INTEL, INTEL PRODUCTS VIETNAM CO. LTD. LOT I2 D1 RD SAIGON HIGH TECH PARK TAN PHU WARD, THU DUC CITY 70000 HO CHI MINH CITY VIETNAM', 'Detailed Specs', 6),
(371, 16, 'Brand', 'Intel', 'Key Specs', 1),
(370, 16, 'Item Weight', '75 g', 'Detailed Specs', 15),
(369, 16, 'Are Batteries Included', 'No', 'Detailed Specs', 14),
(368, 16, 'Wattage', '250 W', 'Detailed Specs', 13),
(367, 16, 'Processor Count', '24', 'Detailed Specs', 12),
(366, 16, 'Model number', 'BX8071514900K', 'Detailed Specs', 11),
(365, 16, 'Product Dimensions', '‎17.78 x 6.35 x 0.1 cm; 75 g', 'Detailed Specs', 10),
(364, 16, 'Item Width', '2.5 Inches', 'Detailed Specs', 9),
(363, 16, 'Item Height', '‎0.1 Centimeters', 'Detailed Specs', 8),
(362, 16, 'Series', '‎Core™ i9-14900K', 'Detailed Specs', 7),
(361, 16, 'Manufacturer', 'Intel', 'Detailed Specs', 6),
(233, 17, 'Brand', 'AMD', 'Key Specs', 1),
(234, 17, 'CPU Manufacturer', 'AMD', 'Key Specs', 2),
(235, 17, 'CPU Model', 'Ryzen 5', 'Key Specs', 3),
(236, 17, 'CPU Speed', '5.3 GHz', 'Key Specs', 4),
(237, 17, 'CPU Socket', 'Socket AM5', 'Key Specs', 5),
(238, 17, 'Manufacturer', 'AMD, AMD', 'Detailed Specs', 6),
(239, 17, 'Series', '‎AMD Ryzen 5 7600X', 'Detailed Specs', 7),
(240, 17, 'Colour', 'Silver', 'Detailed Specs', 8),
(241, 17, 'Product Dimensions', '3.99 x 3.99 x 0.25 cm; 108.86 g', 'Detailed Specs', 9),
(242, 17, 'Model number', 'RYZEN 5 7600X', 'Detailed Specs', 10),
(243, 17, 'Processor Count', '6', 'Detailed Specs', 11),
(244, 17, 'Computer Memory Type', 'DIMM', 'Detailed Specs', 12),
(245, 17, 'Wattage', '105 Watts', 'Detailed Specs', 13),
(246, 18, 'Brand', 'AMD', 'Key Specs', 1),
(247, 18, 'CPU Manufacturer', 'AMD', 'Key Specs', 2),
(248, 18, 'CPU Model', 'Ryzen 7', 'Key Specs', 3),
(249, 18, 'CPU Speed', '5.6 GHz', 'Key Specs', 4),
(250, 18, 'CPU Socket', 'Socket AM5', 'Key Specs', 5),
(251, 18, 'Manufacturer', '‎AMD, AMD', 'Detailed Specs', 6),
(252, 18, 'Product Dimensions', '3.99 x 3.99 x 0.25 cm; 331.12 g', 'Detailed Specs', 7),
(253, 18, 'Model number', 'AMD Ryzen 7 7800X3D', 'Detailed Specs', 8),
(254, 18, 'Processor Count', '1', 'Detailed Specs', 9),
(255, 18, 'Computer Memory Type', 'DIMM', 'Detailed Specs', 10),
(256, 18, 'Wattage', '120 Watts', 'Detailed Specs', 11),
(257, 19, 'Brand', 'AMD', 'Key Specs', 1),
(258, 19, 'CPU Manufacturer', 'AMD', 'Key Specs', 2),
(259, 19, 'CPU Model', 'Ryzen 9', 'Key Specs', 3),
(260, 19, 'CPU Speed', '4.3 GHz', 'Key Specs', 4),
(261, 19, 'CPU Socket', 'Socket AM5', 'Key Specs', 5),
(262, 19, 'Manufacturer', 'AMD, AMD, AMD India Pvt. Ltd,Plot No. 102,103 Export Promotion Industrial Park, Whitefield Bangalore - 560066', 'Detailed Specs', 6),
(263, 19, 'Item Height', '0.1 Inches', 'Detailed Specs', 7),
(264, 19, 'Item Width', '1.6 Inches', 'Detailed Specs', 8),
(265, 19, 'Product Dimensions', '4.06 x 4.06 x 0.25 cm; 90.72 g', 'Detailed Specs', 9),
(266, 19, 'Model number', '‎Ryzen ™ 9 9950X 3D', 'Detailed Specs', 10),
(267, 19, 'Processor Count', '1', 'Detailed Specs', 11),
(268, 19, 'Wattage', '170 Watts', 'Detailed Specs', 12),
(269, 19, 'Are Batteries Included', 'No', 'Detailed Specs', 13),
(270, 19, 'Item Weight', '90.7 g', 'Detailed Specs', 14),
(271, 20, 'Brand', 'AMD', 'Key Specs', 1),
(272, 20, 'CPU Manufacturer', 'AMD', 'Key Specs', 2),
(273, 20, 'CPU Model', 'Ryzen 9', 'Key Specs', 3),
(274, 20, 'CPU Speed', '4.3 GHz', 'Key Specs', 4),
(275, 20, 'CPU Socket', 'Socket AM5', 'Key Specs', 5),
(276, 20, 'Manufacturer', 'AMD, AMD India Pvt. Ltd,Plot No. 102,103 Export Promotion Industrial Park, Whitefield Bangalore - 560066', 'Detailed Specs', 6),
(277, 20, 'Product Dimensions', '‎4 x 4 x 0.1 cm; 90.72 g', 'Detailed Specs', 7),
(278, 20, 'Item model number', '‎AMD Ryzen™ 9 9950X', 'Detailed Specs', 8),
(279, 20, 'Processor Count', '16', 'Detailed Specs', 9),
(280, 20, 'Wattage', '‎1.7E+2', 'Detailed Specs', 10),
(281, 20, 'Item Weight', '90.6 g', 'Detailed Specs', 11),
(282, 21, 'Graphics Coprocessor', 'NVIDIA GeForce RTX 2060', 'Key Specs', 1),
(283, 21, 'Brand', 'GIGABYTE', 'Key Specs', 2),
(284, 21, 'Graphics RAM Size', '6 GB', 'Key Specs', 3),
(285, 21, 'Video Output Interface', 'DisplayPort, HDMI', 'Key Specs', 4),
(286, 21, 'Graphics Processor Manufacturer', 'NVIDIA', 'Key Specs', 5),
(287, 21, 'Manufacturer', 'GIGABYTE', 'Detailed Specs', 6),
(288, 21, 'Model', 'GV-N2060D6-6GD V2', 'Detailed Specs', 7),
(289, 21, 'Model Name', 'NVIDIA GeForce RTX 2060', 'Detailed Specs', 8),
(290, 21, 'Item model number', 'GV-N2060D6-6GD V2', 'Detailed Specs', 9),
(291, 21, 'Memory Storage Capacity', '1 GB', 'Detailed Specs', 10),
(292, 21, 'Hardware Interface', '‎PCI Express x16', 'Detailed Specs', 11),
(293, 21, 'Graphics Card Description', '‎Dedicated', 'Detailed Specs', 12),
(294, 21, 'Graphics Card Ram Size', '6 GB', 'Detailed Specs', 13),
(295, 21, 'Graphics RAM Type', 'GDDR6', 'Detailed Specs', 14),
(296, 21, 'Resolution', '7680 x 4320 Pixels', 'Detailed Specs', 15),
(297, 21, 'Video output interface', 'DisplayPort, HDMI', 'Detailed Specs', 16),
(298, 21, 'Form Factor', 'atx', 'Detailed Specs', 17),
(299, 21, 'Item Weight', '690 g', 'Detailed Specs', 18),
(372, 16, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(360, 14, 'CPU Socket', 'LGA 1200', 'Key Specs', 5),
(359, 14, 'CPU Speed', '5.2 GHz', 'Key Specs', 4),
(358, 14, 'CPU Model', 'Core i9-11900', 'Key Specs', 3),
(350, 13, 'CPU Socket', 'LGA 1200', 'Key Specs', 5),
(373, 16, 'CPU Model', 'Core i9', 'Key Specs', 3),
(374, 16, 'CPU Speed', '3.2 GHz', 'Key Specs', 4),
(375, 16, 'CPU Socket', 'LGA 1700', 'Key Specs', 5),
(384, 15, 'Brand', 'Intel', 'Key Specs', 1),
(385, 15, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(386, 15, 'CPU Model', 'Intel Core i9', 'Key Specs', 3),
(387, 15, 'CPU Speed', '3 GHz', 'Key Specs', 4),
(388, 15, 'CPU Socket', 'LGA 1700', 'Key Specs', 5),
(389, 24, 'Brand', 'MSI', 'Key Specs', 1),
(390, 24, 'Graphics Coprocessor', 'NVIDIA GeForce RTX 3050', 'Key Specs', 2),
(391, 24, 'Graphics RAM Size', '6 GB', 'Key Specs', 3),
(392, 24, 'Video Output Interface', 'DisplayPort, HDMI', 'Key Specs', 4),
(393, 24, 'Graphics Processor Manufacturer', 'NVIDIA', 'Key Specs', 5),
(394, 24, 'Manufacturer', '‎MSI, Micro-Star INT\'L CO., LTD. No.69, Lide St., Zhonghe Dist., New Taipei City 235, Taiwan http://www.msi.com.', 'Detailed Specs', 6),
(395, 24, 'Series', '‎GeForce RTX 3050 VENTUS 2X 6G OC', 'Detailed Specs', 7),
(396, 24, 'Colour', 'Black', 'Detailed Specs', 8),
(397, 24, 'Item Height', '‎0.1 Centimeters', 'Detailed Specs', 9),
(398, 24, 'Item Width', '‎109 Centimeters', 'Detailed Specs', 10),
(399, 24, 'Resolution', '‎3840 x 2160', 'Detailed Specs', 11),
(400, 24, 'Product Dimensions', '‎189 x 109 x 0.1 cm; 381 g', 'Detailed Specs', 12),
(401, 24, 'Item model number', '‎V812-015R', 'Detailed Specs', 13),
(402, 24, 'Computer Memory Type', 'DIMM', 'Detailed Specs', 14),
(403, 24, 'Memory Clock Speed', '‎1400 MHz', 'Detailed Specs', 15),
(404, 24, 'Graphics Coprocessor', '‎NVIDIA GeForce RTX 3050', 'Detailed Specs', 16),
(405, 24, 'Graphics Card Description', 'Dedicated', 'Detailed Specs', 17),
(406, 24, 'Graphics RAM Type', 'GDDR6', 'Detailed Specs', 18),
(407, 24, 'Graphics Card Ram Size', '‎6 GB', 'Detailed Specs', 19),
(408, 24, 'Included Components', '‎‎‎Graphics card; quick setup guide', 'Detailed Specs', 20),
(409, 24, 'Item Weight', '‎381 g', 'Detailed Specs', 21),
(457, 25, 'Item Weight', '‎510 g', 'Detailed Specs', 18),
(456, 25, 'Graphics Card Interface', '‎PCI-Express x16', 'Detailed Specs', 17),
(455, 25, 'Graphics Card Ram Size', '‎8 GB', 'Detailed Specs', 16),
(454, 25, 'Graphics RAM Type', '‎GDDR6', 'Detailed Specs', 15),
(453, 25, 'Memory Clock Speed', '‎17000 MHz', 'Detailed Specs', 14),
(452, 25, 'Item model number', '‎GV-N4060WF2OC-8GD', 'Detailed Specs', 13),
(451, 25, 'Product Dimensions', '‎19.2 x 12 x 4.1 cm; 510 g', 'Detailed Specs', 12),
(450, 25, 'Resolution', '‎7680 x 4320 Pixels', 'Detailed Specs', 11),
(449, 25, 'Item Width', '‎12 Centimeters', 'Detailed Specs', 10),
(448, 25, 'Item Height', '‎41 Millimeters', 'Detailed Specs', 9),
(447, 25, 'Colour', 'Black', 'Detailed Specs', 8),
(446, 25, 'Series', '‎GV-N4060WF2OC-8GD', 'Detailed Specs', 7),
(445, 25, 'Manufacturer', '‎GIGABYTE, Gigabyte', 'Detailed Specs', 6),
(500, 26, 'Graphics Coprocessor', 'AMD Radeon RX 7700 XT', 'Key Specs', 1),
(499, 26, 'Item Weight', '‎930 g', 'Detailed Specs', 17),
(498, 26, 'Wattage', '‎750 Watts', 'Detailed Specs', 16),
(497, 26, 'Graphics Card Ram Size', '‎12 GB', 'Detailed Specs', 15),
(496, 26, 'Memory Clock Speed', '‎18000 MHz', 'Detailed Specs', 14),
(495, 26, 'Item model number', '‎90YV0JZ0-M0NA00', 'Detailed Specs', 13),
(494, 26, 'Product Dimensions', '‎27.99 x 13.39 x 0.25 cm; 930 g', 'Detailed Specs', 12),
(493, 26, 'Resolution', '‎7680 x 4320 Pixels', 'Detailed Specs', 11),
(492, 26, 'Item Width', '‎5.27 Inches', 'Detailed Specs', 10),
(491, 26, 'Item Height', '‎0.1 Inches', 'Detailed Specs', 9),
(490, 26, 'Colour', 'Black', 'Detailed Specs', 8),
(489, 26, 'Series', '‎RX 7700 XT', 'Detailed Specs', 7),
(488, 26, 'Manufacturer', '‎Asus, Asus Technology', 'Detailed Specs', 6),
(458, 25, 'Graphics Coprocessor', 'NVIDIA GeForce RTX 4060', 'Key Specs', 1),
(459, 25, 'Brand', 'GIGABYTE', 'Key Specs', 2),
(460, 25, 'Graphics RAM Size', '8 GB', 'Key Specs', 3),
(461, 25, 'Video Output Interface', 'DisplayPort, HDMI', 'Key Specs', 4),
(462, 25, 'Graphics Processor Manufacturer', 'NVIDIA', 'Key Specs', 5),
(463, 27, 'Graphics Coprocessor', 'AMD Radeon', 'Key Specs', 1),
(464, 27, 'Brand', 'ASUS', 'Key Specs', 2),
(465, 27, 'Graphics RAM Size', '16', 'Key Specs', 3),
(466, 27, 'Video Output Interface', 'DisplayPort, HDMI', 'Key Specs', 4),
(467, 27, 'Graphics Processor Manufacturer', 'ASUS', 'Key Specs', 5),
(468, 27, 'Manufacturer', '‎Asus Technology Pvt Ltd., Asus Technology Pvt Ltd.', 'Detailed Specs', 6),
(469, 27, 'Series', '‎PRIME-RTX5070TI-O16G', 'Detailed Specs', 7),
(470, 27, 'Colour', 'Black', 'Detailed Specs', 8),
(471, 27, 'Item Height', '‎0.1 Centimeters', 'Detailed Specs', 9),
(472, 27, 'Item Width', '‎12.6 Centimeters', 'Detailed Specs', 10),
(473, 27, 'Resolution', '‎3840 x 2160', 'Detailed Specs', 11),
(474, 27, 'Product Dimensions', '‎30.4 x 12.6 x 0.1 cm; 1.17 kg', 'Detailed Specs', 12),
(475, 27, 'Item model number', '‎90YV0MF0-M0NA00', 'Detailed Specs', 13),
(476, 27, 'Memory Clock Speed', '‎28000 MHz', 'Detailed Specs', 14),
(477, 27, 'Graphics Card Description', '‎GeForce RTX™ 5070 Ti, 16GB GDDR7, PCIe® 5.0, HDMI®/DP 2.1, Axial-tech Fans', 'Detailed Specs', 15),
(478, 27, 'Graphics RAM Type', '‎GDDR6', 'Detailed Specs', 16),
(479, 27, 'Graphics Card Ram Size', '‎16', 'Detailed Specs', 17),
(480, 27, 'Graphics Card Interface', '‎PCI Express', 'Detailed Specs', 18),
(481, 27, 'Are Batteries Included', 'No', 'Detailed Specs', 19),
(482, 27, 'Lithium Battery Energy Content', '‎1 Kilowatt Hours', 'Detailed Specs', 20),
(483, 27, 'Lithium Battery Weight', '‎1 Grams', 'Detailed Specs', 21),
(484, 27, 'Included Components', '‎1 x Speedsetup Manual, 1 x G Card', 'Detailed Specs', 22),
(485, 27, 'Manufacturer', '‎Asus Technology Pvt Ltd.', 'Detailed Specs', 23),
(486, 27, 'Country of Origin', 'China', 'Detailed Specs', 24),
(487, 27, 'Item Weight', '‎1 kg 170 g', 'Detailed Specs', 25),
(501, 26, 'Brand', 'ASUS', 'Key Specs', 2),
(502, 26, 'Graphics RAM Size', '12 GB', 'Key Specs', 3),
(503, 26, 'Video Output Interface', 'DisplayPort, HDMI', 'Key Specs', 4),
(504, 26, 'Graphics Processor Manufacturer', 'AMD', 'Key Specs', 5),
(505, 28, 'Brand', 'ASUS', 'Key Specs', 1),
(506, 28, 'CPU Socket', 'LGA 1700', 'Key Specs', 2),
(507, 28, 'Compatible Devices', 'Personal Computer', 'Key Specs', 3),
(508, 28, 'RAM Memory Technology', 'DDR5', 'Key Specs', 4),
(509, 28, 'Compatible Processors', 'Intel® Socket LGA1700 for 13th Gen Intel® Core™ & 12th Gen Intel® Core™', 'Key Specs', 5),
(510, 28, 'Manufacturer', '‎ASUS, ASUS GLOBAL PTE LTD, SINGAPORE', 'Detailed Specs', 6),
(511, 28, 'Series', '‎PRIME B760M-A WIFI', 'Detailed Specs', 7),
(512, 28, 'Item Height', '‎50 Millimeters', 'Detailed Specs', 8),
(513, 28, 'Item Width', '‎24.4 Centimeters', 'Detailed Specs', 9),
(514, 28, 'Product Dimensions', '‎24.4 x 24.4 x 5 cm; 890 g', 'Detailed Specs', 10),
(515, 28, 'Item model number', '‎90MB1EL0-M0IAY0', 'Detailed Specs', 11),
(516, 28, 'Processor Socket', '‎LGA 1700', 'Detailed Specs', 12),
(517, 28, 'Memory Technology', 'DDR5', 'Detailed Specs', 13),
(518, 28, 'Maximum Memory Supported', '‎128 GB', 'Detailed Specs', 14),
(519, 28, 'Memory Clock Speed', '‎5600 MHz', 'Detailed Specs', 15),
(520, 28, 'Wireless Type', '‎2.4 GHz Radio Frequency, 5 GHz Radio Frequency, 802.11a/b/g/n/ac, 802.11ax', 'Detailed Specs', 16),
(521, 28, 'Number of USB 2.0 Ports', '1', 'Detailed Specs', 17),
(522, 28, 'Number of HDMI Ports', '2', 'Detailed Specs', 18),
(523, 28, 'Number of Ethernet Ports', '1', 'Detailed Specs', 19),
(524, 28, 'Item Weight', '‎890 g', 'Detailed Specs', 20),
(525, 29, 'Brand', 'MSI', 'Key Specs', 1),
(526, 29, 'CPU Socket', 'LGA 1851', 'Key Specs', 2),
(527, 29, 'Compatible Devices', 'Personal Computer', 'Key Specs', 3),
(528, 29, 'RAM Memory Technology', 'DDR5', 'Key Specs', 4),
(529, 29, 'Compatible Processors', 'Supports Intel Core Ultra Processors (Series 2)', 'Key Specs', 5),
(530, 29, 'Manufacturer', '‎MSI, Micro-Star INT\'L CO., LTD. No.69, Lide St., Zhonghe Dist., New Taipei City 235, Taiwan http://www.msi.com.', 'Detailed Specs', 6),
(531, 29, 'Series', '‎MEG Z890 GODLIKE', 'Detailed Specs', 7),
(532, 29, 'Item Height', '‎6 Centimeters', 'Detailed Specs', 8),
(533, 29, 'Item Width', '‎30.4 Centimeters', 'Detailed Specs', 9),
(534, 29, 'Product Dimensions', '‎27.7 x 30.4 x 6 cm; 3.1 kg', 'Detailed Specs', 10),
(535, 29, 'Item model number', '‎MEG Z890 GODLIKE', 'Detailed Specs', 11),
(536, 29, 'Processor Socket', '‎LGA 1851', 'Detailed Specs', 12),
(537, 29, 'Memory Clock Speed', '‎9200 MHz', 'Detailed Specs', 13),
(538, 29, 'Number of Ethernet Ports', '2', 'Detailed Specs', 14),
(539, 29, 'Item Weight', '‎3 kg 100 g', 'Detailed Specs', 15),
(540, 30, 'Brand', 'ASUS', 'Key Specs', 1),
(541, 30, 'CPU Socket', 'Socket AM5', 'Key Specs', 2),
(542, 30, 'Compatible Devices', 'Personal Computer', 'Key Specs', 3),
(543, 30, 'RAM Memory Technology', 'DDR5', 'Key Specs', 4),
(544, 30, 'Compatible Processors', 'AMD Ryzen 9000, AMD Ryzen 8000, AMD Ryzen 7000', 'Key Specs', 5),
(545, 30, 'Manufacturer', '‎Asus Technology Pvt Ltd., Asus Technology Pvt Ltd.', 'Detailed Specs', 6),
(546, 30, 'Series', '‎ROG STRIX X870E-E GAMING WIFI', 'Detailed Specs', 7),
(547, 30, 'Item Height', '‎9.6 Centimeters', 'Detailed Specs', 8),
(548, 30, 'Item Width', '‎6 Centimeters', 'Detailed Specs', 9),
(549, 30, 'Product Dimensions', '‎12 x 6 x 9.6 cm; 1.9 kg', 'Detailed Specs', 10),
(550, 30, 'Item model number', '‎ROG STRIX X870E-E GAMING WIFI', 'Detailed Specs', 11),
(551, 30, 'Processor Socket', '‎Socket AM5', 'Detailed Specs', 12),
(552, 30, 'Memory Technology', 'DDR5', 'Detailed Specs', 13),
(553, 30, 'Graphics Card Interface', '‎PCI Express', 'Detailed Specs', 14),
(554, 30, 'Number of HDMI Ports', '1', 'Detailed Specs', 15),
(555, 30, 'Number of Ethernet Ports', '1', 'Detailed Specs', 16),
(556, 30, 'Manufacturer', '‎Asus Technology Pvt Ltd.', 'Detailed Specs', 17),
(557, 30, 'Country of Origin', '‎China', 'Detailed Specs', 18),
(558, 30, 'Item Weight', '‎1 kg 900 g', 'Detailed Specs', 19),
(559, 31, 'Brand', 'Corsair', 'Key Specs', 1),
(560, 31, 'Computer Memory Size', '8 GB', 'Key Specs', 2),
(561, 31, 'RAM Memory Technology', 'DDR4', 'Key Specs', 3),
(562, 31, 'Memory Speed', '1 MHz', 'Key Specs', 4),
(563, 31, 'Compatible Devices', 'Desktop', 'Key Specs', 5),
(564, 31, 'Manufacturer', '‎Corsair, Corsair Memory Co. Ltd., 5F.-1, No.5A, Hangxiang Rd., Dayuan Township, Taoyuan County 33747, Taiwan, TEL:(886) 3-3995803, indiservice@corsair.com', 'Detailed Specs', 6),
(565, 31, 'Series', '‎VENGEANCE LPX', 'Detailed Specs', 7),
(566, 31, 'Form Factor', 'DIMM', 'Detailed Specs', 8),
(567, 31, 'Item Height', '‎34 Millimeters', 'Detailed Specs', 9),
(568, 31, 'Item Width', '‎7 Millimeters', 'Detailed Specs', 10),
(569, 31, 'Product Dimensions', '‎13.5 x 0.7 x 3.4 cm; 38 g', 'Detailed Specs', 11),
(570, 31, 'Batteries', '‎1 AAAA batteries required.', 'Detailed Specs', 12),
(571, 31, 'Item model number', '‎CMK8GX4M1E3200C16', 'Detailed Specs', 13),
(572, 31, 'RAM Size', '‎8 GB', 'Detailed Specs', 14),
(573, 31, 'Memory Technology', '‎DDR4', 'Detailed Specs', 15),
(574, 31, 'Computer Memory Type', '‎DDR4 SDRAM', 'Detailed Specs', 16),
(575, 31, 'Memory Clock Speed', '1', 'Detailed Specs', 17),
(576, 31, 'Hard Disk Description', '‎Solid State Drive', 'Detailed Specs', 18),
(577, 31, 'Voltage', '‎1.2 Volts', 'Detailed Specs', 19),
(578, 31, 'Wattage', '‎50 Watts', 'Detailed Specs', 20),
(579, 31, 'Lithium Battery Weight', '‎5 Grams', 'Detailed Specs', 21),
(580, 31, 'Number of Lithium Ion Cells', '4', 'Detailed Specs', 22),
(581, 31, 'Item Weight', '‎38 g', 'Detailed Specs', 23),
(582, 32, 'Brand', 'G.SKILL', 'Key Specs', 1),
(583, 32, 'Computer Memory Size', '16 GB', 'Key Specs', 2),
(584, 32, 'RAM Memory Technology', 'DDR4', 'Key Specs', 3),
(585, 32, 'Memory Speed', '3200 MT/s', 'Key Specs', 4),
(586, 32, 'Compatible Devices', 'Desktop', 'Key Specs', 5),
(587, 32, 'Manufacturer', '‎G.SKILL, G.Skill', 'Detailed Specs', 6),
(588, 32, 'Series', 'Trident', 'Detailed Specs', 7),
(589, 32, 'Colour', '‎3200Mhz', 'Detailed Specs', 8),
(590, 32, 'Form Factor', 'DIMM', 'Detailed Specs', 9),
(591, 32, 'Item Height', '‎4.4 Centimeters', 'Detailed Specs', 10),
(592, 32, 'Item Width', '‎0.8 Centimeters', 'Detailed Specs', 11),
(593, 32, 'Product Dimensions', '‎13.5 x 0.8 x 4.4 cm; 138 g', 'Detailed Specs', 12),
(594, 32, 'Item model number', '202715', 'Detailed Specs', 13),
(595, 32, 'RAM Size', '‎16 GB', 'Detailed Specs', 14),
(596, 32, 'Memory Technology', 'DDR4', 'Detailed Specs', 15),
(597, 32, 'Computer Memory Type', '‎DDR4 SDRAM', 'Detailed Specs', 16),
(598, 32, 'Memory Clock Speed', '2', 'Detailed Specs', 17),
(599, 32, 'Voltage', '‎1.35 Volts', 'Detailed Specs', 18),
(600, 32, 'Item Weight', '‎138 g', 'Detailed Specs', 19),
(601, 33, 'Brand', 'G.SKILL', 'Key Specs', 1),
(602, 33, 'Computer Memory Size', '12', 'Key Specs', 2),
(603, 33, 'RAM Memory Technology', 'DDR4', 'Key Specs', 3),
(604, 33, 'Memory Speed', '4000 MHz', 'Key Specs', 4),
(605, 33, 'Compatible Devices', 'Desktop', 'Key Specs', 5),
(606, 33, 'Manufacturer', '‎G.SKILL, G.SKILL', 'Detailed Specs', 6),
(607, 33, 'Series', '‎F4-4000C18D-64GVK', 'Detailed Specs', 7),
(608, 33, 'Colour', 'Black', 'Detailed Specs', 8),
(609, 33, 'Form Factor', 'DIMM', 'Detailed Specs', 9),
(610, 33, 'Item Height', '‎85 Centimeters', 'Detailed Specs', 10),
(611, 33, 'Item Width', '‎60 Centimeters', 'Detailed Specs', 11),
(612, 33, 'Product Dimensions', '‎60 x 60 x 85 cm; 80 g', 'Detailed Specs', 12),
(613, 33, 'Item model number', '‎F4-4000C18D-64GVK', 'Detailed Specs', 13),
(614, 33, 'RAM Size', '12', 'Detailed Specs', 14),
(615, 33, 'Memory Technology', 'DDR4', 'Detailed Specs', 15),
(616, 33, 'Computer Memory Type', '‎DDR4 SDRAM', 'Detailed Specs', 16),
(617, 33, 'Memory Clock Speed', '‎4000 MHz', 'Detailed Specs', 17),
(618, 33, 'Voltage', '‎220', 'Detailed Specs', 18),
(619, 33, 'Wattage', '‎3600', 'Detailed Specs', 19),
(620, 33, 'Lithium Battery Weight', '‎2 Grams', 'Detailed Specs', 20),
(621, 33, 'Item Weight', '‎80 g', 'Detailed Specs', 21),
(622, 34, 'Digital Storage Capacity', '2 TB', 'Key Specs', 1),
(623, 34, 'Hard Disk Interface', 'USB 2.0/3.0', 'Key Specs', 2),
(624, 34, 'Connectivity Technology', 'USB', 'Key Specs', 3),
(625, 34, 'Brand', 'Seagate', 'Key Specs', 4),
(626, 34, 'Special Feature', 'Data Recovery Service, Portable', 'Key Specs', 5),
(627, 34, 'Hard Disk Form Factor', '2.5 Inches', 'Key Specs', 6),
(628, 34, 'Hard Disk Description', 'Mechanical Hard Disk', 'Key Specs', 7),
(629, 34, 'Compatible Devices', 'Desktop, Laptop', 'Key Specs', 8),
(630, 34, 'Installation Type', 'External Hard Drive', 'Key Specs', 9),
(631, 34, 'Manufacturer', '‎Seagate Technology (Thailand) Limited. India Customer Care No: 18003092525, Seagate Technology (Thailand) Limited. India Customer Care No: 18003092525', 'Detailed Specs', 10),
(632, 34, 'Colour', '‎Black', 'Detailed Specs', 11),
(633, 34, 'Form Factor', '‎2.5-inch', 'Detailed Specs', 12),
(634, 34, 'Item Height', '‎11.7 Centimeters', 'Detailed Specs', 13),
(635, 34, 'Item Width', '‎1.5 Centimeters', 'Detailed Specs', 14),
(636, 34, 'Product Dimensions', '‎8 x 1.5 x 11.7 cm; 181.44 g', 'Detailed Specs', 15),
(637, 34, 'Item model number', '‎STKM2000400', 'Detailed Specs', 16),
(638, 34, 'Hard Disk Description', '‎Mechanical Hard Disk', 'Detailed Specs', 17),
(639, 34, 'Hard Drive Interface', '‎USB 2.0/3.0', 'Detailed Specs', 18),
(640, 34, 'Hard Disk Rotational Speed', '‎5400 RPM', 'Detailed Specs', 19),
(641, 34, 'Wattage', '3600', 'Detailed Specs', 20),
(642, 34, 'Hardware Platform', '‎Mac, PC', 'Detailed Specs', 21),
(643, 34, 'Included Components', '‎Seagate® Expansion portable hard drive, 18-inch (45.72cm) USB 3.0 cable, Quick start guide, Limited Warranty: APAC - 3 years, Rescue Data Recovery Services', 'Detailed Specs', 22),
(644, 34, 'Item Weight', '‎181 g', 'Detailed Specs', 23),
(645, 35, 'Digital Storage Capacity', '4 TB', 'Key Specs', 1),
(646, 35, 'Hard Disk Interface', 'Serial ATA', 'Key Specs', 2),
(647, 35, 'Connectivity Technology', 'SATA', 'Key Specs', 3),
(648, 35, 'Brand', 'Seagate', 'Key Specs', 4),
(649, 35, 'Special Feature', 'Integrated Data Security', 'Key Specs', 5),
(650, 35, 'Hard Disk Form Factor', '3.5 Inches', 'Key Specs', 6),
(651, 35, 'Hard Disk Description', 'Mechanical Hard Disk', 'Key Specs', 7),
(652, 35, 'Compatible Devices', 'Desktop, Laptop', 'Key Specs', 8),
(653, 35, 'Installation Type', 'Internal Hard Drive', 'Key Specs', 9),
(654, 35, 'Manufacturer', '‎Seagate, Seagate, Cal-Comp Electronics (Thailand) Public Co Ltd. 60 Moo 8, Sethakij Road, Klong Maduea,KratoomBan, Samusakorn,74110,THAILAND Techman Electronics (Thailand) Co. Ltd. No. 236-237 Moo 2 Tambol Nongchark, Amphur, Banbung, Chonburi, Thailand 2', 'Detailed Specs', 10),
(655, 35, 'Series', '‎BarraCuda', 'Detailed Specs', 11),
(656, 35, 'Colour', 'Black', 'Detailed Specs', 12),
(657, 35, 'Form Factor', '‎3.5-inch', 'Detailed Specs', 13),
(658, 35, 'Item Height', '‎0.79 Inches', 'Detailed Specs', 14),
(659, 35, 'Item Width', '‎4 Inches', 'Detailed Specs', 15),
(660, 35, 'Product Dimensions', '‎14.7 x 10.16 x 2 cm; 460 g', 'Detailed Specs', 16),
(661, 35, 'Item model number', '‎ST4000DMB04', 'Detailed Specs', 17),
(662, 35, 'RAM Size', '‎4 TB', 'Detailed Specs', 18),
(663, 35, 'Hard Drive Size', '‎4 TB', 'Detailed Specs', 19),
(664, 35, 'Hard Disk Description', '‎Mechanical Hard Disk', 'Detailed Specs', 20),
(665, 35, 'Hard Disk Rotational Speed', '‎5400 RPM', 'Detailed Specs', 21),
(666, 35, 'Wattage', '125', 'Detailed Specs', 22),
(667, 35, 'Hardware Platform', '‎Desktop', 'Detailed Specs', 23),
(668, 35, 'Included Components', '‎Internal Hard disk', 'Detailed Specs', 24),
(669, 35, 'Item Weight', '‎460 g', 'Detailed Specs', 25),
(670, 36, 'Digital Storage Capacity', '4 TB', 'Key Specs', 1),
(671, 36, 'Hard Disk Interface', 'Solid State', 'Key Specs', 2),
(672, 36, 'Connectivity Technology', 'PCI Express NVMe', 'Key Specs', 3),
(673, 36, 'Brand', 'Samsung', 'Key Specs', 4),
(674, 36, 'Special Feature', 'Backward Compatible', 'Key Specs', 5),
(675, 36, 'Hard Disk Form Factor', '2.5 Inches', 'Key Specs', 6),
(676, 36, 'Hard Disk Description', 'Solid State Drive', 'Key Specs', 7),
(677, 36, 'Compatible Devices', 'Desktop, Gaming Console, Laptop', 'Key Specs', 8),
(678, 36, 'Installation Type', 'Internal Hard Drive', 'Key Specs', 9),
(679, 36, 'Colour', 'Black 990 PRO 4TB', 'Key Specs', 10),
(680, 36, 'Manufacturer', '‎Samsung, Samsung Electronics Co., Ltd. Semiconductor Business Test & Package Center, #158 Baebang-Ro Baebang-Eup, Asan-city Chungcheongnam-Do, 31489 Republic of Korea', 'Detailed Specs', 11),
(681, 36, 'Series', '‎990 PRO', 'Detailed Specs', 12),
(682, 36, 'Colour', '‎Black 990 PRO 4TB', 'Detailed Specs', 13),
(683, 36, 'Form Factor', '‎2.5-inch', 'Detailed Specs', 14),
(684, 36, 'Item Height', '‎0.09 Inches', 'Detailed Specs', 15),
(685, 36, 'Item Width', '‎0.87 Inches', 'Detailed Specs', 16),
(686, 36, 'Product Dimensions', '‎8 x 2.21 x 0.23 cm; 9.07 g', 'Detailed Specs', 17),
(687, 36, 'Item model number', '‎MZ-V9P4T0B/AM', 'Detailed Specs', 18),
(688, 36, 'Hard Drive Size', '‎4 TB', 'Detailed Specs', 19),
(689, 36, 'Hard Disk Description', '‎Solid State Drive', 'Detailed Specs', 20),
(690, 36, 'Hard Drive Interface', '‎Solid State', 'Detailed Specs', 21),
(691, 36, 'Hardware Platform', '‎PC', 'Detailed Specs', 22),
(692, 36, 'Included Components', '‎SSD , User manual', 'Detailed Specs', 23),
(693, 36, 'Manufacturer', '‎Samsung', 'Detailed Specs', 24),
(694, 36, 'Country of Origin', '‎Republic of Korea', 'Detailed Specs', 25),
(695, 36, 'Item Weight', '‎9.07 g', 'Detailed Specs', 26),
(696, 37, 'Model Name', 'MWE Gold 750 V2', 'Key Specs', 1),
(697, 37, 'Brand', 'Cooler Master', 'Key Specs', 2),
(698, 37, 'Compatible Devices', 'Personal Computer', 'Key Specs', 3),
(699, 37, 'Connector Type', 'ATX, EPS, SATA', 'Key Specs', 4),
(700, 37, 'Output Wattage', '750 Watts', 'Key Specs', 5),
(701, 37, 'Manufacturer', '‎Cooler Master, Cooler Master Technology Inc. 8F., No. 788-1, Zhongzheng Rd., Zhonghe Dist., New Taipei City 23586, Taiwan (R.O.C.) TEL: +886-2-2225-3517 / FAX: +886-2-3234-0221', 'Detailed Specs', 6),
(702, 37, 'Series', '‎MWE Gold 750 V2', 'Detailed Specs', 7),
(703, 37, 'Colour', '‎Black', 'Detailed Specs', 8),
(704, 37, 'Form Factor', 'ATX', 'Detailed Specs', 9),
(705, 37, 'Item Height', '‎8.6 Centimeters', 'Detailed Specs', 10),
(706, 37, 'Item Width', '‎15 Centimeters', 'Detailed Specs', 11),
(707, 37, 'Product Dimensions', '‎14 x 15 x 8.6 cm; 2.17 kg', 'Detailed Specs', 12),
(708, 37, 'Item model number', '‎MPE-7501-AFAAG-IN', 'Detailed Specs', 13),
(709, 37, 'Wattage', '‎750 Watts', 'Detailed Specs', 14),
(710, 37, 'Item Weight', '‎2 kg 170 g', 'Detailed Specs', 15),
(711, 38, 'Model Name', 'VS600L', 'Key Specs', 1),
(712, 38, 'Brand', 'Ant Esports', 'Key Specs', 2),
(713, 38, 'Compatible Devices', 'pc', 'Key Specs', 3),
(714, 38, 'Connector Type', 'ATX, EPS', 'Key Specs', 4),
(715, 38, 'Output Wattage', '600 Watts', 'Key Specs', 5),
(716, 38, 'Manufacturer', '‎Ant Esports, Ant Esports', 'Detailed Specs', 6),
(717, 38, 'Series', 'VS600L', 'Detailed Specs', 7),
(718, 38, 'Colour', 'black', 'Detailed Specs', 8),
(719, 38, 'Form Factor', 'ATX', 'Detailed Specs', 9),
(720, 38, 'Item Height', '‎19.5 Centimeters', 'Detailed Specs', 10),
(721, 38, 'Item Width', '‎11.3 Centimeters', 'Detailed Specs', 11),
(722, 38, 'Product Dimensions', '‎26.7 x 11.3 x 19.5 cm; 1.8 kg', 'Detailed Specs', 12),
(723, 38, 'Item model number', '‎VS600L', 'Detailed Specs', 13),
(724, 38, 'Wattage', '‎600 Watts', 'Detailed Specs', 14),
(725, 38, 'Hardware Platform', 'PC', 'Detailed Specs', 15),
(726, 38, 'Included Components', '‎Power Supply, Power Cable, Mounting Screws, User Manual', 'Detailed Specs', 16),
(727, 38, 'Item Weight', '‎1 kg 800 g', 'Detailed Specs', 17),
(728, 39, 'Colour', 'Snow', 'Key Specs', 1),
(729, 39, 'Size', '1200W', 'Key Specs', 2),
(730, 39, 'Style Name', 'Gold ATX 3.0', 'Key Specs', 3),
(731, 39, 'Pattern Name', 'Toughpower', 'Key Specs', 4),
(732, 39, 'Model Name', 'GF3 1200 White', 'Key Specs', 5),
(733, 39, 'Brand', 'Thermaltake', 'Key Specs', 6),
(734, 39, 'Compatible Devices', 'Personal Computer', 'Key Specs', 7),
(735, 39, 'Connector Type', 'ATX', 'Key Specs', 8),
(736, 39, 'Output Wattage', '1200', 'Key Specs', 9),
(737, 39, 'Manufacturer', 'Thermaltake', 'Detailed Specs', 10),
(738, 39, 'Model', '‎TPD-1200AH3FLG', 'Detailed Specs', 11),
(739, 39, 'Model Name', '‎GF3 1200 White', 'Detailed Specs', 12),
(740, 39, 'Product Dimensions', '‎14.99 x 16 x 8.64 cm; 1.78 kg', 'Detailed Specs', 13),
(741, 39, 'Item model number', '‎TPD-1200AH3FLG', 'Detailed Specs', 14),
(742, 39, 'Compatible Devices', '‎Personal Computer', 'Detailed Specs', 15),
(743, 39, 'Number of items', '1', 'Detailed Specs', 16),
(744, 39, 'Audio Wattage', '‎1200', 'Detailed Specs', 17),
(745, 39, 'Material', '‎Information Not Available', 'Detailed Specs', 18),
(746, 39, 'Item Weight', '‎1 kg 780 g', 'Detailed Specs', 19),
(747, 40, 'Brand', 'Ant Esports', 'Key Specs', 1),
(748, 40, 'Compatible Devices', 'Laptop', 'Key Specs', 2),
(749, 40, 'Connectivity Technology', 'USB', 'Key Specs', 3),
(750, 40, 'Keyboard Description', 'Membrane', 'Key Specs', 4),
(751, 40, 'Recommended Uses For Product', 'Gaming', 'Key Specs', 5),
(752, 40, 'Manufacturer', '‎Ant Esports, Ant Esports', 'Detailed Specs', 6),
(753, 40, 'Colour', '‎White', 'Detailed Specs', 7),
(754, 40, 'Item Height', '‎3.6 Centimeters', 'Detailed Specs', 8),
(755, 40, 'Item Width', '‎13.3 Centimeters', 'Detailed Specs', 9),
(756, 40, 'Product Dimensions', '‎44.5 x 13.3 x 3.6 cm; 471 g', 'Detailed Specs', 10),
(757, 40, 'Power Source', '‎Corded Electric', 'Detailed Specs', 11),
(758, 40, 'Operating System', '‎Windows 10', 'Detailed Specs', 12),
(759, 40, 'Are Batteries Included', '‎No', 'Detailed Specs', 13),
(760, 40, 'Included Components', '‎USB Cable', 'Detailed Specs', 14),
(761, 40, 'Item Weight', '‎471 g', 'Detailed Specs', 15),
(762, 41, 'Brand', 'Logitech G', 'Key Specs', 1),
(763, 41, 'Colour', 'Black', 'Key Specs', 2),
(764, 41, 'Connectivity Technology', 'Wired', 'Key Specs', 3),
(765, 41, 'Special Feature', 'HERO 25K gaming sensor', 'Key Specs', 4),
(766, 41, 'Movement Detection Technology', 'Optical', 'Key Specs', 5),
(767, 41, 'Manufacturer', '‎Logitech G, ‎Logitech G, ‎Logitech India Customer Care Toll free Number 1800 572 4730 (9:00am to 6:00pm -Mon- Fri), Logitech India Customer Care Toll free Number 1800 572 4730 (9:00am to 6:00pm -Mon- Fri)', 'Detailed Specs', 6),
(768, 41, 'Colour', 'Black', 'Detailed Specs', 7),
(769, 41, 'Item Height', '‎7.9 Centimeters', 'Detailed Specs', 8),
(770, 41, 'Item Width', '‎41 Millimeters', 'Detailed Specs', 9),
(771, 41, 'Product Dimensions', '‎13.1 x 4.1 x 7.9 cm; 89 g', 'Detailed Specs', 10),
(772, 41, 'Item model number', '‎910-006140', 'Detailed Specs', 11),
(773, 41, 'Power Source', '‎Corded Electric', 'Detailed Specs', 12),
(774, 41, 'Hardware Platform', '‎Personal Computer', 'Detailed Specs', 13),
(775, 41, 'Operating System', '‎pc, macos, windows', 'Detailed Specs', 14),
(776, 41, 'Average Battery Life (in hours)', '‎12 Months', 'Detailed Specs', 15),
(777, 41, 'Are Batteries Included', '‎Yes', 'Detailed Specs', 16),
(778, 41, 'Manufacturer', '‎Logitech G', 'Detailed Specs', 17),
(779, 41, 'Item Weight', '‎89 g', 'Detailed Specs', 18);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(150) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `date_of_birth`, `gender`, `phone`, `email_verified`, `role`, `status`, `profile_image`, `created_at`, `updated_at`) VALUES
(6, 'saitama', 'saitama@gmail.com', '$2y$10$n8WqdeXBY6tDCtAdyCoZy.oY3qOGCWStbQAuSAZaCxGxdAGnzXq/2', '2025-01-28', 'Male', '1234567891', 0, 'user', 'active', NULL, '2025-10-03 23:36:05', '2025-10-05 14:10:05'),
(5, 'john doe', 'johndoe@gmail.com', '$2y$10$CYCeAyUMyILYj9Fx3tq6G.fh8Pgfw3f3wReLg.NXcAY9XOqaw/Poy', '2001-06-13', 'Male', '1234567891', 0, 'user', 'active', NULL, '2025-10-02 01:32:00', '2025-10-02 01:32:55'),
(1, 'admin', 'admin@gmail.com', '$2y$10$1HiCsOc3R2KQEic4tTOotexh.FDZFY/1VSUWWnbe5OWeyImTCqcPa', '1994-06-08', 'Female', '1234567890', 1, 'admin', 'active', './assets/images/admin.jpg', '2025-10-02 01:24:51', '2025-10-05 14:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

DROP TABLE IF EXISTS `user_address`;
CREATE TABLE IF NOT EXISTS `user_address` (
  `address_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line1` text,
  `address_line2` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`address_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `full_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `zip`, `country`, `is_default`) VALUES
(13, 6, 'Saitama', '1234567891', 'ABCD', NULL, 'Z', 'CA', '363530', NULL, 1),
(11, 1, 'Saitama', '1234567891', 'ABCD', '', 'Los Angeles', 'CA', '90001', 'america', 1),
(5, 5, 'John Doe', '1234567891', '456 Oak St', '', 'Los Angeles', 'California', '70808', 'USA', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
