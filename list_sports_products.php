<?php
include 'config.php';
try {
    $stmt = $pdo->prepare("
        SELECT pb.id, pb.name, pp.price, pm.url as primary_image
        FROM product_base pb
        JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
        WHERE pb.category_id = 5
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "--- SPORTS CATEGORY PRODUCTS ---\n";
    foreach ($products as $p) {
        echo "ID: {$p['id']} | Name: {$p['name']} | Price: {$p['price']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
