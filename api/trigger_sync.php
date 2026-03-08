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
$channel_name = $data['channel_name'] ?? null;
$seller_id = $_SESSION['seller_id'];

if (!$marketplace_id || !$channel_name) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Update last_sync timestamp
    $stmt = $pdo->prepare("UPDATE seller_marketplaces 
                           SET last_sync = NOW() 
                           WHERE seller_id = ? AND marketplace_id = ?");
    $stmt->execute([$seller_id, $marketplace_id]);

    // In a real implementation, this would trigger actual API calls to the marketplace
    // For now, we'll just simulate a successful sync
    
    echo json_encode([
        'success' => true,
        'message' => 'Sync completed successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'synced_count' => rand(10, 50) // Simulated
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
