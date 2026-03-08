<?php
session_start();
include 'config.php';

// Demo Login Bypass for Testing
$_SESSION['user_id'] = 1; // Assuming there is at least one user
$_SESSION['email'] = 'test@example.com';
$_SESSION['first_name'] = 'Demo';
$_SESSION['last_name'] = 'User';

header("Location: dashboard.php");
exit();
?>
