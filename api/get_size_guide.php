<?php
header('Content-Type: application/json');
include __DIR__ . '/../config.php';
session_start();

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($product_id <= 0) {
    echo json_encode(['error' => 'Invalid Product ID']);
    exit;
}

try {
    // 1. Fetch Product Category & Brand
    $stmtProd = $pdo->prepare("
        SELECT pb.category_id, s.name as sub_category, b.name as brand
        FROM product_base pb
        LEFT JOIN sub_categories s ON pb.sub_category_id = s.id
        LEFT JOIN product_specs spec ON pb.id = spec.product_id
        LEFT JOIN brands b ON spec.brand_id = b.id
        WHERE pb.id = ?
    ");
    $stmtProd->execute([$product_id]);
    $product = $stmtProd->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // 2. Fetch Size Guide Chart
    // Logic: Look for category specific guide first, else fallback to 'Men' (default)
    // In a real app, logic would map product category to size chart category
    $chart_category = 'Men'; // Default
    if (stripos($product['sub_category'] ?? '', 'Women') !== false) {
        $chart_category = 'Women';
    } elseif (stripos($product['sub_category'] ?? '', 'Kids') !== false) {
        $chart_category = 'Kids';
    }

    // Fetch chart data
    $stmtChart = $pdo->prepare("SELECT uk_size as UK, us_size as US, eu_size as EU, cm_length as CM FROM size_guides WHERE category = ? ORDER BY id ASC");
    $stmtChart->execute([$chart_category]);
    $size_chart = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    if (empty($size_chart)) {
        // Fallback to Men if specific category not found
        $stmtChart->execute(['Men']);
        $size_chart = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Fit Finder Logic (Recommendation)
    $recommendation = null;
    $recommendation_msg = null;

    if ($user_id > 0) {
        $stmtHistory = $pdo->prepare("
            SELECT o.size, b.name as brand
            FROM orders o
            JOIN product_base pb ON o.product_id = pb.id
            LEFT JOIN product_specs spec ON pb.id = spec.product_id
            LEFT JOIN brands b ON spec.brand_id = b.id
            WHERE o.user_id = ? AND o.size IS NOT NULL
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $stmtHistory->execute([$user_id]);
        $orders = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

        if ($orders) {
            $sizes = [];
            $brand_sizes = [];
            
            foreach ($orders as $o) {
                $s = trim($o['size']);
                if (!$s) continue;
                $sizes[] = $s;
                // Check if brand matches current product
                if (isset($product['brand']) && isset($o['brand']) && 
                    strcasecmp($product['brand'], $o['brand']) === 0) {
                    $brand_sizes[] = $s;
                }
            }

            // Decide recommendation
            if (!empty($brand_sizes)) {
                $counts = array_count_values($brand_sizes);
                arsort($counts);
                $best_size = array_key_first($counts);
                $recommendation = $best_size;
                $recommendation_msg = "Based on your past {$product['brand']} orders, we recommend Size <b>$best_size</b>.";
            } elseif (!empty($sizes)) {
                $counts = array_count_values($sizes);
                arsort($counts);
                $best_size = array_key_first($counts);
                $recommendation = $best_size;
                $recommendation_msg = "Based on your previous orders, we recommend Size <b>$best_size</b> for this model.";
            }
        }
    }

    if (!$recommendation) {
        $recommendation_msg = "True to Size. We recommend ordering your usual size.";
        $recommendation = null;
    }

    echo json_encode([
        'chart' => $size_chart,
        'recommendation' => [
            'size' => $recommendation,
            'message' => $recommendation_msg
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
