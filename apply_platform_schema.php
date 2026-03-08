<?php
require_once 'config.php';

try {
    $sql = file_get_contents('create_platform_features.sql');
    $pdo->exec($sql);
    echo "Successfully created platform_features table!\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
