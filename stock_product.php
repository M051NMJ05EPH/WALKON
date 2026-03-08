<?php
session_start();
include 'config.php';
require_once 'includes/activity_logger.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if (!in_array($_SESSION['role'], ['store', 'entrepreneur'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$user_id = $_SESSION['user_id'];

// Get seller_id
$stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
$stmt_seller->execute([$_SESSION['email']]);
$seller = $stmt_seller->fetch();

if (!$seller) {
    echo json_encode(['success' => false, 'message' => 'Seller account not found']);
    exit;
}
$seller_id = $seller['id'];

try {
    $pdo->beginTransaction();

    // 1. Fetch original product
    $stmt = $pdo->prepare("SELECT * FROM product_base WHERE id = ?");
    $stmt->execute([$product_id]);
    $base = $stmt->fetch();

    if (!$base) {
        throw new Exception("Original product not found");
    }

    // Check if already stocked
    $stmt_check = $pdo->prepare("SELECT id FROM product_base WHERE seller_id = ? AND name = ?");
    $stmt_check->execute([$seller_id, $base['name']]);
    if ($stmt_check->fetch()) {
        throw new Exception("You have already stocked this product");
    }

    // 2. Clone product_base
    $stmt_insert = $pdo->prepare("INSERT INTO product_base (seller_id, category_id, sub_category_id, name, status) VALUES (?, ?, ?, ?, 'published')");
    $stmt_insert->execute([$seller_id, $base['category_id'], $base['sub_category_id'], $base['name']]);
    $new_id = $pdo->lastInsertId();

    // 3. Clone Prices
    $stmt_prices = $pdo->prepare("INSERT INTO product_prices (product_id, price, min_price, max_price) SELECT ?, price, min_price, max_price FROM product_prices WHERE product_id = ?");
    $stmt_prices->execute([$new_id, $product_id]);

    // 4. Clone SKU (Modifying to keep unique)
    $stmt_sku = $pdo->prepare("SELECT sku FROM product_skus WHERE product_id = ?");
    $stmt_sku->execute([$product_id]);
    $original_sku = $stmt_sku->fetchColumn();
    $new_sku = $original_sku . "-S" . $seller_id;
    
    $stmt_ins_sku = $pdo->prepare("INSERT INTO product_skus (product_id, sku) VALUES (?, ?)");
    $stmt_ins_sku->execute([$new_id, $new_sku]);

    // 5. Clone Media
    $stmt_media = $pdo->prepare("INSERT INTO product_media (product_id, url, type, is_primary, color) SELECT ?, url, type, is_primary, color FROM product_media WHERE product_id = ?");
    $stmt_media->execute([$new_id, $product_id]);

    // 6. Clone Sizes
    $stmt_sizes = $pdo->prepare("INSERT INTO product_sizes (product_id, size_value) SELECT ?, size_value FROM product_sizes WHERE product_id = ?");
    $stmt_sizes->execute([$new_id, $product_id]);

    // 7. Clone Colors
    $stmt_colors = $pdo->prepare("INSERT INTO product_colors (product_id, color_name, color_hex, color_code) SELECT ?, color_name, color_hex, color_code FROM product_colors WHERE product_id = ?");
    $stmt_colors->execute([$new_id, $product_id]);

    // 8. Clone Descriptions
    $stmt_desc = $pdo->prepare("INSERT INTO product_descriptions (product_id, content) SELECT ?, content FROM product_descriptions WHERE product_id = ?");
    $stmt_desc->execute([$new_id, $product_id]);

    // 9. Clone Specs
    $stmt_specs = $pdo->prepare("INSERT INTO product_specs (product_id, brand_id, gender, heel_height, outer_material, season, shoe_type, occasion) SELECT ?, brand_id, gender, heel_height, outer_material, season, shoe_type, occasion FROM product_specs WHERE product_id = ?");
    $stmt_specs->execute([$new_id, $product_id]);

    // 10. Initialize Stock (Default 10 for showcasing)
    $stmt_stock = $pdo->prepare("INSERT INTO product_stock (product_id, quantity) VALUES (?, 10)");
    $stmt_stock->execute([$new_id]);

    $pdo->commit();
    
    // Log Activity
    $logger = new ActivityLogger($pdo);
    $logger->log($user_id, 'product_stocked', "Stocked product: {$base['name']} (ID: $new_id) from original ID: $product_id");

    echo json_encode(['success' => true, 'message' => 'Product successfully added to your store!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
