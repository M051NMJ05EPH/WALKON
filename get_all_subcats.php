<?php
require 'config.php';
$stmt = $pdo->query('SELECT sc.id, sc.name, c.name as cat_name FROM sub_categories sc JOIN categories c ON sc.category_id = c.id');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
