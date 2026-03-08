<?php
require 'config.php';
$stmt = $pdo->query('SELECT id, name FROM brands');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$brands = [];
foreach ($rows as $row) {
    $brands[$row['name']] = $row['id'];
}
echo json_encode($brands);
