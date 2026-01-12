<?php
include 'config.php';
$email = 'mosinmjoseph2028@mca.ajce.in';
$stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($user);
?>
