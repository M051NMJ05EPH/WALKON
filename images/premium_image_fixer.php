<?php
include 'config.php';

// 1. Setup Folders
$target_dir = 'uploads/products';
if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

// 2. High Quality Image Map (Validated URLs)
$image_map = [
    'Nike' => 'https://images.unsplash.com/photo-1597045566677-8cf032ed6634?fm=jpg&q=80&w=1000',
    'Adidas' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?fm=jpg&q=80&w=1000',
    'Puma' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?fm=jpg&q=80&w=1000',
    'Reebok' => 'https://images.unsplash.com/photo-1608667508764-33cf0726b13a?fm=jpg&q=80&w=1000',
    'Running' => 'https://images.unsplash.com/photo-1565814636199-ae8133055c1c?fm=jpg&q=80&w=1000',
    'Sneaker' => 'https://images.unsplash.com/photo-1597045566677-8cf032ed6634?fm=jpg&q=80&w=1000',
    'Default' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?fm=jpg&q=80&w=1000'
];

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

function downloadFile($url, $save_path) {
    echo "  Downloading: $url -> $save_path ... ";
    
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
            "follow_location" => 1,
            "timeout" => 30
        ]
    ];
    
    $context = stream_context_create($options);
    $data = @file_get_contents($url, false, $context);
    
    if ($data !== false && strlen($data) > 1000) {
        if (file_put_contents($save_path, $data)) {
            echo "SUCCESS\n";
            return true;
        }
    }
    
    echo "FAILED\n";
    return false;
}

try {
    echo "IMAGE FIXER STARTED\n";
    echo "===================\n";

    // 3. Get all products and their current primary image
    $stmt = $pdo->query("SELECT pb.id, pb.name, pm.id as media_id, pm.url 
                         FROM product_base pb 
                         JOIN product_media pm ON pb.id = pm.product_id 
                         WHERE pm.is_primary = 1");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $name = $p['name'];
        $pid = $p['id'];
        $mid = $p['media_id'];
        $current_url = $p['url'];

        // If it's already a local file that works, we can skip
        if (strpos($current_url, 'placeholder.com') !== false || empty($current_url) || !file_exists($current_url)) {
            echo "Fixing ID $pid ($name):\n";
            
            // Determine Best Image
            $target_url = $image_map['Default'];
            foreach ($image_map as $key => $val) {
                if (stripos($name, $key) !== false) {
                    $target_url = $val;
                    break;
                }
            }

            $filename = 'product_' . $pid . '_' . uniqid() . '.jpg';
            $save_path = $target_dir . '/' . $filename;

            if (downloadFile($target_url, $save_path)) {
                $pdo->prepare("UPDATE product_media SET url = ? WHERE id = ?")
                    ->execute([$save_path, $mid]);
            }
        }
    }

    echo "===================\n";
    echo "IMAGE FIXER COMPLETED\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
