-- Seed Data for Smart Pricing Logs
USE walkon_shoes;

-- 1. Ensure logs are clear for the demo
DELETE FROM smart_pricing_log WHERE seller_id = 1;

-- 2. Insert sample adjustments (Assume products 101, 102, 103 from previous seeds)
INSERT INTO smart_pricing_log (seller_id, product_id, old_price, new_price, created_at) VALUES 
(1, 101, 4999.00, 4850.00, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
(1, 102, 2499.00, 2550.00, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1, 103, 7999.00, 7800.00, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 101, 4850.00, 4900.00, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 104, 3499.00, 3600.00, DATE_SUB(NOW(), INTERVAL 3 HOUR));

SELECT 'Sample smart pricing logs seeded successfully.' AS Status;
