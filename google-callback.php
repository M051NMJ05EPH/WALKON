<?php
session_start();
include 'config.php'; // Your PDO database connection and Google OAuth Settings

if (!isset($_GET['code'])) {
    die("Error: Authorization denied or cancelled.");
}

// Exchange code for tokens
$token_url = 'https://oauth2.googleapis.com/token';
$post_data = [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    echo "<h2>Google Token Exchange Failed</h2>";
    echo "<p>The authorization code exchange did not return an access token.</p>";
    echo "<h3>Raw Response from Google:</h3>";
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc;'>" . htmlspecialchars($response) . "</pre>";
    echo "<h3>Sent Data (for verification):</h3>";
    $debug_data = $post_data;
    $debug_data['client_secret'] = '********'; // Hide secret
    echo "<pre style='background:#f4f4f4; padding:15px; border:1px solid #ccc;'>" . print_r($debug_data, true) . "</pre>";
    die();
}

// Get user info
$userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo';
$ch = curl_init($userinfo_url . '?access_token=' . $token['access_token']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userinfo = curl_exec($ch);
curl_close($ch);

$user = json_decode($userinfo, true);

if (!isset($user['email']) || !isset($user['email_verified'])) {
    die("Error: Could not retrieve verified email from Google.");
}

$email = $user['email'];
$name = $user['name'] ?? explode('@', $email)[0];
$google_id = $user['sub'];
$is_verified = $user['email_verified'] ? 1 : 0;
$picture = $user['picture'] ?? '';

// Extract first and last names
$first_name = $user['given_name'] ?? 'User';
$last_name = $user['family_name'] ?? '';

// If names are empty, try splitting "name"
if ($first_name === 'User' && !empty($user['name'])) {
    $parts = explode(' ', $user['name'], 2);
    $first_name = $parts[0];
    $last_name = $parts[1] ?? '';
}

// Store Google user data in session for verification page
$_SESSION['google_pending'] = true;
$_SESSION['google_email'] = $email;
$_SESSION['google_name'] = $name;
$_SESSION['google_first_name'] = $first_name;
$_SESSION['google_last_name'] = $last_name;
$_SESSION['google_id'] = $google_id;
$_SESSION['google_picture'] = $picture;
$_SESSION['google_verified'] = $is_verified;

// Redirect to verification page
header("Location: google-verify.php");
exit();
?>