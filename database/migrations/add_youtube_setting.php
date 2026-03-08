<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute(['social_youtube', 'https://youtube.com/@walkon']);
    echo "Successfully added social_youtube setting!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
