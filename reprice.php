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
    $stmt = $pdo->prepare("SELECT id, price, min_price, max_price FROM products WHERE seller_id = ? AND smart_pricing_status = 1");
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
            
            // Update DB
            $update = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
            $update->execute([$new_price, $p['id']]);
            $count++;
        }
    }

    echo json_encode(['success' => true, 'message' => "Successfully repriced $count products."]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
