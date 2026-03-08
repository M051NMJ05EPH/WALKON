<?php
include 'config.php';
echo "Total Orders: " . $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
