<?php
include 'config.php';
try {
    $pid = 106;
    $new_name = 'Nike Breathable Mesh Speed Runner';
    $nike_brand_id = 1;
    
    $pdo->beginTransaction();
    
    // Update name
    $stmt = $pdo->prepare("UPDATE product_base SET name = ? WHERE id = ?");
    $stmt->execute([$new_name, $pid]);
    
    // Update brand in specs
    $stmt = $pdo->prepare("UPDATE product_specs SET brand_id = ? WHERE product_id = ?");
    $stmt->execute([$nike_brand_id, $pid]);
    
    $pdo->commit();
    echo "Successfully updated product ID 106 to Nike.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
