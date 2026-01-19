-- Seed Data for Daily Sales Analytics
USE walkon_shoes;

-- Clear previous data for seller 1
DELETE FROM daily_sales_analytics WHERE seller_id = 1;

-- Seed last 10 days of varied data
INSERT INTO daily_sales_analytics (seller_id, recorded_date, total_revenue, total_orders, units_sold) VALUES 
(1, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 12500.50, 5, 8),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 8900.00, 3, 4),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 15400.75, 7, 10),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 4500.25, 2, 2),
(1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 22100.00, 9, 14),
(1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 11200.50, 4, 6),
(1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 9800.00, 3, 4),
(1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 18700.25, 8, 11),
(1, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 13400.50, 5, 7),
(1, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 7600.00, 3, 3);

-- Add some older data for seller 2
INSERT INTO daily_sales_analytics (seller_id, recorded_date, total_revenue, total_orders, units_sold) VALUES 
(2, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 5000.00, 2, 2),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 4200.00, 1, 1);

SELECT 'Sample sales data seeded successfully.' AS Status;
