<?php
include 'config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<style>body { font-family: sans-serif; line-height: 1.6; padding: 20px; background: #f4f7f6; color: #333; }
      .success { color: #10b981; font-weight: bold; }
      .info { color: #3b82f6; }
      .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }</style>";

echo "<h1>🚀 Comprehensive Data Seeder</h1>";

try {
    // 1. Ensure Seller Exists for Current Session
    echo "<div class='card'><h2>1. Seller Setup</h2>";
    session_start();
    $email = $_SESSION['email'] ?? 'test@example.com';
    
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller_id = $stmt_seller->fetchColumn();

    if (!$seller_id) {
        $pdo->prepare("INSERT INTO sellers (name, email) VALUES (?, ?)")->execute(['Demo Seller', $email]);
        $seller_id = $pdo->lastInsertId();
        echo "<p class='success'>✅ Created seller for current session: $email (ID: $seller_id)</p>";
    } else {
        echo "<p class='info'>ℹ️ Using current session seller: $email (ID: $seller_id)</p>";
    }
    echo "</div>";

    // 2. Refresh Analytics
    echo "<div class='card'><h2>2. Dashboard Analytics</h2>";
    $pdo->exec("DELETE FROM daily_sales_analytics WHERE seller_id = $seller_id");
    for ($i = 0; $i < 30; $i++) {
        $date = date('Y-m-d', strtotime("-" . $i . " days"));
        $rev = rand(15000, 85000);
        $ords = rand(5, 25);
        $units = $ords + rand(0, 10);
        $stmt = $pdo->prepare("INSERT INTO daily_sales_analytics (seller_id, recorded_date, total_revenue, total_orders, units_sold) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$seller_id, $date, $rev, $ords, $units]);
    }
    echo "<p class='success'>✅ Seeded 30 days of sales analytics.</p>";
    echo "</div>";

    // 3. Update Product Pricing Boundaries
    echo "<div class='card'><h2>3. Auto Pricing Setup</h2>";
    $stmt_prods = $pdo->prepare("SELECT pb.id, pb.name, pp.price FROM product_base pb JOIN product_prices pp ON pb.id = pp.product_id WHERE pb.seller_id = ?");
    $stmt_prods->execute([$seller_id]);
    $products = $stmt_prods->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo "<p style='color:orange'>⚠️ No products found for this seller. Please add products via add_listing.php first.</p>";
    } else {
        foreach ($products as $p) {
            $base_price = (float)$p['price'];
            $min = $base_price * 0.85;
            $max = $base_price * 1.2;
            $status = rand(0, 1);
            
            $stmt = $pdo->prepare("UPDATE product_prices SET min_price = ?, max_price = ?, smart_pricing_status = ? WHERE product_id = ?");
            $stmt->execute([$min, $max, $status, $p['id']]);
            echo "<p>Updated boundaries for: <strong>{$p['name']}</strong> (₹" . number_format($base_price) . ")</p>";
        }
        echo "<p class='success'>✅ All products now have price boundaries and smart status.</p>";
    }
    echo "</div>";

    // 4. Seed Sync Logs
    echo "<div class='card'><h2>4. Marketplace Sync Logs</h2>";
    $pdo->exec("DELETE FROM product_sync_logs WHERE seller_id = $seller_id");
    $channels = ['Amazon', 'Shopify', 'Flipkart', 'Myntra', 'Instagram', 'eBay'];
    $statuses = ['success', 'pending', 'error'];
    
    $stmt_log = $pdo->prepare("INSERT INTO product_sync_logs (seller_id, product_id, channel, status, message) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($products as $p) {
        $num_channels = rand(2, 5);
        $selected = array_rand(array_flip($channels), $num_channels);
        foreach ((array)$selected as $ch) {
            $st = $statuses[array_rand($statuses)];
            $msg = ($st == 'success') ? "Sync completed" : (($st == 'pending') ? "In queue" : "Inventory mismatch error");
            $stmt_log->execute([$seller_id, $p['id'], $ch, $st, $msg]);
        }
    }
    echo "<p class='success'>✅ Injected multi-channel sync logs for all products.</p>";
    echo "</div>";

    // 5. Seed Smart Pricing History
    echo "<div class='card'><h2>5. Price Adjustment History</h2>";
    $pdo->exec("DELETE FROM smart_pricing_log WHERE seller_id = $seller_id");
    $stmt_hist = $pdo->prepare("INSERT INTO smart_pricing_log (seller_id, product_id, old_price, new_price, created_at) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($products as $p) {
        $base_price = (float)$p['price'];
        for ($i = 0; $i < 3; $i++) {
            $old = $base_price + rand(-500, 500);
            $new = $base_price + rand(-500, 500);
            $time = date('Y-m-d H:i:s', strtotime("-" . rand(1, 48) . " hours"));
            $stmt_hist->execute([$seller_id, $p['id'], $old, $new, $time]);
        }
    }
    echo "<p class='success'>✅ Simulated algorithmic price adjustments history.</p>";
    echo "</div>";

    echo "<h2>✨ All Demo Data Successfully Seeded!</h2>";
    echo "<a href='dashboard.php' style='display:inline-block; padding:12px 25px; background:#10b981; color:white; text-decoration:none; border-radius:30px; font-weight:bold;'>Go to Dashboard</a>";

} catch (Exception $e) {
    echo "<h3>❌ Error: " . $e->getMessage() . "</h3>";
}
?>
