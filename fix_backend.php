<?php
include 'config.php';

try {
    // List of tables to check/create
    $tables = [
        "orders" => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            product_id INT NOT NULL,
            customer_name VARCHAR(100),
            customer_email VARCHAR(100),
            customer_phone VARCHAR(20),
            shipping_address TEXT,
            quantity INT NOT NULL,
            unit_price DECIMAL(10, 2) NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            channel VARCHAR(50),
            order_status VARCHAR(50) DEFAULT 'pending',
            payment_status VARCHAR(50) DEFAULT 'pending',
            tracking_number VARCHAR(100),
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            shipped_date DATETIME,
            delivered_date DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_seller (seller_id),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        
        "sellers" => "CREATE TABLE IF NOT EXISTS sellers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            business_name VARCHAR(150),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        echo "Table '$name' checked/created.<br>";
    }

    // --- NEW: Sync Users to Sellers ---
    $stmt_users = $pdo->query("SELECT first_name, last_name, email FROM users");
    $users = $stmt_users->fetchAll();
    
    foreach ($users as $user) {
        $stmt_check = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_check->execute([$user['email']]);
        if (!$stmt_check->fetch()) {
            $name = $user['first_name'] . ' ' . $user['last_name'];
            $stmt_insert = $pdo->prepare("INSERT INTO sellers (name, email, password) VALUES (?, ?, 'social_login_or_legacy')");
            $stmt_insert->execute([$name, $user['email']]);
            echo "Auto-registed user '{$name}' as a seller.<br>";
        }
    }

    // --- NEW: Fix orphaned products (null seller_id) ---
    $first_seller = $pdo->query("SELECT id FROM sellers LIMIT 1")->fetchColumn();
    if ($first_seller) {
        $stmt_fix_prod = $pdo->prepare("UPDATE products SET seller_id = ? WHERE seller_id IS NULL");
        $stmt_fix_prod->execute([$first_seller]);
        $fixed_count = $stmt_fix_prod->rowCount();
        if ($fixed_count > 0) {
            echo "Fixed $fixed_count orphaned products by assigning them to the first seller.<br>";
        }
    }

    // Add some sample order data if empty
    $count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    if ($count == 0) {
        // Need a product and seller first
        $product = $pdo->query("SELECT id, seller_id, price FROM products WHERE seller_id IS NOT NULL LIMIT 1")->fetch();
        if ($product && $product['seller_id']) {
            $stmt = $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, quantity, unit_price, total_amount, channel, payment_status, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product['seller_id'], $product['id'], 'Mosin Joseph', 1, $product['price'], $product['price'], 'Amazon', 'paid', 'completed']);
            echo "Sample order added.<br>";
        } else {
            echo "Warning: No valid products found. Please add a product with a seller first to see sample orders.<br>";
        }
    }

    echo "<br><b>Database backend setup and sync complete!</b><br>";
    echo "<a href='my_orders.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Back to Orders</a>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
