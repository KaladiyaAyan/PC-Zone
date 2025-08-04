-- 1. CREATE DATABASE & USE
CREATE DATABASE IF NOT EXISTS pczone;
USE pczone;

-- 2. USERS TABLE (admin + normal users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    full_name VARCHAR(150),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    status ENUM('active','inactive') DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default admin (password = 'admin123', hashed)
INSERT INTO users (username, email, password, role, status)
VALUES (
  'admin',
  'admin@pczone.com',
  '$2y$10$a0UCxwvfzbhYFWtq7C4MIuGqwPNSoeCQ6A4ZcWLEjrqaHR2dPwXPG',
  'admin',
  'active'
), ('John Doe','jdoe@example.com','$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e','user','active');

-- 3. CATEGORIES (top-level + subcategories via parent_id)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT DEFAULT NULL,
    level INT DEFAULT 0,
    slug VARCHAR(250) UNIQUE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

INSERT INTO categories (name, parent_id, level, slug) VALUES
  ('Processor',      NULL, 0, 'processor'),        
  ('Graphics Card',  NULL, 0, 'graphics-card'),    
  ('Motherboard',    NULL, 0, 'motherboard'),      
  ('RAM',            NULL, 0, 'ram'),              
  ('Storage',        NULL, 0, 'storage'),          
  ('Power Supply',   NULL, 0, 'power-supply'),     
  ('Cabinet',        NULL, 0, 'cabinet'),          
  ('Cooling System', NULL, 0, 'cooling-system'),   
  ('Monitor',        NULL, 0, 'monitor'),          
  ('Keyboard',       NULL, 0, 'keyboard'),         
  ('Mouse',          NULL, 0, 'mouse'),            
  ('Mousepad',       NULL, 0, 'mousepad');
  -- ('Gamepad',        NULL, 0, 'gamepad');       

-- 2. Grab IDs into variables
SET @proc_id      = (SELECT id FROM categories WHERE name = 'Processor');
SET @gc_id        = (SELECT id FROM categories WHERE name = 'Graphics Card');
SET @mb_id        = (SELECT id FROM categories WHERE name = 'Motherboard');
SET @ram_id       = (SELECT id FROM categories WHERE name = 'RAM');
SET @storage_id   = (SELECT id FROM categories WHERE name = 'Storage');
SET @psu_id       = (SELECT id FROM categories WHERE name = 'Power Supply');
SET @cab_id       = (SELECT id FROM categories WHERE name = 'Cabinet');
SET @cooling_id   = (SELECT id FROM categories WHERE name = 'Cooling System');
SET @monitor_id   = (SELECT id FROM categories WHERE name = 'Monitor');
SET @keyboard_id  = (SELECT id FROM categories WHERE name = 'Keyboard');
SET @mouse_id     = (SELECT id FROM categories WHERE name = 'Mouse');
SET @mousepad_id  = (SELECT id FROM categories WHERE name = 'Mousepad');
-- SET @gamepad_id   = (SELECT id FROM categories WHERE name = 'Gamepad');

-- 3. Insert subcategories using those variables
INSERT INTO categories (name, parent_id, level, slug) VALUES
  ('Intel',          @proc_id,     1, 'intel'),
  ('AMD',            @proc_id,     1, 'amd'),
  ('SSD',            @storage_id,  1, 'ssd'),
  ('HDD',            @storage_id,  1, 'hdd'),
  ('NVMe',           @storage_id,  1, 'nvme'),
  ('Air Cooler',     @cooling_id,  1, 'air-cooler'),
  ('Liquid Cooler',  @cooling_id,  1, 'liquid-cooler');
  -- ('Mechanical',     @keyboard_id, 1, 'keyboard'),
  -- ('Mousepad',       @mousepad_id, 1, 'mousepad');

-- 4. BRANDS (linked to PC-part categories)
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    slug VARCHAR(250) UNIQUE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insert brands by category
INSERT INTO brands (name, category_id, slug) VALUES
-- Processor
('Intel',         (SELECT id FROM categories WHERE name='Processor'),    'intel'),
('AMD',           (SELECT id FROM categories WHERE name='Processor'),    'amd'),

-- Graphics Card
('NVIDIA',        (SELECT id FROM categories WHERE name='Graphics Card'),'nvidia'),
('AMD',           (SELECT id FROM categories WHERE name='Graphics Card'),'amd-gpu'),
('ASUS',          (SELECT id FROM categories WHERE name='Graphics Card'),'asus-gpu'),
('MSI',           (SELECT id FROM categories WHERE name='Graphics Card'),'msi-gpu'),
('Gigabyte',      (SELECT id FROM categories WHERE name='Graphics Card'),'gigabyte-gpu'),
('ZOTAC',         (SELECT id FROM categories WHERE name='Graphics Card'),'zotac'),
('Inno3D',        (SELECT id FROM categories WHERE name='Graphics Card'),'inno3d'),
('EVGA',          (SELECT id FROM categories WHERE name='Graphics Card'),'evga'),

-- Motherboard
('ASUS',          (SELECT id FROM categories WHERE name='Motherboard'),   'asus-mb'),
('ASRock',        (SELECT id FROM categories WHERE name='Motherboard'),   'asrock'),
('MSI',           (SELECT id FROM categories WHERE name='Motherboard'),   'msi-mb'),
('Gigabyte',      (SELECT id FROM categories WHERE name='Motherboard'),   'gigabyte-mb'),
('Intel',         (SELECT id FROM categories WHERE name='Motherboard'),   'intel-mb'),
('EVGA',          (SELECT id FROM categories WHERE name='Motherboard'),   'evga-mb'),

-- RAM
('Corsair',       (SELECT id FROM categories WHERE name='RAM'),           'corsair-ram'),
('G.Skill',       (SELECT id FROM categories WHERE name='RAM'),           'gskill'),
('Kingston',      (SELECT id FROM categories WHERE name='RAM'),           'kingston'),
('Crucial',       (SELECT id FROM categories WHERE name='RAM'),           'crucial-ram'),
('ADATA',         (SELECT id FROM categories WHERE name='RAM'),           'adata'),
('Samsung',       (SELECT id FROM categories WHERE name='RAM'),           'samsung-ram'),

-- Storage
('Samsung',       (SELECT id FROM categories WHERE name='Storage'),       'samsung-storage'),
('WD',            (SELECT id FROM categories WHERE name='Storage'),       'wd'),
('Seagate',       (SELECT id FROM categories WHERE name='Storage'),       'seagate'),
('Crucial',       (SELECT id FROM categories WHERE name='Storage'),       'crucial-storage'),
('SanDisk',       (SELECT id FROM categories WHERE name='Storage'),       'sandisk'),
('ADATA',         (SELECT id FROM categories WHERE name='Storage'),       'adata-storage'),
('Kingston',      (SELECT id FROM categories WHERE name='Storage'),       'kingston-storage'),

-- Power Supply
('Corsair',       (SELECT id FROM categories WHERE name='Power Supply'),  'corsair-psu'),
('Antec',         (SELECT id FROM categories WHERE name='Power Supply'),  'antec'),
('Cooler Master', (SELECT id FROM categories WHERE name='Power Supply'),  'cooler-master-psu'),
('EVGA',          (SELECT id FROM categories WHERE name='Power Supply'),  'evga-psu'),
('Thermaltake',   (SELECT id FROM categories WHERE name='Power Supply'),  'thermaltake-psu'),
('NZXT',          (SELECT id FROM categories WHERE name='Power Supply'),  'nzxt-psu'),

-- Cabinet
('NZXT',          (SELECT id FROM categories WHERE name='Cabinet'),        'nzxt-case'),
('Lian Li',       (SELECT id FROM categories WHERE name='Cabinet'),        'lian-li'),
('Corsair',       (SELECT id FROM categories WHERE name='Cabinet'),        'corsair-case'),
('Cooler Master', (SELECT id FROM categories WHERE name='Cabinet'),        'cooler-master-case'),
('Thermaltake',   (SELECT id FROM categories WHERE name='Cabinet'),        'thermaltake-case'),
('Antec',         (SELECT id FROM categories WHERE name='Cabinet'),        'antec-case'),
('DeepCool',      (SELECT id FROM categories WHERE name='Cabinet'),        'deepcool'),

-- Cooling System
('Cooler Master', (SELECT id FROM categories WHERE name='Cooling System'),'cooler-master-cooler'),
('DeepCool',      (SELECT id FROM categories WHERE name='Cooling System'),'deepcool-cooler'),
('NZXT',          (SELECT id FROM categories WHERE name='Cooling System'),'nzxt-cooler'),
('be quiet!',     (SELECT id FROM categories WHERE name='Cooling System'),'be-quiet'),
('Arctic',        (SELECT id FROM categories WHERE name='Cooling System'),'arctic'),
('Thermaltake',   (SELECT id FROM categories WHERE name='Cooling System'),'thermaltake-cooler'),

-- Monitor
('Dell',          (SELECT id FROM categories WHERE name='Monitor'),        'dell-monitor'),
('LG',            (SELECT id FROM categories WHERE name='Monitor'),        'lg-monitor'),
('ASUS',          (SELECT id FROM categories WHERE name='Monitor'),        'asus-monitor'),
('Acer',          (SELECT id FROM categories WHERE name='Monitor'),        'acer-monitor'),
('AOC',           (SELECT id FROM categories WHERE name='Monitor'),        'aoc'),
('Samsung',       (SELECT id FROM categories WHERE name='Monitor'),        'samsung-monitor'),
('MSI',           (SELECT id FROM categories WHERE name='Monitor'),        'msi-monitor'),

-- Keyboard
('Logitech',      (SELECT id FROM categories WHERE name='Keyboard'),       'logitech-keyboard'),
('Redragon',      (SELECT id FROM categories WHERE name='Keyboard'),       'redragon-keyboard'),
('Razer',         (SELECT id FROM categories WHERE name='Keyboard'),       'razer-keyboard'),
('Corsair',       (SELECT id FROM categories WHERE name='Keyboard'),       'corsair-keyboard'),
('HyperX',        (SELECT id FROM categories WHERE name='Keyboard'),       'hyperx'),
('SteelSeries',   (SELECT id FROM categories WHERE name='Keyboard'),       'steelseries'),
('Zebronics',     (SELECT id FROM categories WHERE name='Keyboard'),       'zebronics'),

-- Mouse
('Logitech',      (SELECT id FROM categories WHERE name='Mouse'),          'logitech-mouse'),
('Razer',         (SELECT id FROM categories WHERE name='Mouse'),          'razer-mouse'),
('Redragon',      (SELECT id FROM categories WHERE name='Mouse'),          'redragon-mouse'),
('Corsair',       (SELECT id FROM categories WHERE name='Mouse'),          'corsair-mouse'),
('HP',            (SELECT id FROM categories WHERE name='Mouse'),          'hp-mouse'),
('Zebronics',     (SELECT id FROM categories WHERE name='Mouse'),          'zebronics-mouse'),
('SteelSeries',   (SELECT id FROM categories WHERE name='Mouse'),          'steelseries-mouse'),
('Glorious',      (SELECT id FROM categories WHERE name='Mouse'),          'glorious'),

-- Mousepad & Gamepad (optional extras)
('SteelSeries',   (SELECT id FROM categories WHERE name='Mousepad'),       'steelseries-mousepad'),
('Corsair',       (SELECT id FROM categories WHERE name='Mousepad'),       'corsair-mousepad'),
('Razer',         (SELECT id FROM categories WHERE name='Mousepad'),       'razer-mousepad'),
('Sony',          (SELECT id FROM categories WHERE name='Gamepad'),        'sony-gamepad'),
('Microsoft',     (SELECT id FROM categories WHERE name='Gamepad'),        'microsoft-gamepad'),
('Logitech',      (SELECT id FROM categories WHERE name='Gamepad'),        'logitech-gamepad');

-- 5. (The rest of the improved tables—products, images, specs, reviews, customers, addresses,
--     orders, order_items, cart_items—should be created exactly as in the previously shared schema.)

-- ... [Paste the CREATE TABLE statements for products, product_images, product_specs,
--      product_reviews, customers, addresses, orders, order_items, cart_items here] ...

-- PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(250) NOT NULL,
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
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id)     REFERENCES brands(id)     ON DELETE CASCADE
);

