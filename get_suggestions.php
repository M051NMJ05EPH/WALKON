<?php
// get_suggestions.php
// Returns a JSON array of product names and SKUs matching the query for autocomplete

require_once 'config.php'; // assumes $pdo is defined

header('Content-Type: application/json');

$search = trim($_GET['q'] ?? '');
if (strlen($search) < 1) {
    echo json_encode([]);
    exit;
}

try {
    // Search product names and SKUs, limit to 10 results
    $stmt = $pdo->prepare("SELECT DISTINCT name FROM product_base WHERE name LIKE ? LIMIT 10");
    $stmt->execute(["%{$search}%"]);
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt2 = $pdo->prepare("SELECT DISTINCT sku FROM product_skus WHERE sku LIKE ? LIMIT 10");
    $stmt2->execute(["%{$search}%"]);
    $skus = $stmt2->fetchAll(PDO::FETCH_COLUMN);

    $stmt3 = $pdo->prepare("SELECT DISTINCT name FROM brands WHERE name LIKE ? LIMIT 10");
    $stmt3->execute(["%{$search}%"]);
    $brands = $stmt3->fetchAll(PDO::FETCH_COLUMN);

    $results = array_unique(array_merge($names, $skus, $brands));
    echo json_encode(array_values($results));
} catch (PDOException $e) {
    // In case of error, return empty array
    echo json_encode([]);
}
?>
