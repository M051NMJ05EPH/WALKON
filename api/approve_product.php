<?php
header('Content-Type: application/json');
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$action = $data['action'] ?? ''; // 'approved' or 'rejected'

if (!$product_id || !in_array($action, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE product_base SET approval_status = ? WHERE id = ?");
    $stmt->execute([$action, $product_id]);

    echo json_encode(['success' => true, 'message' => 'Product ' . $action]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
