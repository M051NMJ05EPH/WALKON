<?php
include 'config.php';
try {
    $stmt = $pdo->query("SELECT id, name FROM product_base");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
