CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(150),
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

SET @proc_id      = (SELECT category_id FROM categories WHERE category_name = 'Processor');
SET @storage_id   = (SELECT category_id FROM categories WHERE category_name = 'Storage');
SET @cooling_id   = (SELECT category_id FROM categories WHERE category_name = 'Cooling System');
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
    main_image varchar(255) NOT NULL,
    image_1 varchar(255) NOT NULL,
    image_2 varchar(255) NOT NULL,
    image_3 varchar(255) NOT NULL,
    platform ENUM('intel','amd','both','none') NOT NULL DEFAULT 'none',
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id)     REFERENCES brands(brand_id)     ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_address (
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

CREATE TABLE IF NOT EXISTS product_specs (
    product_spec_id  INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    spec_name VARCHAR(100),
    spec_value VARCHAR(255),
    spec_group VARCHAR(80) DEFAULT NULL,
    display_order INT DEFAULT 0,
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


-- 📌 Category-wise Naming Convention
-- 1. Processor (CPU)

    -- Key Specs

        -- Cores

        -- Threads

        -- Base Clock

        -- Boost Clock

        -- TDP

    -- Detailed Specs

        -- Lithography

        -- Socket Type

        -- Cache (L2 / L3)

        -- Max Memory Support

        -- Integrated Graphics

        -- PCIe Version

-- 2. Graphics Card (GPU)

    -- Key Specs

        -- GPU Model

        -- VRAM Size

        -- VRAM Type

        -- Base Clock

        -- Boost Clock

    -- Detailed Specs

        -- CUDA / Stream / Core Count

        -- Memory Bus Width

        -- Memory Bandwidth

        -- Power Requirement (Wattage + Connector type)

        -- Outputs (HDMI, DP, etc.)

        -- Cooling Type (Dual / Triple Fan, Waterblock)

-- 3. Motherboard

    -- Key Specs

        -- Chipset

        -- Form Factor (ATX / mATX / ITX)

        -- Supported Socket

        -- Memory Slots (Max RAM + Speed)

    -- Detailed Specs

        -- PCIe Slots (x16, x4, etc.)

        -- Storage Support (SATA, M.2 count)

        -- Expansion Slots

        -- USB Ports (rear + internal)

        -- Networking (LAN / WiFi)

        -- Audio Codec

-- 4. RAM (Memory)

    -- Key Specs

        -- Capacity (GB)

        -- Type (DDR3 / DDR4 / DDR5)

        -- Speed (MHz)

        -- CAS Latency

    -- Detailed Specs

        -- Voltage

        -- ECC Support

        -- RGB Lighting

        -- Module Count (1×16GB / 2×8GB etc.)

        -- Heatsink Type

-- 5. Storage (HDD / SSD / NVMe)

    -- Key Specs

        -- Capacity

        -- Type (HDD, SATA SSD, NVMe)

        -- Interface (SATA, PCIe Gen3/4/5)

        -- Max Speed (Read / Write)

    -- Detailed Specs

        -- Endurance (TBW / MTBF)

        -- NAND Type (TLC, QLC)

        -- Cache Size

        -- Form Factor (2.5", M.2 2280, etc.)

-- 6. Power Supply (PSU)

    -- Key Specs

        -- Wattage

        -- Certification (80+ Bronze/Gold/Platinum)

        -- Form Factor (ATX, SFX)

        -- Modular Type (Non / Semi / Fully)

    -- Detailed Specs

        -- PCIe / CPU Connectors

        -- Protections (OCP, OVP, OTP, SCP, etc.)

        -- Dimensions

        -- Efficiency Curve

-- 7. Cooling (Air / Liquid)

    -- Key Specs

        -- Cooler Type (Air / AIO / Custom Loop)

        -- Fan Size (120mm, 140mm)

        -- Radiator Size (240 / 360mm)

        -- Max TDP Support

    -- Detailed Specs

        -- Fan Speed Range

        -- Bearing Type

        -- Noise Level (dBA)

        -- Pump Speed (if liquid)

        -- Socket Compatibility

-- 8. PC Case (Cabinet)

    -- Key Specs

        -- Case Type (ATX, mATX, ITX, Full Tower)

        -- Max GPU Length

        -- Max CPU Cooler Height

        -- PSU Support (ATX, SFX)

    -- Detailed Specs

        -- Drive Bays (2.5", 3.5")

        -- Fan / Radiator Support

        -- Expansion Slots

        -- Front I/O Ports

        -- Side Panel Type (Tempered Glass, Mesh)

-- 9. Monitor

    -- Key Specs

        -- Size (Inches)

        -- Resolution

        -- Refresh Rate

        -- Panel Type (IPS, VA, TN, OLED)

    -- Detailed Specs

        -- Response Time (ms)

        -- HDR Support

        -- Color Gamut (sRGB, DCI-P3 %)

        -- Connectivity (HDMI, DP, USB-C)

        -- Adaptive Sync (G-Sync / FreeSync)