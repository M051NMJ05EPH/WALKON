<?php
include 'config.php';

$seller_id = 1;
$product_name = "Velocity Runner Pro";
$product_sku = "VR-PRO-2026";
$price = 14500.00;
$desc = "The ultimate high-performance running shoe featuring liquid-energy cushioning and neon accents.";
$image_url = "uploads/velocity_runner_pro.png";

try {
    $pdo->beginTransaction();

    // Get Sports Category ID
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Sports'");
    $stmt->execute();
    $cat = $stmt->fetch();
    if (!$cat) {
        throw new Exception("Sports category not found");
    }
    $cat_id = $cat['id'];

    // Get Brand ID (Let's use a generic premium brand or create one, or use existing. Let's use 'Nike' for now or 'Adidas' as it fits. Or maybe 'ASICS'. Let's pick ASICS for high performance.)
    // Actually, user just said "sports shoe will be added". Let's stick to a generic "WalkOn" or existing brand.
    // Let's use 'ASICS' as it's a "Runner".
    $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = 'ASICS'");
    $stmt->execute();
    $brand = $stmt->fetch();
    $brand_id = $brand ? $brand['id'] : 1; // Fallback to 1

    // Base
    $stmt = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, name, status) VALUES (?, ?, ?, 'published')");
    $stmt->execute([$seller_id, $cat_id, $product_name]);
    $pid = $pdo->lastInsertId();

    // SKU
    $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)")->execute([$pid, $product_sku]);

    // Price
    $pdo->prepare("INSERT INTO product_prices (product_id, price) VALUES (?, ?)")->execute([$pid, $price]);

    // Stock
    $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, ?)")->execute([$pid, 50]);

    // Description
    $pdo->prepare("INSERT INTO product_descriptions (product_id, content) VALUES (?, ?)")->execute([$pid, $desc]);

    // Specs
    $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, outer_material, shoe_type) VALUES (?, ?, ?, ?)")
        ->execute([$pid, $brand_id, 'Carbon Fiber', 'Running']);

    // Media
    $pdo->prepare("INSERT INTO product_media (product_id, url, is_primary) VALUES (?, ?, 1)")
        ->execute([$pid, $image_url]);

    $pdo->commit();
    echo "Success: Added '$product_name' (ID: $pid) to Sports category.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
