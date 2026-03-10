<?php
include 'config.php';
$products_to_check = ['Gel-Kayano 30', 'Triple S Sneaker', 'City Formal Derby', 'Arizona Soft Footbed'];
foreach ($products_to_check as $name) {
    $count = $pdo->query("SELECT COUNT(*) FROM product_base WHERE name = '$name'")->fetchColumn();
    echo "$name: $count\n";
}
