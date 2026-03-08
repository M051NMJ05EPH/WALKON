<?php
require 'config.php';

// Requested subdirectories to keep
$keep = ['Men', 'Women', 'Boy', 'Girl', 'Babies', 'Kids', 'Unisex'];

try {
    $pdo->beginTransaction();

    // 1. Log what we are keeping
    $placeholders = str_repeat('?,', count($keep) - 1) . '?';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sub_categories WHERE name NOT IN ($placeholders)");
    $stmt->execute($keep);
    $deleted_count = $stmt->fetchColumn();

    // 2. Delete entries not in the keep list
    $stmt_del = $pdo->prepare("DELETE FROM sub_categories WHERE name NOT IN ($placeholders)");
    $stmt_del->execute($keep);

    $pdo->commit();
    echo "Successfully cleaned up subcategories.\n";
    echo "Removed $deleted_count entries.\n";
    echo "Remaining subcategories: " . implode(', ', $keep) . "\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
?>
