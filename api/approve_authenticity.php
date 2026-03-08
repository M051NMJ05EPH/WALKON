<?php
header('Content-Type: application/json');
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Admin Check
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_role = $stmt->fetchColumn();

if ($user_role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'] ?? null;
$action = $data['action'] ?? ''; // 'verified' or 'rejected'

if (!$request_id || !in_array($action, ['verified', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE product_authenticity SET status = ?, verified_by = ?, verified_at = NOW() WHERE id = ?");
    $stmt->execute([$action, $_SESSION['user_id'], $request_id]);

    echo json_encode(['success' => true, 'message' => 'Authenticity status updated to ' . $action]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
