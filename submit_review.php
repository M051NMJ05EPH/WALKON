<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to write a review.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or rating.']);
    exit;
}

// Optional: Verified Purchase Check
// For now, allow anyone logged in to review, or uncomment below if orders table has user_id
/*
$stmt_check = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND product_id = ? AND status = 'delivered'");
$stmt_check->execute([$user_id, $product_id]);
if (!$stmt_check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You can only review products you have purchased and received.']);
    exit;
}
*/

try {
    // Check if already reviewed
    $stmt_exist = $pdo->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt_exist->execute([$user_id, $product_id]);
    
    if ($stmt_exist->fetch()) {
        // Update existing review
        $stmt = $pdo->prepare("UPDATE product_reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$rating, $comment, $user_id, $product_id]);
        $message = 'Review updated successfully!';
    } else {
        // Insert new review
        $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$product_id, $user_id, $rating, $comment]);
        $message = 'Review submitted successfully!';
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
