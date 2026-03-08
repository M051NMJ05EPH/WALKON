<?php
// api/get_product_details.php - AJAX endpoint for Quick View
header('Content-Type: application/json');
include '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT pb.name, pp.price, b.name as brand,
        (SELECT content FROM product_descriptions pd WHERE pd.product_id = pb.id) as description,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as primary_image,
        (SELECT url FROM product_media pm WHERE pm.product_id = pb.id LIMIT 1) as fallback_image
        FROM product_base pb
        LEFT JOIN product_prices pp ON pb.id = pp.product_id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode([
            'name' => $product['name'],
            'price' => $product['price'],
            'brand' => $product['brand'] ?? 'FOOTWEAR',
            'description' => $product['description'] ? mb_strimwidth(strip_tags($product['description']), 0, 150, "...") : "No description available.",
            'image' => $product['primary_image'] ?? $product['fallback_image'] ?? 'assets/shoe_placeholder.png'
        ]);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
