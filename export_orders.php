<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

try {
    // 1. Get seller record
    $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
    $stmt_seller->execute([$email]);
    $seller = $stmt_seller->fetch();
    $seller_id = $seller ? $seller['id'] : -1;

    $search = trim($_GET['search'] ?? '');
    $order_status = trim($_GET['order_status'] ?? '');
    $payment_status = trim($_GET['payment_status'] ?? '');
    $channel_filter = trim($_GET['channel'] ?? '');

    // 2. Fetch orders (Sync with my_orders.php logic)
    $sql = "SELECT o.*, pb.name as product_name, ps.sku 
            FROM orders o 
            LEFT JOIN product_base pb ON o.product_id = pb.id 
            LEFT JOIN product_skus ps ON pb.id = ps.product_id 
            WHERE o.seller_id = ?";
    
    $params = [$seller_id];

    if ($search) {
        $sql .= " AND (o.id LIKE ? OR pb.name LIKE ? OR o.customer_name LIKE ? OR ps.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($order_status) {
        $sql .= " AND o.order_status = ?";
        $params[] = $order_status;
    }
    if ($payment_status) {
        $sql .= " AND o.payment_status = ?";
        $params[] = $payment_status;
    }
    if ($channel_filter) {
        $sql .= " AND o.channel = ?";
        $params[] = $channel_filter;
    }

    $sql .= " ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSV
    $filename = "orders_report_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['Order ID', 'Product Name', 'SKU', 'Customer Name', 'Channel', 'Total Amount', 'Order Status', 'Payment Status', 'Date']);

    foreach ($orders as $row) {
        fputcsv($output, [
            $row['id'],
            $row['product_name'],
            $row['sku'],
            $row['customer_name'],
            $row['channel'],
            $row['total_amount'],
            $row['order_status'],
            $row['payment_status'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    die("Error exporting orders: " . $e->getMessage());
}
?>
