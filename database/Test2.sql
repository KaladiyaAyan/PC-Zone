-- 1. CREATE DATABASE & USE
CREATE DATABASE IF NOT EXISTS pczone;
USE pczone;

-- 2. USERS TABLE (admin + normal users)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('Male','Female','Other') DEFAULT NULL,
    phone VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    status ENUM('active','inactive') DEFAULT 'active',
    profile_image varchar(255) default null,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Default admin (password = 'admin123', hashed)
INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `date_of_birth`, `gender`, `phone`, `email_verified`, `role`, `status`, `created_at`, `updated_at`) VALUES
(6, 'saitama', 'saitama@gmail.com', '$2y$10$e67jeVQnOImYMIxwqTyxpuJZFMW1zM4qswf1bq/L25tcy2QlYxkyy', '2025-01-28', 'Male', '1234567891', 0, 'user', 'active', '2025-10-04 05:06:05', '2025-10-04 13:56:46'),
(5, 'john doe', 'johndoe@gmail.com', '$2y$10$CYCeAyUMyILYj9Fx3tq6G.fh8Pgfw3f3wReLg.NXcAY9XOqaw/Poy', '2001-06-13', 'Male', '1234567891', 0, 'user', 'active', '2025-10-02 07:02:00', '2025-10-02 07:02:55'),
(1, 'admin', 'admin@gmail.com', '$2y$10$O3ypmFHHIA50WUEFe6BWD.bH4jRaXvxh2j0ZQHOfyYY3k/ckWhYOu', '1990-01-01', 'Male', '1234567890', 1, 'admin', 'active', '2025-10-02 06:54:51', '2025-10-02 06:54:51');

-- 3. CATEGORIES (top-level + subcategories via parent_id)
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT DEFAULT NULL,
    icon_image VARCHAR(255) DEFAULT NULL,   -- filename or relative path, e.g. "cpu.png"
    level INT DEFAULT 0,
    slug VARCHAR(250) UNIQUE,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    sort_order INT DEFAULT 9999,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

INSERT INTO categories (category_name, parent_id, icon_image, level, slug) VALUES
  ('Processor',      NULL,'Processor-Icon.webp', 0,  'processor'),        
  ('Graphics Card',  NULL, 'graphics-card-icon.webp', 0,  'graphics-card'),    
  ('Motherboard',    NULL, 'motherboard-icon.webp', 0, 'motherboard'),      
  ('RAM',            NULL, 'RAM-icon.webp', 0, 'ram'),              
  ('Storage',        NULL, 'ssd-icon.webp', 0, 'storage'),          
  ('Power Supply',   NULL, 'psu-icon.webp', 0, 'power-supply'),     
  ('Cabinet',        NULL, 'cabinet-icon.webp', 0, 'cabinet'),          
  ('Cooling System', NULL, 'liquid-cooler-icon.webp', 0, 'cooling-system'),        
  ('Keyboard',       NULL, 'keyboard-icon.webp', 0, 'keyboard'),         
  ('Mouse',          NULL, 'mouse-icon.webp', 0, 'mouse'),            
  ('Mousepad',       NULL, 'mousepad-icon.webp', 0, 'mousepad');

SET @proc_id      = (SELECT category_id FROM categories WHERE category_name = 'Processor');
SET @storage_id   = (SELECT category_id FROM categories WHERE category_name = 'Storage');
SET @cooling_id   = (SELECT category_id FROM categories WHERE category_name = 'Cooling System');

-- 3. Insert subcategories using those variables
INSERT INTO categories (category_name, parent_id, level, slug) VALUES
  ('Intel',          @proc_id,     1, 'intel'),
  ('AMD',            @proc_id,     1, 'amd'),
  ('SSD',            @storage_id,  1, 'ssd'),
  ('HDD',            @storage_id,  1, 'hdd'),
  ('NVMe',           @storage_id,  1, 'nvme'),
  ('Air Cooler',     @cooling_id,  1, 'air-cooler'),
  ('Liquid Cooler',  @cooling_id,  1, 'liquid-cooler');

-- 4. BRANDS (linked to PC-part categories)
CREATE TABLE IF NOT EXISTS brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL,
    category_id INT,
    slug VARCHAR(250) UNIQUE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Insert brands by category
INSERT INTO brands (brand_name, category_id, slug) VALUES
-- Processor
('Intel',         (SELECT category_id FROM categories WHERE category_name='Processor'),    'intel'),
('AMD',           (SELECT category_id FROM categories WHERE category_name='Processor'),    'amd'),

