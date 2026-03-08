<?php
include 'config.php';
function desc($pdo, $table) {
    echo "--- $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } catch (Exception $e) {
        echo "Error or table missing\n";
    }
    echo "\n";
}

desc($pdo, 'sellers');
desc($pdo, 'brands');
?>
