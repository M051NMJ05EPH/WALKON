<?php
require 'config.php';
$stmt = $pdo->query('SELECT id, name, category_id FROM sub_categories LIMIT 10');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
