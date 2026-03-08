-- Seed Data for Sync Logs
USE walkon_shoes;

-- 1. Get sample product IDs (assume 101-104 from previous seed)
-- Clear previous ones for clean demo if needed
DELETE FROM product_sync_logs;

-- 2. Insert mix of statuses for demonstration
-- Velocity Runner X (101) - Mixed
INSERT INTO product_sync_logs (seller_id, product_id, channel, status, message) VALUES 
(1, 101, 'Amazon', 'success', 'Synced successfully'),
(1, 101, 'Shopify', 'pending', 'Awaiting channel response'),
(1, 101, 'eBay', 'error', 'Invalid API Key');

-- CloudWalk Sneakers (102) - All Success
INSERT INTO product_sync_logs (seller_id, product_id, channel, status) VALUES 
(1, 102, 'Amazon', 'success'),
(1, 102, 'Shopify', 'success'),
(1, 102, 'Instagram', 'success'),
(1, 102, 'TikTok Shop', 'success');

-- Urban Trek Boots (103) - Pending
INSERT INTO product_sync_logs (seller_id, product_id, channel, status) VALUES 
(1, 103, 'Amazon', 'pending'),
(1, 103, 'eBay', 'pending');

SELECT 'Sample sync logs seeded.' AS Status;
