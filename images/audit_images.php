<?php
include 'config.php';
$stmt = $pdo->query("SELECT url, COUNT(*) as count FROM product_media GROUP BY url");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['url'] . " : " . $row['count'] . "\n";
}
?>
