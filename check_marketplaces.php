<?php
include 'config.php';
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'marketplaces'");
    if ($stmt->fetch()) {
        echo "Table 'marketplaces' exists.\n";
        $stmt = $pdo->query("SELECT * FROM marketplaces");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id']} - Name: {$row['name']}\n";
        }
    } else {
        echo "Table 'marketplaces' does not exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