-- Graphics Card
('NVIDIA',        (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'nvidia'),
('ASUS',          (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'asus-gpu'),
('MSI',           (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'msi-gpu'),
('Gigabyte',      (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'gigabyte-gpu'),
('ZOTAC',         (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'zotac'),
('Inno3D',        (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'inno3d'),
('EVGA',          (SELECT category_id FROM categories WHERE category_name='Graphics Card'),'evga'),

-- Motherboard
('ASUS',          (SELECT category_id FROM categories WHERE category_name='Motherboard'),   'asus-mb'),
('ASRock',        (SELECT category_id FROM categories WHERE category_name='Motherboard'),   'asrock'),
('MSI',           (SELECT category_id FROM categories WHERE category_name='Motherboard'),   'msi-mb'),
('Gigabyte',      (SELECT category_id FROM categories WHERE category_name='Motherboard'),   'gigabyte-mb'),
('EVGA',          (SELECT category_id FROM categories WHERE category_name='Motherboard'),   'evga-mb'),

-- RAM
('Corsair',       (SELECT category_id FROM categories WHERE category_name='RAM'),           'corsair-ram'),
('G.Skill',       (SELECT category_id FROM categories WHERE category_name='RAM'),           'gskill'),
('Kingston',      (SELECT category_id FROM categories WHERE category_name='RAM'),           'kingston'),
('Crucial',       (SELECT category_id FROM categories WHERE category_name='RAM'),           'crucial-ram'),
('ADATA',         (SELECT category_id FROM categories WHERE category_name='RAM'),           'adata'),

-- Storage
('Samsung',       (SELECT category_id FROM categories WHERE category_name='Storage'),       'samsung-storage'),
('WD',            (SELECT category_id FROM categories WHERE category_name='Storage'),       'wd'),
('Seagate',       (SELECT category_id FROM categories WHERE category_name='Storage'),       'seagate'),
('Crucial',       (SELECT category_id FROM categories WHERE category_name='Storage'),       'crucial-storage'),
('ADATA',         (SELECT category_id FROM categories WHERE category_name='Storage'),       'adata-storage'),
('Kingston',      (SELECT category_id FROM categories WHERE category_name='Storage'),       'kingston-storage'),

-- Power Supply
('Corsair',       (SELECT category_id FROM categories WHERE category_name='Power Supply'),  'corsair-psu'),
('Antec',         (SELECT category_id FROM categories WHERE category_name='Power Supply'),  'antec'),
('Cooler Master', (SELECT category_id FROM categories WHERE category_name='Power Supply'),  'cooler-master-psu'),
('EVGA',          (SELECT category_id FROM categories WHERE category_name='Power Supply'),  'evga-psu'),
('Thermaltake',   (SELECT category_id FROM categories WHERE category_name='Power Supply'),  'thermaltake-psu'),
('NZXT',          (SELECT category_id FROM categories WHERE category_name='Power Supply'),  'nzxt-psu'),
('Ant Esports',   (SELECT category_id FROM categories WHERE category_name='Power Supply'),        'ant-esports-psu'),

-- Cabinet
('NZXT',          (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'nzxt-case'),
('Lian Li',       (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'lian-li-case'),
('Cooler Master', (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'cooler-master-case'),
('Thermaltake',   (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'thermaltake-case'),
('Antec',         (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'antec-case'),
('DeepCool',      (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'deepcool-case'),
('Ant Esports',   (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'ant-esports-case'),

-- Cooling System
('Cooler Master', (SELECT category_id FROM categories WHERE category_name='Cooling System'),'cooler-master-cooler'),
('DeepCool',      (SELECT category_id FROM categories WHERE category_name='Cooling System'),'deepcool-cooler'),
('NZXT',          (SELECT category_id FROM categories WHERE category_name='Cooling System'),'nzxt-cooler'),
('be quiet!',     (SELECT category_id FROM categories WHERE category_name='Cooling System'),'be-quiet'),
('Arctic',        (SELECT category_id FROM categories WHERE category_name='Cooling System'),'arctic'),
('Thermaltake',   (SELECT category_id FROM categories WHERE category_name='Cooling System'),'thermaltake-cooler'),
('Corsair',       (SELECT category_id FROM categories WHERE category_name='Cooling System'),'corsair-cooler'),
('Lian Li',       (SELECT category_id FROM categories WHERE category_name='Cooling System'),'lian-li-cooler'),

-- Keyboard
('Ant Esports',   (SELECT category_id FROM categories WHERE category_name='Keyboard'),        'ant-esports-keyboard'),
('Logitech',      (SELECT category_id FROM categories WHERE category_name='Keyboard'),       'logitech-keyboard'),
('Razer',         (SELECT category_id FROM categories WHERE category_name='Keyboard'),       'razer-keyboard'),
('Corsair',       (SELECT category_id FROM categories WHERE category_name='Keyboard'),       'corsair-keyboard'),
('HyperX',        (SELECT category_id FROM categories WHERE category_name='Keyboard'),       'hyperx'),
('Zebronics',     (SELECT category_id FROM categories WHERE category_name='Keyboard'),       'zebronics'),

-- Mouse
('Logitech',      (SELECT category_id FROM categories WHERE category_name='Mouse'),          'logitech-mouse'),
('Razer',         (SELECT category_id FROM categories WHERE category_name='Mouse'),          'razer-mouse'),
('Redragon',      (SELECT category_id FROM categories WHERE category_name='Mouse'),          'redragon-mouse'),
('Corsair',       (SELECT category_id FROM categories WHERE category_name='Mouse'),          'corsair-mouse'),
('Zebronics',     (SELECT category_id FROM categories WHERE category_name='Mouse'),          'zebronics-mouse'),

-- Mousepad & Gamepad (optional extras)
('SteelSeries',   (SELECT category_id FROM categories WHERE category_name='Mousepad'),       'steelseries-mousepad'),
('Corsair',       (SELECT category_id FROM categories WHERE category_name='Mousepad'),       'corsair-mousepad'),
('Razer',         (SELECT category_id FROM categories WHERE category_name='Mousepad'),       'razer-mousepad');

-- PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS products (
   product_id  INT AUTO_INCREMENT PRIMARY KEY,
   product_name VARCHAR(250) NOT NULL,
   sku VARCHAR(50) UNIQUE,
   slug VARCHAR(250) UNIQUE,
   description TEXT,
   price DECIMAL(10, 2) NOT NULL,
   discount DECIMAL(6,2) DEFAULT 0,
   stock INT DEFAULT 0,
   weight DECIMAL(6,2),
   rating FLOAT DEFAULT 0,
   brand_id INT,
   category_id INT,
   main_image varchar(255) NOT NULL,
   image_1 varchar(255),
   image_2 varchar(255),
   image_3 varchar(255),
   platform ENUM('intel','amd','both','none') NOT NULL DEFAULT 'none',
   is_featured BOOLEAN DEFAULT 0,
   is_active BOOLEAN DEFAULT 1,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
   FOREIGN KEY (brand_id)     REFERENCES brands(brand_id)     ON DELETE CASCADE
);

INSERT INTO `products` (`product_id`, `product_name`, `sku`, `slug`, `description`, `price`, `discount`, `stock`, `weight`, `rating`, `brand_id`, `category_id`, `main_image`, `image_1`, `image_2`, `image_3`, `platform`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Lian Li O11 Dynamic EVO XL Full-Tower Compter Case/Gaming Cabinet - White | Support EATX/ATX/Micro-ATX/MINI-ITX - G99.O11DEXL-W.in', 'B0CGM6RKV8', 'lian-li-o11-dynamic-evo-xl-full-tower-computer-case-gaming-cabinet-white', 'White, Full-Tower, 522 x 304 x 531.9 mm , 4.0mm Tempered Glass Aluminum 8 Expansion Slots, Storage : Behind MB Tray: 3 X 2.5ʹʹ SSD Hard Drive Cage: 4 X 3.5ʹʹ HDD or 2.5ʹʹ SSD I/O Panel : Power Button , Reset Button , USB 3.0 x 4 , Audio x 1 , USB Type C , Color Button , Mode Button Fan Support : Top - 120mm x3 / 140mm x3, Side- 120mm x3 / 140mm x3, Bottom- 120mm x3/ 140mm x3, Rear- 120 mm x1 or 2 GPU Length Clearance : 460mm(Max) ; CPU Cooler Height Clearance : 167mm(Max)', 30999.00, 24.00, 9, 0.65, 4.9, 33, 7, '610tNgEZ6LL._SX679_.jpg', '61zXV1X5zTL._SX679_.jpg', '712etNmCVRL._SX679_.jpg', '71O8DnFAk5L._SX679_.jpg', 'both', 1, 1, '2025-10-02 01:24:52', '2025-10-02 01:24:52'),
(1, 'NZXT H6 Flow | CC-H61FB-01 | Compact Dual-Chamber Mid-Tower Airflow Case | Panoramic Glass Panels | High-Performance Airflow Panels | Includes 3 x 120mm Fans | Cable Management | Black', 'B0C89FCDFP', 'nzxt-h6-flow', 'Wraparound glass panels with a seamless edge provides an unobstructed view of the inside to highlight key components. Compact dual-chamber design improves overall thermal performance and creates a clean, uncrowded aesthetic. Includes three pre-installed 120mm fans positioned at an ideal angle for superb out-of-the-box cooling. The top and side panels feature an airflow-optimized perforation pattern to enhance overall performance and filter dust. An intuitive cable management system simplifies the build process by using wide channels and straps.', 13999.00, 23.00, 10, 0.65, 4.7, 32, 7, '71x+i8yRgrL._SY450_.jpg', '71YDILR+QnL._SY450_.jpg', '71vtU8bv48L._SY450_.jpg', '71u5IWhR-aL._SY450_.jpg', 'both', 1, 1, '2025-10-02 01:24:52', '2025-10-02 01:24:52'),
(12, 'Intel Core I7-13700F Desktop Processor 16 Cores (8 P-Cores + 8 E-Cores) 30Mb Cache,Up to 5.2 Ghz,LGA 1151', 'B0BQ6CSY9C', 'intel-core-i7-13700f-desktop-processor-16-cores-8-pcores-8-ecores-30mb-cacheup-to-52-ghzlga-1151', '16 cores (8 P-cores + 8 E-cores) and 24 threads\r\nPerformance hybrid architecture integrates two core microarchitectures, prioritizing and distributing workloads to optimize performance\r\nUp to 5.2 GHz. 30M Cache\r\nCompatible with Intel 600 series and 700 series chipset-based motherboards\r\nTurbo Boost Max Technology 3.0, and PCIe 5.0 & 4.0 support. Intel Optane Memory support. Intel Laminar RH1 Cooler included. Discrete graphics required', 51000.00, 43.00, 16, 0.00, 0, 1, 1, '1759555129_image1.jpg', '1759555129_image2.jpg', '1759555129_image3.jpg', '1759555129_image4.jpg', 'intel', 0, 1, '2025-10-03 18:02:31', '2025-10-05 06:14:25'),
(13, 'Intel Core i7-11700K LGA1200 Desktop Processor 8 Cores up to 5GHz 16MB Cache with Integrated Intel UHD 750 Graphics', 'B08X6ND3WP', 'intel-core-i7-11700k-lga1200-desktop-processor-8-cores-up-to-5ghz-16mb-cache-with-integrated-intel-uhd-750-graphics', 'Introducing the newest and fastest 11th Gen Intel Core i7 desktop processor, built based on 14 nm lithography supporting Socket type LGA 1200. The Processors features 8 Core which allow the processor to run multiple programs simultaneously without slowing down the system, while the 16 threads allow instructions to be handled by a single CPU core along with Hyper Threading Technology.\r\nWith 3.60 GHz Base frequency, the Intel Turbo Boost 3.0 technology cranks maximum turbo frequency up to blazing 5.00 GHz. The processor is desirable for a gamer looking for a fantastic in-game experience and a creator that is ready to do more creating and sharing alike.\r\nAll this paired with 16MB of Intel Smart Cache. It has a TDP rating of 125W with max memory size of 128GB dual-channel DDR4 support for up-to 3200Mhz with Intel top notch security features.\r\nThis processor is designed for users who value fast responsiveness and comes with built-in Intel UHD Graphics 750 and 4K support at 60Hz, with the cutting-edge processor architecture. The graphics processor is bundled with DirectX support, OpenGL support and supports up to 3 displays offering you a never like gaming experience.\r\nPlay, record and stream simultaneously with high FPS and effortlessly switch to heavy multitasking workloads.', 58500.00, 39.00, 12, 0.00, 0, 1, 1, '1759555450_image1.jpg', '1759555450_image2.jpg', '1759555450_image3.jpg', '1759555450_image4.jpg', 'intel', 0, 1, '2025-10-03 18:02:31', '2025-10-05 06:14:42'),
(4, 'Ant Esports ICE-C612 V2 ARGB CPU Cooler| Support Intel LGA1200, LGA115X, LGA20XX, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 'B084G3MJPZ', 'ant-esports-ice-c612-v2-argb-cpu-cooler', 'Efficient Heat Dissipation: The Ant Esports ICE-C612 V2 CPU air cooler is designed for optimal heat dissipation, featuring a 153mm tall aluminum heatsink and six 6mm thick copper heatpipes. This advanced cooling solution ensures efficient heat transfer from the CPU to the heatsink, effectively reducing temperatures and maintaining peak performance even during demanding tasks.\r\n   Enhanced Cooling Performance: Equipped with a high-performance PWM 120mm ARGB fan, the ICE-C612 V2 cooler offers not only excellent cooling efficiency but also adds a vibrant visual flair to your system. The fans adjustable speed through pulse-width modulation (PWM) ensures a fine balance between cooling power and noise levels, keeping your CPU operating at an ideal temperature while maintaining a quiet environment\r\n   Optimized Surface Area: The interlocked aluminum heatsink design of the ICE-C612 V2 is engineered to provide a larger surface area for heat dissipation. This design maximizes the contact area between the heatsink and the surrounding air, allowing for quicker and more effective heat dispersion. Whether you are running intensive applications or engaging in heavy gaming sessions, this cooler helps maintain stable and consistent performance.\r\n   Wide Compatibility: The Ant Esports ICE-C612 V2 CPU air cooler offers broad compatibility with major Intel and AMD platforms, including the latest LGA 1700 and AM5 sockets. This versatility makes it an ideal choice for both current and future system builds, allowing you to upgrade your CPU without worrying about changing cooling solutions.\r\n   Easy Installation: Installing the ICE-C612 V2 cooler is a hassle-free process thanks to its user-friendly design. The included mounting hardware and easy-to-follow instructions ensure a smooth installation experience, even for users with minimal technical expertise. With its secure mounting mechanism, you can trust that your cooler will be properly seated for optimal thermal performance.\r\n   Support Intel LGA1200, LGA1150, LGA1151, LGA1155, LGA1156, LGA2066, LGA2011-v3, LGA2011, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 3499.00, 64.00, 6, 0.65, 4.1, 38, 18, '51pCa994ysL._SY450_.jpg', '61PedtDNzIL._SY450_.jpg', '71kG6EFIMwL._SY450_.jpg', '61Yb+64vAkL._SY450_.jpg', 'both', 1, 1, '2025-10-02 01:24:52', '2025-10-02 01:24:52'),
(11, 'Intel Core i7-11700 Desktop Processor 8 Cores up to 4.9 GHz LGA1200 (Intel 500 Series & Select 400 Series Chipset) 65W', 'B08X6QHYDL', 'intel-core-i7-11700-desktop-processor-8-cores-up-to-49-ghz-lga1200-intel-500-series-select-400-series-chipset-65w', 'Compatible with Intel 500 series & select Intel 400 series chipset based motherboards\r\nIntel Turbo Boost Max Technology 3.0 Support\r\nIntel Optane Memory Support\r\nPCIe Gen 4.0 Support\r\nThermal solution included', 45000.00, 44.00, 32, 0.00, 0, 1, 1, '1759554881_image1.jpg', '1759554881_image2.jpg', '1759554881_image3.jpg', '1759554881_image4.jpg', 'intel', 0, 1, '2025-10-03 18:02:31', '2025-10-05 06:14:05'),
(5, 'Intel® Core™ i3-12100 Processor 12M Cache, up to 4.30 GHz', 'B09NPHJLPT', 'intel-core-i3-12100-processor-12m-cache-up-to-430-ghz', 'Intel Core i3-12100 Processor 12M Cache, up to 4.30 GHz\r\nIt ensures a hassle-free usage\r\nIt is durable and long lasting.', 18600.00, 30.00, 20, 0.00, 0, 1, 1, '1759496274_image1.jpg', '17594961701.jpg', '17594961702.jpg', '17594961703.jpg', 'intel', 1, 1, '2025-10-03 01:56:10', '2025-10-05 06:13:26'),
(6, 'Intel® Core™ i5-13600KF Processor 24M Cache, up to 5.10 GHz', 'B0BG64N549', 'intel-core-i5-13600kf-processor-24m-cache-up-to-510-ghz', '24M Cache, up to 5.10 GHz', 36000.00, 20.00, 15, 0.00, 0, 1, 1, '1759497517.jpg', '17594975171.jpg', '17594975172.jpg', '17594975173.jpg', 'intel', 0, 1, '2025-10-03 02:18:37', '2025-10-05 06:13:45'),
(3, 'Cooler Master RR-212S-20PC-R1 Hyper 212 RGB Black Edition CPU Air Cooler 4 Direct Contact Heat Pipes 120mm RGB Fan', 'B07H22TC1N', 'cooler-master-rr-212s-20pc-r1-hyper-212-rgb-black-edition-cpu-air-cooler-4-direct-contact-heat-pipes-120mm-rgb-fan', 'Cooler Master Hyper 212 RGB Black Edition Cooling Fan Heatsink - 57.3 CFM - 30 dB(A) Noise - 4-pin PWM Fan - Socket R4 LGA-2066, Socket LGA 2011-v3, Socket R LGA-2011, Socket H4 LGA-1151, Socket H3 LGA-1150, Socket H2 LGA-1155, Socket H LGA-1156, Socket B LGA-1366, Socket AM4, Socket AM3+, Socket AM3 PGA-941, ... Compatible Processor Socket - RGB LED - Aluminum - 18.3 Year Life', 12000.00, 16.00, 4, 0.65, 4, 34, 18, '81B-HuW8ydL._SY450_.jpg', '81jR4Io8OwL._SY450_.jpg', '71Q3El-2flL._SY450_.jpg', '71+9-o7dIwL._SY450_.jpg', 'both', 1, 1, '2025-10-02 01:24:52', '2025-10-02 01:24:52'),
(14, 'Intel Core i9-11900 LGA1200 Desktop Processor 8 Cores up to 5.1GHz 16MB Cache with Integrated Intel UHD 750 Graphics', 'B08X5XVLL9', 'intel-core-i9-11900-lga1200-desktop-processor-8-cores-up-to-51ghz-16mb-cache-with-integrated-intel-uhd-750-graphics', 'Introducing the 11th Gen Intel Core i9 desktop processor, this processor is 14 nm processor which supports LGA 1200. The Unlocked processors features 8 Core which allow the processor to run multiple programs simultaneously without slowing down the system, while the 16 threads allow instructions to be handled by a single CPU core.\r\nWith 2.5GHz Base frequency, Intel\'s Turbo Boost 3.0 technology cranks maximum turbo frequency up to blazing 5.1 GHz. The processor is desirable for a gamer looking for a fantastic in-game experience and a creator that is ready to do more creating and sharing alike.\r\nAll this paired with 16MB of Intel Smart Cache. It has a TDP rating of 65W with max memory size of 128GB dual-channel DDR4 support for up to 3200Mhz with Intel top notch security features.\r\nThis processor is designed for users who value fast responsiveness, comes with The i9-11900 features integrated Intel UHD 750 Graphics driven by the powerful Xe architecture.\r\nA thermal solution is included to help maintain optimal temperatures and the processor is backed by a 3-year warranty.', 63000.00, 39.00, 10, 0.00, 0, 1, 1, '1759558551.jpg', '17595585511.jpg', '17595585512.jpg', '17595585513.jpg', 'intel', 0, 1, '2025-10-04 00:45:51', '2025-10-05 06:15:06'),
(15, 'Intel® Core™ i9-13900K Processor 36M Cache, up to 5.80 GHz', 'B0BG67ZG5R', 'intel-core-i9-13900k-processor-36m-cache-up-to-580-ghz', '36M Cache, up to 5.80 GHz', 94000.00, 16.00, 8, 0.00, 0, 1, 1, '1759558853.jpg', '17595588531.jpg', '17595588532.jpg', '17595588533.jpg', 'intel', 0, 1, '2025-10-04 00:50:53', '2025-10-05 06:16:03'),
(16, 'Intel® Core™ i9-14900K New Gaming Desktop Processor 24 cores (8 P-cores + 16 E-cores) with Integrated Graphics - Unlocked', 'B0CGJDKLB8', 'intel-core-i9-14900k-new-gaming-desktop-processor-24-cores-8-p-cores-16-e-cores-with-integrated-graphics---unlocked', 'Game without compromise. Play harder and work smarter with Intel Core 14th Gen processors\r\n24 cores (8 P-cores + 16 E-cores) and 32 threads. Integrated Intel UHD Graphics 770 included\r\nLeading max clock speed of up to 6.0 GHz gives you smoother game play, higher frame rates, and rapid responsiveness\r\nCompatible with Intel 600-series (with potential BIOS update) or 700-series chipset-based motherboards\r\nDDR4 and DDR5 platform support cuts your load times and gives you the space to run the most demanding games', 86000.00, 35.00, 6, 0.00, 0, 1, 1, '1759559486.jpg', '17595594861.jpg', '17595594862.jpg', '17595594863.jpg', 'intel', 1, 1, '2025-10-04 01:01:26', '2025-10-05 06:15:25'),
(17, 'AMD 7000 Series Ryzen 5 7600X Desktop Processor 6 cores 12 Threads 38 MB Cache 4.7 GHz Upto 5.3 GHz AM5 Socket (100-100000593WOF)', 'B0BBJDS62N', 'amd-7000-series-ryzen-5-7600x-desktop-processor-6-cores-12-threads-38-mb-cache-47-ghz-upto-53-ghz-am5-socket-100-100000593wof', '6 Cores & 12 Threads, 38 MB Cache\r\nBase Clock: 4.7 GHz, Max Boost Clock: up to 5.3 GHz\r\nMemory Support: DDR5 5200MHz, Memory Channels: 2, TDP: 65W, PCI Express Generation : PCIe Gen 5\r\nCompatible with Motherboards based on 600 Series Chipset, Socket AM5\r\nOn Chip Graphic Card , Included Heatsink Fan: No', 42150.00, 20.00, 11, 0.00, 0, 2, 1, '1759559853.jpg', '17595598531.jpg', '17595598532.jpg', '17595598533.jpg', 'amd', 0, 1, '2025-10-04 01:07:33', '2025-10-04 14:24:30'),
(18, 'AMD 7000 Series Ryzen 7 7800X 3D Desktop Processor 8 cores 16 Threads 104 MB Cache 4.2 GHz Upto 5.6 GHz AM5 Socket (100-100000910WOF)', 'B0BTZB7F88', 'amd-7000-series-ryzen-7-7800x-3d-desktop-processor-8-cores-16-threads-104-mb-cache-42-ghz-upto-56-ghz-am5-socket-100-100000910wof', '8 Cores & 16 Threads, 104 MB Cache\r\nBase Clock: 4.2 GHz, Max Boost Clock: up to 5.6 GHz\r\nMemory Support: DDR5 5200MHz, Memory Channels: 2, TDP: 120W, PCI Express Generation : PCIe Gen 5\r\nCompatible with Motherboards based on 600 Series Chipset, Socket AM5\r\nOn Chip Graphic Card', 70000.00, 45.00, 12, 0.00, 0, 2, 1, '1759560629.jpg', '17595606291.jpg', '17595606292.jpg', '17595606293.jpg', 'amd', 0, 1, '2025-10-04 01:20:29', '2025-10-04 14:24:36'),
(19, 'AMD Ryzen 9 9950X3D Desktop Processor with Integrated Radeon Graphics, 16 cores 32 Threads 128MB Cache Base Clock 4.3 GHz Up to 5.7GHz AM5 Socket System Memory DDR5 Up to 5600 MT/s - 100-100000719WOF', 'B0DVZSG8D5', 'amd-ryzen-9-9950x3d-desktop-processor-with-integrated-radeon-graphics-16-cores-32-threads-128mb-cache-base-clock-43-ghz-up-to-57ghz-am5-socket-system-memory-ddr5-up-to-5600-mts---100-100000719wof', 'Ultimate 16-Core Powerhouse: Featuring 16 cores and 32 threads, this CPU delivers unparalleled performance for the most demanding gaming and content creation task\r\n2nd Gen AMD 3D V-Cache for Extreme Performance: Leverages the advanced 2nd generation of 3D V-Cache technology, significantly boosting gaming frame rates and accelerating content creation workflows.\r\nZen 5 Architecture with Blazing Fast Boost Clocks: Built on the cutting-edge Zen 5 architecture, achieving boost clocks up to 5.7 GHz for exceptional responsiveness and speed.\r\nAdvanced DDR5 and PCIe 5.0 Support: Fully supports DDR5 memory with AMD EXPO technology and PCIe 5.0, enabling next-generation connectivity and memory performance.\r\nRobust Overclocking and Tuning Capabilities: Unlocked for overclocking, with Precision Boost Overdrive and Curve Optimizer Voltage Offsets, providing extensive customization options for enthusiasts.\r\nComprehensive Connectivity and Integrated Graphics: Offers a wide range of connectivity options, including USB 3.2 Gen 2, and features integrated AMD Radeon Graphics for basic display needs.\r\nSupporting Chipsets:A620 , X670E , X670 , B650E , B650 , X870E , X870 , B840 , B850', 119000.00, 29.00, 4, 0.00, 0, 2, 1, '1759560884.jpg', '17595608841.jpg', '17595608842.jpg', '17595608843.jpg', 'amd', 1, 1, '2025-10-04 01:24:44', '2025-10-04 14:24:48'),
(20, 'AMD Ryzen 9 9950X Desktop Processor Zen 5 Architecture with Integrated Radeon Graphics, 16 cores 32 Threads 64MB Cache, Base Clock 4.3GHz Upto 5.7GHz AM5 Socket, System Memory DDR5-100-100001277WOF', 'B0D6NNRBGP', 'amd-ryzen-9-9950x-desktop-processor-zen-5-architecture-with-integrated-radeon-graphics-16-cores-32-threads-64mb-cache-base-clock-43ghz-upto-57ghz-am5-socket-system-memory-ddr5-100-100001277wof', 'Core and Thread Count: 16 cores, 32 threads for exceptional multitasking and heavy workloads. Advanced Architecture: Built on Zen 5 architecture for optimized efficiency and power consumption.\r\nClock Speed/Cache : Up to 5.7 GHz boost clock for lightning-fast performance. Large 64MB L3 cache for optimized data access and reduced latency.\r\nMemory Support/PCIe : DDR5 memory support for high bandwidth and low latency. PCIe 5.0 for blazing-fast data transfer speeds to support the latest storage and graphics cards\r\nDDR5 Memory Support: Compatible with high-speed DDR5 memory for optimal system performance.\r\nSupporting Chipsets : A620 , X670E , X670 , B650E , B650 , X870E , X870', 94000.00, 16.00, 16, 0.00, 0, 2, 1, '1759561089.jpg', '17595610891.jpg', '17595610892.jpg', '17595610893.jpg', 'amd', 0, 1, '2025-10-04 01:28:09', '2025-10-04 14:24:53'),
(21, 'GIGABYTE nVidia GeForce RTX 2060 D6 6GB v2.0 Video Card, PCI-E 3.0, 1680 MHz Core Clock, 3x DIsplayPort 1.4, 1x HDMI 2.0', 'B095SWPGVR', 'gigabyte-nvidia-geforce-rtx-2060-d6-6gb-v20-video-card-pci-e-30-1680-mhz-core-clock-3x-displayport-14-1x-hdmi-20', 'RT Cores: Dedicated ray tracing hardware enables fast real-time ray tracing with physically accurate shadows, reflections, refractions, and global illumination.\r\nTensor Cores: Artificial intelligence is driving the greatest technology advancement in history, and Turing is bringing it to computer graphics. Experience AI-processing horsepower that accelerates gaming performance with NVIDIA DLSS 2.0.\r\nNext-Gen Shading: Variable Rate Shading focuses processing power on areas of rich detail, boosting overall performance without affecting perceived image quality. Mesh Shaders advanced geometry processing supports an order of magnitude more objects per-scene, allowing the creation of rich complex worlds.\r\nCore Clock: 1680MHz\r\nWINDFORCE 2X Cooler', 69999.00, 39.00, 13, 0.00, 0, 6, 2, '1759561566.jpg', '17595615661.jpg', '17595615662.jpg', '17595615663.jpg', 'none', 0, 1, '2025-10-04 01:36:06', '2025-10-04 01:36:06');



-- user_address
CREATE TABLE IF NOT EXISTS user_address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    full_name VARCHAR(150),
    phone VARCHAR(20),
    address_line1 TEXT,
    address_line2 TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip VARCHAR(20),
    country VARCHAR(100),
    is_default BOOLEAN DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 8. user_address
INSERT INTO `user_address` (`address_id`, `user_id`, `full_name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `zip`, `country`, `is_default`) VALUES
(10, 6, 'Saitama', '1234567891', 'ABCD', NULL, 'Z', 'CA', '363530', NULL, 1),
(11, 1, 'Admin', '1234567891', 'ABCD', NULL, 'Los Angeles', 'CA', '90001', NULL, 1),
(5, 5, 'John Doe', '1234567891', '456 Oak St', '', 'Los Angeles', 'California', '70808', 'USA', 1);

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(6,2) DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(order_id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

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
(12, 1, 1, 1, 10779.23, 23.00, 10779.23, '2025-10-05 11:40:16', '2025-10-05 11:40:16');

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('cash_on_delivery','credit_card','debit_card','upi') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    payment_status ENUM('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

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
(12, 12, 'upi', 10779.23, 'INR', 'Pending', '2025-10-05 11:40:16');


-- PRODUCT SPECS (key-value pairs)
CREATE TABLE IF NOT EXISTS product_specs (
    product_spec_id  INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    spec_name VARCHAR(100),
    spec_value VARCHAR(255),
    spec_group VARCHAR(80) DEFAULT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

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
(388, 15, 'CPU Socket', 'LGA 1700', 'Key Specs', 5);


-- PRODUCT REVIEWS
CREATE TABLE IF NOT EXISTS product_reviews (
    product_review_id  INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id)  REFERENCES products(product_id)   ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)  ON DELETE CASCADE
);
INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 1, 5, 'Great processor!'),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 2, 4, 'Good processor'),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 3, 3, 'Average processor'),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 4, 2, 'Bad processor'),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 5, 1, 'Terrible processor');

-- CART ITEMS
CREATE TABLE IF NOT EXISTS cart (
    cart_item_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    product_name varchar(255) NOT NULL,
    price int(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(product_id)  ON DELETE CASCADE
);

INSERT INTO `cart` (`cart_item_id`, `user_id`, `product_id`, `quantity`, `product_name`, `price`, `created_at`) VALUES
(11, 5, 16, 1, 'Intel® Core™ i9-14900K New Gaming Desktop Processor 24 cores (8 P-cores + 16 E-cores) with Integrated Graphics - Unlocked', 55999, '2025-10-04 13:00:14'),
(12, 5, 3, 1, 'Cooler Master RR-212S-20PC-R1 Hyper 212 RGB Black Edition CPU Air Cooler 4 Direct Contact Heat Pipes 120mm RGB Fan', 12000, '2025-10-04 13:00:32'),
(13, 5, 4, 1, 'Ant Esports ICE-C612 V2 ARGB CPU Cooler| Support Intel LGA1200, LGA115X, LGA20XX, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5', 3499, '2025-10-04 13:00:33'),
(14, 5, 19, 1, 'AMD Ryzen 9 9950X3D Desktop Processor with Integrated Radeon Graphics, 16 cores 32 Threads 128MB Cache Base Clock 4.3 GHz Up to 5.7GHz AM5 Socket System Memory DDR5 Up to 5600 MT/s - 100-100000719WOF', 119000, '2025-10-04 13:00:43');
