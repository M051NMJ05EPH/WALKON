<?php
include 'config.php';

try {
    echo "Testing Featured Products Query:\n";
    $stmt = $pdo->prepare("
        SELECT pb.name, c.name as category_name, b.name as brand_name
        FROM product_base pb
        JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_media pm ON pb.id = pm.product_id AND pm.is_primary = 1
        LEFT JOIN categories c ON pb.category_id = c.id
        LEFT JOIN product_specs ps ON pb.id = ps.product_id
        LEFT JOIN brands b ON ps.brand_id = b.id
        WHERE pb.status = 'published'
        ORDER BY pb.created_at DESC
        LIMIT 8
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $brand = !empty($p['brand_name']) ? $p['brand_name'] : "N/A (Using Category: {$p['category_name']})";
        echo "Product: {$p['name']} | Brand: {$brand}\n";
    }

    if (empty($products)) {
        echo "No featured products found.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
