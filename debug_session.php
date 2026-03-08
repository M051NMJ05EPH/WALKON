<?php
session_start();
include 'config.php';

echo "<h1>Session & Seller Debug</h1>";
echo "Session User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Session Email: " . ($_SESSION['email'] ?? 'Not set') . "<br>";

if (isset($_SESSION['email'])) {
    $stmt = $pdo->prepare("SELECT id, name, email FROM sellers WHERE email = ?");
    $stmt->execute([$_SESSION['email']]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($seller) {
        echo "Found Seller: <pre>" . print_r($seller, true) . "</pre>";
    } else {
        echo "No seller found with this email in the database.<br>";
        
        // Show all sellers
        $all = $pdo->query("SELECT id, name, email FROM sellers")->fetchAll(PDO::FETCH_ASSOC);
        echo "All Sellers in DB: <pre>" . print_r($all, true) . "</pre>";
    }
} else {
    echo "Please log in first.<br>";
}
?>
