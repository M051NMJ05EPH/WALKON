<?php
require 'config.php';

try {
    $stmt = $pdo->query("SHOW TABLES");
    echo "Tables in " . $db . ":\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }

    if ($pdo->query("SHOW TABLES LIKE 'brands'")->rowCount() > 0) {
        $stmt = $pdo->query("SELECT * FROM brands");
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\nExisting Brands:\n";
        foreach ($brands as $brand) {
            echo "- " . $brand['name'] . "\n";
        }
    } else {
        echo "\nBrands table does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
