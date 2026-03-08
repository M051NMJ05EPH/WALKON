<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// 1. Receive Access Token from Frontend
$input = json_decode(file_get_contents('php://input'), true);
$accessToken = $input['access_token'] ?? null;

if (!$accessToken) {
    echo json_encode(['success' => false, 'error' => 'No access token received']);
    exit;
}

// 2. Verify Token & Get User Info from Google
// We use the UserInfo endpoint which proves the token is valid and belongs to the user
$userInfoUrl = "https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $accessToken;

// Use curl for better reliability than file_get_contents
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// IMPORTANT: Force IPv4 if local issues persist, but usually not needed. 
// curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode(['success' => false, 'error' => 'Failed to verify token with Google']);
    exit;
}

$googleUser = json_decode($response, true);

if (!isset($googleUser['email'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid Google User Data']);
    exit;
}

// 3. User Data
$email = $googleUser['email'];
$googleId = $googleUser['sub'];
$firstName = $googleUser['given_name'] ?? 'User';
$lastName = $googleUser['family_name'] ?? '';
$picture = $googleUser['picture'] ?? '';

try {
    // 4. Check if user exists in Database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // User exists: Login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
    } else {
        // User new: Register
        // Create a random secure password (they can reset it later if they want to use email/pass)
        $randomPassword = bin2hex(random_bytes(16));
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
        
        $newUserId = $pdo->lastInsertId();
        
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database Error: ' . $e->getMessage()]);
}
?>
