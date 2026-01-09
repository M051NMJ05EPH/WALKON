<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if (!isset($_GET['token']) && !isset($_POST['token'])) {
    die("Invalid request!");
}

$token = $_GET['token'] ?? $_POST['token'];

// Verify token
$stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token!");
}

// Check expiration
if (strtotime($user['reset_expires']) < time()) {
    die("Token expired! Please request a new one.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($password) || empty($confirm)) {
        $error = "Please fill in all fields!";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Update password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        
        if ($stmt->execute([$hashed, $user['id']])) {
            $success = "Password updated successfully! <a href='login.php'>Login now</a>";
        } else {
            $error = "Failed to update password. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WALKON Shoes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body, html { height:100%; background:#0f172a; color:white; }
        .container {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .wrapper {
            width: 100%;
            max-width: 500px;
            background: #1e293b;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            text-align: center;
        }
        h2 { margin-bottom: 20px; color: #fff; }
        .form-group { margin-bottom: 20px; text-align: left; }
        input {
            width: 100%;
            padding: 16px;
            background: #334155;
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
        }
        input:focus { outline: none; box-shadow: 0 0 0 3px rgba(40,167,69,0.3); }
        .btn {
            width: 100%;
            padding: 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover { background: #218838; }
        .error, .success { padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .error { background: rgba(239,68,68,0.2); color: #fca5a5; }
        .success { background: rgba(34,197,94,0.2); color: #86efac; }
    </style>
</head>
<body>
    <div class="container">
        <div class="wrapper">
            <h2>Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php else: ?>
            
            <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <input type="password" name="password" required placeholder="New Password">
                </div>
                <div class="form-group">
                    <input type="password" name="confirm" required placeholder="Confirm Password">
                </div>
                
                <button type="submit" class="btn">Update Password</button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
