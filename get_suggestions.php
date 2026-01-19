<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$email = $_SESSION['email'];
$query = trim($_GET['q'] ?? '');

if (strlen($query) < 1) {
    echo json_encode([]);
    exit();
}

try {
    // Get seller_id
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    $seller_id = $seller ? $seller['id'] : -1;

    // Fetch product names and SKUs that match the query
    $stmt = $pdo->prepare("
        (SELECT name as text FROM product_base WHERE seller_id = ? AND name LIKE ? LIMIT 5)
        UNION
        (SELECT sku as text FROM product_skus ps JOIN product_base pb ON ps.product_id = pb.id WHERE pb.seller_id = ? AND sku LIKE ? LIMIT 5)
        LIMIT 10
    ");
    
    $searchTerm = "%$query%";
    $stmt->execute([$seller_id, $searchTerm, $seller_id, $searchTerm]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(array_values(array_unique($suggestions)));

} catch (PDOException $e) {
    echo json_encode([]);
}
?>
