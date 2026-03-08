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
    // Create channel_settings table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS channel_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        marketplace_id INT NOT NULL,
        sync_frequency VARCHAR(20) DEFAULT 'daily',
        price_margin DECIMAL(5,2) DEFAULT 0.00,
        description_override TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_seller_marketplace (seller_id, marketplace_id)
    )");

    // Save or update settings
    $stmt = $pdo->prepare("INSERT INTO channel_settings 
                          (seller_id, marketplace_id, sync_frequency, price_margin, description_override)
                          VALUES (?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE
                          sync_frequency = VALUES(sync_frequency),
                          price_margin = VALUES(price_margin),
                          description_override = VALUES(description_override)");
    
    $stmt->execute([
        $seller_id,
        $marketplace_id,
        $data['sync_frequency'] ?? 'daily',
        $data['price_margin'] ?? 0,
        $data['description_override'] ?? null
    ]);

    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
