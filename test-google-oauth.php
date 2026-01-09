\u003c?php
/**
 * Google OAuth Diagnostic Test Script
 * Run this file to check if your system is ready for Google Sign-In
 * Access: http://localhost/MINIPROJECT2.0/test-google-oauth.php
 */

echo "\u003c!DOCTYPE html\u003e\u003chtml\u003e\u003chead\u003e\u003ctitle\u003eGoogle OAuth Diagnostic\u003c/title\u003e\u003c/head\u003e\u003cbody style='font-family:Arial; padding:40px; background:#f5f5f5;'\u003e";
echo "\u003ch1 style='color:#16a34a;'\u003eGoogle OAuth Diagnostic Test\u003c/h1\u003e";

$tests = [];

// Test 1: cURL Extension
echo "\u003ch2\u003e1. Checking cURL Extension\u003c/h2\u003e";
if (function_exists('curl_version')) {
    $curl_info = curl_version();
    echo "\u003cp style='color:green;'\u003e✓ cURL is enabled (Version: {$curl_info['version']})\u003c/p\u003e";
    $tests['curl'] = true;
} else {
    echo "\u003cp style='color:red;'\u003e✗ cURL is NOT enabled. Enable it in php.ini\u003c/p\u003e";
    $tests['curl'] = false;
}

