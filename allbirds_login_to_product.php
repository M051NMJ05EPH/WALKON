<?php

// Configuration
$baseUrl = 'https://www.allbirds.com';
$loginUrl = $baseUrl . '/account/login';
$productUrl = $baseUrl . '/products/mens-wool-runners'; // Change to your desired product page, e.g., '/products/tree-runner-go'

// Replace with real credentials (for testing only – never hardcode in production!)
$email = 'your@email.com';
$password = 'yourpassword';

// Optional: Where to save cookies for session persistence
$cookieFile = 'cookies.txt';

$ch = curl_init();

// Step 1: Load homepage (to get initial cookies/session)
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36');
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

$homepage = curl_exec($ch);
if (curl_errno($ch)) {
    die('Error loading homepage: ' . curl_error($ch));
}

// Step 2: "Click" login button – go to login page (optional, but ensures form token if needed)
curl_setopt($ch, CURLOPT_URL, $loginUrl);
$loginPage = curl_exec($ch);
if (curl_errno($ch)) {
    die('Error loading login page: ' . curl_error($ch));
}
echo "Reached login page successfully.\n";

// Step 3: Submit login form (POST request)
// Common Allbirds/Shopify form fields – inspect the login page if they change
$postData = [
    'customer[email]'    => $email,
    'customer[password]' => $password,
    // 'form_type' => 'customer_login', // Sometimes needed
    // 'utf8' => '✓',
];

curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

$afterLogin = curl_exec($ch);
if (curl_errno($ch)) {
    die('Login error: ' . curl_error($ch));
}

// Basic check for successful login (look for account dashboard elements or redirect)
if (strpos($afterLogin, 'account') !== false || strpos($afterLogin, 'Log out') !== false) {
    echo "Login successful!\n";
} else {
    echo "Login may have failed – check credentials or CAPTCHA.\n";
    // file_put_contents('after_login.html', $afterLogin); // Debug: save page
}

// Step 4: Now navigate to the product detail page (while logged in)
curl_setopt($ch, CURLOPT_URL, $productUrl);
curl_setopt($ch, CURLOPT_POST, false); // GET request

$productPage = curl_exec($ch);
if (curl_errno($ch)) {
    die('Error loading product page: ' . curl_error($ch));
}

echo "Successfully reached product page after login!\n";

// Output or process product details
echo substr($productPage, 0, 1000) . "...\n"; // Preview

// Optional: Save the product page HTML for further parsing (e.g., extract title, price, images)
file_put_contents('product_page_after_login.html', $productPage);

curl_close($ch);
unlink($cookieFile); // Clean up (optional)

?>