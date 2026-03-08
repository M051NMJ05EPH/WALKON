-- ============================================
-- WalkOn Shoes - Normalized Product Schema
-- ============================================

USE walkon_shoes;

-- 1. Create Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create Sub-Categories Table
CREATE TABLE IF NOT EXISTS sub_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_sub (category_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create Product Details Table
CREATE TABLE IF NOT EXISTS product_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL UNIQUE,
    description TEXT,
    images JSON,
    primary_image_url VARCHAR(500),
    specifications JSON,
    brand VARCHAR(100),
    material VARCHAR(100),
    color VARCHAR(100),
    size_chart VARCHAR(255),
    warranty_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Initial Data for Categories
INSERT IGNORE INTO categories (name, slug) VALUES 
('Sneakers', 'sneakers'),
('Boots', 'boots'),
('Sandals', 'sandals'),
('Formal', 'formal'),
('Sports', 'sports');

-- 5. Initial Data for Sub-Categories (linked to all categories for now as per current UI)
-- In a real scenario, sub-categories might be specific, but the UI shows Men/Women/Kids/Unisex for everything.
INSERT IGNORE INTO sub_categories (category_id, name, slug) 
SELECT id, 'Men', 'men' FROM categories;
INSERT IGNORE INTO sub_categories (category_id, name, slug) 
SELECT id, 'Women', 'women' FROM categories;
INSERT IGNORE INTO sub_categories (category_id, name, slug) 
SELECT id, 'Kids', 'kids' FROM categories;
INSERT IGNORE INTO sub_categories (category_id, name, slug) 
SELECT id, 'Unisex', 'unisex' FROM categories;

-- 6. Update Products Table Structure
ALTER TABLE products 
ADD COLUMN category_id INT AFTER seller_id,
ADD COLUMN sub_category_id INT AFTER category_id;

-- (Indexes)
ALTER TABLE products
ADD INDEX idx_category_id (category_id),
ADD INDEX idx_sub_category_id (sub_category_id);

-- 7. Add Constraints (Commented out until data migration is done)
-- ALTER TABLE products ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;
-- ALTER TABLE products ADD FOREIGN KEY (sub_category_id) REFERENCES sub_categories(id) ON DELETE SET NULL;
