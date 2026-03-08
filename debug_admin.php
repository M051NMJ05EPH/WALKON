<?php
include 'config.php';

echo "<h1>🔍 Database Debugger</h1>";
echo "<p>Connected to: <strong>" . htmlspecialchars($db) . "</strong></p>";

try {
    // 1. Check if database exists
    $pdo->exec("USE `$db` ");
    echo "✅ Database `$db` found.<br>";

    // 2. Check if users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if ($tables) {
        echo "✅ Table `users` exists.<br>";
        
        // 3. Check for the admin user
        $stmt = $pdo->prepare("SELECT id, email, role, is_verified FROM users WHERE email = ?");
        $stmt->execute(['admin@walkon.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "✅ Admin record found:<br>";
            echo "<pre>"; print_r($user); echo "</pre>";
        } else {
            echo "❌ <strong>Admin user NOT found!</strong><br>";
            echo "Please run <a href='setup_admin.php'>setup_admin.php</a> to create it.";
        }

        // 4. List first 5 users for context
        $all_users = $pdo->query("SELECT email FROM users LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        echo "<br><strong>First 5 users in DB:</strong><br>";
        echo "<ul><li>" . implode("</li><li>", $all_users) . "</li></ul>";

    } else {
        echo "❌ <strong>Table `users` DOES NOT exist!</strong><br>";
        echo "Please run your database setup scripts first.";
    }

} catch (PDOException $e) {
    echo "<h2>❌ Database Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    // Check available databases
    echo "<br><strong>Available Databases:</strong><br>";
    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul><li>" . implode("</li><li>", $dbs) . "</li></ul>";
}
?>
