<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

echo "<h2>Force Column Repair</h2>";

try {
    // 1. Check orders table
    $stmt = $pdo->query("DESCRIBE orders");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(", ", $cols) . "<br>";

    // Fix status column
    if (!in_array('status', $cols)) {
        if (in_array('order_status', $cols)) {
            echo "Renaming 'order_status' to 'status'... ";
            $pdo->exec("ALTER TABLE orders CHANGE order_status status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
            echo "Done.<br>";
        } else {
            echo "Adding missing 'status' column... ";
            $pdo->exec("ALTER TABLE orders ADD status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending' AFTER total_price");
            echo "Done.<br>";
        }
    }

    // Fix total_price column
    if (!in_array('total_price', $cols)) {
        if (in_array('total_amount', $cols)) {
            echo "Renaming 'total_amount' to 'total_price'... ";
            $pdo->exec("ALTER TABLE orders CHANGE total_amount total_price DECIMAL(10,2) NOT NULL");
            echo "Done.<br>";
        } else {
            echo "Adding missing 'total_price' column... ";
            $pdo->exec("ALTER TABLE orders ADD total_price DECIMAL(10,2) NOT NULL AFTER unit_price");
            echo "Done.<br>";
        }
    }

    // Sync sellers just in case
    $pdo->exec("INSERT IGNORE INTO sellers (name, email, password, business_name) 
                SELECT CONCAT(first_name, ' ', last_name), email, 'social_reg', 'Default Store' FROM users");

    echo "<br><b style='color:green'>Repair Complete!</b><br>";
    echo "<a href='my_orders.php'>Go to My Orders</a>";

} catch (Exception $e) {
    echo "<b style='color:red'>Error:</b> " . $e->getMessage();
}
?>
