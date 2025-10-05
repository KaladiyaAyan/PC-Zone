-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 04, 2025 at 05:42 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

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
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `category_id`, `slug`) VALUES
(1, 'Intel', 1, 'intel'),
(2, 'AMD', 1, 'amd'),
(3, 'NVIDIA', 2, 'nvidia'),
(4, 'ASUS', 2, 'asus-gpu'),
(5, 'MSI', 2, 'msi-gpu'),
(6, 'Gigabyte', 2, 'gigabyte-gpu'),
(7, 'ZOTAC', 2, 'zotac'),
(8, 'Inno3D', 2, 'inno3d'),
(9, 'EVGA', 2, 'evga'),
(10, 'ASUS', 3, 'asus-mb'),
(11, 'ASRock', 3, 'asrock'),
(12, 'MSI', 3, 'msi-mb'),
(13, 'Gigabyte', 3, 'gigabyte-mb'),
(14, 'EVGA', 3, 'evga-mb'),
(15, 'Corsair', 4, 'corsair-ram'),
(16, 'G.Skill', 4, 'gskill'),
(17, 'Kingston', 4, 'kingston'),
(18, 'Crucial', 4, 'crucial-ram'),
(19, 'ADATA', 4, 'adata'),
(20, 'Samsung', 5, 'samsung-storage'),
(21, 'WD', 5, 'wd'),
(22, 'Seagate', 5, 'seagate'),
(23, 'Crucial', 5, 'crucial-storage'),
(24, 'ADATA', 5, 'adata-storage'),
(25, 'Kingston', 5, 'kingston-storage'),
(26, 'Corsair', 6, 'corsair-psu'),
(27, 'Antec', 6, 'antec'),
(28, 'Cooler Master', 6, 'cooler-master-psu'),
(29, 'EVGA', 6, 'evga-psu'),
(30, 'Thermaltake', 6, 'thermaltake-psu'),
(31, 'NZXT', 6, 'nzxt-psu'),
(32, 'NZXT', 7, 'nzxt-case'),
(33, 'Lian Li', 7, 'lian-li-case'),
(34, 'Cooler Master', 7, 'cooler-master-case'),
(35, 'Thermaltake', 7, 'thermaltake-case'),
(36, 'Antec', 7, 'antec-case'),
(37, 'DeepCool', 7, 'deepcool-case'),
(38, 'Ant Esports', 7, 'ant-esports-case'),
(39, 'Cooler Master', 8, 'cooler-master-cooler'),
(40, 'DeepCool', 8, 'deepcool-cooler'),
(41, 'NZXT', 8, 'nzxt-cooler'),
(42, 'be quiet!', 8, 'be-quiet'),
(43, 'Arctic', 8, 'arctic'),
(44, 'Thermaltake', 8, 'thermaltake-cooler'),
(45, 'Corsair', 8, 'corsair-cooler'),
(46, 'Lian Li', 8, 'lian-li-cooler'),
(47, 'Dell', 9, 'dell-monitor'),
(48, 'LG', 9, 'lg-monitor'),
(49, 'ASUS', 9, 'asus-monitor'),
(50, 'Acer', 9, 'acer-monitor'),
(51, 'Samsung', 9, 'samsung-monitor'),
(52, 'MSI', 9, 'msi-monitor'),
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
(65, 'Razer', 12, 'razer-mousepad');

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `parent_id`, `icon_image`, `level`, `slug`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Processor', NULL, 'Processor-Icon.webp', 0, 'processor', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(2, 'Graphics Card', NULL, 'graphics-card-icon.webp', 0, 'graphics-card', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(3, 'Motherboard', NULL, 'motherboard-icon.webp', 0, 'motherboard', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(4, 'RAM', NULL, 'RAM-icon.webp', 0, 'ram', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(5, 'Storage', NULL, 'ssd-icon.webp', 0, 'storage', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(6, 'Power Supply', NULL, 'psu-icon.webp', 0, 'power-supply', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(7, 'Cabinet', NULL, 'cabinet-icon.webp', 0, 'cabinet', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(8, 'Cooling System', NULL, 'liquid-cooler-icon.webp', 0, 'cooling-system', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(9, 'Monitor', NULL, 'monitor-icon.webp', 0, 'monitor', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(10, 'Keyboard', NULL, 'keyboard-icon.webp', 0, 'keyboard', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(11, 'Mouse', NULL, 'mouse-icon.webp', 0, 'mouse', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(12, 'Mousepad', NULL, 'mousepad-icon.webp', 0, 'mousepad', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(13, 'Intel', 1, NULL, 1, 'intel', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(14, 'AMD', 1, NULL, 1, 'amd', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(15, 'SSD', 5, NULL, 1, 'ssd', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(16, 'HDD', 5, NULL, 1, 'hdd', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(17, 'NVMe', 5, NULL, 1, 'nvme', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(18, 'Air Cooler', 8, NULL, 1, 'air-cooler', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(19, 'Liquid Cooler', 8, NULL, 1, 'liquid-cooler', 'active', 9999, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(21, 'Razer', 20, NULL, 0, 'razer', 'active', 9999, '2025-10-03 14:05:01', '2025-10-03 14:05:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `billing_address_id` int NOT NULL,
  `shipping_address_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('Pending','Processing','Shipped','Delivered','Cancelled','Returned') DEFAULT 'Pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipping_method` varchar(100) DEFAULT NULL,
  `order_notes` text,
  `paid_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `refunded_at` datetime DEFAULT NULL,
  `shipped_date` date DEFAULT NULL,
  `delivered_date` date DEFAULT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `billing_address_id` (`billing_address_id`),
  KEY `shipping_address_id` (`shipping_address_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `discount` decimal(6,2) DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method` enum('cash_on_delivery','credit_card','debit_card','upi') NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `sku`, `slug`, `description`, `price`, `discount`, `stock`, `weight`, `rating`, `brand_id`, `category_id`, `main_image`, `image_1`, `image_2`, `image_3`, `platform`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(11, 'Intel Core i7-11700 Desktop Processor 8 Cores up to 4.9 GHz LGA1200 (Intel 500 Series & Select 400 Series Chipset) 65W', 'B08X6QHYDL', 'intel-core-i7-11700-desktop-processor-8-cores-up-to-49-ghz-lga1200-intel-500-series-select-400-series-chipset-65w', 'Compatible with Intel 500 series & select Intel 400 series chipset based motherboards\r\nIntel Turbo Boost Max Technology 3.0 Support\r\nIntel Optane Memory Support\r\nPCIe Gen 4.0 Support\r\nThermal solution included', 24999.00, 44.00, 32, 0.00, 0, 1, 1, '1759554881_image1.jpg', '1759554881_image2.jpg', '1759554881_image3.jpg', '1759554881_image4.jpg', 'none', 0, 1, '2025-10-03 23:32:31', '2025-10-04 05:14:41'),
(5, 'Intel® Core™ i3-12100 Processor 12M Cache, up to 4.30 GHz', 'B09NPHJLPT', 'intel-core-i3-12100-processor-12m-cache-up-to-430-ghz', 'Intel Core i3-12100 Processor 12M Cache, up to 4.30 GHz\r\nIt ensures a hassle-free usage\r\nIt is durable and long lasting.', 12999.00, 30.00, 20, 0.00, 0, 1, 1, '1759496274_image1.jpg', '17594961701.jpg', '17594961702.jpg', '17594961703.jpg', 'none', 1, 1, '2025-10-03 07:26:10', '2025-10-03 07:39:24'),
(6, 'Intel® Core™ i5-13600KF Processor 24M Cache, up to 5.10 GHz', 'B0BG64N549', 'intel-core-i5-13600kf-processor-24m-cache-up-to-510-ghz', '24M Cache, up to 5.10 GHz', 20889.00, 20.00, 15, 0.00, 0, 1, 1, '1759497517.jpg', '17594975171.jpg', '17594975172.jpg', '17594975173.jpg', 'none', 0, 1, '2025-10-03 07:48:37', '2025-10-03 23:26:50'),
(3, 'Cooler Master RR-212S-20PC-R1 Hyper 212 RGB Black Edition CPU Air Cooler 4 Direct Contact Heat Pipes 120mm RGB Fan', 'B07H22TC1N', 'cooler-master-rr-212s-20pc-r1-hyper-212-rgb-black-edition-cpu-air-cooler-4-direct-contact-heat-pipes-120mm-rgb-fan', 'Cooler Master Hyper 212 RGB Black Edition Cooling Fan Heatsink - 57.3 CFM - 30 dB(A) Noise - 4-pin PWM Fan - Socket R4 LGA-2066, Socket LGA 2011-v3, Socket R LGA-2011, Socket H4 LGA-1151, Socket H3 LGA-1150, Socket H2 LGA-1155, Socket H LGA-1156, Socket B LGA-1366, Socket AM4, Socket AM3+, Socket AM3 PGA-941, ... Compatible Processor Socket - RGB LED - Aluminum - 18.3 Year Life', 12000.00, 16.00, 4, 0.65, 4, 34, 18, '81B-HuW8ydL._SY450_.jpg', '81jR4Io8OwL._SY450_.jpg', '71Q3El-2flL._SY450_.jpg', '71+9-o7dIwL._SY450_.jpg', 'both', 1, 1, '2025-10-02 06:54:52', '2025-10-02 06:54:52'),
(4, 'Ant Esports ICE-C612 V2 ARGB CPU Cooler| Support Intel LGA1200, LGA115X, LGA20XX, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 'B084G3MJPZ', 'ant-esports-ice-c612-v2-argb-cpu-cooler', 'Efficient Heat Dissipation: The Ant Esports ICE-C612 V2 CPU air cooler is designed for optimal heat dissipation, featuring a 153mm tall aluminum heatsink and six 6mm thick copper heatpipes. This advanced cooling solution ensures efficient heat transfer from the CPU to the heatsink, effectively reducing temperatures and maintaining peak performance even during demanding tasks.\r\n   Enhanced Cooling Performance: Equipped with a high-performance PWM 120mm ARGB fan, the ICE-C612 V2 cooler offers not only excellent cooling efficiency but also adds a vibrant visual flair to your system. The fans adjustable speed through pulse-width modulation (PWM) ensures a fine balance between cooling power and noise levels, keeping your CPU operating at an ideal temperature while maintaining a quiet environment\r\n   Optimized Surface Area: The interlocked aluminum heatsink design of the ICE-C612 V2 is engineered to provide a larger surface area for heat dissipation. This design maximizes the contact area between the heatsink and the surrounding air, allowing for quicker and more effective heat dispersion. Whether you are running intensive applications or engaging in heavy gaming sessions, this cooler helps maintain stable and consistent performance.\r\n   Wide Compatibility: The Ant Esports ICE-C612 V2 CPU air cooler offers broad compatibility with major Intel and AMD platforms, including the latest LGA 1700 and AM5 sockets. This versatility makes it an ideal choice for both current and future system builds, allowing you to upgrade your CPU without worrying about changing cooling solutions.\r\n   Easy Installation: Installing the ICE-C612 V2 cooler is a hassle-free process thanks to its user-friendly design. The included mounting hardware and easy-to-follow instructions ensure a smooth installation experience, even for users with minimal technical expertise. With its secure mounting mechanism, you can trust that your cooler will be properly seated for optimal thermal performance.\r\n   Support Intel LGA1200, LGA1150, LGA1151, LGA1155, LGA1156, LGA2066, LGA2011-v3, LGA2011, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 3499.00, 64.00, 6, 0.65, 4.1, 38, 18, '51pCa994ysL._SY450_.jpg', '61PedtDNzIL._SY450_.jpg', '71kG6EFIMwL._SY450_.jpg', '61Yb+64vAkL._SY450_.jpg', 'both', 1, 1, '2025-10-02 06:54:52', '2025-10-02 06:54:52'),
(2, 'Lian Li O11 Dynamic EVO XL Full-Tower Compter Case/Gaming Cabinet - White | Support EATX/ATX/Micro-ATX/MINI-ITX - G99.O11DEXL-W.in', 'B0CGM6RKV8', 'lian-li-o11-dynamic-evo-xl-full-tower-computer-case-gaming-cabinet-white', 'White, Full-Tower, 522 x 304 x 531.9 mm , 4.0mm Tempered Glass Aluminum 8 Expansion Slots, Storage : Behind MB Tray: 3 X 2.5ʹʹ SSD Hard Drive Cage: 4 X 3.5ʹʹ HDD or 2.5ʹʹ SSD I/O Panel : Power Button , Reset Button , USB 3.0 x 4 , Audio x 1 , USB Type C , Color Button , Mode Button Fan Support : Top - 120mm x3 / 140mm x3, Side- 120mm x3 / 140mm x3, Bottom- 120mm x3/ 140mm x3, Rear- 120 mm x1 or 2 GPU Length Clearance : 460mm(Max) ; CPU Cooler Height Clearance : 167mm(Max)', 30999.00, 24.00, 9, 0.65, 4.9, 33, 7, '610tNgEZ6LL._SX679_.jpg', '61zXV1X5zTL._SX679_.jpg', '712etNmCVRL._SX679_.jpg', '71O8DnFAk5L._SX679_.jpg', 'both', 1, 1, '2025-10-02 06:54:52', '2025-10-02 06:54:52'),
(1, 'NZXT H6 Flow | CC-H61FB-01 | Compact Dual-Chamber Mid-Tower Airflow Case | Panoramic Glass Panels | High-Performance Airflow Panels | Includes 3 x 120mm Fans | Cable Management | Black', 'B0C89FCDFP', 'nzxt-h6-flow', 'Wraparound glass panels with a seamless edge provides an unobstructed view of the inside to highlight key components. Compact dual-chamber design improves overall thermal performance and creates a clean, uncrowded aesthetic. Includes three pre-installed 120mm fans positioned at an ideal angle for superb out-of-the-box cooling. The top and side panels feature an airflow-optimized perforation pattern to enhance overall performance and filter dust. An intuitive cable management system simplifies the build process by using wide channels and straps.', 13999.00, 23.00, 10, 0.65, 4.7, 32, 7, '71x+i8yRgrL._SY450_.jpg', '71YDILR+QnL._SY450_.jpg', '71vtU8bv48L._SY450_.jpg', '71u5IWhR-aL._SY450_.jpg', 'both', 1, 1, '2025-10-02 06:54:52', '2025-10-02 06:54:52'),
(12, 'Intel Core I7-13700F Desktop Processor 16 Cores (8 P-Cores + 8 E-Cores) 30Mb Cache,Up to 5.2 Ghz,LGA 1151', 'B0BQ6CSY9C', 'intel-core-i7-13700f-desktop-processor-16-cores-8-pcores-8-ecores-30mb-cacheup-to-52-ghzlga-1151', '16 cores (8 P-cores + 8 E-cores) and 24 threads\r\nPerformance hybrid architecture integrates two core microarchitectures, prioritizing and distributing workloads to optimize performance\r\nUp to 5.2 GHz. 30M Cache\r\nCompatible with Intel 600 series and 700 series chipset-based motherboards\r\nTurbo Boost Max Technology 3.0, and PCIe 5.0 & 4.0 support. Intel Optane Memory support. Intel Laminar RH1 Cooler included. Discrete graphics required', 29000.00, 43.00, 16, 0.00, 0, 1, 1, '1759555129_image1.jpg', '1759555129_image2.jpg', '1759555129_image3.jpg', '1759555129_image4.jpg', 'none', 0, 1, '2025-10-03 23:32:31', '2025-10-04 05:18:49'),
(13, 'Intel Core i7-11700K LGA1200 Desktop Processor 8 Cores up to 5GHz 16MB Cache with Integrated Intel UHD 750 Graphics', 'B08X6ND3WP', 'intel-core-i7-11700k-lga1200-desktop-processor-8-cores-up-to-5ghz-16mb-cache-with-integrated-intel-uhd-750-graphics', 'Introducing the newest and fastest 11th Gen Intel Core i7 desktop processor, built based on 14 nm lithography supporting Socket type LGA 1200. The Processors features 8 Core which allow the processor to run multiple programs simultaneously without slowing down the system, while the 16 threads allow instructions to be handled by a single CPU core along with Hyper Threading Technology.\r\nWith 3.60 GHz Base frequency, the Intel Turbo Boost 3.0 technology cranks maximum turbo frequency up to blazing 5.00 GHz. The processor is desirable for a gamer looking for a fantastic in-game experience and a creator that is ready to do more creating and sharing alike.\r\nAll this paired with 16MB of Intel Smart Cache. It has a TDP rating of 125W with max memory size of 128GB dual-channel DDR4 support for up-to 3200Mhz with Intel top notch security features.\r\nThis processor is designed for users who value fast responsiveness and comes with built-in Intel UHD Graphics 750 and 4K support at 60Hz, with the cutting-edge processor architecture. The graphics processor is bundled with DirectX support, OpenGL support and supports up to 3 displays offering you a never like gaming experience.\r\nPlay, record and stream simultaneously with high FPS and effortlessly switch to heavy multitasking workloads.', 29000.00, 39.00, 12, 0.00, 0, 1, 1, '1759555450_image1.jpg', '1759555450_image2.jpg', '1759555450_image3.jpg', '1759555450_image4.jpg', 'none', 0, 1, '2025-10-03 23:32:31', '2025-10-04 05:24:10');

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
) ENGINE=MyISAM AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_specs`
--

