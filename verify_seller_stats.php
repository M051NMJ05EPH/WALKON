<?php
include 'config.php';

function getStatsForSeller($pdo, $seller_id) {
    if ($seller_id == -1) return [0, 0, 0, 0];

    $stmt_products = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE seller_id = ?");
    $stmt_products->execute([$seller_id]);
    $total_products = $stmt_products->fetchColumn();

    $stmt_revenue = $pdo->prepare("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled' AND seller_id = ?");
    $stmt_revenue->execute([$seller_id]);
    $total_revenue = $stmt_revenue->fetchColumn() ?: 0;

    $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE seller_id = ?");
    $stmt_orders->execute([$seller_id]);
    $total_orders = $stmt_orders->fetchColumn();

    $stmt_active = $pdo->prepare("SELECT COUNT(*) FROM product_base WHERE status = 'published' AND seller_id = ?");
    $stmt_active->execute([$seller_id]);
    $active_listings = $stmt_active->fetchColumn();

    return [$total_products, $total_revenue, $total_orders, $active_listings];
}

try {
    // Let's pick a few sellers and check their stats
    $sellers = $pdo->query("SELECT id, email, business_name FROM sellers LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "--- Seller Statistics Verification ---\n";
    foreach ($sellers as $seller) {
        $stats = getStatsForSeller($pdo, $seller['id']);
        echo "Seller: " . $seller['business_name'] . " (" . $seller['email'] . ")\n";
        echo "  Total Products: " . $stats[0] . "\n";
        echo "  Total Revenue:  " . $stats[1] . "\n";
        echo "  Total Orders:   " . $stats[2] . "\n";
        echo "  Active Listings: " . $stats[3] . "\n";
        echo "--------------------------------------\n";
    }

    // Check a non-existent seller
    $stats = getStatsForSeller($pdo, -1);
    echo "Non-existent Seller:\n";
    echo "  Total Products: " . $stats[0] . " (Expected: 0)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
