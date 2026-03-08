<?php
include 'config.php';
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'wishlist'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'wishlist' exists.\n";
    } else {
        echo "Table 'wishlist' DOES NOT exist.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
