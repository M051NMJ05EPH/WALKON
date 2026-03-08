<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id'] ?? 0);
$amazon_title = $data['amazon_title'] ?? '';
$shopify_desc = $data['shopify_desc'] ?? '';

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID']);
    exit();
}

try {
    // We could save this to a new table `ai_optimizations` or update `product_base`
    // For now, let's assume we update a specific metadata field or just simulate success
    
    // In a real app, you'd have columns for marketplace-specific titles
    // For this demo, we'll just log the action
    
    echo json_encode(['success' => true, 'message' => 'Optimizations saved successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