-- 4. PRODUCTS
INSERT INTO products (name, sku, slug, description, price, discount, stock, weight, rating, brand_id, category_id, is_featured, is_active) VALUES
  ('Intel Core i5-12400F', 'CPU-INT-12400F', 'intel-core-i5-12400f',
     '6-core 12-thread desktop processor, 2.5 GHz base clock, 4.4 GHz boost.', 
     179.99, 10.00, 25, 0.65, 4.5,
     (SELECT id FROM brands WHERE slug='intel'),
     (SELECT id FROM categories WHERE slug='processor'),
     TRUE, TRUE),
  ('AMD Ryzen 5 5600X', 'CPU-AMD-5600X', 'amd-ryzen-5-5600x',
     '6-core 12-thread desktop CPU, 3.7 GHz base clock, 4.6 GHz boost.',
     199.99, 0.00, 30, 0.65, 4.7,
     (SELECT id FROM brands WHERE slug='amd'),
     (SELECT id FROM categories WHERE slug='processor'),
     TRUE, TRUE),
  ('Corsair Vengeance LPX 16GB', 'RAM-COR-16GB', 'corsair-vengeance-lpx-16gb',
     '2×8 GB DDR4-3200 MHz memory kit, low-profile heat spreader.', 
     79.99, 5.00, 50, 0.12, 4.3,
     (SELECT id FROM brands WHERE slug='corsair-ram'),
     (SELECT id FROM categories WHERE slug='ram'),
     FALSE, TRUE),
  ('NVIDIA GeForce RTX 3060', 'GPU-NVIDIA-RTX-3060', 'nvidia-geforce-rtx-3060',
     '8 GB GDDR6 graphics card, 1920 x 1080 resolution, 144Hz refresh rate.',
     599.99, 0.00, 20, 0.12, 4.8,
     (SELECT id FROM brands WHERE slug='nvidia'),
     (SELECT id FROM categories WHERE slug='graphics-card'),
     TRUE, TRUE);


