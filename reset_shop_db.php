<?php
include 'config.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h1>Starting Hard Reset (Drop & Recreate)...</h1>";

    // Disable Foreign Key Checks to allow dropping
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // List of tables to reset
    $tables = [
        'product_skus',
        'product_specs',
        'product_media',
        'product_prices',
        'product_base',
        'sub_categories',
        'categories',
        'sellers',
        'brands'
    ];

    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
        echo "🗑️ Dropped table: $table<br>";
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // --- RE-CREATE TABLES ---

    // 1. Create Sellers Table
    $sql_sellers = "CREATE TABLE sellers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        business_name VARCHAR(150),
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_sellers);
    echo "✅ Created: sellers<br>";

    // 2. Create Categories Table
    $sql_categories = "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image_url VARCHAR(500),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_categories);
    echo "✅ Created: categories<br>";

    // 3. Create Sub-Categories Table
    $sql_subcats = "CREATE TABLE sub_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_subcats);
    echo "✅ Created: sub_categories<br>";

    // 4. Create Brands Table
    $sql_brands = "CREATE TABLE brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        logo_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_brands);
    echo "✅ Created: brands<br>";

    // 5. Create Product Base Table
    $sql_products = "CREATE TABLE product_base (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        category_id INT,
        sub_category_id INT,
        name VARCHAR(255) NOT NULL,
        status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_products);
    echo "✅ Created: product_base<br>";

    // 6. Create Product Prices Table
    $sql_prices = "CREATE TABLE product_prices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        min_price DECIMAL(10, 2),
        max_price DECIMAL(10, 2),
        smart_pricing_status BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_prices);
    echo "✅ Created: product_prices<br>";

    // 7. Create Product Media Table
    $sql_media = "CREATE TABLE product_media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        url VARCHAR(500) NOT NULL,
        type ENUM('image', 'video') DEFAULT 'image',
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_media);
    echo "✅ Created: product_media<br>";

    // 8. Create Product Specs (Brand links)
     $sql_specs = "CREATE TABLE product_specs (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_specs);
    echo "✅ Created: product_specs<br>";
    
    // 9. Create Product SKUs
    $sql_skus = "CREATE TABLE product_skus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        sku VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql_skus);
    echo "✅ Created: product_skus<br>";

    echo "<h3>Hard Reset Complete - DB Clean!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
