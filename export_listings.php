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
    $status_filter = trim($_GET['status'] ?? '');
    $category_filter = trim($_GET['category'] ?? '');
    $channel_filter = trim($_GET['channel'] ?? '');

    // Build Dynamic Query (Sync with my_listings.php logic)
    // Build Dynamic Query (Sync with my_listings.php logic)
    $query = "SELECT 
                pb.id, 
                pb.name as product_name, 
                ps.sku, 
                c.name as category, 
                pp.price, 
                pst.quantity, 
                pb.status, 
                GROUP_CONCAT(pch.channel_name) as channels, 
                pb.created_at
              FROM product_base pb
              LEFT JOIN product_skus ps ON pb.id = ps.product_id
              LEFT JOIN product_prices pp ON pb.id = pp.product_id
              LEFT JOIN product_stock pst ON pb.id = pst.product_id
              LEFT JOIN categories c ON pb.category_id = c.id
              LEFT JOIN product_channels pch ON pb.id = pch.product_id
              WHERE pb.seller_id = ?";
              
    $params = [$seller_id];

    if ($search) {
        $query .= " AND (pb.name LIKE ? OR ps.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($status_filter) {
        $query .= " AND pb.status = ?";
        $params[] = $status_filter;
    }
    if ($category_filter) {
        $query .= " AND c.name = ?";
        $params[] = $category_filter;
    }
    if ($channel_filter) {
        // Filter products that have the specific channel
        $query .= " AND EXISTS (SELECT 1 FROM product_channels pch_filter WHERE pch_filter.product_id = pb.id AND pch_filter.channel_name = ?)";
        $params[] = $channel_filter;
    }

    $query .= " GROUP BY pb.id, ps.sku, c.name, pp.price, pst.quantity, pb.status, pb.created_at";
    $query .= " ORDER BY pb.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate CSV
    $filename = "listings_report_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['ID', 'Product Name', 'SKU', 'Category', 'Price', 'Quantity', 'Status', 'Channels', 'Created At']);

    foreach ($products as $row) {
        fputcsv($output, [
            $row['id'],
            $row['product_name'],
            $row['sku'],
            $row['category'],
            $row['price'],
            $row['quantity'],
            $row['status'],
            $row['channels'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit();

} catch (PDOException $e) {
    die("Error exporting listings: " . $e->getMessage());
}
?>