-- PRODUCT IMAGES
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_path VARCHAR(255),
    is_main BOOLEAN DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. PRODUCT IMAGES
INSERT INTO product_images (product_id, image_path, is_main) VALUES
  ((SELECT id FROM products WHERE sku='CPU-INT-12400F'), 'intel12400f-main.jpg', TRUE),
  ((SELECT id FROM products WHERE sku='CPU-INT-12400F'), 'intel12400f-box.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='CPU-AMD-5600X'), 'ryzen5600x-main.jpg', TRUE),
  ((SELECT id FROM products WHERE sku='RAM-COR-16GB'), 'vengeance-lpx-16gb.jpg', TRUE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-main.jpg', TRUE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-box.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-inside.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-side.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-back.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-top.jpg', FALSE),
  -- ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-bottom.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-left.jpg', FALSE),
  -- ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-right.jpg', FALSE),
  ((SELECT id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-front.jpg', FALSE);

-- PRODUCT SPECS (key-value pairs)
CREATE TABLE IF NOT EXISTS product_specs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    spec_name VARCHAR(100),
    spec_value VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 6. PRODUCT SPECS
INSERT INTO product_specs (product_id, spec_name, spec_value) VALUES
  ((SELECT id FROM products WHERE sku='CPU-INT-12400F'), 'Cores',       '6'),
  ((SELECT id FROM products WHERE sku='CPU-INT-12400F'), 'Threads',     '12'),
  ((SELECT id FROM products WHERE sku='CPU-INT-12400F'), 'Base Clock',  '2.5 GHz'),
  ((SELECT id FROM products WHERE sku='CPU-AMD-5600X'), 'Base Clock',  '3.7 GHz'),
  ((SELECT id FROM products WHERE sku='RAM-COR-16GB'),   'Capacity',    '16 GB'),
  ((SELECT id FROM products WHERE sku='RAM-COR-16GB'),   'Speed',       '3200 MHz');



-- CUSTOMERS
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. CUSTOMERS
INSERT INTO customers (name, email, phone) VALUES
  ('Alice Johnson', 'alice@johnson.com', '777-777-7777'),
  ('Bob Smith',     'bob@smith.com',     '555-555-5555');

-- ADDRESSES
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    full_name VARCHAR(150),
    phone VARCHAR(20),
    address_line1 TEXT,
    address_line2 TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip VARCHAR(20),
    country VARCHAR(100),
    is_default BOOLEAN DEFAULT 0,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- 8. ADDRESSES
INSERT INTO addresses (customer_id, full_name, phone, address_line1, city, state, zip, country, is_default) VALUES
  ((SELECT id FROM customers WHERE email='alice@johnson.com'),
   'Alice Johnson','777-777-7777','123 Maple St','Springfield','IL','62701','USA', TRUE),
  ((SELECT id FROM customers WHERE email='bob@smith.com'),
   'Bob Smith','555-555-5555','456 Oak Ave','Metropolis','NY','10001','USA', TRUE);


-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cod','card','upi','netbanking') DEFAULT 'cod',
    address_id INT,
    shipped_at DATETIME,
    delivered_at DATETIME,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (address_id)   REFERENCES addresses(id) ON DELETE SET NULL
);

-- 9. ORDERS
INSERT INTO orders (customer_id, customer_name, total_amount, status, payment_method, address_id) VALUES
  ((SELECT id FROM customers WHERE email='alice@johnson.com'),
   'Alice Johnson', 179.99, 'completed', 'card',
   (SELECT id FROM addresses WHERE customer_id=(SELECT id FROM customers WHERE email='alice@johnson.com'))),
  ((SELECT id FROM customers WHERE email='bob@smith.com'),
   'Bob Smith',  79.99, 'pending',   'cod',
   (SELECT id FROM addresses WHERE customer_id=(SELECT id FROM customers WHERE email='bob@smith.com')));


-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 10. ORDER ITEMS
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
  ((SELECT id FROM orders  WHERE customer_name='Alice Johnson'), (SELECT id FROM products WHERE sku='CPU-INT-12400F'), 1, 179.99),
  ((SELECT id FROM orders  WHERE customer_name='Bob Smith'),     (SELECT id FROM products WHERE sku='RAM-COR-16GB'),   1, 79.99);


-- PRODUCT REVIEWS
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    customer_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id)  REFERENCES products(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id)  ON DELETE CASCADE
);

-- 11. PRODUCT REVIEWS
INSERT INTO product_reviews (product_id, customer_id, rating, comment) VALUES
  ((SELECT id FROM products WHERE sku='CPU-INT-12400F'),
   (SELECT id FROM customers WHERE email='alice@johnson.com'),
   5, 'Excellent performance for gaming and productivity.'),
  ((SELECT id FROM products WHERE sku='RAM-COR-16GB'),
   (SELECT id FROM customers WHERE email='bob@smith.com'),
   4, 'Good value and stable overclock. Nice heatspreaders.');



-- CART ITEMS
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE
);

-- 12. CART ITEMS
INSERT INTO cart_items (customer_id, product_id, quantity) VALUES
  ((SELECT id FROM customers WHERE email='bob@smith.com'), (SELECT id FROM products WHERE sku='CPU-AMD-5600X'), 1),
  ((SELECT id FROM customers WHERE email='alice@johnson.com'), (SELECT id FROM products WHERE sku='RAM-COR-16GB'), 2);