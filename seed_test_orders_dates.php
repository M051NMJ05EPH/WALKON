<?php
require_once 'config.php';

$dates = ['2026-01-28', '2026-01-29', '2026-01-30'];

// Fetch products. prioritize those with 'img_' images if possible, or just all.
// The user said "all images are added", so let's try to get all products.
// We'll join with product_prices to get the price.

$sql = "SELECT p.id, p.seller_id, p.name, pr.price 
        FROM product_base p 
        JOIN product_prices pr ON p.id = pr.product_id 
        WHERE p.status = 'published' OR p.status = 'draft'"; // Include drafts just in case

try {
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($products) . " products.\n";

    if (count($products) == 0) {
        die("No products found to seed orders for.\n");
    }

    $orders_count = 0;

    foreach ($dates as $date) {
        echo "Seeding orders for date: $date\n";
        
        foreach ($products as $product) {
            // Generate some random variation in time
            $time = str_pad(rand(9, 20), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $datetime = "$date $time";
            
            // Random customer details
            $customers = ['Amit Kumar', 'Priya Singh', 'Rahul Sharma', 'Sneha Gupta', 'Vikram Patel'];
            $customer_name = $customers[array_rand($customers)];
            $customer_email = strtolower(str_replace(' ', '.', $customer_name)) . '@example.com';
            
            $quantity = rand(1, 3);
            $total_price = $product['price'] * $quantity;
            
            $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            // Weighted random status
            $rand = rand(1, 100);
            if ($rand <= 20) $status = 'pending';
            elseif ($rand <= 40) $status = 'processing';
            elseif ($rand <= 70) $status = 'shipped';
            elseif ($rand <= 90) $status = 'delivered';
            else $status = 'cancelled';

            $insertSql = "INSERT INTO orders (seller_id, product_id, customer_name, customer_email, quantity, unit_price, total_price, status, order_date, channel) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Direct')";
            
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                $product['seller_id'],
                $product['id'],
                $customer_name,
                $customer_email,
                $quantity,
                $product['price'],
                $total_price,
                $status,
                $datetime
            ]);
            
            $orders_count++;
        }
    }

    echo "Successfully inserted $orders_count orders across 3 dates.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
