<?php
require_once 'config.php';

$features = [
    [
        'title' => 'Multi-Channel Sync',
        'description' => 'Instant inventory and order synchronization across 15+ global marketplaces.',
        'icon' => 'fas fa-layer-group'
    ],
    [
        'title' => 'Smart Analytics',
        'description' => 'Deep insights into your sales performance with AI-driven forecasting.',
        'icon' => 'fas fa-chart-line'
    ],
    [
        'title' => 'Auto-Pricing',
        'description' => 'Stay competitive with real-time price matching algorithms.',
        'icon' => 'fas fa-bolt'
    ]
];

try {
    $pdo->beginTransaction();
    
    // Clear existing features to avoid duplicates
    $pdo->exec("TRUNCATE TABLE platform_features");
    
    $stmt = $pdo->prepare("INSERT INTO platform_features (title, description, icon) VALUES (?, ?, ?)");
    
    foreach ($features as $feature) {
        $stmt->execute([$feature['title'], $feature['description'], $feature['icon']]);
    }
    
    $pdo->commit();
    echo "Successfully seeded platform features!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error seeding data: " . $e->getMessage() . "\n";
}
?>
