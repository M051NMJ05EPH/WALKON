<?php
include 'config.php';
try {
    $stmt_products = $pdo->prepare("SELECT COUNT(*) FROM product_base");
    $stmt_products->execute();
    $total_products = $stmt_products->fetchColumn();

    $stmt_revenue = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'");
    $stmt_revenue->execute();
    $total_revenue = $stmt_revenue->fetchColumn() ?: 0;

    $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM orders");
    $stmt_orders->execute();
    $total_orders = $stmt_orders->fetchColumn();

    $stmt_active = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE status = 'published'");
    $stmt_active->execute();
    $active_listings = $stmt_active->fetchColumn();

    echo "Total Products: " . $total_products . "\n";
    echo "Total Revenue: " . $total_revenue . "\n";
    echo "Total Orders: " . $total_orders . "\n";
    echo "Active Listings: " . $active_listings . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
