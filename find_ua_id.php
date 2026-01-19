<?php
include 'config.php';
try {
    $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = 'Under Armour Curry 10'");
    $stmt->execute();
    $product = $stmt->fetch();
    if ($product) {
        echo "Product ID for Under Armour Curry 10: " . $product['id'] . "\n";
    } else {
        echo "Product not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
