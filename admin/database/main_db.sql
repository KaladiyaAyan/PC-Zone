-- 1. CREATE DATABASE & USE
CREATE DATABASE IF NOT EXISTS pczone;
USE pczone;

-- 2. USERS TABLE (admin + normal users)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(150),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('Male','Female','Other') DEFAULT NULL,
    phone VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    status ENUM('active','inactive') DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Default admin (password = 'admin123', hashed)
INSERT INTO users (first_name, last_name, email, password, date_of_birth, gender, phone, email_verified, role) VALUES
  ('Admin', 'User', 'admin@pczone', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', '1990-01-01', 'Male', '1234567890', 1, 'admin'),
  ('John', 'Doe', 'jdoe@example.com', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', '1990-01-01', 'Male', '1234567890', 1, 'user'),
  ('Jane', 'Smith', 'jane@pczone', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', '1990-01-01', 'Female', '1234567890', 1, 'user'),
  ('Maya', 'Singh', 'maya@singh', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', '1990-01-01', 'Female', '1234567890', 1, 'user');

-- INSERT INTO users (first_name, last_name, email, password, role) VALUES
--   ('Admin', 'User', 'admin@pczone', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', 'admin'),
--   ('John', 'Doe', 'jdoe@example.com', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', 'user'),
--   ('Jane', 'Smith', 'jane@pczone', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', 'user');


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
  ('Monitor',        NULL, 'monitor-icon.webp', 0, 'monitor'),          
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

-- Cabinet
('NZXT',          (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'nzxt-case'),
('Lian Li',       (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'lian-li'),
('Cooler Master', (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'cooler-master-case'),
('Thermaltake',   (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'thermaltake-case'),
('Antec',         (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'antec-case'),
('DeepCool',      (SELECT category_id FROM categories WHERE category_name='Cabinet'),        'deepcool'),

-- Cooling System
('Cooler Master', (SELECT category_id FROM categories WHERE category_name='Cooling System'),'cooler-master-cooler'),
('DeepCool',      (SELECT category_id FROM categories WHERE category_name='Cooling System'),'deepcool-cooler'),
('NZXT',          (SELECT category_id FROM categories WHERE category_name='Cooling System'),'nzxt-cooler'),
('be quiet!',     (SELECT category_id FROM categories WHERE category_name='Cooling System'),'be-quiet'),
('Arctic',        (SELECT category_id FROM categories WHERE category_name='Cooling System'),'arctic'),
('Thermaltake',   (SELECT category_id FROM categories WHERE category_name='Cooling System'),'thermaltake-cooler'),

-- Monitor
('Dell',          (SELECT category_id FROM categories WHERE category_name='Monitor'),        'dell-monitor'),
('LG',            (SELECT category_id FROM categories WHERE category_name='Monitor'),        'lg-monitor'),
('ASUS',          (SELECT category_id FROM categories WHERE category_name='Monitor'),        'asus-monitor'),
('Acer',          (SELECT category_id FROM categories WHERE category_name='Monitor'),        'acer-monitor'),
('Samsung',       (SELECT category_id FROM categories WHERE category_name='Monitor'),        'samsung-monitor'),
('MSI',           (SELECT category_id FROM categories WHERE category_name='Monitor'),        'msi-monitor'),

-- Keyboard
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

-- 4. PRODUCTS
INSERT INTO products (product_name, sku, slug, description, price, discount, stock, weight, rating, brand_id, category_id, main_image, image_1, image_2, platform, is_featured, is_active) VALUES
  ('Intel Core i9-12900K', 'CPU-INT-12900K', 'intel-core-i9-12900k',
     '12th Gen desktop processor, hybrid architecture, high single-thread performance.',
     499.99, 0.00, 20, 0.65, 4.7,
     (SELECT brand_id FROM brands WHERE slug='intel'),
     (SELECT category_id FROM categories WHERE slug='processor'),
      '61UVtHz+IlL._SX522_.jpg',
      '51mhFDz4r3L._SL1080_.jpg',
      '61kZ8MUKn0L._SL1280_.jpg',
     'intel',TRUE, TRUE),

  ('Intel Core i7-12700K', 'CPU-INT-12700K', 'intel-core-i7-12700k',
     'High performance 12th Gen processor for gaming and content creation.',
     349.99, 0.00, 15, 0.65, 4.6,
     (SELECT brand_id FROM brands WHERE slug='intel'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     '51NlTpm+7KL._SX522_.jpg',
     '51+Ax9Jna4L._SX522_.jpg',
     '515aMcQXdiL._SX522_.jpg',
     'intel',TRUE, TRUE),
   ('Intel Core i7-13700K', 'CPU-INT-i7-13700K', 'intel-core-i7-13700k',
     'High performance 12th Gen processor for gaming and content creation.',
     349.99, 0.00, 15, 0.65, 4.6,
     (SELECT brand_id FROM brands WHERE slug='intel'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     '61m0zH-NiTL._SX679_.jpg',
     '612rDWKp8-L._SX679_.jpg',
     null,
     'intel',TRUE, TRUE),
   ('Intel Core i5-12400F', 'CPU-INT-i5-12400F', 'intel-core-i5-12400f',
     'High performance 12th Gen processor for gaming and content creation.',
     299.99, 0.00, 15, 0.65, 4.6,
     (SELECT brand_id FROM brands WHERE slug='intel'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     '51EwtPjHkIL._SX679_.jpg',
     '518PbvkOn4L._SX679_.jpg',
     '51A6E9HPocL._SX679_.jpg',
     'intel',TRUE, TRUE),

  ('AMD Ryzen 7 5800X', 'CPU-AMD-5800X', 'amd-ryzen-7-5800x',
     '8-core Zen3 CPU. Excellent single-thread and multi-thread performance.',
     299.99, 0.00, 30, 0.65, 4.6,
     (SELECT brand_id FROM brands WHERE slug='amd'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     '61IIbwz-+ML._SX679_.jpg',
     '41s2lwvRsrL.jpg',
     '51HqC0rU9HL._SX679_.jpg',
     'amd',TRUE, TRUE),
   ('AMD Ryzen 7 7800X3D', 'CPU-AMD-7800X3D', 'amd-ryzen-7-7800x3d',
     '8-core Zen4 CPU. Excellent single-thread and multi-thread performance.',
     299.99, 0.00, 30, 0.65, 4.6,
     (SELECT brand_id FROM brands WHERE slug='amd'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     '51dOdTAF2gL._SX679_.jpg',
     '51mUlXh-JWL._SX679_.jpg',
     null,
     'amd',TRUE, TRUE),

  ('Corsair Vengeance LPX 16GB', 'RAM-COR-16GB', 'corsair-vengeance-lpx-16gb',
     '2×8 GB DDR4-3200 MHz memory kit, low-profile heat spreader.',
     79.99, 5.00, 50, 0.12, 4.3,
     (SELECT brand_id FROM brands WHERE slug='corsair-ram'),
     (SELECT category_id FROM categories WHERE slug='ram'),
     '51Gs2sm696L._SX679_.jpg',
     '51-xIDDKW2L._SX679_.jpg',
     '512BfBMLaVL._SX679_.jpg',
     'both',FALSE, TRUE),

  ('Samsung 970 EVO Plus 1TB NVMe', 'SSD-SAM-970EVO-1TB', 'samsung-970-evo-plus-1tb',
     'High-performance NVMe SSD, up to 3500 MB/s read speeds.',
     129.99, 10.00, 40, 0.05, 4.8,
     (SELECT brand_id FROM brands WHERE slug='samsung-storage'),
     (SELECT category_id FROM categories WHERE slug='nvme'),
     '81zE8qvJbdL._SX679_.jpg',
     '71OYNmVRFhL._SX679_.jpg',
     null,
     'both',TRUE, TRUE),

  ('Seagate BarraCuda 2TB HDD', 'HDD-ST-2TB', 'seagate-barracuda-2tb',
     'Reliable 7200 RPM mechanical hard drive for mass storage.',
     59.99, 0.00, 100, 0.45, 4.1,
     (SELECT brand_id FROM brands WHERE slug='seagate'),
     (SELECT category_id FROM categories WHERE slug='hdd'),
     '71NyznvXLOL._SX679_.jpg',
     '81WueVKTzuL._SX679_.jpg',
     '7108Bo7fN0L._SX679_.jpg',
     'both',FALSE, TRUE),

  ('Gigabyte RTX 3060 Windforce OC 12GB', 'GV-N3060WF2OC-12GD', 'gigabyte-rtx-3060-windforce-oc-12gb',
     '12 GB GDDR6 graphics card for 1080p/1440p gaming.',
     599.99, 0.00, 20, 1.20, 4.8,
     (SELECT brand_id FROM brands WHERE slug='gigabyte-gpu'),
     (SELECT category_id FROM categories WHERE slug='graphics-card'),
     '71OFKtclW4L._SX679_.jpg',
     '71YF6p0RPqL._SX679_.jpg',
     '71arsLWFVDL._SX679_.jpg',
     'both',TRUE, TRUE),
   ('NVIDIA GeForce RTX 4080 16GB GDDR6X Graphics Card', '900-1G136-2560-000', 'nvidia-geforce-rtx-4080',
     'Top-tier GPU for 4K gaming and heavy compute.',
     1999.99, 0.00, 5, 2.0, 4.9,
     (SELECT brand_id FROM brands WHERE slug='nvidia'),
     (SELECT category_id FROM categories WHERE slug='graphics-card'),
     '71OMxfZoyML._SX679_.jpg',
     '71Kjyrdle3L._SX679_.jpg',
     '61oHZyzFq4L._SX679_.jpg',
     'both',TRUE, TRUE),

  ('PNY NVIDIA GeForce RTX 4090 24GB Verto Triple Fan Graphics Card DLSS 3 (384-bit PCIe 4.0, GDDR6X, Supports 4k, Anti-Sag Bracket, HDMI/DisplayPort) - VCG409024TFXPB1', 'GPU-NVIDIA-RTX-4090', 'nvidia-geforce-rtx-4090',
     'Top-tier GPU for 4K gaming and heavy compute.',
     1999.99, 0.00, 5, 2.0, 4.9,
     (SELECT brand_id FROM brands WHERE slug='nvidia'),
     (SELECT category_id FROM categories WHERE slug='graphics-card'),
     '619QTa9dXXL._SX522_.jpg',
     '51TOFDWee3L._SX522_.jpg',
     '51BNol+lU3L._SX522_.jpg',
     'both',TRUE, TRUE),

   ('AMD Radeon RX 7900 XT', 'GPU-AMD-RX-7900XT', 'amd-radeon-rx-7900xt',
     'Top-tier GPU for 4K gaming and heavy compute.',
     1999.99, 0.00, 5, 2.0, 4.9,
     (SELECT brand_id FROM brands WHERE slug='amd'),
     (SELECT category_id FROM categories WHERE slug='graphics-card'),
     null,
     null,
     null,
     'both',TRUE, TRUE),

  ('ASUS ROG STRIX B660-F Motherboard', 'MB-ASUS-B660F', 'asus-rog-strix-b660-f',
     'ATX motherboard with robust power delivery and RGB headers.',
     189.99, 0.00, 30, 1.0, 4.5,
     (SELECT brand_id FROM brands WHERE slug='ASUS-MB'),
     (SELECT category_id FROM categories WHERE slug='motherboard'),
     null,
     null,
     null,
     'intel',FALSE, TRUE),

   ('Corsair Vengeance LPX 16GB 2x8GB 32000MHz', 'CMK8GX4M1E3200C16', 'corsair-vengeance-lpx-32gb',
     '2x8 GB DDR4-3200 MHz memory kit, low-profile heat spreader.',
     79.99, 5.00, 50, 0.12, 4.3,
     (SELECT brand_id FROM brands WHERE slug='corsair-ram'),
     (SELECT category_id FROM categories WHERE slug='ram'),
     null,
     null,
     null,
     'both',TRUE, TRUE),

  
     ('G.Skill Trident Z5 RGB 32GB (2×16GB) DDR5 6000MHz', 'F5-6000J3636F16GX2-TZ5RW', 'ram-g-skill-trident-z5-rgb-32gb-ddr5-6000mhz',
     '2x16 GB DDR5-6000 MHz memory kit, low-profile heat spreader.',
     79.99, 5.00, 50, 0.12, 4.3,
     (SELECT brand_id FROM brands WHERE slug='g-skill-ram'),
     (SELECT category_id FROM categories WHERE slug='ram'),
     null,
     null,
     null,
     'both',TRUE, TRUE),

  ('Corsair RM750x 750W PSU', 'PSU-COR-750W', 'corsair-rm750x-750w',
     'Fully modular 80+ Gold power supply, high quality Japanese capacitors.',
     119.99, 0.00, 25, 2.2, 4.6,
     (SELECT brand_id FROM brands WHERE slug='Corsair-PSU'),
     (SELECT category_id FROM categories WHERE slug='power-supply'),
     null,
     null,
     null,
     'both',FALSE, TRUE),

  ('NZXT H510 Compact Case', 'CASE-NZXT-H510', 'nzxt-h510-compact-case',
     'Mid-tower ATX case with tempered glass and clean cable management.',
     79.99, 0.00, 35, 6.00, 4.2,
     (SELECT brand_id FROM brands WHERE slug='nzxt-case'),
     (SELECT category_id FROM categories WHERE slug='cabinet'),
     null,
     null,
     null,
     'both',FALSE, TRUE),

  ('Cooler Master Hyper 212', 'COOLER-CM-212', 'cooler-master-hyper-212',
     'Affordable air cooler with good thermal performance.',
     34.99, 0.00, 60, 0.8, 4.1,
     (SELECT brand_id FROM brands WHERE slug='Cooler Master'),
     (SELECT category_id FROM categories WHERE slug='air-cooler'),
     null,
     null,
     null,
     'both',FALSE, TRUE),

  ('Dell 27" 1440p 165Hz', 'MON-DLL-27-1440P', 'dell-27-1440p-165hz',
     '27-inch QHD gaming monitor with 165Hz refresh and Adaptive Sync.',
     349.99, 0.00, 10, 5.5, 4.4,
     (SELECT brand_id FROM brands WHERE slug='dell-monitor'),
     (SELECT category_id FROM categories WHERE slug='monitor'),
     null,
     null,
     null,
     'both',TRUE, TRUE),

  ('Logitech G413 Mechanical Keyboard', 'KB-LOG-G413', 'logitech-g413-mechanical',
     'Tenkeyless mechanical keyboard with Romer-G switches.',
     69.99, 0.00, 45, 1.0, 4.3,
     (SELECT brand_id FROM brands WHERE slug='logitech-keyboard'),
     (SELECT category_id FROM categories WHERE slug='keyboard'),
     null,
     null,
     null,
     'both',FALSE, TRUE),

  ('Razer DeathAdder V2', 'M-RZR-DA-V2', 'razer-deathadder-v2',
     'Ergonomic gaming mouse with high-precision sensor.',
     49.99, 0.00, 60, 0.12, 4.5,
     (SELECT brand_id FROM brands WHERE slug='razer-mouse'),
     (SELECT category_id FROM categories WHERE slug='mouse'),
     null,
     null,
     null,
     'both',FALSE, TRUE),

  ('SteelSeries QcK Large Mousepad', 'MP-SS-QCK-L', 'steelseries-qck-large',
     'Large cloth mousepad with non-slip rubber base.',
     14.99, 0.00, 120, 0.2, 4.2,
     (SELECT brand_id FROM brands WHERE slug='steelseries-mousepad'),
     (SELECT category_id FROM categories WHERE slug='mousepad'),
     null,
     null,
     null,
     'both',FALSE, TRUE);
  

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
INSERT INTO user_address (user_id, full_name, phone, address_line1, address_line2, city, state, zip, country, is_default) VALUES
  ((SELECT user_id FROM users WHERE email='alice@johnson.com'),
   'Alice Johnson', '777-777-7777', '123 Main St', 'Apt 4B', 'New York', 'NY', '10001', 'United States', 1),

  ((SELECT user_id FROM users WHERE email='jdoe@example.com'),
   'John Doe', '888-888-8888', '456 Oak St', NULL, 'Los Angeles', 'CA', '90001', 'United States', 1),

  ((SELECT user_id FROM users WHERE email='bob@smith.com'),
   'Bob Smith', '555-555-5555', '456 Elm St', 'Suite 5', 'San Francisco', 'CA', '94101', 'United States', 1),

  ((SELECT user_id FROM users WHERE email='maya@singh.com'),
   'Maya Singh', '666-666-6666', '12 MG Road', '3rd Floor', 'Mumbai', 'MH', '400001', 'India', 1);



-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    billing_address_id INT NOT NULL,
    shipping_address_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    -- payment_method ENUM('cash_on_delivery','credit_card','debit_card','upi') NOT NULL,
    -- payment_status ENUM('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
    order_status ENUM('Pending','Processing','Shipped','Delivered','Cancelled','Returned') DEFAULT 'Pending',
    tracking_number VARCHAR(100) DEFAULT NULL,
    shipping_method VARCHAR(100) DEFAULT NULL,
    order_notes TEXT DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    refunded_at DATETIME DEFAULT NULL,
    shipped_date DATE DEFAULT NULL,
    delivered_date DATE DEFAULT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (billing_address_id) REFERENCES user_address(address_id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_address_id) REFERENCES user_address(address_id) ON DELETE CASCADE
);

INSERT INTO orders (user_id, billing_address_id, shipping_address_id, total_amount, order_status) VALUES
  ((SELECT user_id FROM users WHERE email='alice@johnson.com'),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='alice@johnson.com') AND is_default=1),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='alice@johnson.com') AND is_default=1),
   99.99, 'Pending'),

  ((SELECT user_id FROM users WHERE email='jdoe@example.com'),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='jdoe@example.com') AND is_default=1),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='jdoe@example.com') AND is_default=1),
   49.99, 'Pending'),

  ((SELECT user_id FROM users WHERE email='bob@smith.com'),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='bob@smith.com') AND is_default=1),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='bob@smith.com') AND is_default=1),
   79.99, 'Pending'),

  ((SELECT user_id FROM users WHERE email='maya@singh.com'),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='maya@singh.com') AND is_default=1),
   (SELECT address_id FROM user_address WHERE user_id=(SELECT user_id FROM users WHERE email='maya@singh.com') AND is_default=1),
   59.99, 'Pending');

-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(6,2) DEFAULT 0,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(order_id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);
INSERT INTO order_items (order_id, product_id, quantity, unit_price, discount, total_price) VALUES
  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='alice@johnson.com')),
   (SELECT product_id FROM products WHERE product_name='Laptop'),
   1, 99.99, 0, 99.99),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='jdoe@example.com')),
   (SELECT product_id FROM products WHERE product_name='Smartphone'),
   1, 49.99, 0, 49.99),  

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='bob@smith.com')),
   (SELECT product_id FROM products WHERE product_name='Tablet'),
   1, 79.99, 0, 79.99),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='maya@singh.com')),
   (SELECT product_id FROM products WHERE product_name='Headphones'),
   1, 59.99, 0, 59.99);


CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('cash_on_delivery','credit_card','debit_card','upi') NOT NULL,
    transaction_id VARCHAR(150) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'INR',
    payment_status ENUM('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
    paid_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);
INSERT INTO payments (order_id, payment_method, transaction_id, amount, currency, payment_status, paid_at) VALUES
  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='alice@johnson.com')),
   'cash_on_delivery', NULL, 99.99, 'INR', 'Paid', NOW()),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='jdoe@example.com')),
   'cash_on_delivery', NULL, 49.99, 'INR', 'Paid', NOW()),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='bob@smith.com')),
   'cash_on_delivery', NULL, 79.99, 'INR', 'Paid', NOW()),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='maya@singh.com')),
   'cash_on_delivery', NULL, 59.99, 'INR', 'Paid', NOW());

CREATE TABLE IF NOT EXISTS shipments (
    shipment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tracking_number VARCHAR(100) NOT NULL,
    shipping_method VARCHAR(100) NOT NULL,
    shipped_date DATETIME DEFAULT NULL,
    delivered_date DATETIME DEFAULT NULL,
    status ENUM('Pending','Shipped','Delivered','Returned') DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);
INSERT INTO shipments (order_id, tracking_number, shipping_method, shipped_date, delivered_date, status) VALUES
  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='alice@johnson.com')),
   'ABC123', 'UPS', NULL, NULL, 'Pending'),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='jdoe@example.com')),
   'DEF456', 'FedEx', NULL, NULL, 'Pending'),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='bob@smith.com')),
   'GHI789', 'UPS', NULL, NULL, 'Pending'),

  ((SELECT order_id FROM orders WHERE user_id=(SELECT user_id FROM users WHERE email='maya@singh.com')),
   'JKL012', 'FedEx', NULL, NULL, 'Pending');

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
-- 6. PRODUCT SPECS
-- CPU 1: Intel Core i7-13700K
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Cores', '16 (8P+8E)', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Threads', '24', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Base Clock', '3.4 GHz', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Boost Clock', '5.4 GHz', 'Key Specs', 4),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'TDP', '125W', 'Key Specs', 5);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Lithography', 'Intel 7', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Socket Type', 'LGA1700', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Cache', '30MB Intel Smart Cache', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Memory Support', 'DDR4-3200 / DDR5-5600', 'Detailed Specs', 4),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'PCIe Version', 'PCIe 5.0 / 4.0', 'Detailed Specs', 5),
((SELECT product_id FROM products WHERE sku='CPU-INT-i7-13700K'), 'Integrated Graphics', 'Intel UHD 770', 'Detailed Specs', 6);

