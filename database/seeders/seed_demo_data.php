<?php
include 'config.php';

echo "<h2>Starting Data Seeding...</h2>";

try {
    // 1. Get Seller ID
    $stmt_seller = $pdo->query("SELECT id FROM sellers LIMIT 1");
    $seller_id = $stmt_seller->fetchColumn();

    if (!$seller_id) {
        // Create a default seller if none exists
        $pdo->exec("INSERT INTO sellers (name, email) VALUES ('Demo Seller', 'test@example.com')");
        $seller_id = $pdo->lastInsertId();
        echo "<p>Created default seller ID: $seller_id</p>";
    }

    // 2. Clear existing demo data (Optional: Be careful)
    //$pdo->exec("DELETE FROM orders");
    //$pdo->exec("DELETE FROM daily_sales_analytics");

    // 3. Get existing products
    $stmt_products = $pdo->query("SELECT id, name FROM product_base");
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        die("Error: No products found. Please add products first.");
    }

    $channels = ['Amazon', 'Flipkart', 'Shopify', 'Ebay', 'TikTok Shop'];
    $order_statuses = ['pending', 'shipped', 'delivered', 'cancelled'];
    $payment_statuses = ['paid', 'pending', 'refunded'];

    // 4. Seed Orders (Last 10 orders)
    echo "<h3>Seeding Orders...</h3>";
    for ($i = 0; $i < 10; $i++) {
        $p = $products[array_rand($products)];
        $channel = $channels[array_rand($channels)];
        $status = $order_statuses[array_rand($order_statuses)];
        $payment = $payment_statuses[array_rand($payment_statuses)];
        $amount = rand(2000, 15000);
        $date = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days"));

        $stmt = $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, customer_email, total_price, status, channel, order_date, unit_price, quantity) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $seller_id,
            $p['id'],
            "Demo Customer " . ($i + 1),
            "customer" . ($i + 1) . "@example.com",
            $amount,
            $status,
            $channel,
            $date,
            $amount,
            1
        ]);
        echo "<li>Added order for " . htmlspecialchars($p['name']) . " via $channel</li>";
    }

    // 5. Seed Analytics (Last 30 days)
    echo "<h3>Seeding Analytics Trends...</h3>";
    for ($i = 0; $i < 30; $i++) {
        $date = date('Y-m-d', strtotime("-" . $i . " days"));
        $daily_rev = rand(5000, 50000);
        $daily_ord = rand(1, 10);
        $daily_units = $daily_ord + rand(0, 5);

        $stmt_check = $pdo->prepare("SELECT id FROM daily_sales_analytics WHERE seller_id = ? AND recorded_date = ?");
        $stmt_check->execute([$seller_id, $date]);
        
        if (!$stmt_check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO daily_sales_analytics (seller_id, recorded_date, total_revenue, total_orders, units_sold) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$seller_id, $date, $daily_rev, $daily_ord, $daily_units]);
        }
    }

    echo "<h3>✅ Data Seeding Completed!</h3>";
    echo "<p><a href='dashboard.php' style='padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;'>Return to Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h3>❌ Error: " . $e->getMessage() . "</h3>";
}
