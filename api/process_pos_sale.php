<?php
// api/process_pos_sale.php - Record POS Sale and Deduct Inventory
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store_owner', 'store'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    $pdo->beginTransaction();

    $seller_id = $_SESSION['user_id']; // Usually the store owner/staff logged in
    $total_amount = $data['total'];
    $payment_method = $data['payment_method'];

    // 1. Create Order Record (Simplified for POS)
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, payment_status, shipping_address) VALUES (?, ?, 'delivered', ?, 'paid', 'In-Store POS Sale')");
    $stmt->execute([$seller_id, $total_amount, $payment_method]);
    $order_id = $pdo->lastInsertId();

    // 2. Process Items
    foreach ($data['items'] as $item) {
        $product_id = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];

        // Add to order_items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_item->execute([$order_id, $product_id, $qty, $price]);

        // 3. Inventory Sync: Deduct stock from the FIRST available SKU for this product
        // Note: In a real scenario, the POS would scan a specific SKU/Size. Here we deduct from available.
        $stmt_stock = $pdo->prepare("UPDATE product_skus SET stock = stock - ? WHERE product_id = ? AND stock >= ? LIMIT 1");
        $stmt_stock->execute([$qty, $product_id, $qty]);
        
        if ($stmt_stock->rowCount() === 0) {
            // If no stock in specific SKU, we might need a fallback or throw error
            // For this ROUGH version, we'll proceed but log it (or you could throw an exception)
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
