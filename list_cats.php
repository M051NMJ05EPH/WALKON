<?php
include 'config.php';

$cats = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cats as $c) {
    echo "ID: {$c['id']} | Name: {$c['name']} | Slug: {$c['slug']}\n";
}
?>
