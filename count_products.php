<?php
include 'config.php';
$p_count = $pdo->query("SELECT COUNT(*) FROM product_base")->fetchColumn();
$s_count = $pdo->query("SELECT COUNT(*) FROM product_specs")->fetchColumn();
echo "Products: $p_count | Specs: $s_count";
?>
