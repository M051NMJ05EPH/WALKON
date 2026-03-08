<?php
include 'config.php';

$target_dir = 'uploads/categories';
if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

$cat_images = [
    'Sneakers' => 'https://images.unsplash.com/photo-1597045566677-8cf032ed6634?fm=jpg&q=80&w=1200',
    'Boots' => 'https://images.unsplash.com/photo-1520639889313-72721e0ab9ef?fm=jpg&q=80&w=1200',
    'Sports' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?fm=jpg&q=80&w=1200',
    'Running Shoes' => 'https://images.unsplash.com/photo-1608667508764-33cf0726b13a?fm=jpg&q=80&w=1200',
    'Formal Shoes' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?fm=jpg&q=80&w=1200',
    'Sandals & Slides' => 'https://images.unsplash.com/photo-1603808041750-51c880a5f0c4?fm=jpg&q=80&w=1200'
];

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
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as $cat) {
        $name = $cat['name'];
        if (isset($cat_images[$name])) {
            $url = $cat_images[$name];
            $filename = 'cat_' . $cat['id'] . '.jpg';
            $save_path = $target_dir . '/' . $filename;
            
            if (downloadFile($url, $save_path)) {
                $pdo->prepare("UPDATE categories SET image_url = ? WHERE id = ?")
                    ->execute([$save_path, $cat['id']]);
            }
        }
    }
    echo "Category images updated.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
