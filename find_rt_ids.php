<?php
include 'config.php';
try {
    $names = ['Reebok Nano X3', 'Timberland 6-Inch Premium'];
    foreach ($names as $name) {
        $stmt = $pdo->prepare("SELECT id, name FROM product_base WHERE name = ?");
        $stmt->execute([$name]);
        $product = $stmt->fetch();
        if ($product) {
            echo "Product ID for {$product['name']}: {$product['id']}\n";
        } else {
            echo "Product '{$name}' not found.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