-- CPU 2: AMD Ryzen 7 7800X3D
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Cores', '8', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Threads', '16', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Base Clock', '4.2 GHz', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Boost Clock', '5.0 GHz', 'Key Specs', 4),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'TDP', '120W', 'Key Specs', 5);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Lithography', 'TSMC 5nm', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Socket Type', 'AM5', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Cache', '96MB L3 (3D V-Cache)', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Memory Support', 'DDR5-5200, Dual Channel', 'Detailed Specs', 4),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'PCIe Version', 'PCIe 5.0', 'Detailed Specs', 5),
((SELECT product_id FROM products WHERE sku='CPU-RYZ-7-7800X3D'), 'Integrated Graphics', 'None', 'Detailed Specs', 6);

-- CPU 3: Intel Core i5-12400F
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Cores', '6', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Threads', '12', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Base Clock', '2.5 GHz', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Boost Clock', '4.4 GHz', 'Key Specs', 4),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'TDP', '65W', 'Key Specs', 5);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Lithography', 'Intel 7', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Socket Type', 'LGA1700', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Cache', '18MB Intel Smart Cache', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Memory Support', 'DDR4-3200 / DDR5-4800', 'Detailed Specs', 4),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'PCIe Version', 'PCIe 5.0 / 4.0', 'Detailed Specs', 5),
((SELECT product_id FROM products WHERE sku='CPU-INT-i5-12400F'), 'Integrated Graphics', 'None (F-Series)', 'Detailed Specs', 6);

