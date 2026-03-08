<?php
/**
 * Authentication and Authorization Helper
 * Provides role-based access control and activity logging
 */

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Require user to be authenticated
 * Redirects to login if not logged in
 */
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /WALKON-rough/login.php");
        exit();
    }
}

/**
 * Require specific role(s)
 * @param string|array $roles Single role or array of allowed roles
 */
function requireRole($roles) {
    requireAuth();
    
    if (!isset($_SESSION['role'])) {
        header("Location: /WALKON-rough/login.php");
        exit();
    }
    
    // Convert single role to array for consistency
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    // Check if user's role is in allowed roles
    if (!in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        die("Access Denied: You don't have permission to access this page.");
    }
}

/**
 * Check if user has specific permission
 * @param string $permission Permission key to check
 * @return bool
 */
function hasPermission($permission) {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Admins, Entrepreneurs and Stores have broad permissions
    if (in_array($_SESSION['role'], ['admin', 'entrepreneur', 'store'])) {
        return true;
    }
    
    // Check custom permissions
    $stmt = $pdo->prepare("SELECT is_granted FROM staff_permissions WHERE user_id = ? AND permission_key = ?");
    $stmt->execute([$_SESSION['user_id'], $permission]);
    $result = $stmt->fetch();
    
    return $result ? (bool)$result['is_granted'] : false;
}

/**
 * Log user activity
 * @param string $action Action performed
 * @param string $details Optional details
 */
function logActivity($action, $details = null) {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO user_activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $action, $details, $ip_address]);
    } catch (PDOException $e) {
        // Silent fail - don't break application if logging fails
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

/**
 * Get current user's full name
 * @return string
 */
function getCurrentUserName() {
    $first = $_SESSION['first_name'] ?? '';
    $last = $_SESSION['last_name'] ?? '';
    $full = trim($first . ' ' . $last);
    return $full ?: ($_SESSION['email'] ?? 'User');
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is store/entrepreneur
 * @return bool
 */
function isSeller() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['entrepreneur', 'store']);
}

function isStoreOwner() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['entrepreneur', 'store', 'store_owner']);
}

/**
 * Check if current user can manage users
 * @return bool
 */
function canManageUsers() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'entrepreneur', 'store', 'store_owner']);
}
?>
