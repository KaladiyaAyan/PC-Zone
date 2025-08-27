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
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT DEFAULT NULL,
    level INT DEFAULT 0,
    slug VARCHAR(250) UNIQUE,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL,
    category_id INT,
    slug VARCHAR(250) UNIQUE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);
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
CREATE TABLE IF NOT EXISTS product_images (
    product_image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_path VARCHAR(255),
    is_main BOOLEAN DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS product_specs (
    product_spec_id  INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    spec_name VARCHAR(100),
    spec_value VARCHAR(255),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);
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
CREATE TABLE IF NOT EXISTS cart_items (
    cart_item_id  INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(product_id)  ON DELETE CASCADE
);