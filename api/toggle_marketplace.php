<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$marketplace_id = $data['marketplace_id'] ?? null;
$seller_id = $_SESSION['seller_id'];

if (!$marketplace_id) {
    echo json_encode(['success' => false, 'message' => 'Marketplace ID missing']);
    exit();
}

try {
    // Check current status
    $stmt = $pdo->prepare("SELECT status FROM seller_marketplaces WHERE seller_id = ? AND marketplace_id = ?");
    $stmt->execute([$seller_id, $marketplace_id]);
    $current = $stmt->fetch();

    if ($current && $current['status'] === 'connected') {
        // Disconnect
        $stmt = $pdo->prepare("UPDATE seller_marketplaces SET status = 'disconnected' WHERE seller_id = ? AND marketplace_id = ?");
        $stmt->execute([$seller_id, $marketplace_id]);
        $new_status = 'disconnected';
    } else {
        // Connect (Insert or Update)
        $stmt = $pdo->prepare("INSERT INTO seller_marketplaces (seller_id, marketplace_id, status) 
                               VALUES (?, ?, 'connected') 
                               ON DUPLICATE KEY UPDATE status = 'connected'");
        $stmt->execute([$seller_id, $marketplace_id]);
        $new_status = 'connected';
    }

    echo json_encode(['success' => true, 'status' => $new_status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
