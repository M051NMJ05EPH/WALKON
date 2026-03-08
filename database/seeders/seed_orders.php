<?php
/**
 * Seed Orders - Populates sample transactions with payment data
 */
include __DIR__ . '/../../config.php';

try {
    $pdo->exec("USE `walkon_shoes_v2` ");
    echo "<h1>🛒 Seeding Sample Orders</h1>";

    // 1. Get Seller
    $seller = $pdo->query("SELECT id FROM sellers LIMIT 1")->fetch();
    if (!$seller) die("No sellers found. Run seeder first.");
    $seller_id = $seller['id'];

    // 2. Get Products
    $products = $pdo->query("SELECT id, name FROM product_base LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($products)) die("No products found. Run product seeder first.");

    // 3. Sample Data Arrays
    $customers = ['Amit Sharma', 'Priya Patel', 'Rajesh Kumar', 'Sneha Reddy', 'Vikram Singh', 'Ananya Gupta'];
    $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    $payment_statuses = ['unpaid', 'paid', 'failed'];
    $channels = ['Direct', 'Amazon', 'Flipkart', 'Instagram'];

    $added = 0;
    for ($i = 0; $i < 15; $i++) {
        $product = $products[array_rand($products)];
        $customer = $customers[array_rand($customers)];
        $status = $statuses[array_rand($statuses)];
        $pay_status = ($status === 'delivered') ? 'paid' : $payment_statuses[array_rand($payment_statuses)];
        $channel = $channels[array_rand($channels)];
        $qty = rand(1, 2);
        
        // Fetch price
        $price_stmt = $pdo->prepare("SELECT price FROM product_prices WHERE product_id = ? LIMIT 1");
        $price_stmt->execute([$product['id']]);
        $unit_price = $price_stmt->fetchColumn() ?: 4999.00;
        $total_price = $unit_price * $qty;

        $date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 10) . ' days'));

        $stmt = $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, customer_email, channel, quantity, unit_price, total_price, status, payment_status, payment_link, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $payment_link = "https://rzp.io/l/walkon-demo-" . rand(100, 999);
        
        $stmt->execute([
            $seller_id, 
            $product['id'], 
            $customer, 
            strtolower(str_replace(' ', '.', $customer)) . "@example.com",
            $channel,
            $qty,
            $unit_price,
            $total_price,
            $status,
            $pay_status,
            $payment_link,
            $date
        ]);
        $added++;
    }

    echo "✅ Successfully added <strong>$added</strong> orders to walkon_shoes_v2.<br>";
    echo "<p><a href='my_orders.php'>View Orders Page</a></p>";

} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
