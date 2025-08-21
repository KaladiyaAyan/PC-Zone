-- 1. CREATE DATABASE & USE
CREATE DATABASE IF NOT EXISTS pczone;
USE pczone;

-- 2. USERS TABLE (admin + normal users)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
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
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT DEFAULT NULL,
    level INT DEFAULT 0,
    slug VARCHAR(250) UNIQUE,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

INSERT INTO categories (category_name, parent_id, level, slug) VALUES
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

-- 5. (The rest of the improved tables—products, images, specs, reviews, customers, addresses,
--     orders, order_items, cart_items—should be created exactly as in the previously shared schema.)

-- ... [Paste the CREATE TABLE statements for products, product_images, product_specs,
--      product_reviews, customers, addresses, orders, order_items, cart_items here] ...

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
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id)     REFERENCES brands(brand_id)     ON DELETE CASCADE
);

-- 4. PRODUCTS
INSERT INTO products (product_name, sku, slug, description, price, discount, stock, weight, rating, brand_id, category_id, is_featured, is_active) VALUES
  ('Intel Core i5-12400F', 'CPU-INT-12400F', 'intel-core-i5-12400f',
     '6-core 12-thread desktop processor, 2.5 GHz base clock, 4.4 GHz boost.', 
     179.99, 10.00, 25, 0.65, 4.5,
     (SELECT brand_id FROM brands WHERE slug='intel'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     TRUE, TRUE),
  ('AMD Ryzen 5 5600X', 'CPU-AMD-5600X', 'amd-ryzen-5-5600x',
     '6-core 12-thread desktop CPU, 3.7 GHz base clock, 4.6 GHz boost.',
     199.99, 0.00, 30, 0.65, 4.7,
     (SELECT brand_id FROM brands WHERE slug='amd'),
     (SELECT category_id FROM categories WHERE slug='processor'),
     TRUE, TRUE),
  ('Corsair Vengeance LPX 16GB', 'RAM-COR-16GB', 'corsair-vengeance-lpx-16gb',
     '2×8 GB DDR4-3200 MHz memory kit, low-profile heat spreader.', 
     79.99, 5.00, 50, 0.12, 4.3,
     (SELECT brand_id FROM brands WHERE slug='corsair-ram'),
     (SELECT category_id FROM categories WHERE slug='ram'),
     FALSE, TRUE),
  ('NVIDIA GeForce RTX 3060', 'GPU-NVIDIA-RTX-3060', 'nvidia-geforce-rtx-3060',
     '8 GB GDDR6 graphics card, 1920 x 1080 resolution, 144Hz refresh rate.',
     599.99, 0.00, 20, 0.12, 4.8,
     (SELECT brand_id FROM brands WHERE slug='nvidia'),
     (SELECT category_id FROM categories WHERE slug='graphics-card'),
     TRUE, TRUE);



-- CUSTOMERS
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('Male','Female','Other') DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    newsletter_subscribed BOOLEAN DEFAULT FALSE,
    status ENUM('active','inactive','banned') DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 7. CUSTOMERS
INSERT INTO customers (first_name, last_name, email, phone, password, date_of_birth, gender, profile_image, newsletter_subscribed) VALUES
  ('Alice', 'Johnson', 'alice@johnson.com', '777-777-7777', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', '1990-01-01', 'Female', 'https://example.com/alice.jpg', TRUE),
  ('Bob', 'Smith', 'bob@smith.com', '555-555-5555', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', '1985-05-15', 'Male', 'https://example.com/bob.jpg', FALSE);


-- ADDRESSES
CREATE TABLE IF NOT EXISTS addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
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
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

-- 8. ADDRESSES
INSERT INTO addresses (customer_id, full_name, phone, address_line1, address_line2, city, state, zip, country) VALUES
  ((SELECT customer_id FROM customers WHERE email='alice@johnson.com'),
   'Alice Johnson',
   '777-777-7777',
   '123 Main St',
   'Apt 4B',
   'New York',
   'NY',
   '10001',
   'United States'),
  ((SELECT customer_id FROM customers WHERE email='bob@smith.com'),
   'Bob Smith',
   '555-555-5555',
   '456 Elm St',
   NULL,
   'San Francisco',
   'CA',
   '94101',
   'United States');


-- ORDERS
-- CREATE TABLE orders (
--     order_id INT AUTO_INCREMENT PRIMARY KEY,
--     customer_id INT NOT NULL,
--     shipping_address_id INT NOT NULL,
--     total_amount DECIMAL(10,2) NOT NULL,
--     payment_method VARCHAR(50) NOT NULL,
--     status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
--     order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
--     shipped_date DATE DEFAULT NULL,
--     delivered_date DATE DEFAULT NULL,
--     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
--     FOREIGN KEY (shipping_address_id) REFERENCES addresses(address_id) ON DELETE CASCADE
-- );
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
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
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (billing_address_id) REFERENCES addresses(address_id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_address_id) REFERENCES addresses(address_id) ON DELETE CASCADE
);
INSERT INTO orders (customer_id, billing_address_id, shipping_address_id, total_amount) VALUES
  ((SELECT customer_id FROM customers WHERE email='alice@johnson.com'),
   (SELECT address_id FROM addresses WHERE customer_id=(SELECT customer_id FROM customers WHERE email='alice@johnson.com')),
   (SELECT address_id FROM addresses WHERE customer_id=(SELECT customer_id FROM customers WHERE email='alice@johnson.com')),
   999.99),
  ((SELECT customer_id FROM customers WHERE email='bob@smith.com'),
   (SELECT address_id FROM addresses WHERE customer_id=(SELECT customer_id FROM customers WHERE email='bob@smith.com')),
   (SELECT address_id FROM addresses WHERE customer_id=(SELECT customer_id FROM customers WHERE email='bob@smith.com')),
   499.99);

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
  ((SELECT order_id FROM orders WHERE customer_id=(SELECT customer_id FROM customers WHERE email='alice@johnson.com')),
   (SELECT product_id FROM products WHERE slug='corsair-vengeance-lpx-16gb'),
   1,
   999.99, 0.00, 999.99),
  ((SELECT order_id FROM orders WHERE customer_id=(SELECT customer_id FROM customers WHERE email='bob@smith.com')),
   (SELECT product_id FROM products WHERE slug='nvidia-geforce-rtx-3060'),
   1,
   499.99, 0.00, 499.99);

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
  ((SELECT order_id FROM orders WHERE customer_id=(SELECT customer_id FROM customers WHERE email='alice@johnson.com')), 'cash_on_delivery', NULL, 999.99, 'INR', 'Paid', NOW()),
  ((SELECT order_id FROM orders WHERE customer_id=(SELECT customer_id FROM customers WHERE email='bob@smith.com')), 'cash_on_delivery', NULL, 499.99, 'INR', 'Paid', NOW());

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
  ((SELECT order_id FROM orders WHERE customer_id=(SELECT customer_id FROM customers WHERE email='alice@johnson.com')), '1234567890', 'FedEx', NOW(), NULL, 'Shipped'),
  ((SELECT order_id FROM orders WHERE customer_id=(SELECT customer_id FROM customers WHERE email='bob@smith.com')), '9876543210', 'UPS', NOW(), NULL, 'Shipped');

-- PRODUCT IMAGES
CREATE TABLE IF NOT EXISTS product_images (
    product_image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_path VARCHAR(255),
    is_main BOOLEAN DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- 5. PRODUCT IMAGES
INSERT INTO product_images (product_id, image_path, is_main) VALUES
  ((SELECT product_id FROM products WHERE sku='CPU-INT-12400F'), 'intel12400f-main.jpg', TRUE),
  ((SELECT product_id FROM products WHERE sku='CPU-INT-12400F'), 'intel12400f-box.jpg', FALSE),
  ((SELECT product_id FROM products WHERE sku='CPU-AMD-5600X'), 'ryzen5600x-main.jpg', TRUE),
  ((SELECT product_id FROM products WHERE sku='RAM-COR-16GB'), 'XPG_ADATA_D30_DDR4_16GB_3200MHz_2.jpg', TRUE),
  ((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-main.jpg', TRUE),
  ((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-left.jpg', FALSE),
  ((SELECT product_id FROM products WHERE sku='GPU-NVIDIA-RTX-3060'), 'rtx3060-front.jpg', FALSE);

-- PRODUCT SPECS (key-value pairs)
CREATE TABLE IF NOT EXISTS product_specs (
    product_spec_id  INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    spec_name VARCHAR(100),
    spec_value VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- 6. PRODUCT SPECS
INSERT INTO product_specs (product_id, spec_name, spec_value) VALUES
  ((SELECT product_id FROM products WHERE sku='CPU-INT-12400F'), 'Cores',       '6'),
  ((SELECT product_id FROM products WHERE sku='CPU-INT-12400F'), 'Threads',     '12'),
  ((SELECT product_id FROM products WHERE sku='CPU-INT-12400F'), 'Base Clock',  '2.5 GHz'),
  ((SELECT product_id FROM products WHERE sku='CPU-AMD-5600X'), 'Base Clock',  '3.7 GHz'),
  ((SELECT product_id FROM products WHERE sku='RAM-COR-16GB'),   'Capacity',    '16 GB'),
  ((SELECT product_id FROM products WHERE sku='RAM-COR-16GB'),   'Speed',       '3200 MHz');


-- PRODUCT REVIEWS
CREATE TABLE IF NOT EXISTS product_reviews (
    product_review_id  INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    customer_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id)  REFERENCES products(product_id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)  ON DELETE CASCADE
);

-- 11. PRODUCT REVIEWS
INSERT INTO product_reviews (product_id, customer_id, rating, comment) VALUES
  ((SELECT product_id FROM products WHERE sku='CPU-INT-12400F'),
   (SELECT customer_id FROM customers WHERE email='alice@johnson.com'),
   5, 'Excellent performance for gaming and productivity.'),
  ((SELECT product_id FROM products WHERE sku='RAM-COR-16GB'),
   (SELECT customer_id FROM customers WHERE email='bob@smith.com'),
   4, 'Good value and stable overclock. Nice heatspreaders.');



-- CART ITEMS
CREATE TABLE IF NOT EXISTS cart_items (
    cart_item_id  INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(product_id)  ON DELETE CASCADE
);

-- 12. CART ITEMS
INSERT INTO cart_items (customer_id, product_id, quantity) VALUES
  ((SELECT customer_id FROM customers WHERE email='bob@smith.com'), (SELECT product_id FROM products WHERE sku='CPU-AMD-5600X'), 1),
  ((SELECT customer_id FROM customers WHERE email='alice@johnson.com'), (SELECT product_id FROM products WHERE sku='RAM-COR-16GB'), 2);