<?php
// api/bulk_update_products.php - Backend for Batch Inventory Management
header('Content-Type: application/json');
session_start();
include '../config.php';

// Auth Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['store', 'entrepreneur', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];
$action = $data['action'] ?? '';
$value = $data['value'] ?? '';

if (empty($ids) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

try {
    $pdo->beginTransaction();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    switch ($action) {
        case 'status':
            $stmt = $pdo->prepare("UPDATE product_base SET status = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$value], $ids));
            break;

        case 'price':
            foreach ($ids as $id) {
                // Get current price
                $s = $pdo->prepare("SELECT price FROM product_prices WHERE product_id = ?");
                $s->execute([$id]);
                $current_price = (float)$s->fetchColumn();

                $new_price = $current_price;
                if (strpos($value, '%') !== false) {
                    $percent = (float)str_replace(['+', '%'], '', $value);
                    $new_price = $current_price * (1 + ($percent / 100));
                } elseif (strpos($value, '+') !== false || strpos($value, '-') !== false) {
                    $new_price = $current_price + (float)$value;
                } else {
                    $new_price = (float)$value; // Fixed price
                }

                $u = $pdo->prepare("UPDATE product_prices SET price = ? WHERE product_id = ?");
                $u->execute([max(0, $new_price), $id]);
            }
            break;

        case 'delete':
            // Soft delete/Archive or hard delete depending on policy. 
            // Here we do hard delete per user request context of management.
            $stmt1 = $pdo->prepare("DELETE FROM product_prices WHERE product_id IN ($placeholders)");
            $stmt1->execute($ids);
            $stmt2 = $pdo->prepare("DELETE FROM product_stock WHERE product_id IN ($placeholders)");
            $stmt2->execute($ids);
            $stmt3 = $pdo->prepare("DELETE FROM product_base WHERE id IN ($placeholders)");
            $stmt3->execute($ids);
            break;

        default:
            throw new Exception("Invalid action type.");
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
