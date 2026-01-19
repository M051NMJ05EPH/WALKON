<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header("Location: my_listings.php");
    exit();
}

try {
    // 1. Get the seller_id for the current user
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$_SESSION['email']]);
    $seller = $stmt_seller->fetch();
    
    if (!$seller) {
        die("Seller record not found.");
    }
    
    $seller_id = $seller['id'];

    // 2. Verify that the product belongs to this seller (checking product_base)
    $stmt_check = $pdo->prepare("SELECT id FROM product_base WHERE id = ? AND seller_id = ?");
    $stmt_check->execute([$product_id, $seller_id]);
    $product = $stmt_check->fetch();

    if (!$product) {
        die("Product not found or access denied.");
    }

    // 3. Delete the product
    // Because of ON DELETE CASCADE in ultra_normalize.sql, this deletes from all child tables (skus, prices, stock, media, etc.) automatically.
    $stmt_delete = $pdo->prepare("DELETE FROM product_base WHERE id = ?");
    $stmt_delete->execute([$product_id]);

    header("Location: my_listings.php?msg=Product removed successfully");
    exit();

} catch (PDOException $e) {
    die("Error deleting product: " . $e->getMessage());
}
?>
