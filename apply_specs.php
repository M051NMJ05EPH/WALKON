<?php
include 'config.php';

$sql = file_get_contents('add_specs_tables.sql');

try {
    $pdo->exec($sql);
    echo "Successfully applied database changes.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
