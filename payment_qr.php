<?php
session_start();
include 'config.php';

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    die("Error: Order ID is required.");
}

try {
    $stmt = $pdo->prepare("SELECT o.*, pb.name as product_name 
                           FROM orders o 
                           LEFT JOIN product_base pb ON o.product_id = pb.id 
                           WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Error: Order not found.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$amount = $order['total_price'];
$upiId = 'walkon@okaxis';
$name = 'WalkOn Store';
$cleanAmount = number_format($amount, 2, '.', '');
$upiString = "upi://pay?pa={$upiId}&pn=" . rawurlencode($name) . "&am={$cleanAmount}&cu=INR&tn=" . rawurlencode("Payment for ORD-{$order_id}");
$qrUrl = "https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl=" . rawurlencode($upiString) . "&choe=UTF-8";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Payment - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #a855f7;
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --text-white: #ffffff;
            --text-muted: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg-dark); color: var(--text-white); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .payment-card {
            background: var(--card-bg);
            max-width: 450px;
            width: 100%;
            border-radius: 40px;
            padding: 50px 40px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        .logo { font-size: 1.8rem; font-weight: 900; margin-bottom: 30px; letter-spacing: -1px; }
        .logo span { color: var(--primary-purple); }

        .qr-wrapper {
            background: white;
            padding: 20px;
            border-radius: 30px;
            margin: 30px auto;
            display: inline-block;
            box-shadow: 0 0 40px rgba(168, 85, 247, 0.2);
        }
        .qr-wrapper img { width: 250px; height: 250px; border-radius: 15px; }

        .amount { font-size: 2.5rem; font-weight: 900; color: var(--primary-purple); margin-bottom: 5px; }
        .order-info { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px; }

        .upi-box {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-bottom: 30px;
        }

        .btn-confirm {
            display: block;
            width: 100%;
            padding: 18px;
            background: var(--primary-purple);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 800;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(168, 85, 247, 0.3);
        }
        .btn-confirm:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(168, 85, 247, 0.4); }

        .footer-note { font-size: 0.8rem; color: var(--text-muted); margin-top: 30px; }
    </style>
</head>
<body>

    <div class="payment-card">
        <div class="logo"><span>WALK</span>ON</div>
        
        <h2 style="margin-bottom: 10px;">Complete Payment</h2>
        <p class="order-info">For Order #ORD-<?php echo $order_id; ?> (<?php echo htmlspecialchars($order['product_name']); ?>)</p>

        <div class="amount">₹<?php echo number_format($amount, 2); ?></div>
        
        <div class="qr-wrapper">
            <img src="<?php echo $qrUrl; ?>" alt="Payment QR Code">
        </div>

        <div class="upi-box">
            Scan with GPay, PhonePe, or Paytm<br>
            VPA: <strong style="color: white;"><?php echo $upiId; ?></strong>
        </div>

        <a href="my_orders.php" class="btn-confirm">I have paid!</a>

        <div class="footer-note">
            <i class="fas fa-lock"></i> Secure 256-bit encrypted transaction
        </div>
    </div>

</body>
</html>
