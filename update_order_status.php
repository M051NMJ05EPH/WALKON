<?php
session_start();
include 'config.php';
include 'includes/auth_check.php';
require_once 'includes/activity_logger.php';

// Authentication & Seller Check
if (!isset($_SESSION['user_id']) || !isSeller()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
$new_status = isset($data['status']) ? trim($data['status']) : '';

if (!$order_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$seller_id = $_SESSION['seller_id'];

try {
    // Verify the order belongs to this seller
    $stmt_check = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND seller_id = ?");
    $stmt_check->execute([$order_id, $seller_id]);
    if (!$stmt_check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Order not found or access denied']);
        exit;
    }

    // Update status and tracking info
    $tracking_number = isset($data['tracking_number']) ? trim($data['tracking_number']) : null;
    
    $sql = "UPDATE orders SET status = ?, updated_at = NOW()";
    $params = [$new_status];
    
    if ($tracking_number) {
        $sql .= ", tracking_number = ?";
        $params[] = $tracking_number;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $order_id;

    $stmt_upd = $pdo->prepare($sql);
    $stmt_upd->execute($params);

    // Log Activity
    $logger = new ActivityLogger($pdo);
    $logger->log($_SESSION['user_id'], 'order_status_updated', "Updated Order #$order_id status to: $new_status");

    echo json_encode(['success' => true, 'message' => 'Order status updated to ' . ucfirst($new_status)]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
