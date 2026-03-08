<?php
include 'config.php';

try {
    echo "<h1>📦 Seeding Sample Orders...</h1><hr>";

    $seller_id = 1;

    // 1. Fetch valid products
    $stmt = $pdo->query("SELECT id, name FROM product_base WHERE seller_id = $seller_id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) == 0) {
        die("❌ No products found for Seller $seller_id. Please seed catalog first.");
    }

    // 2. Sample Data
    $customers = ['Aarav Patel', 'Vihaan Singh', 'Aditya Sharma', 'Kabir Das', 'Reyansh Gupta', 'Vivaan Kumar', 'Sai Iyer', 'Arjun Reddy'];
    $channels = ['Amazon', 'Flipkart', 'Website', 'Myntra', 'Ajio'];
    $statuses = ['pending', 'shipped', 'delivered', 'cancelled', 'processing'];
    
    $orders_count = 0;

    // 3. Generate 15 Orders
    for ($i = 0; $i < 15; $i++) {
        $prod = $products[array_rand($products)];
        
        // Fetch price
        $stmtPrice = $pdo->prepare("SELECT price FROM product_prices WHERE product_id = ?");
        $stmtPrice->execute([$prod['id']]);
        $price = $stmtPrice->fetchColumn() ?: 2999;

        $status = $statuses[array_rand($statuses)];
        $customer = $customers[array_rand($customers)];
        $channel = $channels[array_rand($channels)];
        
        // Logic for consistency
        $payment_status = 'paid';
        if ($status == 'pending' || $status == 'processing') {
            $payment_status = (rand(0, 1) == 0) ? 'unpaid' : 'paid';
        }
        if ($status == 'cancelled') $payment_status = 'refunded';

        $date = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days -" . rand(0, 24) . " hours"));

        $sql = "INSERT INTO orders (seller_id, product_id, customer_name, total_price, status, payment_status, channel, order_date, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([$seller_id, $prod['id'], $customer, $price, $status, $payment_status, $channel, $date]);
        
        echo "✅ Created Order: <b>{$status}</b> for {$prod['name']} (₹{$price})<br>";
        $orders_count++;
    }

    echo "<hr><h3>🎉 Success! $orders_count orders generated.</h3>";
    echo "<a href='../../my_orders.php'>View Orders</a>";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
