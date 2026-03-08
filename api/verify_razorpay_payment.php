<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$razorpay_payment_id = $data['razorpay_payment_id'] ?? '';
$razorpay_order_id = $data['razorpay_order_id'] ?? '';
$razorpay_signature = $data['razorpay_signature'] ?? '';

$key_secret = "RIjcqpEg6Wc7RDfIoKc8uIeP";

$generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $key_secret);

if (hash_equals($generated_signature, $razorpay_signature)) {
    // Payment verified successfully.
    
    try {
        $pdo->beginTransaction();
        
        $user_id = $_SESSION['user_id'] ?? 0;
        $customer_name = $_SESSION['first_name'] ?? 'Customer';
        $customer_email = $_SESSION['email'] ?? 'customer@walkon.com';
        
        // Check if this was a cart purchase or single product purchase
        // We look into the session or we can assume cart if not specified
        // For simplicity, let's check product context passed via verify call or session
        $buy_now_product_id = $_SESSION['buy_now_product_id'] ?? null;
        $buy_now_qty = $_SESSION['buy_now_qty'] ?? 1;

        if ($buy_now_product_id) {
            // Single Product Purchase
            $stmt = $pdo->prepare("SELECT seller_id, name FROM product_base pb WHERE id = ?");
            $stmt->execute([$buy_now_product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Get price
                $p_stmt = $pdo->prepare("SELECT price FROM product_prices WHERE product_id = ?");
                $p_stmt->execute([$buy_now_product_id]);
                $price = $p_stmt->fetchColumn() ?: 0;
                
                $total_price = $price * $buy_now_qty;

                $ins = $pdo->prepare("INSERT INTO orders (user_id, seller_id, product_id, customer_name, customer_email, total_price, status, payment_status, channel, order_date) 
                                     VALUES (?, ?, ?, ?, ?, ?, 'pending', 'paid', 'Website', NOW())");
                $ins->execute([$user_id, $product['seller_id'], $buy_now_product_id, $customer_name, $customer_email, $total_price]);
                
                // Update stock
                $pdo->prepare("UPDATE product_stock SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?")
                    ->execute([$buy_now_qty, $buy_now_product_id, $buy_now_qty]);
            }
            // Clear Buy Now session
            unset($_SESSION['buy_now_product_id']);
            unset($_SESSION['buy_now_qty']);
        } else {
            // Cart Purchase
            $stmt = $pdo->prepare("SELECT c.product_id, c.quantity, pb.seller_id, pp.price 
                                 FROM cart c 
                                 JOIN product_base pb ON c.product_id = pb.id 
                                 JOIN product_prices pp ON pb.id = pp.product_id
                                 WHERE c.user_id = ?");
            $stmt->execute([$user_id]);
            $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cart_items as $item) {
                $total_price = $item['price'] * $item['quantity'];
                $ins = $pdo->prepare("INSERT INTO orders (user_id, seller_id, product_id, customer_name, customer_email, total_price, status, payment_status, channel, order_date) 
                                     VALUES (?, ?, ?, ?, ?, ?, 'pending', 'paid', 'Website', NOW())");
                $ins->execute([$user_id, $item['seller_id'], $item['product_id'], $customer_name, $customer_email, $total_price]);
                
                // Update stock
                $pdo->prepare("UPDATE product_stock SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?")
                    ->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            }

            // Clear Cart
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Payment verified and order created']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error creating order: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
}
