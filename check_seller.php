<?php
include 'config.php';
try {
    $seller = $pdo->query("SELECT * FROM sellers LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($seller) {
        echo "Found seller: " . htmlspecialchars($seller['business_name'] ?: 'No Business Name') . " (ID: " . $seller['id'] . ")<br>";
        if (empty($seller['business_name'])) {
            $pdo->exec("UPDATE sellers SET business_name = 'WalkOn Official Store' WHERE id = " . $seller['id']);
            echo "Updated seller business name.<br>";
        }
    } else {
        echo "No sellers found. Creating default...<br>";
        $pdo->exec("INSERT INTO sellers (name, email, password, business_name) VALUES ('Admin', 'admin@walkon.com', 'hashedpassword', 'WalkOn Official Store')");
        echo "Created default seller.<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
