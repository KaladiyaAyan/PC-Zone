-- database/init_users.sql

-- CREATE DATABASE IF NOT EXISTS pczone;
-- USE pczone;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert a default admin user
-- password is 'admin123' → hashed via PHP password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$a0UCxwvfzbhYFWtq7C4MIuGqwPNSoeCQ6A4ZcWLEjrqaHR2dPwXPG', 'admin');

-- Insert a default user
-- password is 'user123' → hashed via PHP password_hash('user123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES
('user', '$2y$10$e0NRPqRWPvYdXQFSEaZdmaeE2VJ7/GRiIixJKM6pXfv2e6zxrio4e', 'user');



-- Products table
-- CREATE TABLE IF NOT EXISTS products (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(255) NOT NULL,
--   description TEXT,
--   brand VARCHAR(100),
--   model_number VARCHAR(100),
--   category VARCHAR(100) NOT NULL,
--   stock INT NOT NULL DEFAULT 0,
--   price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- );
-- INSERT INTO products (name, description, brand, model_number, category, stock, price) VALUES
-- ('Sample Product', 'This is a sample product.', 'Sample Brand', '1234', 'Sample Category', 10, 99.99),
-- ('Sample Product 2', 'This is another sample product.', 'Sample Brand 2', '5678', 'Sample Category 2', 5, 49.99),
-- ('Sample Product 3', 'This is yet another sample product.', 'Sample Brand 3', '9012', 'Sample Category 3', 15, 79.99),
-- ('Sample Product 4', 'This is the last sample product.', 'Sample Brand 4', '3456', 'Sample Category 4', 8, 59.99),
-- ('Sample Product 5', 'This is the final sample product.', 'Sample Brand 5', '7890', 'Sample Category 5', 12, 69.99);


-- products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  stock INT DEFAULT 0,
  brand_id INT,
  --  brand VARCHAR(100) NOT NULL,
  category_id INT,
  -- category VARCHAR(100) NOT NULL,
  image1 VARCHAR(255),
  image2 VARCHAR(255),
  image3 VARCHAR(255),
  image4 VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
);

INSERT INTO products (name, description, price, stock, brand_id, category_id, image1, image2, image3, image4) VALUES
  ('Sample Product', 'This is a sample product.', 99.99, 10, 1, 1, 'image1.jpg', 'image2.jpg', 'image3.jpg', 'image4.jpg');
-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Create brands table with relation to categories
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insert categories
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
('Mouse'),
('Headphone'),
('Laptop'),
('Motherboard Combo');

-- Insert brands with corresponding category_id (assuming auto-increment matches order above)
INSERT INTO brands (name, category_id) VALUES
('Intel', 1),
('AMD', 1),
('NVIDIA', 2),
('ASUS', 2),
('MSI', 2),
('ZOTAC', 2),
('ASRock', 3),
('Gigabyte', 3),
('Corsair', 4),
('G.Skill', 4),
('Kingston', 4),
('Samsung', 5),
('WD', 5),
('Seagate', 5),
('Crucial', 5),
('Cooler Master', 6),
('Antec', 6),
('Corsair', 6),
('NZXT', 7),
('Lian Li', 7),
('DeepCool', 8),
('Noctua', 8),
('be quiet!', 8),
('Dell', 9),
('LG', 9),
('ASUS', 9),
('Logitech', 10),
('Redragon', 10),
('Razer', 10),
('HP', 11),
('Zebronics', 11),
('Cosmic Byte', 11),
('Boat', 12),
('JBL', 12),
('Sony', 12),
('Lenovo', 13),
('Acer', 13),
('ASUS', 13),
('Intel + ASUS', 14),
('AMD + MSI', 14);


-- CREATE TABLE categories (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(100) NOT NULL UNIQUE
-- );

-- CREATE TABLE brands (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(100) NOT NULL UNIQUE
-- );

-- INSERT INTO categories (name) VALUES ('Processor'), ('Graphics Card'), ('Motherboard'), ('Ram'), ('SSD'), ('HDD'), ('Power Supply'), ('Cabinet'), ('CPU Cooler'), ('Case Fan'), ('Monitor'), ('Keyboard'), ('Mouse'), ('Thermal Paste'), ('Capture Card'), ('Sound Card'), ('Networking Card');
-- INSERT INTO brands (name) VALUES ('Intel'), ('AMD'), ('NVIDIA'), ('ASUS'), ('MSI'), ('Gigabyte'), ('Corsair'), ('Cooler Master'), ('Zotac'), ('Kingston'), ('G.Skill'), ('Samsung'), ('Seagate'), ('Western Digital'), ('NZXT'), ('Antec'), ('Thermaltake'), ('DeepCool'), ('Razer'), ('Logitech'), ('Adata'), ('HyperX');
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

INSERT INTO orders (customer_id,customer_name, total_amount) VALUES
(1, 'John Doe', 99.99),
(2, 'Jane Doe 2', 49.99),
(3, 'Bob Smith', 79.99),
(4, 'Charlie Brown', 59.99),
(5, 'Alice Johnson', 69.99);

-- Order items (many-to-many between orders & products)
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 99.99),
(2, 2, 1, 49.99),
(3, 3, 1, 79.99),
(4, 4, 1, 59.99),
(5, 5, 1, 69.99);