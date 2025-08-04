-- database/init_users.sql

-- CREATE DATABASE IF NOT EXISTS pczone;
-- USE pczone;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150),
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT 0,
    last_login DATETIME DEFAULT NULL;
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert a default admin user
-- password is 'admin123' → hashed via PHP password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$a0UCxwvfzbhYFWtq7C4MIuGqwPNSoeCQ6A4ZcWLEjrqaHR2dPwXPG', 'admin');

-- Insert a default user
-- password is 'user123' → hashed via PHP password_hash('user123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, role) VALUES
('user','Kx4Q0@example.com' , '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', 'user');



-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Insert top-level categories
INSERT INTO categories (name) VALUES 
('Processor'),
('Graphics Card'),
('Motherboard'),
('RAM'),
('Storage'),
('Power Supply'),
('Cabinet'),
('Cooling System'),
('Monitor'),
('Keyboard'),
('Mouse');

-- Add parent_id column to categories
ALTER TABLE categories
ADD COLUMN parent_id INT DEFAULT NULL,
ADD FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Insert subcategories using temporary variables
SET @processor_id = (SELECT id FROM categories WHERE name = 'Processor');
SET @storage_id = (SELECT id FROM categories WHERE name = 'Storage');
SET @cooling_id = (SELECT id FROM categories WHERE name = 'Cooling System');

INSERT INTO categories (name, parent_id) VALUES
('Intel', @processor_id),
('AMD', @processor_id),
('SSD', @storage_id),
('HDD', @storage_id),
('NVMe', @storage_id),
('Air Cooler', @cooling_id),
('Liquid Cooler', @cooling_id);

-- Create brands table with relation to categories
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insert brands with corresponding category_id
INSERT INTO brands (name, category_id) 
SELECT 'Intel', id FROM categories WHERE name = 'Processor'
UNION ALL SELECT 'AMD', id FROM categories WHERE name = 'Processor'
UNION ALL SELECT 'NVIDIA', id FROM categories WHERE name = 'Graphics Card'
UNION ALL SELECT 'ASUS', id FROM categories WHERE name = 'Graphics Card'
UNION ALL SELECT 'MSI', id FROM categories WHERE name = 'Graphics Card'
UNION ALL SELECT 'ZOTAC', id FROM categories WHERE name = 'Graphics Card'
UNION ALL SELECT 'ASRock', id FROM categories WHERE name = 'Motherboard'
UNION ALL SELECT 'Gigabyte', id FROM categories WHERE name = 'Motherboard'
UNION ALL SELECT 'Corsair', id FROM categories WHERE name = 'RAM'
UNION ALL SELECT 'G.Skill', id FROM categories WHERE name = 'RAM'
UNION ALL SELECT 'Kingston', id FROM categories WHERE name = 'RAM'
UNION ALL SELECT 'Samsung', id FROM categories WHERE name = 'Storage'
UNION ALL SELECT 'WD', id FROM categories WHERE name = 'Storage'
UNION ALL SELECT 'Seagate', id FROM categories WHERE name = 'Storage'
UNION ALL SELECT 'Crucial', id FROM categories WHERE name = 'Storage'
UNION ALL SELECT 'Cooler Master', id FROM categories WHERE name = 'Power Supply'
UNION ALL SELECT 'Antec', id FROM categories WHERE name = 'Power Supply'
UNION ALL SELECT 'Corsair', id FROM categories WHERE name = 'Power Supply'
UNION ALL SELECT 'NZXT', id FROM categories WHERE name = 'Cabinet'
UNION ALL SELECT 'Lian Li', id FROM categories WHERE name = 'Cabinet'
UNION ALL SELECT 'DeepCool', id FROM categories WHERE name = 'Cooling System'
UNION ALL SELECT 'Noctua', id FROM categories WHERE name = 'Cooling System'
UNION ALL SELECT 'be quiet!', id FROM categories WHERE name = 'Cooling System'
UNION ALL SELECT 'Dell', id FROM categories WHERE name = 'Monitor'
UNION ALL SELECT 'LG', id FROM categories WHERE name = 'Monitor'
UNION ALL SELECT 'ASUS', id FROM categories WHERE name = 'Monitor'
UNION ALL SELECT 'Logitech', id FROM categories WHERE name = 'Keyboard'
UNION ALL SELECT 'Redragon', id FROM categories WHERE name = 'Keyboard'
UNION ALL SELECT 'Razer', id FROM categories WHERE name = 'Keyboard'
UNION ALL SELECT 'HP', id FROM categories WHERE name = 'Mouse'
UNION ALL SELECT 'Zebronics', id FROM categories WHERE name = 'Mouse'
UNION ALL SELECT 'Cosmic Byte', id FROM categories WHERE name = 'Mouse'

-- products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  stock INT DEFAULT 0,
  brand_id INT,
  category_id INT,
  image1 VARCHAR(255),
  image2 VARCHAR(255),
  image3 VARCHAR(255),
  image4 VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
);

