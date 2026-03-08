<?php
session_start();
header('Content-Type: application/json');

require_once '../config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

// Require staff, inventory manager, store owner, or admin
try {
    requireRole(['staff', 'store_owner', 'admin']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$logger = new ActivityLogger($pdo);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'update_status':
        updateOrderStatus();
        break;
    case 'add_note':
        addOrderNote();
        break;
    case 'get_notes':
        getOrderNotes();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function updateOrderStatus() {
    global $pdo, $logger;
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $order_id = $input['order_id'] ?? 0;
        $status = $input['status'] ?? '';
        
        if (!$order_id || !$status) {
            throw new Exception('Missing required fields');
        }
        
        // Check if staff belongs to the store of this order
        if (!isAdmin()) {
            $seller_id = $_SESSION['seller_id'];
            $stmt = $pdo->prepare("SELECT seller_id FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order_seller_id = $stmt->fetchColumn();
            
            if ($order_seller_id != $seller_id) {
                throw new Exception('Access Denied: Order does not belong to your store');
            }
        }
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        
        $logger->log("order_status_updated", json_encode(['order_id' => $order_id, 'new_status' => $status]));
        
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function addOrderNote() {
    global $pdo, $logger;
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $order_id = $input['order_id'] ?? 0;
        $note = trim($input['note'] ?? '');
        
        if (!$order_id || empty($note)) {
            throw new Exception('Missing required fields');
        }
        
        // Access check
        if (!isAdmin()) {
            $seller_id = $_SESSION['seller_id'];
            $stmt = $pdo->prepare("SELECT seller_id FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            if ($stmt->fetchColumn() != $seller_id) {
                throw new Exception('Access Denied');
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO order_notes (order_id, user_id, note) VALUES (?, ?, ?)");
        $stmt->execute([$order_id, $_SESSION['user_id'], $note]);
        
        echo json_encode(['success' => true, 'message' => 'Note added successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getOrderNotes() {
    global $pdo;
    try {
        $order_id = $_GET['order_id'] ?? 0;
        if (!$order_id) throw new Exception('Order ID required');
        
        $stmt = $pdo->prepare("
            SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as author 
            FROM order_notes n 
            JOIN users u ON n.user_id = u.id 
            WHERE n.order_id = ? 
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$order_id]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'notes' => $notes]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
