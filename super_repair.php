<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

echo "<h2>WALKON Super Repair & Diagnostics</h2>";

try {
    // 1. Check current database
    $current_db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Current Database: <b>$current_db</b><br><br>";

    // 2. Ensure SELLERS table is current
    echo "Checking 'sellers' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS sellers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        business_name VARCHAR(150),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK.<br>";

    // 3. Ensure ORDERS table matches my_orders.php expectations
    echo "Checking 'orders' table schema... ";
    $tableExists = $pdo->query("SHOW TABLES LIKE 'orders'")->rowCount();
    
    if ($tableExists) {
        $cols = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_COLUMN);
        $needs_recreate = false;
        
        // my_orders.php expects 'status' and 'total_price'
        if (!in_array('status', $cols)) $needs_recreate = true;
        if (!in_array('total_price', $cols)) $needs_recreate = true;
        
        if ($needs_recreate) {
            echo "Schema mismatch detected. Recreating 'orders' table... ";
            $pdo->exec("DROP TABLE IF EXISTS orders;");
            $tableExists = false; // Trigger creation below
        }
    }

    if (!$tableExists) {
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        echo "Created with correct schema.<br>";
    } else {
        echo "Already correct.<br>";
    }

    // 4. Sync Users to Sellers
    echo "Syncing users to sellers... ";
    $stmt_users = $pdo->query("SELECT first_name, last_name, email FROM users");
    $users = $stmt_users->fetchAll();
    $new_sellers = 0;
    foreach ($users as $user) {
        $stmt_check = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_check->execute([$user['email']]);
        if (!$stmt_check->fetch()) {
            $name = $user['first_name'] . ' ' . $user['last_name'];
            $stmt_insert = $pdo->prepare("INSERT INTO sellers (name, email, password, business_name) VALUES (?, ?, 'social_login_or_legacy', ?)");
            $stmt_insert->execute([$name, $user['email'], $name . " Store"]);
            $new_sellers++;
        }
    }
    echo "$new_sellers new sellers added.<br>";

    // 5. Check dependencies for sample data
    echo "Checking 'product_base' for dependencies... ";
    $pb_exists = $pdo->query("SHOW TABLES LIKE 'product_base'")->rowCount();
    $pp_exists = $pdo->query("SHOW TABLES LIKE 'product_prices'")->rowCount();
    
    if (!$pb_exists || !$pp_exists) {
        echo "<b>ERROR: product_base or product_prices missing!</b> Running core database setup... ";
        // Import basic normalized structure if missing
        $sql = file_get_contents('walkon_database.sql');
        // We need to strip the CREATE DATABASE/USE commands to stay in current DB
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
        $sql = preg_replace('/USE.*?;/is', '', $sql);
        $pdo->exec($sql);
        echo "Done.<br>";
    } else {
        echo "OK.<br>";
    }

    // 6. Add sample data if orders empty
    $orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    if ($orderCount == 0) {
        echo "Seeding sample order... ";
        $product = $pdo->query("SELECT pb.id, pb.seller_id, pp.price 
                               FROM product_base pb 
                               JOIN product_prices pp ON pb.id = pp.product_id 
                               LIMIT 1")->fetch();
        
        if ($product) {
            $stmt = $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, quantity, unit_price, total_price, channel, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$product['seller_id'], $product['id'], 'Sample Customer', 1, $product['price'], $product['price'], 'Amazon']);
            echo "Success.<br>";
        } else {
            echo "Warning: No products found to create sample order.<br>";
        }
    }

    echo "<br><b style='color:green'>Repair completed!</b><br>";
    echo "<a href='my_orders.php' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px; margin-top:10px; display:inline-block;'>Go to My Orders</a>";

} catch (Exception $e) {
    echo "<br><b style='color:red'>FATAL ERROR:</b> " . $e->getMessage();
}
?>
