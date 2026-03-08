<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$role = $_SESSION['role'] ?? 'customer';

try {
    $seller_id = $_SESSION['seller_id'] ?? null;
    if (!$seller_id && in_array($role, ['entrepreneur', 'store', 'store_owner'])) {
        $stmt_seller = $pdo->prepare("SELECT id FROM sellers WHERE email = ?");
        $stmt_seller->execute([$email]);
        $seller = $stmt_seller->fetch();
        $seller_id = $seller ? $seller['id'] : -1;
    }

    $search = trim($_GET['search'] ?? '');
    $order_status = trim($_GET['order_status'] ?? '');
    $payment_status = trim($_GET['payment_status'] ?? '');
    $channel_filter = trim($_GET['channel'] ?? '');

    // 2. Fetch orders (Sync with my_orders.php logic)
    $sql = "SELECT o.*, pb.name as product_name, ps.sku 
            FROM orders o 
            LEFT JOIN product_base pb ON o.product_id = pb.id 
            LEFT JOIN product_skus ps ON pb.id = ps.product_id 
            WHERE 1=1";
    
    $params = [];
    
    if ($role === 'customer') {
        $sql .= " AND o.user_id = ?";
        $params[] = $_SESSION['user_id'];
    } elseif ($role !== 'admin') {
        $sql .= " AND o.seller_id = ?";
        $params[] = $seller_id;
    }

    if ($search) {
        $sql .= " AND (o.id LIKE ? OR pb.name LIKE ? OR o.customer_name LIKE ? OR ps.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($order_status) {
        $sql .= " AND o.status = ?";
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

    $sql .= " ORDER BY o.order_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSV
    $filename = "walkon_orders_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['Order ID', 'Product Name', 'SKU', 'Customer Name', 'Channel', 'Total Amount (₹)', 'Status', 'Payment', 'Date']);

    foreach ($orders as $row) {
        fputcsv($output, [
            "#" . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            $row['product_name'] ?? 'N/A',
            $row['sku'] ?? 'N/A',
            $row['customer_name'] ?? 'N/A',
            $row['channel'] ?? 'Website',
            number_format($row['total_price'], 2),
            ucfirst($row['status'] ?? 'pending'),
            ucfirst($row['payment_status'] ?? 'unpaid'),
            date('d-M-Y H:i', strtotime($row['order_date']))
        ]);
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    die("Error exporting orders: " . $e->getMessage());
}
?>
