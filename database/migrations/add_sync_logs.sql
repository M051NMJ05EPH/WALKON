-- Product Sync Database Schema
-- Tracks synchronization history for products in the normalized schema

USE walkon_shoes;

CREATE TABLE IF NOT EXISTS product_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    channel VARCHAR(50) NOT NULL,
    status ENUM('pending', 'success', 'error', 'failed') DEFAULT 'pending',
    message TEXT,
    sync_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE,
    INDEX idx_seller_product (seller_id, product_id),
    INDEX idx_channel_status (channel, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Product Sync logs table created successfully.' AS Status;
