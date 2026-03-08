<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'customer';

try {
    // Fetch order details
    $sql = "SELECT o.*, pb.name as product_name, pb.id as pid, pp.price as unit_price, ps.sku,
            (SELECT url FROM product_media pm WHERE pm.product_id = pb.id AND is_primary = 1 LIMIT 1) as image
            FROM orders o
            JOIN product_base pb ON o.product_id = pb.id
            JOIN product_prices pp ON pb.id = pp.product_id
            LEFT JOIN product_skus ps ON pb.id = ps.product_id
            WHERE o.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found");
    }

    // Security check: Only customer who bought it or the seller/admin can see invoice
    if ($role === 'customer' && $order['user_id'] != $user_id) {
        die("Unauthorized access");
    }
    if ($role === 'store' || $role === 'entrepreneur' || $role === 'store_owner') {
        if ($order['seller_id'] != $_SESSION['seller_id']) {
             die("Unauthorized access");
        }
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['id'] ?> | WALKON</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; padding: 50px; color: #1e293b; line-height: 1.6; }
        .invoice-box { max-width: 800px; margin: auto; padding: 40px; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .logo { font-size: 2rem; font-weight: 800; color: #010101; }
        .logo span { color: #10b981; }
        .invoice-title { font-size: 1.5rem; font-weight: 700; text-align: right; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .details h4 { margin-bottom: 10px; font-size: 0.8rem; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table th { background: #f8fafc; padding: 15px; text-align: left; border-bottom: 2px solid #e2e8f0; font-size: 0.8rem; text-transform: uppercase; }
        .table td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .total-box { margin-left: auto; width: 300px; background: #f8fafc; padding: 20px; border-radius: 12px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: 500; }
        .grand-total { font-size: 1.2rem; font-weight: 800; color: #10b981; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 10px; }
        .footer { text-align: center; margin-top: 60px; color: #94a3b8; font-size: 0.9rem; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; }
        }
        .btn-print { background: #10b981; color: #fff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-block; cursor: pointer; border: none; }
    </style>
</head>
<body>

<div class="no-print" style="max-width: 800px; margin: 0 auto 20px; text-align: right;">
    <button onclick="window.print()" class="btn-print">Download as PDF / Print</button>
</div>

<div class="invoice-box">
    <div class="header">
        <div class="logo">WALK<span>ON</span></div>
        <div class="invoice-title">
            INVOICE<br>
            <span style="font-size: 0.9rem; color: #64748b; font-weight: 500;">#INV-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
        </div>
    </div>

    <div class="details">
        <div>
            <h4>Billed To:</h4>
            <strong><?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?></strong><br>
            <?= htmlspecialchars($order['customer_email'] ?? 'customer@mail.com') ?><br>
            <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?><br>
            <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'Online Purchase')) ?>
        </div>
        <div style="text-align: right;">
            <h4>Invoice Date:</h4>
            <?= date('j F Y', strtotime($order['order_date'])) ?><br><br>
            <h4>Order Source:</h4>
            <?= htmlspecialchars($order['channel'] ?? 'Website') ?>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Qty</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($order['product_name']) ?></strong><br>
                    <span style="font-size: 0.75rem; color: #64748b;">SKU: <?= htmlspecialchars($order['sku'] ?? 'WALK-001') ?></span>
                </td>
                <td>1</td>
                <td style="text-align: right;">₹<?= number_format($order['total_price'], 2) ?></td>
                <td style="text-align: right;">₹<?= number_format($order['total_price'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span>Subtotal</span>
            <span>₹<?= number_format($order['total_price'], 2) ?></span>
        </div>
        <div class="total-row">
            <span>Shipping</span>
            <span>FREE</span>
        </div>
        <div class="total-row">
            <span>Tax (GST 18%)</span>
            <span>Included</span>
        </div>
        <div class="total-row grand-total">
            <span>Grand Total</span>
            <span>₹<?= number_format($order['total_price'], 2) ?></span>
        </div>
    </div>

    <div class="footer">
        Thank you for shopping with WALKON Shoes.<br>
        For support, contact us at help@walkon.com
    </div>
</div>

</body>
</html>
