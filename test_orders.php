<?php
// Simple test page without authentication to verify orders display
include 'config.php';

try {
    // Use seller_id = 1 for testing
    $seller_id = 1;
    
    $sql = "SELECT 
                o.*, 
                pb.name as product_name, 
                ps.sku
            FROM orders o 
            LEFT JOIN product_base pb ON o.product_id = pb.id
            LEFT JOIN product_skus ps ON pb.id = ps.product_id
            WHERE o.seller_id = ?
            ORDER BY o.order_date DESC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("<div style='padding: 20px; background: #fee; color: #c00;'>Error: " . $e->getMessage() . "</div>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Orders Test - WALKON</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #0B0F19; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #10b981; color: black; }
        .status { padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; }
        .status-shipped { background: #10b981; color: black; }
        .status-pending { background: #fbbf24; color: black; }
        .status-delivered { background: #3b82f6; color: white; }
        .status-cancelled { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <h1 style="color: #10b981;">Orders Test Page</h1>
    <p>Total Orders Found: <strong><?php echo count($orders); ?></strong></p>
    
    <?php if (count($orders) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#ORD-<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['sku']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <span class="status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo ucfirst($order['payment_status']); ?></td>
                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: #fbbf24;">No orders found.</p>
    <?php endif; ?>
    
    <div style="margin-top: 30px; padding: 15px; background: #1f2937; border-radius: 8px;">
        <h3 style="color: #10b981;">✓ Test Results:</h3>
        <ul>
            <li>Database connection: <strong style="color: #10b981;">OK</strong></li>
            <li>Orders query: <strong style="color: #10b981;">OK</strong></li>
            <li>Data retrieval: <strong style="color: #10b981;">OK</strong></li>
        </ul>
        <p><a href="my_orders.php" style="color: #10b981;">Go to My Orders Page →</a></p>
    </div>
</body>
</html>
