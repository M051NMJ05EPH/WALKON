<?php
include 'config.php';
$s = $pdo->query('SELECT * FROM sub_categories WHERE category_id=3')->fetchAll(PDO::FETCH_ASSOC);
print_r($s);
?>
