<?php
include 'config.php';
try {
    $name = 'Breathable Mesh Speed Runner';
    $stmt = $pdo->prepare("
        SELECT pb.id, pb.name, b.name as brand_name, b.id as brand_id 
        FROM product_base pb
        LEFT JOIN product_specs ps ON pb.id = ps.product_id
        LEFT JOIN brands b ON ps.brand_id = b.id
        WHERE pb.name = ?
    ");
    $stmt->execute([$name]);
    $product = $stmt->fetch();
    if ($product) {
        echo "Product ID: " . $product['id'] . "\n";
        echo "Current Name: " . $product['name'] . "\n";
        echo "Current Brand: " . $product['brand_name'] . " (ID: " . $product['brand_id'] . ")\n";
    } else {
        echo "Product not found.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
