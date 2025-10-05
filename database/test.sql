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
('AMD',           (SELECT category_id FROM categories WHERE category_name='Processor'),    'amd');

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