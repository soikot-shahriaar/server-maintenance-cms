<?php
/**
 * User Model
 * Handles user-related database operations
 */

require_once __DIR__ . '/../config/config.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create a new user
     */
    public function create($username, $email, $password, $fullName, $role = 'staff') {
        try {
            // Check if username or email already exists
            if ($this->usernameExists($username)) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            if ($this->emailExists($email)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$username, $email, $passwordHash, $fullName, $role]);
            
            if ($result) {
                return ['success' => true, 'message' => 'User created successfully', 'user_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create user'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Authenticate user login
     */
    public function login($username, $password) {
        try {
            $sql = "SELECT id, username, email, password_hash, full_name, role, is_active FROM users WHERE username = ? AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                return ['success' => true, 'message' => 'Login successful', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT id, username, email, full_name, role, created_at, is_active FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get all users
     */
    public function getAll($limit = null, $offset = 0) {
        try {
            $sql = "SELECT id, username, email, full_name, role, created_at, is_active FROM users ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$limit, $offset]);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Update user
     */
    public function update($id, $username, $email, $fullName, $role) {
        try {
            // Check if username or email already exists for other users
            if ($this->usernameExists($username, $id)) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            if ($this->emailExists($email, $id)) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$username, $email, $fullName, $role, $id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'User updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($id, $newPassword) {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$passwordHash, $id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to change password'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Deactivate user
     */
    public function deactivate($id) {
        try {
            $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'User deactivated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to deactivate user'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check if username exists
     */
    private function usernameExists($username, $excludeId = null) {
        try {
            $sql = "SELECT id FROM users WHERE username = ?";
            $params = [$username];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Check if email exists
     */
    private function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT id FROM users WHERE email = ?";
            $params = [$email];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get total user count
     */
    public function getTotalCount() {
        try {
            $sql = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>