-- GPU 1: NVIDIA GeForce RTX 4080
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'GPU Model', 'GeForce RTX 4080', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'VRAM Size', '16GB', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'VRAM Type', 'GDDR6X', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Base Clock', '2205 MHz', 'Key Specs', 4),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Boost Clock', '2505 MHz', 'Key Specs', 5);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'CUDA Cores', '9728', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Memory Bus Width', '256-bit', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Memory Bandwidth', '716.8 GB/s', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Power Requirement', '320W (3x 8-pin / 16-pin)', 'Detailed Specs', 4),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Outputs', 'HDMI 2.1, 3x DisplayPort 1.4a', 'Detailed Specs', 5),
((SELECT product_id FROM products WHERE sku='900-1G136-2560-000'), 'Cooling Type', 'Triple Fan', 'Detailed Specs', 6);


-- GPU 2: AMD Radeon RX 7900 XT
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'GPU Model', 'Radeon RX 7900 XT', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'VRAM Size', '20GB', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'VRAM Type', 'GDDR6', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Base Clock', '2000 MHz', 'Key Specs', 4),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Boost Clock', '2400 MHz', 'Key Specs', 5);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Stream Processors', '5376', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Memory Bus Width', '320-bit', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Memory Bandwidth', '800 GB/s', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Power Requirement', '315W (2x 8-pin)', 'Detailed Specs', 4),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Outputs', 'HDMI 2.1, 2x DisplayPort 2.1, USB-C', 'Detailed Specs', 5),
((SELECT product_id FROM products WHERE sku='GPU-AMD-RX-7900XT'), 'Cooling Type', 'Triple Fan', 'Detailed Specs', 6);

