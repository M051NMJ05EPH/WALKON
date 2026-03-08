<?php
include 'config.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h1>Seeding Product Sync Logs...</h1>";

    // 1. Get a seller
    $stmt_seller = $pdo->query("SELECT id FROM sellers LIMIT 1");
    $seller = $stmt_seller->fetch();
    if (!$seller) {
        die("No sellers found. Please seed sellers first.");
    }
    $seller_id = $seller['id'];
    echo "Found Seller ID: $seller_id<br>";

    // 2. Get some products
    $stmt_prods = $pdo->prepare("SELECT id, name FROM product_base WHERE seller_id = ? LIMIT 5");
    $stmt_prods->execute([$seller_id]);
    $products = $stmt_prods->fetchAll();

    if (count($products) == 0) {
        die("No products found for seller $seller_id. Please seed products first.");
    }

    // 3. Insert logs
    $channels = ['Amazon', 'Shopify', 'eBay', 'Instagram', 'TikTok Shop'];
    $statuses = ['success', 'pending', 'error', 'failed'];

    $stmt_insert = $pdo->prepare("INSERT INTO product_sync_logs (seller_id, product_id, channel, status, message) VALUES (?, ?, ?, ?, ?)");

    foreach ($products as $p) {
        echo "Seeding logs for: " . htmlspecialchars($p['name']) . "<br>";
        
        // Add 2-3 random channel logs for each product
        $selected_channels = array_rand(array_flip($channels), rand(2, 4));
        foreach ((array)$selected_channels as $ch) {
            $status = $statuses[array_rand($statuses)];
            $message = ($status == 'error' || $status == 'failed') ? "API Connection Timeout" : "Sync successful";
            $stmt_insert->execute([$seller_id, $p['id'], $ch, $status, $message]);
            echo " - $ch: $status<br>";
        }
    }

    echo "<h3>Seeding Complete!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>
