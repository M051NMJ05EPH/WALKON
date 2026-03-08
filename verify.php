<?php
include 'config.php';

$token = $_GET['token'] ?? '';

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    if ($stmt->rowCount() > 0) {
        $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?")
            ->execute([$token]);
        $message = "Email verified successfully! You can now <a href='login.php'>log in</a>.";
    } else {
        $message = "Invalid or expired verification link.";
    }
} else {
    $message = "No token provided.";
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify Email - Walkon Shoes</title></head>
<body style="text-align:center; padding:50px; font-family:Poppins,sans-serif;">
    <h2><?php echo $message; ?></h2>
</body>
</html>