INSERT INTO `product_specs` (`product_spec_id`, `product_id`, `spec_name`, `spec_value`, `spec_group`, `display_order`) VALUES
(185, 13, 'CPU Speed', '5 GHz', 'Key Specs', 4),
(184, 13, 'CPU Model', 'Core i7-10700K', 'Key Specs', 3),
(183, 13, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(182, 12, 'Wattage', '65 Watts', 'Detailed Specs', 9),
(181, 12, 'model number', 'BX8071513700F', 'Detailed Specs', 8),
(180, 12, 'Product Dimensions', '12.7 x 10.16 x 0.25 cm; 453.59 g', 'Detailed Specs', 7),
(179, 12, 'Manufacturer', 'Intel', 'Detailed Specs', 6),
(178, 12, 'CPU Socket', 'LGA 1151', 'Key Specs', 5),
(177, 12, 'CPU Speed', '13700 GHz', 'Key Specs', 4),
(176, 12, 'CPU Model', 'Core i7', 'Key Specs', 3),
(175, 12, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(174, 12, 'Brand', 'Intel', 'Key Specs', 1),
(173, 11, 'CPU Socket', 'LGA 1700', 'Key Specs', 5),
(172, 11, 'CPU Speed', '4.9 GHz', 'Key Specs', 4),
(171, 11, 'CPU Model', 'Core i7-10700', 'Key Specs', 3),
(170, 11, 'CPU Manufacturer', 'Intel', 'Key Specs', 2),
(168, 11, 'Wattage', '65 Watts', 'Detailed Specs', 12),
(169, 11, 'Brand', 'Intel', 'Key Specs', 1),
(167, 11, 'Compatible Devices', 'Personal Computer', 'Detailed Specs', 11),
(166, 11, 'model number', 'BX8070811700', 'Detailed Specs', 10),
(165, 11, 'Product Dimensions', '‎3.81 x 3.81 x 0.51 cm; 200 g', 'Detailed Specs', 9),
(164, 11, 'Model Name', 'i7-11700', 'Detailed Specs', 8),
(163, 11, 'Manufacturer', '‎INTEL PRODUCTS VIETNAM CO. LTD., NO. 8-1, KEXIN ROAD CHENGDU HIGH-TECH ZONE (WEST) 611731 CHENGDU CHINA, Intel', 'Detailed Specs', 6),
(140, NULL, 'CPU Manufacturer', '‎INTEL, INTEL PRODUCTS VIETNAM CO. LTD. LOT I2 D1 RD SAIGON HIGH TECH PARK TAN PHU WARD, THU DUC CITY 70000 HO CHI MINH CITY VIETNAM', 'Detailed Specs', 10),
(139, NULL, 'Processor Count', '4', 'Detailed Specs', 10),
(138, NULL, 'Brand', 'LGA 1700', 'Key Specs', 10),
(137, NULL, 'Brand', '32 (8P+8E)', 'Key Specs', 10),
(136, NULL, 'asdfa', 'sdfa', 'General', 10),
(135, NULL, 'sdfasdfa', 'sdfasdfa', 'General', 10),
(134, NULL, '12122', '2121212121', 'dsfasdasdfafafdfa', 12),
(133, NULL, 'asdf', 'asdfa', 'adfasdf', 12),
(132, NULL, 'adsfasdf', 'asdfas', 'adfasdf', 10),
(131, NULL, 'asdfasdf', 'awfsdf', 'adfasdf', 10),
(130, 5, 'CPU Socket', 'LGA 1700', 'Key Specs', 4),
(129, 5, 'CPU Speed', '4.3 GHz', 'Key Specs', 3),
(128, 5, 'CPU Model', 'Core i3', 'Key Specs', 2),
(127, 5, 'CPU Manufacturer', 'Intel', 'Key Specs', 1),
(126, 5, 'Manufacturer', 'CNA2 Intel Products Chengdu Ltd NO.8-1 Kexin Road Chengdu High-tech Zone Chengdu Sichuang 611731 China, Intel', 'Detailed Specs', 5),
(125, 5, 'Compatible Devices', 'Laptops, Monitors, PC', 'Detailed Specs', 7),
(124, 5, 'Processor Count', '4', 'Detailed Specs', 6),
(93, NULL, 'Integrated Graphics', 'Intel UHD 770', 'Detailed Specs', 6),
(92, NULL, 'PCIe Version', 'PCIe 5.0 / 4.0', 'Detailed Specs', 5),
(91, NULL, 'Memory Support', 'DDR4-3200 / DDR5-5600', 'Detailed Specs', 4),
(90, NULL, 'Cache', '30MB Intel Smart Cache', 'Detailed Specs', 3),
(89, NULL, 'Socket Type', 'LGA1700', 'Detailed Specs', 2),
(88, NULL, 'Lithography', 'Intel 7', 'Detailed Specs', 1),
(87, NULL, 'TDP', '125W', 'Key Specs', 5),
(86, NULL, 'Boost Clock', '5.4 GHz', 'Key Specs', 4),
(85, NULL, 'Base Clock', '3.4 GHz', 'Key Specs', 3),
(84, NULL, 'Threads', '24', 'Key Specs', 2),
(83, NULL, 'Cores', '16 (8P+8E)', 'Key Specs', 1),
(186, 13, 'CPU Socket', 'LGA 1200', 'Key Specs', 5),
(187, 13, 'CPU Manufacturer', 'Intel', 'Detailed Specs', 6),
(188, 13, 'Manufacturer', '‎INTEL PRODUCTS VIETNAM CO. LTD., NO. 8-1, KEXIN ROAD CHENGDU HIGH-TECH ZONE (WEST) 611731 CHENGDU CHINA, Intel', 'Detailed Specs', 7),
(189, 13, 'Model', 'BX8070811700K', 'Detailed Specs', 8),
(190, 13, 'Model Name', 'i7-11700K', 'Detailed Specs', 9),
(191, 13, 'Product Dimensions', '‎11.6 x 4.4 x 10.1 cm; 87 g', 'Detailed Specs', 10),
(192, 13, 'model number', 'BX8070811700K', 'Detailed Specs', 11),
(193, 13, 'Ram Memory Technology', 'DDR4', 'Detailed Specs', 12),
(194, 13, 'Wattage', '125 Watts', 'Detailed Specs', 13);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
CREATE TABLE IF NOT EXISTS `shipments` (
  `shipment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `shipping_method` varchar(100) NOT NULL,
  `shipped_date` datetime DEFAULT NULL,
  `delivered_date` datetime DEFAULT NULL,
  `status` enum('Pending','Shipped','Delivered','Returned') DEFAULT 'Pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shipment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `date_of_birth`, `gender`, `phone`, `email_verified`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$O3ypmFHHIA50WUEFe6BWD.bH4jRaXvxh2j0ZQHOfyYY3k/ckWhYOu', '1990-01-01', 'Male', '1234567890', 1, 'admin', 'active', NULL, '2025-10-02 12:24:51', '2025-10-02 12:24:51'),
(5, 'john doe', 'johndoe@gmail.com', '$2y$10$CYCeAyUMyILYj9Fx3tq6G.fh8Pgfw3f3wReLg.NXcAY9XOqaw/Poy', '2001-06-13', 'Male', '1234567891', 0, 'user', 'active', NULL, '2025-10-02 12:32:00', '2025-10-02 12:32:55');

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `full_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `zip`, `country`, `is_default`) VALUES
(5, 5, 'John Doe', '1234567891', '456 Oak St', '', 'Los Angeles', 'California', '70808', 'USA', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
