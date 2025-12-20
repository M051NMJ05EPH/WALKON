<?php
session_start();

header('Content-Type: application/json');

$credential = $_POST['credential'] ?? null;

if (!$credential) {
    echo json_encode(['success' => false, 'error' => 'No credential provided']);
    exit;
}

// Your Google Client ID
$CLIENT_ID = "YOUR_CLIENT_ID.apps.googleusercontent.com"; // ← Replace with your Client ID

// Fetch token info from Google
$tokenInfo = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . $credential);

if (!$tokenInfo) {
    echo json_encode(['success' => false, 'error' => 'Failed to verify token']);
    exit;
}

$data = json_decode($tokenInfo, true);

// Verify the token
if (
    isset($data['error']) ||
    $data['aud'] !== $CLIENT_ID ||
    $data['iss'] !== 'https://accounts.google.com' ||
    !isset($data['email_verified']) ||
    $data['email_verified'] !== true
) {
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

// Token is valid! Get user info
$user = [
    'email' => $data['email'],
    'name'  => $data['name'] ?? $data['email'],
    'picture' => $data['picture'] ?? '',
    'google_id' => $data['sub']
];

// Save to session (user is now logged in)
$_SESSION['user'] = $user;
$_SESSION['logged_in'] = true;

// Return success
echo json_encode([
    'success' => true,
    'name' => $user['name'],
    'email' => $user['email'],
    'picture' => $user['picture']
]);

exit;
?>