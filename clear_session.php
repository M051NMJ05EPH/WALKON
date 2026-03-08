<?php
// Emergency session clearer - visit this page to break redirect loops
session_start();
session_unset();
session_destroy();

// Also clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url=login.php">
    <title>Session Cleared</title>
    <style>
        body { font-family: Arial, sans-serif; background: #05070a; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { text-align: center; padding: 40px; background: rgba(255,255,255,0.05); border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); }
        h2 { color: #10b981; margin-bottom: 10px; }
        p { color: #94a3b8; }
        a { color: #10b981; }
    </style>
</head>
<body>
<div class="box">
    <h2>✓ Session Cleared</h2>
    <p>All cookies and session data have been cleared.</p>
    <p>Redirecting to <a href="login.php">login page</a> in 2 seconds...</p>
</div>
</body>
</html>
