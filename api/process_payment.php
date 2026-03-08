<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Basic validation
$payment_method = $_POST['payment_method'] ?? '';
$customer_name = $_POST['full_name'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? '';
$total_price = $_POST['total_price'] ?? 0;

if (empty($payment_method) || empty($customer_name) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

try {
    // Simulate order creation or update
    // For this demo, we'll insert a new record into the orders table
    // Assuming seller_id is 1 (Owner) and product_id is a sample
    
    $seller_id = 1;
    $product_id = 1; // Default sample product
    
    $stmt = $pdo->prepare("INSERT INTO orders (seller_id, product_id, customer_name, total_price, status, payment_status, channel, order_date, created_at) 
                           VALUES (?, ?, ?, ?, 'processing', ?, 'Website', NOW(), NOW())");
    
    // Determine payment status based on method (COD is unpaid, others are paid for this demo)
    $payment_status = ($payment_method === 'cod') ? 'unpaid' : 'paid';
    
    $stmt->execute([$seller_id, $product_id, $customer_name, $total_price, $payment_status]);
    $order_id = $pdo->lastInsertId();

    // --- NEW: Wallet & Commission Logic ---
    if ($payment_status === 'paid' && $total_price > 0) {
        $commission_rate = 0.10; // 10% platform commission
        $commission_amount = $total_price * $commission_rate;
        $net_amount = $total_price - $commission_amount;

        // Get or Create seller's wallet
        $stmt_wallet = $pdo->prepare("SELECT id FROM wallets WHERE seller_id = ?");
        $stmt_wallet->execute([$seller_id]);
        $wallet = $stmt_wallet->fetch();
        
        if (!$wallet) {
            $pdo->prepare("INSERT INTO wallets (seller_id, balance) VALUES (?, 0)")->execute([$seller_id]);
            $wallet_id = $pdo->lastInsertId();
        } else {
            $wallet_id = $wallet['id'];
        }

        // Update balance
        $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?")
            ->execute([$net_amount, $wallet_id]);
        
        // Record transaction
        $pdo->prepare("INSERT INTO wallet_transactions (wallet_id, order_id, type, amount, commission_deducted, description) VALUES (?, ?, 'credit', ?, ?, ?)")
            ->execute([$wallet_id, $order_id, $net_amount, $commission_amount, "Payment for Order #$order_id (10% Comm. Deducted)"]);
    }
    // --------------------------------------

    echo json_encode([
        'success' => true, 
        'message' => 'Payment processed successfully!',
        'order_id' => $order_id,
        'redirect' => 'my_orders.php'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
