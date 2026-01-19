<?php
include 'config.php';

try {
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1");
    $stmt->execute();
    echo "<h1>All existing users have been verified successfully!</h1>";
    echo "<p>You can now <a href='login.php'>Login here</a>.</p>";
} catch (PDOException $e) {
    echo "<h1>Error:</h1> " . $e->getMessage();
}
?>
