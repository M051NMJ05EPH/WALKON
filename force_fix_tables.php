<?php
include 'config.php';

function force_repair_table($pdo, $tableName, $createSqlInnoDB) {
    echo "<h3>Repairing $tableName...</h3>";
    
    // 1. Try generic drop
    try {
        $pdo->exec("DROP TABLE IF EXISTS $tableName");
    } catch (Exception $e) { echo "Drop failed (ignore): " . $e->getMessage() . "<br>"; }

    // 2. Try creating as MyISAM (doesn't use .ibd)
    // This often fixes the dictionary mismatch
    $createSqlMyISAM = str_replace("ENGINE=InnoDB", "ENGINE=MyISAM", $createSqlInnoDB);
    $createSqlMyISAM = str_replace("FOREIGN KEY", "-- FOREIGN KEY", $createSqlMyISAM); // Remove FKs for MyISAM simplicity
    
    try {
        $pdo->exec($createSqlMyISAM);
        echo "Created as MyISAM (Temp fix)...<br>";
        
        // 3. Drop the MyISAM table
        $pdo->exec("DROP TABLE $tableName");
        echo "Dropped MyISAM version...<br>";
    } catch (Exception $e) {
        echo "⚠️ MyISAM trick failed: " . $e->getMessage() . "<br>";
    }

    // 4. Create as InnoDB
    try {
        $pdo->exec($createSqlInnoDB);
        echo "<b style='color:green'>✅ Successfully restored $tableName as InnoDB</b><br>";
    } catch (Exception $e) {
        echo "<b style='color:red'>❌ Failed to create InnoDB: " . $e->getMessage() . "</b><br>";
    }
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // --- SELLERS ---
    $sql_sellers = "CREATE TABLE sellers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        business_name VARCHAR(150),
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'sellers', $sql_sellers);

    // --- CATEGORIES ---
    $sql_categories = "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image_url VARCHAR(500),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'categories', $sql_categories);

    // --- SUB CATEGORIES ---
    $sql_subcats = "CREATE TABLE sub_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'sub_categories', $sql_subcats);
    
    // --- BRANDS ---
    $sql_brands = "CREATE TABLE brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        logo_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'brands', $sql_brands);

    // --- PRODUCT BASE ---
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
    force_repair_table($pdo, 'product_base', $sql_products);

    // --- PRODUCT PRICES ---
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
    force_repair_table($pdo, 'product_prices', $sql_prices);

    // --- PRODUCT MEDIA ---
    $sql_media = "CREATE TABLE product_media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        url VARCHAR(500) NOT NULL,
        type ENUM('image', 'video') DEFAULT 'image',
        is_primary BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'product_media', $sql_media);

   // --- PRODUCT SKUS ---
    $sql_skus = "CREATE TABLE product_skus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        sku VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'product_skus', $sql_skus);

    // --- PRODUCT STOCK ---
    $sql_stock = "CREATE TABLE product_stock (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'product_stock', $sql_stock);

    // --- ORDERS ---
    $sql_orders = "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT 0,
        seller_id INT NOT NULL,
        product_id INT NOT NULL,
        customer_name VARCHAR(100),
        customer_email VARCHAR(100),
        total_price DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        payment_status VARCHAR(20) DEFAULT 'unpaid',
        channel VARCHAR(50) DEFAULT 'Website',
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'orders', $sql_orders);

    // --- WALLETS ---
    $sql_wallets = "CREATE TABLE wallets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        balance DECIMAL(15, 2) DEFAULT 0.00,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'wallets', $sql_wallets);

    // --- WALLET TRANSACTIONS ---
    $sql_wallet_trans = "CREATE TABLE wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        wallet_id INT NOT NULL,
        order_id INT,
        type ENUM('credit', 'debit') NOT NULL,
        amount DECIMAL(15, 2) NOT NULL,
        commission_deducted DECIMAL(15, 2) DEFAULT 0.00,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    force_repair_table($pdo, 'wallet_transactions', $sql_wallet_trans);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

} catch (PDOException $e) {
    echo "Main Error: " . $e->getMessage();
}
?>
