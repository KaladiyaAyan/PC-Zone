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
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Default admin (password = 'admin123', hashed)
INSERT INTO users (username, email, password, date_of_birth, gender, phone, email_verified, role) VALUES
  ('admin', 'admin@gmail.com', '$2y$10$O3ypmFHHIA50WUEFe6BWD.bH4jRaXvxh2j0ZQHOfyYY3k/ckWhYOu', '1990-01-01', 'Male', '1234567890', 1, 'admin'),
  ('John', 'jdoe@example.com', '$2y$10$O3ypmFHHIA50WUEFe6BWD.bH4jRaXvxh2j0ZQHOfyYY3k/ckWhYOu', '1990-01-01', 'Male', '1234567890', 1, 'user'),
  ('Jane', 'jane@pczone', '$2y$10$O3ypmFHHIA50WUEFe6BWD.bH4jRaXvxh2j0ZQHOfyYY3k/ckWhYOu', '1990-01-01', 'Female', '1234567890', 1, 'user'),
  ('Maya', 'maya@singh', '$2y$10$O3ypmFHHIA50WUEFe6BWD.bH4jRaXvxh2j0ZQHOfyYY3k/ckWhYOu', '1990-01-01', 'Female', '1234567890', 1, 'user');

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
INSERT INTO products (
    product_name, sku, slug, description, price, discount, stock, weight, rating, 
    brand_id, category_id, main_image, image_1, image_2, image_3, platform, 
    is_featured, is_active
) VALUES
(
    'NZXT H6 Flow | CC-H61FB-01 | Compact Dual-Chamber Mid-Tower Airflow Case | Panoramic Glass Panels | High-Performance Airflow Panels | Includes 3 x 120mm Fans | Cable Management | Black',
    'B0C89FCDFP', 
    'nzxt-h6-flow', 
    'Wraparound glass panels with a seamless edge provides an unobstructed view of the inside to highlight key components. Compact dual-chamber design improves overall thermal performance and creates a clean, uncrowded aesthetic. Includes three pre-installed 120mm fans positioned at an ideal angle for superb out-of-the-box cooling. The top and side panels feature an airflow-optimized perforation pattern to enhance overall performance and filter dust. An intuitive cable management system simplifies the build process by using wide channels and straps.',
    13999,
    23, 
    10, 
    0.65, 
    4.7,
    (SELECT brand_id FROM brands WHERE slug='nzxt-case'),
    (SELECT category_id FROM categories WHERE slug='cabinet'),
    '71x+i8yRgrL._SY450_.jpg',
    '71YDILR+QnL._SY450_.jpg',
    '71vtU8bv48L._SY450_.jpg',
    '71u5IWhR-aL._SY450_.jpg',
    'both',
    TRUE, 
    TRUE
),
(
    'Lian Li O11 Dynamic EVO XL Full-Tower Compter Case/Gaming Cabinet - White | Support EATX/ATX/Micro-ATX/MINI-ITX - G99.O11DEXL-W.in',
    'B0CGM6RKV8', 
    'lian-li-o11-dynamic-evo-xl-full-tower-computer-case-gaming-cabinet-white',
    'White, Full-Tower, 522 x 304 x 531.9 mm , 4.0mm Tempered Glass Aluminum 8 Expansion Slots, Storage : Behind MB Tray: 3 X 2.5ʹʹ SSD Hard Drive Cage: 4 X 3.5ʹʹ HDD or 2.5ʹʹ SSD I/O Panel : Power Button , Reset Button , USB 3.0 x 4 , Audio x 1 , USB Type C , Color Button , Mode Button Fan Support : Top - 120mm x3 / 140mm x3, Side- 120mm x3 / 140mm x3, Bottom- 120mm x3/ 140mm x3, Rear- 120 mm x1 or 2 GPU Length Clearance : 460mm(Max) ; CPU Cooler Height Clearance : 167mm(Max)',
    30999,
    24, 
    9, 
    0.65, 
    4.9,
    (SELECT brand_id FROM brands WHERE slug='lian-li-case'),
    (SELECT category_id FROM categories WHERE slug='cabinet'),
    '610tNgEZ6LL._SX679_.jpg',
    '61zXV1X5zTL._SX679_.jpg',
    '712etNmCVRL._SX679_.jpg',
    '71O8DnFAk5L._SX679_.jpg',
    'both',
    TRUE, 
    TRUE
),
(
    'Cooler Master RR-212S-20PC-R1 Hyper 212 RGB Black Edition CPU Air Cooler 4 Direct Contact Heat Pipes 120mm RGB Fan',
    'B07H22TC1N', 
    'cooler-master-rr-212s-20pc-r1-hyper-212-rgb-black-edition-cpu-air-cooler-4-direct-contact-heat-pipes-120mm-rgb-fan',
    'Cooler Master Hyper 212 RGB Black Edition Cooling Fan Heatsink - 57.3 CFM - 30 dB(A) Noise - 4-pin PWM Fan - Socket R4 LGA-2066, Socket LGA 2011-v3, Socket R LGA-2011, Socket H4 LGA-1151, Socket H3 LGA-1150, Socket H2 LGA-1155, Socket H LGA-1156, Socket B LGA-1366, Socket AM4, Socket AM3+, Socket AM3 PGA-941, ... Compatible Processor Socket - RGB LED - Aluminum - 18.3 Year Life',
    12000,
    16, 
    4, 
    0.65, 
    4,
    (SELECT brand_id FROM brands WHERE slug='cooler-master-case'),
    (SELECT category_id FROM categories WHERE slug='air-cooler'),
    '81B-HuW8ydL._SY450_.jpg',
    '81jR4Io8OwL._SY450_.jpg',
    '71Q3El-2flL._SY450_.jpg',
    '71+9-o7dIwL._SY450_.jpg',
    'both',
    TRUE, 
    TRUE
),
(
   'Ant Esports ICE-C612 V2 ARGB CPU Cooler| Support Intel LGA1200, LGA115X, LGA20XX, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5',
   'B084G3MJPZ',
   'ant-esports-ice-c612-v2-argb-cpu-cooler',
   'Efficient Heat Dissipation: The Ant Esports ICE-C612 V2 CPU air cooler is designed for optimal heat dissipation, featuring a 153mm tall aluminum heatsink and six 6mm thick copper heatpipes. This advanced cooling solution ensures efficient heat transfer from the CPU to the heatsink, effectively reducing temperatures and maintaining peak performance even during demanding tasks.
   Enhanced Cooling Performance: Equipped with a high-performance PWM 120mm ARGB fan, the ICE-C612 V2 cooler offers not only excellent cooling efficiency but also adds a vibrant visual flair to your system. The fans adjustable speed through pulse-width modulation (PWM) ensures a fine balance between cooling power and noise levels, keeping your CPU operating at an ideal temperature while maintaining a quiet environment
   Optimized Surface Area: The interlocked aluminum heatsink design of the ICE-C612 V2 is engineered to provide a larger surface area for heat dissipation. This design maximizes the contact area between the heatsink and the surrounding air, allowing for quicker and more effective heat dispersion. Whether you are running intensive applications or engaging in heavy gaming sessions, this cooler helps maintain stable and consistent performance.
   Wide Compatibility: The Ant Esports ICE-C612 V2 CPU air cooler offers broad compatibility with major Intel and AMD platforms, including the latest LGA 1700 and AM5 sockets. This versatility makes it an ideal choice for both current and future system builds, allowing you to upgrade your CPU without worrying about changing cooling solutions.
   Easy Installation: Installing the ICE-C612 V2 cooler is a hassle-free process thanks to its user-friendly design. The included mounting hardware and easy-to-follow instructions ensure a smooth installation experience, even for users with minimal technical expertise. With its secure mounting mechanism, you can trust that your cooler will be properly seated for optimal thermal performance.
   Support Intel LGA1200, LGA1150, LGA1151, LGA1155, LGA1156, LGA2066, LGA2011-v3, LGA2011, LGA1366, LGA1700 and AMD FM1, FM2, FM2+, AM2, AM2+, AM3, AM3+, AM4, AM5',
   3499,
   64, 
   6, 
   0.65, 
   4.1,
   (SELECT brand_id FROM brands WHERE slug='ant-esports-case'),
   (SELECT category_id FROM categories WHERE slug='air-cooler'),
   '51pCa994ysL._SY450_.jpg',
   '61PedtDNzIL._SY450_.jpg',
   '71kG6EFIMwL._SY450_.jpg',
   '61Yb+64vAkL._SY450_.jpg',
   'both',
   TRUE, 
   TRUE
);
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
INSERT INTO cart (user_id, product_id, quantity) VALUES
((SELECT user_id FROM users WHERE email='alice@johnson.com'), (SELECT product_id FROM products WHERE product_name='Laptop'), 1),
((SELECT user_id FROM users WHERE email='jdoe@example.com'), (SELECT product_id FROM products WHERE product_name='Smartphone'), 1),
((SELECT user_id FROM users WHERE email='bob@smith.com'), (SELECT product_id FROM products WHERE product_name='Tablet'), 1),
((SELECT user_id FROM users WHERE email='maya@singh.com'), (SELECT product_id FROM products WHERE product_name='Headphones'), 1);
