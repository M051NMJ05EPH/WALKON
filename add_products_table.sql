-- ============================================
-- WalkOn Shoes - Products Table Setup
-- ============================================
-- This script creates the products table for managing shoe listings
-- Run this in phpMyAdmin or MySQL command line

USE walkon_shoes;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    min_price DECIMAL(10, 2),
    max_price DECIMAL(10, 2),
    quantity INT DEFAULT 0,
    channels TEXT COMMENT 'Comma-separated list of channels (e.g., Amazon,eBay,Shopify)',
    images TEXT COMMENT 'JSON array of image URLs',
    image_url VARCHAR(500) COMMENT 'Primary product image',
    name VARCHAR(255) COMMENT 'Alias for product_name for backward compatibility',
    stock INT COMMENT 'Alias for quantity for backward compatibility',
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    smart_pricing_status BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    sales INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_seller_id (seller_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample products
INSERT INTO products (
    seller_id, 
    product_name, 
    name,
    sku, 
    description, 
    category, 
    price, 
    min_price, 
    max_price, 
    quantity, 
    stock,
    channels, 
    images,
    image_url,
    status,
    smart_pricing_status
) VALUES
(
    1,
    'Nike Air Max 270',
    'Nike Air Max 270',
    'NIKE-AM270-BLK-10',
    'Premium running shoes with Air Max cushioning technology. Perfect for daily wear and athletic activities.',
    'Running Shoes',
    129.99,
    99.99,
    149.99,
    50,
    50,
    'Amazon,eBay,Shopify',
    '["https://via.placeholder.com/400x400/1976D2/FFFFFF?text=Nike+Air+Max+270"]',
    'https://via.placeholder.com/400x400/1976D2/FFFFFF?text=Nike+Air+Max+270',
    'active',
    TRUE
),
(
    1,
    'Adidas Ultraboost 22',
    'Adidas Ultraboost 22',
    'ADIDAS-UB22-WHT-9',
    'Revolutionary running shoes with Boost technology for maximum energy return and comfort.',
    'Running Shoes',
    180.00,
    150.00,
    200.00,
    30,
    30,
    'Amazon,Shopify',
    '["https://via.placeholder.com/400x400/00796B/FFFFFF?text=Adidas+Ultraboost"]',
    'https://via.placeholder.com/400x400/00796B/FFFFFF?text=Adidas+Ultraboost',
    'active',
    TRUE
),
(
    1,
    'Puma RS-X Sneakers',
    'Puma RS-X Sneakers',
    'PUMA-RSX-RED-8',
    'Retro-inspired sneakers with bold colors and chunky silhouette. Perfect for streetwear fashion.',
    'Casual Shoes',
    110.00,
    90.00,
    130.00,
    25,
    25,
    'eBay,Shopify',
    '["https://via.placeholder.com/400x400/D32F2F/FFFFFF?text=Puma+RS-X"]',
    'https://via.placeholder.com/400x400/D32F2F/FFFFFF?text=Puma+RS-X',
    'active',
    FALSE
),
(
    2,
    'New Balance 990v5',
    'New Balance 990v5',
    'NB-990V5-GRY-10.5',
    'Classic American-made running shoes with premium suede and mesh construction.',
    'Running Shoes',
    175.00,
    160.00,
    190.00,
    40,
    40,
    'Amazon,eBay',
    '["https://via.placeholder.com/400x400/757575/FFFFFF?text=New+Balance+990"]',
    'https://via.placeholder.com/400x400/757575/FFFFFF?text=New+Balance+990',
    'active',
    TRUE
),
(
    2,
    'Converse Chuck Taylor All Star',
    'Converse Chuck Taylor All Star',
    'CONV-CT-BLK-9',
    'Iconic canvas sneakers that never go out of style. Available in classic black.',
    'Casual Shoes',
    55.00,
    45.00,
    65.00,
    100,
    100,
    'Amazon,eBay,Shopify',
    '["https://via.placeholder.com/400x400/212121/FFFFFF?text=Converse+Chuck"]',
    'https://via.placeholder.com/400x400/212121/FFFFFF?text=Converse+Chuck',
    'active',
    FALSE
);

-- Success message
SELECT 'Products table created successfully!' AS Status;
SELECT COUNT(*) AS 'Total Products' FROM products;
