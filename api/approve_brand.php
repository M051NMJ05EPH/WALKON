<?php
header('Content-Type: application/json');
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'] ?? null;
$action = $data['action'] ?? ''; // 'approved' or 'rejected'
$feedback = $data['feedback'] ?? '';

if (!$request_id || !in_array($action, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE brand_approvals SET status = ?, admin_feedback = ? WHERE id = ?");
    $stmt->execute([$action, $feedback, $request_id]);

    echo json_encode(['success' => true, 'message' => 'Brand authorization ' . $action]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
