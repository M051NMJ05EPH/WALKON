<?php
header('Content-Type: application/json');
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$dispute_id = $data['dispute_id'] ?? null;
$action = $data['action'] ?? ''; // 'resolved' or 'rejected'

if (!$dispute_id || !in_array($action, ['resolved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE disputes SET status = ? WHERE id = ?");
    $stmt->execute([$action, $dispute_id]);

    echo json_encode(['success' => true, 'message' => 'Dispute ' . $action]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
