-- Smart Pricing Log Database Schema
-- Tracks automated price adjustments

USE walkon_shoes;

CREATE TABLE IF NOT EXISTS smart_pricing_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    old_price DECIMAL(10, 2),
    new_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE,
    INDEX idx_seller_date (seller_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Smart Pricing Log table created successfully.' AS Status;
