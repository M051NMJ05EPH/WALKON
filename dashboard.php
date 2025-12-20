<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WALKON - Dashboard</title>
    <style>
        /* Paste the full dashboard CSS + modal + JavaScript from my previous message here */
        /* (The long code with product cards and modal – exactly the same) */
        body { background: #f8f9fa; }
        /* ... all the styles and the product list with 10 shoes ... */
    </style>
</head>
<body>
<div class="container">
    <div class="logo">WALKON</div>
    <p style="font-size:24px;color:#28a745;margin:20px 0;">
        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
    </p>
    <div class="dashboard">
        <h3>Your Products</h3>
        <!-- Paste the full product-list with 10 clickable shoe cards and modal here -->
    </div>
    <a href="login.php?logout=1" class="logout-btn">Logout</a>
</div>

<!-- Modal code here (same as before) -->
<script>
    // Same JavaScript for modal as previous message
</script>
</body>
</html>