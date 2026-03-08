<?php
include 'config.php';

$tables = [
    'categories',
    'sub_categories',
    'brands',
    'materials',
    'marketplaces',
    'product_base',
    'sellers',
    'users'
];

echo "DATABASE_STATUS_START\n";
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "$table: $count\n";
    } catch (Exception $e) {
        echo "$table: ERROR (" . $e->getMessage() . ")\n";
    }
}
echo "DATABASE_STATUS_END\n";
?>
