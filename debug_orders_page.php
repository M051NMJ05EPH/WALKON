<?php
// Quick test script to check orders data and queries
include 'config.php';

echo "=== ORDERS PAGE DEBUG ===\n\n";

// Test 1: Check database connection
try {
    $pdo->query("SELECT 1");
    echo "✓ Database connection: OK\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Test 2: Check if orders table exists
try {
    $result = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($result->rowCount() > 0) {
        echo "✓ Orders table: EXISTS\n\n";
    } else {
        die("✗ Orders table: NOT FOUND\n");
    }
} catch (Exception $e) {
    die("✗ Error checking orders table: " . $e->getMessage() . "\n");
}

// Test 3: Check orders count
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Total orders in database: " . $count['count'] . "\n\n";
} catch (Exception $e) {
    die("✗ Error counting orders: " . $e->getMessage() . "\n");
}

// Test 4: Check sellers table
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sellers");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Total sellers in database: " . $count['count'] . "\n\n";
} catch (Exception $e) {
    echo "✗ Sellers table issue: " . $e->getMessage() . "\n\n";
}

// Test 5: Sample order query (the one used in my_orders.php)
try {
    $sql = "SELECT 
                o.*, 
                pb.name as product_name, 
                ps.sku,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
            FROM orders o 
            LEFT JOIN product_base pb ON o.product_id = pb.id
            LEFT JOIN product_skus ps ON pb.id = ps.product_id
            LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✓ Order query executed successfully\n";
    echo "  Retrieved " . count($orders) . " sample orders\n\n";
    
    if (count($orders) > 0) {
        echo "Sample order data:\n";
        print_r($orders[0]);
    }
    
} catch (Exception $e) {
    echo "✗ Order query failed: " . $e->getMessage() . "\n\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>
