<?php
/**
 * Admin Middleware
 * 
 * Middleware for protecting admin-only routes and verifying admin privileges
 * Handles JWT token validation and role-based access control
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/../models/User.php';

class AdminMiddleware
{
    private $jwtService;
    private $userModel;
    private $currentAdmin;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->userModel = new User();
    }

    /**
     * Check if current user is admin
     * 
     * @return bool True if user is admin
     * 
     * TODO: Implement admin check
     */
    public function isAdmin()
    {
        // TODO: Implement admin check logic
        return $this->getCurrentUser() && in_array($this->getCurrentUser()['role'], ['admin', 'moderator']);
    }

    /**
     * Require admin access - redirects or returns error if not admin
     * 
     * @return void
     * 
     * TODO: Implement admin requirement
     */
    public function requireAdmin()
    {
        try {
            $user = $this->getCurrentUser();
            
            if (!$user) {
                $this->sendUnauthorizedResponse('Authentication required');
                return;
            }
            
            if (!in_array($user['role'], ['admin', 'moderator'])) {
                $this->sendForbiddenResponse('Admin access required');
                return;
            }
            
            $this->currentAdmin = $user;
            
        } catch (Exception $e) {
            $this->sendUnauthorizedResponse('Invalid authentication token');
        }
    }

    /**
     * Require specific admin role
     * 
     * @param string $requiredRole Required role (admin, moderator)
     * @return void
     * 
     * TODO: Implement role requirement
     */
    public function requireRole($requiredRole)
    {
        $this->requireAdmin();
        
        $user = $this->getCurrentUser();
        
        if (!$user || !$this->hasRole($user['role'], $requiredRole)) {
            $this->sendForbiddenResponse("Role '$requiredRole' required");
        }
    }

    /**
     * Check if user has required role
     * 
     * @param string $userRole User's role
     * @param string $requiredRole Required role
     * @return bool True if user has role
     */
    private function hasRole($userRole, $requiredRole)
    {
        $roleHierarchy = [
            'moderator' => 1,
            'admin' => 2
        ];
        
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 1;
        
        return $userLevel >= $requiredLevel;
    }

    /**
     * Get current authenticated user
     * 
     * @return array|null User data or null if not authenticated
     * 
     * TODO: Implement current user retrieval
     */
    public function getCurrentUser()
    {
        try {
            $token = $this->extractToken();
            
            if (!$token) {
                return null;
            }
            
            // TODO: Implement JWT validation
            // $payload = $this->jwtService->validateToken($token);
            // if (!$payload) {
            //     return null;
            // }
            // 
            // // Get fresh user data from database
            // $user = $this->userModel->findById($payload['user_id']);
            // return $user ? $user : null;
            
            // Placeholder for development
            return [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'full_name' => 'Admin User'
            ];
            
        } catch (Exception $e) {
            error_log('Admin middleware error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current admin user (after requireAdmin check)
     * 
     * @return array Admin user data
     */
    public function getCurrentAdmin()
    {
        return $this->currentAdmin;
    }

    /**
     * Extract JWT token from Authorization header
     * 
     * @return string|null Token or null if not found
     */
    private function extractToken()
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Also check query parameter for GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        return null;
    }

    /**
     * Send unauthorized response
     * 
     * @param string $message Error message
     * @return void
     */
    private function sendUnauthorizedResponse($message)
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => $message
        ]);
        exit;
    }

    /**
     * Send forbidden response
     * 
     * @param string $message Error message
     * @return void
     */
    private function sendForbiddenResponse($message)
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Forbidden',
            'message' => $message
        ]);
        exit;
    }

    /**
     * Log admin action for audit trail
     * 
     * @param string $action Action performed
     * @param string $targetType Type of target (user, post, etc.)
     * @param int $targetId ID of target
     * @param array $details Additional details
     * @return void
     * 
     * TODO: Implement admin action logging
     */
    public function logAction($action, $targetType = null, $targetId = null, $details = [])
    {
        // TODO: Implement admin action logging
        // $adminLogModel = new AdminLog();
        // $adminLogModel->log(
        //     $this->currentAdmin['id'],
        //     $action,
        //     $targetType,
        //     $targetId,
        //     $details
        // );
        
        error_log("Admin action: {$action} by admin ID {$this->currentAdmin['id']}");
    }

    /**
     * Check rate limiting for admin actions
     * 
     * @param string $action Action being performed
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if allowed
     * 
     * TODO: Implement rate limiting
     */
    public function checkRateLimit($action, $maxAttempts = 10, $timeWindow = 60)
    {
        // TODO: Implement rate limiting using Redis or database
        // For now, always allow (development mode)
        return true;
    }

    /**
     * Validate admin input data
     * 
     * @param array $data Input data to validate
     * @param array $rules Validation rules
     * @return array Validated data
     * 
     * TODO: Implement input validation
     */
    public function validateInput($data, $rules)
    {
        // TODO: Implement input validation
        // Use a validation library like Respect/Validation
        return $data;
    }

    /**
     * Sanitize admin input data
     * 
     * @param array $data Input data to sanitize
     * @return array Sanitized data
     * 
     * TODO: Implement input sanitization
     */
    public function sanitizeInput($data)
    {
        // TODO: Implement input sanitization
        // Use filter_var() and other sanitization functions
        return $data;
    }
}
?>