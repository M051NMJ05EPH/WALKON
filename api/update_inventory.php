<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$field = $data['field'] ?? null;
$value = $data['value'] ?? null;
$seller_id = $_SESSION['seller_id'];

if (!$product_id || !$field) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Verify ownership
$stmt = $pdo->prepare("SELECT id FROM product_base WHERE id = ? AND seller_id = ?");
$stmt->execute([$product_id, $seller_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Product not found or access denied']);
    exit();
}

try {
    $pdo->beginTransaction();

    switch ($field) {
        case 'price':
            $stmt = $pdo->prepare("UPDATE product_prices SET price = ? WHERE product_id = ?");
            $stmt->execute([$value, $product_id]);
            break;
        case 'stock':
            $stmt = $pdo->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ?");
            $stmt->execute([$value, $product_id]);
            break;
        case 'status':
            $stmt = $pdo->prepare("UPDATE product_base SET status = ? WHERE id = ?");
            $stmt->execute([$value, $product_id]);
            break;
        default:
            throw new Exception("Invalid field mapping");
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
