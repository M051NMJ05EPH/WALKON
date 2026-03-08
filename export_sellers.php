<?php
session_start();
include 'config.php';

// Auth Check (same as sellers.php)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'customer';

// Ensure the user has permission to download (e.g., limit to entrepreneurs, admins, or store owners)
if ($user_role === 'customer') {
    die("Access denied. You do not have permission to download this file.");
}

// Fetch Active Sellers
try {
    $stmt = $pdo->prepare("
        SELECT s.id, 
               COALESCE(s.business_name, s.name) as business_name, 
               s.name as owner_name,
               s.created_at,
               (SELECT COUNT(*) FROM product_base pb WHERE pb.seller_id = s.id AND pb.status = 'published') as product_count
        FROM sellers s
        WHERE (SELECT COUNT(*) FROM product_base pb WHERE pb.seller_id = s.id AND pb.status = 'published') > 0
        ORDER BY product_count DESC
    ");
    $stmt->execute();
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sellers)) {
        die("No active sellers found to export.");
    }

    // Set headers to force download
    $filename = "WalkOn_Sellers_Ecosystem_" . date('Y-m-d') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // Add Byte Order Mark (BOM) for Excel UTF-8 compatibility
    fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

    // Write the column headers
    fputcsv($output, [
        'Seller ID',
        'Business Name',
        'Owner Name', 
        'Established Date',
        'Active Products'
    ]);

    // Loop over the data and write it out
    foreach ($sellers as $seller) {
        $row = [
            $seller['id'],
            $seller['business_name'],
            $seller['owner_name'],
            date('F Y', strtotime($seller['created_at'])),
            $seller['product_count']
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit();

} catch (PDOException $e) {
    die("Error exporting sellers: " . $e->getMessage());
}
?>
