<?php
session_start();
include 'config.php'; // Your PDO database connection

// Check if there's a pending Google verification
if (!isset($_SESSION['google_pending']) || $_SESSION['google_pending'] !== true) {
    header("Location: login.php");
    exit();
}

// Validate action parameter
$action = $_POST['action'] ?? '';

if ($action === 'cancel') {
    // User cancelled - Clear all Google session data and redirect to login
    unset($_SESSION['google_pending']);
    unset($_SESSION['google_email']);
    unset($_SESSION['google_name']);
    unset($_SESSION['google_first_name']);
    unset($_SESSION['google_last_name']);
    unset($_SESSION['google_id']);
    unset($_SESSION['google_picture']);
    unset($_SESSION['google_verified']);
    
    // Redirect to login with optional message
    header("Location: login.php");
    exit();
    
} elseif ($action === 'continue') {
    // User confirmed - Create/link account and log them in
    
    // Retrieve Google data from session
    $email = $_SESSION['google_email'];
    $first_name = $_SESSION['google_first_name'];
    $last_name = $_SESSION['google_last_name'];
    $google_id = $_SESSION['google_id'];
    $is_verified = $_SESSION['google_verified'];
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, google_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        // Existing user - Sign In
        if (empty($existing_user['google_id'])) {
            // Link Google account to existing user
            $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?")
                ->execute([$google_id, $existing_user['id']]);
        }
        
        // Fetch full user data to get names
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->execute([$existing_user['id']]);
        $user_data = $stmt->fetch();

        // Set session variables for logged-in user
        $_SESSION['user_id'] = $existing_user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $user_data['first_name'];
        $_SESSION['last_name'] = $user_data['last_name'];
        
    } else {
        // New user - Create account
        $random_pass = bin2hex(random_bytes(16)); // Random password (not used for Google login)
        $hashed_pass = password_hash($random_pass, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, google_id, is_verified, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$first_name, $last_name, $email, $hashed_pass, $google_id, $is_verified]);
        
        $new_id = $pdo->lastInsertId();
        
        // Set session variables for logged-in user
        $_SESSION['user_id'] = $new_id;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
    }
    
    // Clear all temporary Google session data
    unset($_SESSION['google_pending']);
    unset($_SESSION['google_email']);
    unset($_SESSION['google_name']);
    unset($_SESSION['google_first_name']);
    unset($_SESSION['google_last_name']);
    unset($_SESSION['google_id']);
    unset($_SESSION['google_picture']);
    unset($_SESSION['google_verified']);
    
    // Redirect to dashboard
    header("Location: dashboard.php");
    exit();
    
} else {
    // Invalid action - redirect to login
    header("Location: login.php");
    exit();
}
?>
