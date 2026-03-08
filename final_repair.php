<?php
include 'config.php';
try {
    echo "Starting schema repair...<br>";
    $stmt = $pdo->query("DESCRIBE orders");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 1. Repair status column
    if (in_array('order_status', $cols) && !in_array('status', $cols)) {
        echo "Renaming 'order_status' to 'status'... ";
        $pdo->exec("ALTER TABLE orders CHANGE order_status status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        echo "Done.<br>";
    } elseif (!in_array('status', $cols)) {
        echo "Adding 'status' column... ";
        $pdo->exec("ALTER TABLE orders ADD status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        echo "Done.<br>";
    }

    // 2. Repair total_price column
    if (in_array('total_amount', $cols) && !in_array('total_price', $cols)) {
        echo "Renaming 'total_amount' to 'total_price'... ";
        $pdo->exec("ALTER TABLE orders CHANGE total_amount total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        echo "Done.<br>";
    } elseif (!in_array('total_price', $cols)) {
        echo "Adding 'total_price' column... ";
        $pdo->exec("ALTER TABLE orders ADD total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        echo "Done.<br>";
    }

    echo "<h3>Repair Complete!</h3>";
    echo "<a href='my_orders.php'>Visit Orders Page</a>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
