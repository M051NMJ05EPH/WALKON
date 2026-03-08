<?php
include 'config.php';
function desc($pdo, $table) {
    echo "--- $table ---\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']}\n";
    }
    echo "\n";
}

desc($pdo, 'users');
desc($pdo, 'product_base');
desc($pdo, 'sellers');
desc($pdo, 'brands');
?>
