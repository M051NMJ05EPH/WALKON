<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$amount = $data['amount'] ?? 0;
$product_id = $data['product_id'] ?? null;
$qty = $data['qty'] ?? 1;

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount.']);
    exit;
}

// Store product context in session if it's a single product purchase
if ($product_id) {
    $_SESSION['buy_now_product_id'] = $product_id;
    $_SESSION['buy_now_qty'] = $qty;
} else {
    // Clear any leftover Buy Now session if doing a cart checkout
    unset($_SESSION['buy_now_product_id']);
    unset($_SESSION['buy_now_qty']);
}

$key_id = "rzp_test_SJT6Nr8fIlTpbw";
$key_secret = "RIjcqpEg6Wc7RDfIoKc8uIeP";

$receipt = "rcptid_" . uniqid();

$order_data = [
    "amount" => $amount * 100, // paise
    "currency" => "INR",
    "receipt" => $receipt
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Enable resolving over IPv4/IPv6 automatically or set specific SSL versions if needed
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['success' => false, 'message' => 'cURL Error: ' . $err]);
} else {
    $res = json_decode($response, true);
    if(isset($res['id'])) {
        echo json_encode(['success' => true, 'order_id' => $res['id']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create order', 'razorpay_response' => $res]);
    }
}
