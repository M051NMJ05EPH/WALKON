-- Bulk Operations Database Schema
-- Table to track history of bulk actions performed on products

USE walkon_shoes;

CREATE TABLE IF NOT EXISTS bulk_operations_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    affected_count INT DEFAULT 0,
    action_value VARCHAR(255),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
    INDEX idx_seller_created (seller_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Bulk Operations log table created successfully.' AS Status;