ALTER TABLE products
  ADD sku VARCHAR(50) UNIQUE,
  ADD weight DECIMAL(6,2),
  ADD discount DECIMAL(6,2) DEFAULT 0,
  ADD rating FLOAT DEFAULT 0,
  ADD is_featured BOOLEAN DEFAULT 0,
  ADD is_active BOOLEAN DEFAULT 1,
  ADD slug VARCHAR(255) UNIQUE;


CREATE TABLE product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  image_path VARCHAR(255),
  is_main BOOLEAN DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


-- Insert sample product
SET @processor_id = (SELECT id FROM categories WHERE name = 'Processor');
SET @intel_brand_id = (SELECT id FROM brands WHERE name = 'Intel' LIMIT 1);

INSERT INTO products (name, description, price, stock, brand_id, category_id, image1, image2, image3, image4) VALUES
  ('Sample Product', 'This is a sample product.', 99.99, 10, @intel_brand_id, @processor_id, 'image1.jpg', 'image2.jpg', 'image3.jpg', 'image4.jpg');

CREATE TABLE product_specs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  spec_name VARCHAR(100),
  spec_value VARCHAR(255),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE product_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  customer_id INT,
  rating INT CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);






-- Customers table
CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  phone VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO customers (name, email, phone) VALUES
('John Doe', 'O7y0u@example.com', '123-456-7890'),
('Jane Doe', 'jane@doe', '987-654-3210'),
('Bob Smith', 'bob@smith', '555-555-5555'),
('Alice Johnson', 'alice@johnson', '777-777-7777'),
('Charlie Brown', 'charlie@brown', '888-888-8888');

CREATE TABLE addresses (
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

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  customer_name VARCHAR(150) NOT NULL,
  order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_amount DECIMAL(12,2) NOT NULL,
  status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
ALTER TABLE orders
  ADD payment_method ENUM('cod','card','upi','netbanking') DEFAULT 'cod',
  ADD address_id INT,
  ADD shipped_at DATETIME,
  ADD delivered_at DATETIME,
  ADD FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL;


INSERT INTO orders (customer_id,customer_name, total_amount) VALUES
(1, 'John Doe', 99.99),
(2, 'Jane Doe 2', 49.99),
(3, 'Bob Smith', 79.99),
(4, 'Charlie Brown', 59.99),
(5, 'Alice Johnson', 69.99);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample order items
SET @sample_product_id = (SELECT id FROM products WHERE name = 'Sample Product' LIMIT 1);

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, @sample_product_id, 1, 99.99),
(2, @sample_product_id, 1, 49.99),
(3, @sample_product_id, 1, 79.99),
(4, @sample_product_id, 1, 59.99),
(5, @sample_product_id, 1, 69.99);

CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT,
  product_id INT,
  quantity INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
