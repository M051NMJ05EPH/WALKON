<?php
/**
 * Activity Logger
 * Centralized logging system for tracking user activities
 */

require_once __DIR__ . '/../config.php';

class ActivityLogger {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log a user action
     */
    public function log($user_id, $action, $details = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO user_activity_logs (user_id, action, details, ip_address) 
                 VALUES (?, ?, ?, ?)"
            );
            
            $stmt->execute([$user_id, $action, $details, $ip_address]);
            return true;
        } catch (PDOException $e) {
            error_log("Activity logging failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user login
     */
    public function logLogin($user_id, $email) {
        $this->log($user_id, 'user_login', "User logged in: $email");
        
        // Update last_login timestamp
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("Failed to update last_login: " . $e->getMessage());
        }
    }
    
    /**
     * Log user logout
     */
    public function logLogout($user_id, $email) {
        $this->log($user_id, 'user_logout', "User logged out: $email");
    }
    
    /**
     * Log user created
     */
    public function logUserCreated($created_by, $new_user_id, $new_user_email, $role) {
        $this->log($created_by, 'user_created', "Created new user: $new_user_email (Role: $role, ID: $new_user_id)");
    }
    
    /**
     * Log user updated
     */
    public function logUserUpdated($updated_by, $user_id, $changes) {
        $details = "Updated user ID $user_id: " . json_encode($changes);
        $this->log($updated_by, 'user_updated', $details);
    }
    
    /**
     * Log user deactivated
     */
    public function logUserDeactivated($deactivated_by, $user_id, $email) {
        $this->log($deactivated_by, 'user_deactivated', "Deactivated user: $email (ID: $user_id)");
    }
    
    /**
     * Log user activated
     */
    public function logUserActivated($activated_by, $user_id, $email) {
        $this->log($activated_by, 'user_activated', "Activated user: $email (ID: $user_id)");
    }
    
    /**
     * Log setting change
     */
    public function logSettingChange($user_id, $setting_key, $old_value, $new_value) {
        $details = "Changed '$setting_key' from '$old_value' to '$new_value'";
        $this->log($user_id, 'setting_changed', $details);
    }
    
    /**
     * Log permission granted
     */
    public function logPermissionGranted($granted_by, $user_id, $permission) {
        $this->log($granted_by, 'permission_granted', "Granted '$permission' to user ID $user_id");
    }
    
    /**
     * Get recent activities for a user
     */
    public function getRecentActivities($user_id, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM user_activity_logs 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ?"
            );
            $stmt->execute([$user_id, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all recent activities (admin/store owner only)
     */
    public function getAllRecentActivities($limit = 50) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT ual.*, u.email, u.first_name, u.last_name 
                 FROM user_activity_logs ual 
                 JOIN users u ON ual.user_id = u.id 
                 ORDER BY ual.created_at DESC 
                 LIMIT ?"
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch all activities: " . $e->getMessage());
            return [];
        }
    }
}
?>
