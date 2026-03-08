-- 1. Brands Table
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    logo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Sub Categories Table
CREATE TABLE IF NOT EXISTS sub_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed Sub Categories (Target Groups)
-- Disabling FK checks to allow truncation if table is referenced
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE sub_categories;

-- Inserting Demographic Target Groups as Sub Categories
-- Linking to Category ID 1 (Assuming 'Shoes' or main category)
INSERT INTO sub_categories (category_id, name) VALUES 
(1, 'Men'), (1, 'Women'), (1, 'Boys'), (1, 'Girls'), 
(1, 'Kids'), (1, 'Babies'), (1, 'Unisex');
SET FOREIGN_KEY_CHECKS = 1;

-- 4. Product Specs Table (One-to-One with product_base)
CREATE TABLE IF NOT EXISTS product_specs (
    product_id INT PRIMARY KEY,
    brand_id INT,
    gender VARCHAR(50),
    heel_height VARCHAR(50),
    outer_material VARCHAR(100),
    season VARCHAR(100),
    shoe_type VARCHAR(100),
    occasion VARCHAR(100),
    FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Seed Brands (if empty)
INSERT IGNORE INTO brands (name) VALUES 
('Nike'), ('Adidas'), ('Puma'), ('Reebok'), ('Vans'), ('Converse'), 
('New Balance'), ('Under Armour'), ('Skechers'), ('ASICS'),
('Timberland'), ('Dr. Martens'), ('Clarks'), ('Crocs'), ('Bata');

-- 6. Materials Table
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Seed Materials
INSERT IGNORE INTO materials (name) VALUES 
('Leather'), ('Suede'), ('Canvas'), ('Mesh'), ('Synthetic'), 
('Rubber'), ('Velvet'), ('Denim'), ('Cotton'), ('Polyester');
