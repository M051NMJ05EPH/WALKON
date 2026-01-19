-- Sales Analytics Database Schema
-- Table to store aggregated daily performance metrics

USE walkon_shoes;

CREATE TABLE IF NOT EXISTS daily_sales_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    recorded_date DATE NOT NULL,
    total_revenue DECIMAL(15, 2) DEFAULT 0.00,
    total_orders INT DEFAULT 0,
    units_sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seller_date (seller_id, recorded_date),
    INDEX idx_seller_date (seller_id, recorded_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Daily Sales Analytics table created successfully.' AS Status;
