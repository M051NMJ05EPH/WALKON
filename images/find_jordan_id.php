<?php
include 'config.php';
try {
    $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = 'Nike Air Jordan 1 High'");
    $stmt->execute();
    $product = $stmt->fetch();
    if ($product) {
        echo "Product ID for Nike Air Jordan 1 High: " . $product['id'] . "\n";
    } else {
        echo "Product not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
