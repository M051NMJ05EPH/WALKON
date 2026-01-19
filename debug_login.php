<?php
include 'config.php';

$email = 'reibinchackothomas2028@mca.ajce.in';

try {
    $stmt = $pdo->prepare("SELECT id, email, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<h1>User Found!</h1>";
        echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
        echo "<p>Verified: " . ($user['is_verified'] ? 'YES' : 'NO') . "</p>";
        
        if (!$user['is_verified']) {
            $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?")->execute([$email]);
            echo "<p><strong>Update:</strong> User has now been verified!</p>";
        }
        
        echo "<p>You should now be able to login at <a href='login.php'>login.php</a>.</p>";
    } else {
        echo "<h1>User NOT found in database!</h1>";
        echo "<p>Please make sure you have registered with this email: $email</p>";
    }
} catch (PDOException $e) {
    echo "<h1>Database Error:</h1> " . $e->getMessage();
}
?>
