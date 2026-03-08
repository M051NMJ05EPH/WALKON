<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

echo "<div style='padding:40px; font-family:\"Outfit\", sans-serif; background:#0B0F19; color:white; min-height:100vh; line-height:1.6;'>";
echo "<h2 style='color:#10b981; border-bottom:1px solid #1f2937; padding-bottom:10px;'>WALKON System Synchronization</h2>";

try {
    // 0. Ensure Core Infrastructure exists (product_base is required for orders FK)
    $table_check = $pdo->query("SHOW TABLES LIKE 'product_base'")->rowCount();
    if (!$table_check) {
        echo "<p style='color:#fbbf24'>⚠ Core tables missing. Initializing from master schema...</p>";
        $sql = file_get_contents('walkon_database.sql');
        // Clean SQL of database creation commands to keep it in current DB
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
        $sql = preg_replace('/USE.*?;/is', '', $sql);
        $pdo->exec($sql);
        echo "✔ Base catalog structure created.<br>";
    }

    // 1. Ensure Sellers Table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS sellers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        business_name VARCHAR(150),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "✔ Sellers infrastructure verified.<br>";

    // 2. Repair Orders Table (Check for 'status' column)
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    $orderTableExists = $stmt->rowCount();
    $needsFix = false;

    if ($orderTableExists) {
        $cols = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('status', $cols) || !in_array('total_price', $cols)) {
            $needsFix = true;
        }
    } else {
        $needsFix = true;
    }

    if ($needsFix) {
        echo "<p style='color:#10b981'>⚙ Correcting orders schema...</p>";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS orders;");
        $pdo->exec("CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seller_id INT NOT NULL,
            product_id INT NOT NULL,
            customer_name VARCHAR(100),
            customer_email VARCHAR(100),
            customer_phone VARCHAR(20),
            shipping_address TEXT,
            channel VARCHAR(50) DEFAULT 'Direct',
            quantity INT NOT NULL,
            unit_price DECIMAL(10, 2) NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES product_base(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        echo "✔ Orders table synchronized.<br>";
    } else {
        echo "✔ Orders schema verified.<br>";
    }

    // 3. Sync Sellers from Users
    $stmt_users = $pdo->query("SELECT first_name, last_name, email FROM users");
    $sync_count = 0;
    while ($user = $stmt_users->fetch()) {
        $stmt_check = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_check->execute([$user['email']]);
        if (!$stmt_check->fetch()) {
            $name = $user['first_name'] . ' ' . $user['last_name'];
            $stmt_insert = $pdo->prepare("INSERT INTO sellers (name, email, password, business_name) VALUES (?, ?, 'social_login_or_legacy', ?)");
            $stmt_insert->execute([$name, $user['email'], $name . " Store"]);
            $sync_count++;
        }
    }
    if ($sync_count > 0) echo "✔ Synced $sync_count user(s) to seller profiles.<br>";

    // 4. Sample Data
    $count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    if ($count == 0) {
        $product = $pdo->query("SELECT pb.id, pb.seller_id, pp.price FROM product_base pb JOIN product_prices pp ON pb.id = pp.product_id LIMIT 1")->fetch();
        if ($product) {
            $stmt = $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, quantity, unit_price, total_price, channel, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$product['seller_id'], $product['id'], 'Mosin Joseph', 1, $product['price'], $product['price'], 'Amazon']);
            echo "✔ Sample order generated for testing.<br>";
        }
    }

    echo "<div style='margin-top:30px;'>";
    echo "<h3 style='color:#10b981'>System Ready!</h3>";
    echo "<p>All database tables are now synchronized with the premium UI requirements.</p>";
    echo "<a href='my_orders.php' style='display:inline-block; padding:12px 30px; background:#10b981; color:white; text-decoration:none; border-radius:50px; font-weight:700; margin-top:10px;'>Back to My Orders</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<h3 style='color:#ef4444'>Sync Interrupted</h3>";
    echo "<div style='background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); padding:20px; border-radius:15px; color:#f87171; margin-bottom:20px;'>";
    echo "<b>Error Details:</b> " . htmlspecialchars($e->getMessage());
    echo "</div>";
    echo "<p>Try refreshing this page. If the error persists, please verify your database connection in `config.php`.</p>";
}
echo "</div>";
?>
