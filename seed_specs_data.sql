-- Seed Data for Product Specifications (Corrected IDs)
USE walkon_shoes;

-- 1. Ensure specifications are clear for these products
DELETE FROM product_specs WHERE product_id IN (101, 102, 103, 104);

-- 2. Insert specifications for featured products
-- Brand IDs: 1 (Nike), 2 (Adidas), 3 (PUMA), 16 (Timberland)
INSERT INTO product_specs (product_id, brand_id, gender, heel_height, outer_material, season, shoe_type, occasion) VALUES 
(101, 3, 'Men', '30mm', 'Mesh', 'All Season', 'Running Shoes', 'Sports'),
(102, 1, 'Unisex', '20mm', 'Leather', 'Summer', 'Sneakers', 'Casual'),
(103, 16, 'Men', '40mm', 'Leather', 'Winter', 'Boots', 'Outdoor'),
(104, 1, 'Men', '25mm', 'Synthetic', 'All Season', 'Tennis Shoes', 'Professional');

SELECT 'Sample product specifications seeded successfully.' AS Status;
