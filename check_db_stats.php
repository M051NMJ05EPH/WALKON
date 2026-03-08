<?php
include 'config.php';
$tables = ['categories', 'sub_categories', 'product_base', 'product_media', 'brands', 'sellers', 'marketplaces', 'orders'];
foreach($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: $count\n";
    } catch(Exception $e) {
        echo "$t: Error - " . $e->getMessage() . "\n";
    }
}
?>
