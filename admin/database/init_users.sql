-- database/init_users.sql

-- CREATE DATABASE IF NOT EXISTS pcparts_db;
-- USE pcparts_db;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,    -- store password_hash()
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin user
-- password is 'admin123' → hashed via PHP password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$tolsuyCpvyhx.sokC.N8U.ujGX/BX0JcrrlLuCIu17k12t.PMkJj.', 'admin');

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
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  stock INT DEFAULT 0,
  brand_id INT,
   brand VARCHAR(100) NOT NULL,
  category_id INT,
  category VARCHAR(100) NOT NULL,
  image1 VARCHAR(255),
  image2 VARCHAR(255),
  image3 VARCHAR(255),
  image4 VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (brand_id) REFERENCES brands(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);



INSERT INTO products (name, description, price, category, brand, stock, image1, image2)
VALUES (
  'Intel Core i9-13900K',
  'Powerful 24-core processor with ultra-high frequency for gaming and productivity. Compatible with latest Z790 motherboards. Excellent thermal performance.',
  58999.99,
  'CPU',
  'Intel',
  25,
  'i9-13900K-front.jpg',
  'i9-13900K-back.jpg'
),
(
  'AMD Ryzen 9 5950X',
  'High-performance 32-core processor for high-end gaming and productivity. Compatible with latest Z790 motherboards. Excellent thermal performance.',
  79999.99,
  'CPU',
  'AMD',
  15,
  'ryzen-9-5950X-front.jpg',
  'ryzen-9-5950X-back.jpg'
),
(
  'NVIDIA RTX 3090',
  'Powerful graphics card for gaming and productivity. Compatible with latest Z790 motherboards. Excellent thermal performance.',
  129999.99,
  'GPU',
  'NVIDIA',
  10,
  'rtx3090-front.jpg',
  'rtx3090-back.jpg'
),
(
  'Intel Core i9-14900K',
  'Powerful 24-core processor with ultra-high frequency for gaming and productivity. Compatible with latest Z790 motherboards. Excellent thermal performance.',
  58999.99,
  'CPU',
  'Intel',
  25,
  'i9-14900K-front.jpg',
  'i9-14900K-back.jpg'
);


-- brands table
CREATE TABLE brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- categories table
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);
INSERT INTO brands (name) VALUES ('Intel'), ('AMD'), ('NVIDIA');
INSERT INTO categories (name) VALUES ('CPU'), ('GPU'), ('Motherboard');
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