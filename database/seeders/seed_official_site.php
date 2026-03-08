<?php
include 'config.php';

try {
    // Check if it already exists
    $stmt = $pdo->prepare("SELECT id FROM marketplaces WHERE name = 'Official Website'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "Official Website already exists in marketplaces.\n";
        exit;
    }

    // Insert Official Website
    $sql = "INSERT INTO marketplaces (name, logo_url, description, website_url, display_order) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'Official Website',
        'assets/shoe_logo_green.png', // Using the internal logo
        'Your primary direct-to-consumer channel. Manage your own storefront, brand experience, and customer relationships directly.',
        'index.php', // Links back to our own site or a specific preview
        0 // Priority order
    ]);

    echo "✅ Official Website added to marketplaces successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
