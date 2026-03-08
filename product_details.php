<?php
$id = isset($_GET['id']) ? $_GET['id'] : '';
header("Location: product_detail.php?id=" . $id);
exit;
?>
