<?php
// Function to download image
function downloadImage($url, $savePath) {
    echo "Downloading $url to $savePath...\n";
    $ch = curl_init($url);
    $fp = fopen($savePath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    
    if ($error) {
        echo "Error downloading: $error\n";
        return false;
    }
    return true;
}

// URLs for replacement images
// Reebok: A clean training shoe (white/grey)
$reebok_url = 'https://images.unsplash.com/photo-1579338559194-a162d19bd842?auto=format&fit=crop&w=800&q=80';
// Timberland: A classic brown boot
$timberland_url = 'https://images.unsplash.com/photo-1605034313761-73ea4a0cfbf3?auto=format&fit=crop&w=800&q=80';

downloadImage($reebok_url, 'uploads/reebok_nano_x3_alt.jpg');
downloadImage($timberland_url, 'uploads/timberland_6inch_alt.jpg');

echo "Download complete.\n";
?>
