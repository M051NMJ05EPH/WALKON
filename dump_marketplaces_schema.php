<?php
include 'config.php';
$stmt = $pdo->query("DESCRIBE marketplaces");
$schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($schema);
?>
