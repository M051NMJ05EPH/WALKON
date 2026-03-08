-- Create Database
CREATE DATABASE IF NOT EXISTS walkon_shoes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE walkon_shoes;

-- Table 1: Sellers (for login/register)
CREATE TABLE sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    business_name VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table 2: Products (Main Product Table)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT,
    sku VARCHAR(50) UNIQUE,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    sizes VARCHAR(255),
    colors VARCHAR(255),
    category VARCHAR(100),
    subcategory VARCHAR(100),
    quantity INT DEFAULT 0,
    channels TEXT,                    -- Amazon, Flipkart, etc.
    publish_days INT DEFAULT 0,
    images TEXT,                      -- Comma-separated image URLs
    status ENUM('draft','published','scheduled') DEFAULT 'draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
);

-- Table 3: Product Images (Optional - for better structure)
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_url TEXT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert a test seller (for instant login)
INSERT INTO sellers (name, email, password, business_name) VALUES 
('Demo Seller', 'demo@walkon.com', 'pass123', 'WALKON Store');

-- Optional: Create index for faster search
CREATE INDEX idx_category ON products(category);
CREATE INDEX idx_sku ON products(sku);