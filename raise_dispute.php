<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? null;
$customer_id = $_SESSION['user_id'];
$message = "";

// Fetch order details to ensure it belongs to the customer
$stmt_order = $pdo->prepare("SELECT o.*, s.business_name as seller_name FROM orders o JOIN sellers s ON o.seller_id = s.id WHERE o.id = ? AND o.customer_name = (SELECT first_name FROM users WHERE id = ?)"); // Note: Simplistic join for demo
$stmt_order->execute([$order_id, $customer_id]);
$order = $stmt_order->fetch();

if (!$order) {
    die("Order not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_dispute'])) {
    $reason = $_POST['reason'];
    $description = $_POST['description'];
    $seller_id = $order['seller_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO disputes (order_id, customer_id, seller_id, reason, description, status) VALUES (?, ?, ?, ?, ?, 'open')");
        $stmt->execute([$order_id, $customer_id, $seller_id, $reason, $description]);
        
        $message = "<div class='alert alert-success'>Dispute raised successfully. Our team will review your case.</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raise Dispute | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --bg: #030712;
            --card-bg: #111827;
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin:0; padding:0; box-sizing:border-box; font-family:'Outfit', sans-serif; }
        body { background: var(--bg); color: var(--text-main); min-height: 100vh; padding: 40px; }

        .container { max-width: 700px; margin: 0 auto; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--text-dim); text-decoration: none; margin-bottom: 30px; }

        .form-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--border);
        }

        .order-info {
            background: rgba(16, 185, 129, 0.05);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .form-group { margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-dim); }
        select, textarea {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--border);
            color: #fff;
            outline: none;
            font-size: 1rem;
        }
        textarea { height: 150px; resize: none; }
        select:focus, textarea:focus { border-color: var(--primary); }

        .btn-submit {
            background: var(--primary);
            color: #000;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }

        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: var(--primary); border: 1px solid var(--primary); }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

    <div class="container">
        <a href="my_orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Orders</a>

        <header style="margin-bottom:40px;">
            <h1 style="font-size: 2.5rem; margin-bottom:10px;">Report an Issue</h1>
            <p style="color: var(--text-dim);">Tell us what went wrong with your order.</p>
        </header>

        <?= $message ?>

        <div class="form-card">
            <div class="order-info">
                <div style="font-size: 0.8rem; text-transform:uppercase; color:var(--text-dim); margin-bottom:5px;">Order #<?= $order_id ?></div>
                <div style="font-weight:700; font-size:1.2rem;"><?= htmlspecialchars($order['seller_name']) ?></div>
                <div style="font-size: 0.9rem; color:var(--text-dim);">Placed on: <?= date('M d, Y', strtotime($order['created_at'])) ?></div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Why are you raising this dispute?</label>
                    <select name="reason" required>
                        <option value="">-- Select Reason --</option>
                        <option value="Product not received">Product not received</option>
                        <option value="Damaged product">Damaged product</option>
                        <option value="Wrong item sent">Wrong item sent</option>
                        <option value="Item not as described">Item not as described</option>
                        <option value="Quality issues">Quality issues</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tell us more</label>
                    <textarea name="description" placeholder="Please provide details about the issue..." required></textarea>
                </div>

                <button type="submit" name="submit_dispute" class="btn-submit">Submit Dispute</button>
            </form>
        </div>
    </div>

</body>
</html>
