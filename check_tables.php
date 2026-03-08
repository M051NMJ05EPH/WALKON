<?php
include 'config.php';
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in " . $db . ":\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\nSchema of sellers:\n";
    $stmt = $pdo->query("DESCRIBE sellers");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
