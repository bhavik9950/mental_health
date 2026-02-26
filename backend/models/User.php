<?php
/**
 * User Model
 * 
 * Handles user data operations for authentication and profile management
 * Implements secure password handling and user validation
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/PasswordService.php';

class User
{
    private $db;
    private $passwordService;
    private $table = 'users';
    
    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->passwordService = new PasswordService();
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User information
     * @return int|false User ID on success, false on failure
     */
    public function create($userData)
    {
        try {
            // Validate required fields
            if (!isset($userData['email']) || !isset($userData['password'])) {
                return false;
            }
            
            // Validate password strength
            $validation = $this->passwordService->validateStrength($userData['password']);
            if (!$validation['valid']) {
                error_log("Password validation failed: " . implode(", ", $validation['errors']));
                return false;
            }
            
            // Check if email already exists
            if ($this->emailExists($userData['email'])) {
                error_log("Email already exists: " . $userData['email']);
                return false;
            }
            
            // Hash password
            $passwordHash = $this->passwordService->hash($userData['password']);
            
            // Prepare SQL
            $sql = "INSERT INTO {$this->table} 
                    (username, email, password_hash, full_name, role, is_verified, is_active, created_at) 
                    VALUES (:username, :email, :password_hash, :full_name, :role, :is_verified, :is_active, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':username' => $userData['username'] ?? substr($userData['email'], 0, strpos($userData['email'], '@')),
                ':email' => $userData['email'],
                ':password_hash' => $passwordHash,
                ':full_name' => $userData['full_name'] ?? null,
                ':role' => $userData['role'] ?? 'user',
                ':is_verified' => $userData['is_verified'] ?? 0,
                ':is_active' => $userData['is_active'] ?? 1
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find user by email
     * 
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function findByEmail($email)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            return $user ? $user : false;
            
        } catch (PDOException $e) {
            error_log("Find user by email error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return array|false User data or false if not found
     */
    public function findByUsername($username)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            return $user ? $user : false;
            
        } catch (PDOException $e) {
            error_log("Find user by username error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function findById($id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            
            return $user ? $user : false;
            
        } catch (PDOException $e) {
            error_log("Find user by ID error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify user credentials
     * 
     * @param string $email User email
     * @param string $password Plain text password
     * @return array|false User data on success, false on failure
     */
    public function verifyCredentials($email, $password)
    {
        try {
            $user = $this->findByEmail($email);
            
            if (!$user) {
                return false;
            }
            
            // Check if user is active
            if (!$user['is_active']) {
                error_log("User account is inactive: " . $email);
                return false;
            }
            
            // Verify password
            if (!$this->passwordService->verify($password, $user['password_hash'])) {
                error_log("Invalid password for user: " . $email);
                return false;
            }
            
            // Remove password hash from returned data
            unset($user['password_hash']);
            
            return $user;
            
        } catch (Exception $e) {
            error_log("Verify credentials error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password
     * 
     * @param int $id User ID
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function updatePassword($id, $newPassword)
    {
        try {
            // Validate password strength
            $validation = $this->passwordService->validateStrength($newPassword);
            if (!$validation['valid']) {
                return false;
            }
            
            $passwordHash = $this->passwordService->hash($newPassword);
            
            $sql = "UPDATE {$this->table} SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':password_hash' => $passwordHash,
                ':id' => $id
            ]);
            
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user information
     * 
     * @param int $id User ID
     * @param array $userData Updated user data
     * @return bool Success status
     */
    public function update($id, $userData)
    {
        try {
            $allowedFields = ['username', 'full_name', 'avatar_url', 'bio'];
            $updates = [];
            $params = [':id' => $id];
            
            foreach ($userData as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $updates[] = "updated_at = NOW()";
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @param int|null $excludeId User ID to exclude
     * @return bool True if exists
     */
    public function emailExists($email, $excludeId = null)
    {
        try {
            $sql = "SELECT id FROM {$this->table} WHERE email = :email";
            $params = [':email' => $email];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $sql .= " LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if username exists
     * 
     * @param string $username Username to check
     * @param int|null $excludeId User ID to exclude
     * @return bool True if exists
     */
    public function usernameExists($username, $excludeId = null)
    {
        try {
            $sql = "SELECT id FROM {$this->table} WHERE username = :username";
            $params = [':username' => $username];
            
            if ($excludeId) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }
            
            $sql .= " LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Username exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users with pagination
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string $search Search term
     * @param string $role Filter by role
     * @return array Users with pagination
     */
    public function getAll($page = 1, $limit = 20, $search = '', $role = '')
    {
        try {
            $conditions = ['is_active = 1'];
            $params = [];
            
            if ($search) {
                $conditions[] = "(username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            
            if ($role) {
                $conditions[] = "role = :role";
                $params[':role'] = $role;
            }
            
            $whereClause = "WHERE " . implode(' AND ', $conditions);
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            // Get users
            $offset = ($page - 1) * $limit;
            $sql = "SELECT id, username, email, full_name, role, is_verified, created_at 
                    FROM {$this->table} 
                    {$whereClause} 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            $stmt->execute($params);
            
            $users = $stmt->fetchAll();
            
            return [
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_users' => $total,
                    'per_page' => $limit
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return ['users' => [], 'pagination' => []];
        }
    }
    
    /**
     * Update user role
     * 
     * @param int $id User ID
     * @param string $role New role
     * @return bool Success status
     */
    public function updateRole($id, $role)
    {
        try {
            $sql = "UPDATE {$this->table} SET role = :role, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':role' => $role,
                ':id' => $id
            ]);
            
        } catch (PDOException $e) {
            error_log("Update role error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deactivate user account
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function deactivate($id)
    {
        try {
            $sql = "UPDATE {$this->table} SET is_active = 0, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':id' => $id]);
            
        } catch (PDOException $e) {
            error_log("Deactivate user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user statistics
     * 
     * @return array Statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [];
            
            // Total users
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_active = 1";
            $stmt = $this->db->query($sql);
            $stats['total_users'] = $stmt->fetch()['count'];
            
            // Users by role
            $sql = "SELECT role, COUNT(*) as count FROM {$this->table} WHERE is_active = 1 GROUP BY role";
            $stmt = $this->db->query($sql);
            $stats['by_role'] = $stmt->fetchAll();
            
            // New users today
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE DATE(created_at) = CURDATE() AND is_active = 1";
            $stmt = $this->db->query($sql);
            $stats['new_today'] = $stmt->fetch()['count'];
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Get statistics error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'by_role' => [],
                'new_today' => 0
            ];
        }
    }
}
?>