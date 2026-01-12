<?php
include 'config.php';
$email = 'mosinmjoseph2028@mca.ajce.in';
$stmt = $pdo->prepare("UPDATE users SET first_name = 'mosin', last_name = 'm joseph' WHERE email = ?");
$stmt->execute([$email]);
echo "Updated " . $stmt->rowCount() . " record.";
?>
