<?php
header('Content-Type: application/json');
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit();
}

try {
    // Verify product belongs to seller
    $seller_id = $_SESSION['seller_id'];
    $stmt = $pdo->prepare("SELECT id FROM product_base WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product not found or access denied']);
        exit();
    }

    // Generate unique serial number (example logic)
    $serial_number = 'WALKON-' . strtoupper(substr(uniqid(), -8)) . '-' . str_pad($product_id, 4, '0', STR_PAD_LEFT);
    $batch_number = 'B-' . date('Ymd');
    
    // URL for verification
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('api/generate_qr.php', '', $_SERVER['REQUEST_URI']);
    $verifyUrl = $baseUrl . "verify_product.php?sn=" . $serial_number;
    
    // Google Charts QR Code API
    $qr_code_url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($verifyUrl) . "&choe=UTF-8";

    // Insert into product_authenticity
    $stmt = $pdo->prepare("INSERT INTO product_authenticity (product_id, serial_number, batch_number, qr_code_url, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$product_id, $serial_number, $batch_number, $qr_code_url]);

    echo json_encode([
        'success' => true, 
        'message' => 'QR Code generated successfully',
        'serial_number' => $serial_number,
        'qr_code_url' => $qr_code_url
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