-- GPU 3: NVIDIA GeForce RTX 3060
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'GPU Model', 'GeForce RTX 3060', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'VRAM Size', '12GB', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'VRAM Type', 'GDDR6', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Base Clock', '1320 MHz', 'Key Specs', 4),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Boost Clock', '1777 MHz', 'Key Specs', 5);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'CUDA Cores', '3584', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Memory Bus Width', '192-bit', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Memory Bandwidth', '360 GB/s', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Power Requirement', '170W (1x 8-pin)', 'Detailed Specs', 4),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Outputs', 'HDMI 2.1, 3x DisplayPort 1.4a', 'Detailed Specs', 5),
((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'Cooling Type', 'Dual Fan', 'Detailed Specs', 6);

-- RAM 1: Corsair Vengeance LPX 16GB (2×8GB) DDR4 3200MHz
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'Capacity', '16GB (2×8GB)', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'Type', 'DDR4', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'Speed', '3200MHz', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'CAS Latency', 'CL16', 'Key Specs', 4);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'Voltage', '1.35V', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'ECC Support', 'No', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'RGB Lighting', 'No', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='CMK8GX4M1E3200C16'), 'Heatsink Type', 'Low Profile Black Aluminum', 'Detailed Specs', 4);

-- RAM 2: G.Skill Trident Z5 RGB 32GB (2×16GB) DDR5 6000MHz
-- Key Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'Capacity', '32GB (2×16GB)', 'Key Specs', 1),
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'Type', 'DDR5', 'Key Specs', 2),
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'Speed', '6000MHz', 'Key Specs', 3),
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'CAS Latency', 'CL36', 'Key Specs', 4);

