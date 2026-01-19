<?php
include 'config.php';

echo "--- Inspecting Legacy Data ---\n";
// ID 1 and 3
$stmt = $pdo->query("SELECT id, product_name, images FROM products WHERE id IN (1, 3)");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID {$r['id']} ({$r['product_name']}): {$r['images']}\n";
}

echo "\n--- Cleaning Bad URLs ---\n";
// Delete rows where URL is likely a webpage
$bad_domains = ['amazon', 'myntra', 'flipkart', 'relaxofootwear', 'kickscrew', 'airoxnigen', 'istockphoto', 'vsathletics', 'goxip', 'google', 'adidas.co', 'nike.com']; 
// Also specific extensions that are definitely pages
$bad_extensions = ['.html', '.php', '.asp', '.jsp'];

$deleted = 0;
foreach ($bad_domains as $d) {
    $sql = "DELETE FROM product_media WHERE url LIKE ? AND url NOT LIKE '%.jpg' AND url NOT LIKE '%.png' AND url NOT LIKE '%.webp' AND url NOT LIKE '%.jpeg' AND url NOT LIKE 'images.unsplash.com%'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$d%"]);
    $deleted += $stmt->rowCount();
}

foreach ($bad_extensions as $ext) {
    $sql = "DELETE FROM product_media WHERE url LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$ext%"]);
    $deleted += $stmt->rowCount();
}
echo "Deleted $deleted invalid media rows.\n";

echo "\n--- Promoting Valid Images ---\n";
// Ensure every product has a primary image if it has any media
$products = $pdo->query("SELECT id FROM product_base")->fetchAll(PDO::FETCH_COLUMN);
foreach ($products as $pid) {
    // Check if has primary
    $has_prim = $pdo->query("SELECT COUNT(*) FROM product_media WHERE product_id = $pid AND is_primary = 1")->fetchColumn();
    
    if (!$has_prim) {
        // Find a candidate (prefer local/valid ext)
        $stmt = $pdo->query("SELECT id, url FROM product_media WHERE product_id = $pid ORDER BY id ASC");
        $medias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($medias)) {
            $best_id = $medias[0]['id'];
            foreach ($medias as $m) {
                if (strpos($m['url'], 'uploads/') === 0) {
                    $best_id = $m['id'];
                    break;
                }
            }
            $pdo->query("UPDATE product_media SET is_primary = 1 WHERE id = $best_id");
            echo "Promoted media ID $best_id for Product $pid to primary.\n";
        }
    }
}
?>
