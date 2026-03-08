<?php
include 'config.php';

try {
    echo "Testing Insert...<br>";
    
    // 1. Get a seller
    $stmt = $pdo->query("SELECT id FROM sellers LIMIT 1");
    $seller = $stmt->fetch();
    if (!$seller) {
        $pdo->exec("INSERT INTO sellers (name, email, password) VALUES ('Test', 'test@test.com', '123')");
        $seller_id = $pdo->lastInsertId();
    } else {
        $seller_id = $seller['id'];
    }
    
    // 2. Insert Base
    $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, name, status) VALUES (?, 'Test Product', 'draft')");
    $stmt->execute([$seller_id]);
    $pid = $pdo->lastInsertId();
    echo "Inserted Product ID: $pid<br>";
    
    // 3. Insert Description
    $stmt = $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, 'Test Content')");
    $stmt->execute([$pid, 'Test Content']);
    echo "Inserted Description for Product ID: $pid<br>";
    
    // 4. Insert Stock
    $stmt = $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, 10)");
    $stmt->execute([$pid]);
    echo "Inserted Stock for Product ID: $pid<br>";
    
    echo "Test Complete Success.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
