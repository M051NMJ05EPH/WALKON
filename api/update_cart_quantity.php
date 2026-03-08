<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$quantity = $data['quantity'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$product_id || $quantity === null) {
    echo json_encode(['success' => false, 'message' => 'Required parameters missing']);
    exit();
}

if ($quantity <= 0) {
    // If quantity is 0 or less, remove the item
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $action = 'removed';
} else {
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $user_id, $product_id]);
    $action = 'updated';
}

// Get total cart count and subtotal
$stmt = $pdo->prepare("
    SELECT SUM(c.quantity) as total_items, SUM(c.quantity * pp.price) as subtotal
    FROM cart c
    JOIN product_prices pp ON c.product_id = pp.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'action' => $action,
    'total_items' => $stats['total_items'] ?? 0,
    'subtotal' => $stats['subtotal'] ?? 0
]);