// Test 2: Database Connection
echo "\u003ch2\u003e2. Checking Database Connection\u003c/h2\u003e";
try {
    include 'config.php';
    echo "\u003cp style='color:green;'\u003e✓ Database connection successful\u003c/p\u003e";
    $tests['db'] = true;
    
    // Test 3: Check users table structure
    echo "\u003ch2\u003e3. Checking Users Table Schema\u003c/h2\u003e";
    $stmt = $pdo-\u003equery(\"DESCRIBE users\");
    $columns = $stmt-\u003efetchAll(PDO::FETCH_COLUMN);
    
    $required_columns = ['id', 'first_name', 'last_name', 'email', 'password', 'google_id', 'is_verified'];
    $missing = [];
    
    echo \"\u003ctable border='1' cellpadding='10' style='background:white; border-collapse:collapse;'\u003e\";
    echo \"\u003ctr\u003e\u003cth\u003eColumn\u003c/th\u003e\u003cth\u003eStatus\u003c/th\u003e\u003c/tr\u003e\";
    
    foreach ($required_columns as $col) {
        $exists = in_array($col, $columns);
        $status = $exists ? \"\u003cspan style='color:green;'\u003e✓ Present\u003c/span\u003e\" : \"\u003cspan style='color:red;'\u003e✗ Missing\u003c/span\u003e\";
        echo \"\u003ctr\u003e\u003ctd\u003e$col\u003c/td\u003e\u003ctd\u003e$status\u003c/td\u003e\u003c/tr\u003e\";
        if (!$exists) $missing[] = $col;
    }
    echo \"\u003c/table\u003e\";
    
    if (empty($missing)) {
        echo \"\u003cp style='color:green;'\u003e✓ All required columns exist\u003c/p\u003e\";
        $tests['schema'] = true;
    } else {
        echo \"\u003cp style='color:red;'\u003e✗ Missing columns: \" . implode(', ', $missing) . \"\u003c/p\u003e\";
        echo \"\u003cp\u003e\u003cstrong\u003eFix:\u003c/strong\u003e Run this SQL:\u003c/p\u003e\";
        foreach ($missing as $col) {
            if ($col === 'google_id') {
                echo \"\u003ccode\u003eALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL;\u003c/code\u003e\u003cbr\u003e\";
            }
        }
        $tests['schema'] = false;
    }
    
} catch (Exception $e) {
    echo \"\u003cp style='color:red;'\u003e✗ Database error: \" . $e-\u003egetMessage() . \"\u003c/p\u003e\";
    $tests['db'] = false;
    $tests['schema'] = false;
}

// Test 4: Google OAuth Configuration
echo \"\u003ch2\u003e4. Checking Google OAuth Configuration\u003c/h2\u003e\";
if (file_exists('google-config.php')) {
    include 'google-config.php';
    echo \"\u003cp style='color:green;'\u003e✓ google-config.php found\u003c/p\u003e\";
    echo \"\u003ctable border='1' cellpadding='10' style='background:white; border-collapse:collapse;'\u003e\";
    echo \"\u003ctr\u003e\u003cth\u003eSetting\u003c/th\u003e\u003cth\u003eValue\u003c/th\u003e\u003c/tr\u003e\";
    echo \"\u003ctr\u003e\u003ctd\u003eClient ID\u003c/td\u003e\u003ctd\u003e\" . GOOGLE_CLIENT_ID . \"\u003c/td\u003e\u003c/tr\u003e\";
    echo \"\u003ctr\u003e\u003ctd\u003eClient Secret\u003c/td\u003e\u003ctd\u003e\" . substr(GOOGLE_CLIENT_SECRET, 0, 10) . \"...\u003c/td\u003e\u003c/tr\u003e\";
    echo \"\u003ctr\u003e\u003ctd\u003eRedirect URI\u003c/td\u003e\u003ctd\u003e\" . GOOGLE_REDIRECT_URI . \"\u003c/td\u003e\u003c/tr\u003e\";
    echo \"\u003c/table\u003e\";
    $tests['config'] = true;
} else {
    echo \"\u003cp style='color:red;'\u003e✗ google-config.php not found\u003c/p\u003e\";
    $tests['config'] = false;
}

// Test 5: Session Support
echo \"\u003ch2\u003e5. Checking Session Support\u003c/h2\u003e\";
if (session_status() === PHP_SESSION_ACTIVE || session_start()) {
    echo \"\u003cp style='color:green;'\u003e✓ Sessions are working\u003c/p\u003e\";
    $tests['session'] = true;
} else {
    echo \"\u003cp style='color:red;'\u003e✗ Session support issue\u003c/p\u003e\";
    $tests['session'] = false;
}

// Test 6: Required Files
echo \"\u003ch2\u003e6. Checking Required Files\u003c/h2\u003e\";
$required_files = ['google-login.php', 'google-callback.php', 'config.php'];
$all_files_exist = true;
foreach ($required_files as $file) {
    $exists = file_exists($file);
    $status = $exists ? \"\u003cspan style='color:green;'\u003e✓\u003c/span\u003e\" : \"\u003cspan style='color:red;'\u003e✗\u003c/span\u003e\";
    echo \"\u003cp\u003e$status $file\u003c/p\u003e\";
    if (!$exists) $all_files_exist = false;
}
$tests['files'] = $all_files_exist;

// Summary
echo \"\u003chr\u003e\";
echo \"\u003ch2\u003eSummary\u003c/h2\u003e\";
$all_pass = !in_array(false, $tests);

if ($all_pass) {
    echo \"\u003cdiv style='background:#d1fae5; padding:20px; border-radius:8px; border-left:4px solid #16a34a;'\u003e\";
    echo \"\u003ch3 style='color:#16a34a;'\u003e✓ All Tests Passed!\u003c/h3\u003e\";
    echo \"\u003cp\u003eYour system is ready for Google Sign-In. If it's still not working:\u003c/p\u003e\";
    echo \"\u003cul\u003e\";
    echo \"\u003cli\u003eVerify your Google Cloud Console redirect URI matches exactly: \u003ccode\u003e\" . GOOGLE_REDIRECT_URI . \"\u003c/code\u003e\u003c/li\u003e\";
    echo \"\u003cli\u003eCheck browser console for JavaScript errors\u003c/li\u003e\";
    echo \"\u003cli\u003eTry in incognito/private browsing mode\u003c/li\u003e\";
    echo \"\u003cli\u003eClear browser cookies and cache\u003c/li\u003e\";
    echo \"\u003c/ul\u003e\";
    echo \"\u003c/div\u003e\";
} else {
    echo \"\u003cdiv style='background:#fee2e2; padding:20px; border-radius:8px; border-left:4px solid #ef4444;'\u003e\";
    echo \"\u003ch3 style='color:#ef4444;'\u003e✗ Some Tests Failed\u003c/h3\u003e\";
    echo \"\u003cp\u003ePlease fix the issues above before using Google Sign-In.\u003c/p\u003e\";
    echo \"\u003c/div\u003e\";
}

echo \"\u003chr\u003e\";
echo \"\u003cp\u003e\u003ca href='login.php' style='color:#16a34a;'\u003e← Back to Login\u003c/a\u003e\u003c/p\u003e\";
echo \"\u003c/body\u003e\u003c/html\u003e\";
?\u003e
