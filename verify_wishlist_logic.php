<?php
session_start();
include 'config.php';

$_SESSION['user_id'] = 1; 
$product_id = 126; // Valid ID (Reebok Nano X3)

echo "Testing with User ID: {$_SESSION['user_id']} and Product ID: $product_id\n";

// 1. Initial Check
$stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$_SESSION['user_id'], $product_id]);
$initial = $stmt->fetch();
echo "Initial Status: " . ($initial ? "In List" : "Not in List") . "\n";

// 2. Perform Toggle (Add if missing, Remove if present)
if (!$initial) {
    echo "Action: Adding...\n";
    $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $product_id]);
} else {
    echo "Action: Removing (Cleanup first)...\n";
    $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $product_id]);
    echo "Action: Adding Back...\n";
    $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $product_id]);
}

// 3. Verify Added
$stmt->execute([$_SESSION['user_id'], $product_id]);
echo "After Add: " . ($stmt->fetch() ? "In List (Success)" : "Not in List (Fail)") . "\n";

// 4. Cleanup (Remove)
$pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $product_id]);
echo "Action: Cleanup Removed.\n";

?>
