<?php
include 'config.php';
try {
    $stmt = $pdo->prepare("SELECT id FROM product_base WHERE name = 'Clarks CraftMaster II'");
    $stmt->execute();
    $product = $stmt->fetch();
    if ($product) {
        echo "Product ID for Clarks CraftMaster II: " . $product['id'] . "\n";
    } else {
        echo "Product not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