-- Detailed Specs
INSERT INTO product_specs (product_id, spec_name, spec_value, spec_group, display_order) VALUES
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'Voltage', '1.35V', 'Detailed Specs', 1),
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'ECC Support', 'No', 'Detailed Specs', 2),
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'RGB Lighting', 'Yes, Addressable RGB', 'Detailed Specs', 3),
((SELECT product_id FROM products WHERE sku='F5-6000J3636F16GX2-TZ5RW'), 'Heatsink Type', 'Aluminum Heatspreader', 'Detailed Specs', 4);


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
CREATE TABLE IF NOT EXISTS cart_items (
    cart_item_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(product_id)  ON DELETE CASCADE
);
INSERT INTO cart_items (user_id, product_id, quantity) VALUES
((SELECT user_id FROM users WHERE email='alice@johnson.com'), (SELECT product_id FROM products WHERE product_name='Laptop'), 1),
((SELECT user_id FROM users WHERE email='jdoe@example.com'), (SELECT product_id FROM products WHERE product_name='Smartphone'), 1),
((SELECT user_id FROM users WHERE email='bob@smith.com'), (SELECT product_id FROM products WHERE product_name='Tablet'), 1),
((SELECT user_id FROM users WHERE email='maya@singh.com'), (SELECT product_id FROM products WHERE product_name='Headphones'), 1);
