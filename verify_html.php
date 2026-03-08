<?php
$url = "http://localhost/MINIPROJECT2.0/product_details.php?id=126";
$html = file_get_contents($url);

if ($html) {
    if (strpos($html, 'class="color-dot"') !== false) {
        echo "✅ Color dots found.\n";
    } else {
        echo "❌ Color dots NOT found.\n";
    }
    
    if (strpos($html, 'class="size-box"') !== false) {
        echo "✅ Size boxes found.\n";
    } else {
        echo "❌ Size boxes NOT found.\n";
    }

    // Print a sample to be sure
    preg_match_all('/class="size-box"[^>]*>(.*?)<\/span>/', $html, $matches);
    if (!empty($matches[1])) {
        echo "Sizes found: " . implode(", ", $matches[1]) . "\n";
    }
} else {
    echo "Failed to fetch page.\n";
}
?>
