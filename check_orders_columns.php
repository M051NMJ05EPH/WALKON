<?php
include 'config.php';
$stmt = $pdo->query("DESCRIBE orders");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    echo $col['Field'] . "\n";
}
?>
