<?php
include 'config.php';
try {
    $categories = ["Sandals & Slides", "Formal Shoes"];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_base pb JOIN categories c ON pb.category_id = c.id WHERE c.name = ?");
        $stmt->execute([$cat]);
        $count = $stmt->fetchColumn();
        echo "Category: $cat - Products: $count\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
