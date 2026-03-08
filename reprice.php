<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch enabled products
    $stmt = $pdo->prepare("SELECT pp.product_id as id, pp.price, pp.min_price, pp.max_price 
                           FROM product_prices pp 
                           JOIN product_base pb ON pp.product_id = pb.id 
                           WHERE pb.seller_id = ? AND pp.smart_pricing_status = 1");
    $stmt->execute([$user_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;

    // 2. Repricing Logic
    foreach ($products as $p) {
        $min = floatval($p['min_price']);
        $max = floatval($p['max_price']);
        
        if ($min > 0 && $max >= $min) {
            // Simulated Logic: Pick a random price between Min and Max
            // In a real app, this would use API data from competitors.
            // Algorithm: Target price = Min + (Random % of (Max - Min))
            
            $range = $max - $min;
            $random_factor = rand(10, 90) / 100; // Keep it somewhat in the middle
            $new_price = $min + ($range * $random_factor);
            
            // Round to 2 decimals
            $new_price = round($new_price, 2);
            
            // Update DB (targeting product_prices)
            $update = $pdo->prepare("UPDATE product_prices SET price = ? WHERE product_id = ?");
            $update->execute([$new_price, $p['id']]);

            // 3. Log the change
            $log = $pdo->prepare("INSERT INTO smart_pricing_log (seller_id, product_id, old_price, new_price) VALUES (?, ?, ?, ?)");
            $log->execute([$user_id, $p['id'], $p['price'], $new_price]);
            
            $count++;
        }
    }

    echo json_encode(['success' => true, 'message' => "Successfully repriced $count products."]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
