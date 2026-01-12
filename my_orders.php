<?php
session_start();
include 'config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

try {
    // 1. Get seller record
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    
    if (!$seller) {
        // Auto-fix: If seller doesn't exist, redirect to fix_backend or show error
        die("<div style='padding:40px; text-align:center; font-family:sans-serif;'>
                <h2>Account Sync Required</h2>
                <p>Your seller profile needs to be synchronized. Please run the backend fix to continue.</p>
                <a href='fix_backend.php' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Run Backend Sync</a>
             </div>");
    }

    $seller_id = $seller['id'];

    // 2. Fetch orders with product details
    $sql = "SELECT o.*, p.product_name, p.sku, p.images 
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            WHERE o.seller_id = ? 
            ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    if ($e->getCode() == '42S02') { // Table not found
        die("<div style='padding:40px; text-align:center; font-family:sans-serif;'>
                <h2>Database Table Missing</h2>
                <p>The orders table is missing from your database.</p>
                <a href='fix_backend.php' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Create Tables Now</a>
             </div>");
    }
    die("Error fetching orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders & Payment Status - WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --secondary: #1e293b;
            --text-dark: #333;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --border: #e9ecef;
        }
        
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }
        body { background: var(--bg-light); color: var(--text-dark); padding: 40px; }

        .container { max-width: 1200px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-size: 28px; }
        
        .btn-back {
            background: #fff; color: var(--text-dark); padding: 10px 20px; border-radius: 30px; text-decoration: none; font-weight: 500; border: 1px solid var(--border);
        }

        .order-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-collapse: collapse;
        }
        .order-table th, .order-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .order-table th { background: #fcfcfc; font-weight: 600; color: #555; font-size: 14px; }
        
        .product-cell { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; background: #eee; }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-paid { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        
        .empty-state { text-align: center; padding: 60px; color: #888; background: white; border-radius: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Orders & Payment Status</h1>
            <p>Track your sales and payments</p>
        </div>
        <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if (count($orders) > 0): ?>
        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php 
                        $images = json_decode($order['images'], true);
                        $img_url = !empty($images) ? $images[0] : 'https://via.placeholder.com/50';
                    ?>
                    <tr>
                        <td>#ORD-<?php echo $order['id']; ?></td>
                        <td>
                            <div class="product-cell">
                                <img src="<?php echo htmlspecialchars($img_url); ?>" class="product-img" onerror="this.src='https://via.placeholder.com/50'">
                                <div>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                    <div style="font-size:12px; color:#888;">SKU: <?php echo htmlspecialchars($order['sku']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                            <div style="font-size:12px; color:#888;"><?php echo htmlspecialchars($order['channel']); ?></div>
                        </td>
                        <td style="font-weight:700;">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo htmlspecialchars($order['order_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                                <?php echo htmlspecialchars($order['payment_status']); ?>
                            </span>
                        </td>
                        <td style="font-size:13px; color:#666;"><?php echo date('d M, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-cart" style="font-size: 50px; margin-bottom: 20px;"></i>
            <h3>No orders found yet</h3>
            <p>Your orders will appear here once customers start buying your products.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
