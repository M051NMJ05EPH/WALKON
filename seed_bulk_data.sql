-- Seed Data for Bulk Operations & Products
USE walkon_shoes;

-- 1. Ensure a seller exists (assuming ID 1)
-- (Sellers usually exist from login, but we ensure one for the seeds)

-- 2. Add Sample Products to Normalized Tables
INSERT INTO product_base (id, seller_id, name, category_id, sub_category_id, status, created_at) VALUES 
(101, 1, 'Velocity Runner X', 1, 1, 'published', NOW()),
(102, 1, 'CloudWalk Sneakers', 1, 2, 'published', NOW()),
(103, 1, 'Urban Trek Boots', 1, 2, 'draft', NOW()),
(104, 1, 'Elite Court Pro', 1, 1, 'published', NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 3. Add SKU info
INSERT INTO product_skus (product_id, sku) VALUES 
(101, 'VRX-001'),
(102, 'CWS-002'),
(103, 'UTB-003'),
(104, 'ECP-004')
ON DUPLICATE KEY UPDATE sku=VALUES(sku);

-- 4. Add Prices
INSERT INTO product_prices (product_id, price, min_price, max_price, smart_pricing_status) VALUES 
(101, 4999.00, 4000.00, 6000.00, 1),
(102, 2499.00, 2000.00, 3000.00, 0),
(103, 7999.00, 7000.00, 9000.00, 1),
(104, 3499.00, 3000.00, 4000.00, 0)
ON DUPLICATE KEY UPDATE price=VALUES(price);

-- 5. Add Stock
INSERT INTO product_stock (product_id, quantity) VALUES 
(101, 50), (102, 120), (103, 15), (104, 85)
ON DUPLICATE KEY UPDATE quantity=VALUES(quantity);

-- 6. Seed Bulk Operation Logs
INSERT INTO bulk_operations_log (seller_id, action_type, affected_count, action_value, created_at) VALUES 
(1, 'set_price', 15, '2999', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'status_active', 8, '', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(1, 'price_percentage', 22, '10', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'set_stock', 12, '100', DATE_SUB(NOW(), INTERVAL 2 DAY));

SELECT 'Sample data for Bulk Operations seeded successfully.' AS Status;
