<?php
/**
 * Authentication Controller
 * 
 * Handles user registration, login, logout, token refresh, and password reset
 * Implements JWT-based authentication with refresh tokens
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/../services/PasswordService.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/Database.php';

class AuthController
{
    private $jwtService;
    private $passwordService;
    private $userModel;
    private $db;
    
    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->passwordService = new PasswordService();
        $this->userModel = new User();
        
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        
        // Set headers
        $this->setHeaders();
    }
    
    /**
     * Set CORS and content type headers
     */
    private function setHeaders()
    {
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * User Registration
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($input['email']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password are required']);
                return;
            }
            
            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email format']);
                return;
            }
            
            // Validate password strength
            $validation = $this->passwordService->validateStrength($input['password']);
            if (!$validation['valid']) {
                http_response_code(400);
                echo json_encode(['error' => 'Password is too weak', 'details' => $validation['errors']]);
                return;
            }
            
            // Check if email already exists
            if ($this->userModel->emailExists($input['email'])) {
                http_response_code(409);
                echo json_encode(['error' => 'Email already registered']);
                return;
            }
            
            // Check if username exists (if provided)
            if (isset($input['username']) && $this->userModel->usernameExists($input['username'])) {
                http_response_code(409);
                echo json_encode(['error' => 'Username already taken']);
                return;
            }
            
            // Create user
            $userId = $this->userModel->create([
                'email' => $input['email'],
                'password' => $input['password'],
                'username' => $input['username'] ?? null,
                'full_name' => $input['full_name'] ?? null,
                'role' => 'user',
                'is_verified' => 0,
                'is_active' => 1
            ]);
            
            if (!$userId) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create user']);
                return;
            }
            
            // Generate tokens
            $accessToken = $this->jwtService->generateToken([
                'user_id' => $userId,
                'email' => $input['email'],
                'type' => 'access'
            ]);
            
            $refreshToken = $this->jwtService->generateRefreshToken($userId);
            
            // Store refresh token
            $this->storeRefreshToken($userId, $refreshToken);
            
            http_response_code(201);
            echo json_encode([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $userId,
                    'email' => $input['email'],
                    'username' => $input['username'] ?? substr($input['email'], 0, strpos($input['email'], '@')),
                    'full_name' => $input['full_name'] ?? null,
                    'role' => 'user'
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ]);
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error during registration']);
        }
    }
    
    /**
     * User Login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($input['email']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password are required']);
                return;
            }
            
            // Verify credentials
            $user = $this->userModel->verifyCredentials($input['email'], $input['password']);
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid email or password']);
                return;
            }
            
            // Generate tokens
            $accessToken = $this->jwtService->generateToken([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'type' => 'access'
            ]);
            
            $refreshToken = $this->jwtService->generateRefreshToken($user['id']);
            
            // Store refresh token
            $this->storeRefreshToken($user['id'], $refreshToken);
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Remove sensitive data
            unset($user['password_hash']);
            
            echo json_encode([
                'message' => 'Login successful',
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ]);
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error during login']);
        }
    }
    
    /**
     * Refresh Access Token
     */
    public function refresh()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['refresh_token'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Refresh token is required']);
                return;
            }
            
            // Validate refresh token
            $payload = $this->jwtService->validateRefreshToken($input['refresh_token']);
            
            if (!$payload) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid or expired refresh token']);
                return;
            }
            
            // Verify refresh token exists in database
            if (!$this->isValidRefreshToken($payload['user_id'], $input['refresh_token'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Refresh token has been revoked']);
                return;
            }
            
            // Get user
            $user = $this->userModel->findById($payload['user_id']);
            
            if (!$user || !$user['is_active']) {
                http_response_code(401);
                echo json_encode(['error' => 'User not found or inactive']);
                return;
            }
            
            // Generate new access token
            $accessToken = $this->jwtService->generateToken([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'type' => 'access'
            ]);
            
            echo json_encode([
                'access_token' => $accessToken
            ]);
            
        } catch (Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error during token refresh']);
        }
    }
    
    /**
     * User Logout
     */
    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['refresh_token'])) {
                // Validate token to get user_id
                $payload = $this->jwtService->validateRefreshToken($input['refresh_token']);
                
                if ($payload) {
                    // Remove refresh token
                    $this->removeRefreshToken($payload['user_id'], $input['refresh_token']);
                }
            }
            
            echo json_encode(['message' => 'Logged out successfully']);
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error during logout']);
        }
    }
    
    /**
     * Get Current User
     */
    public function me()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            // Get token from header
            $token = $this->getBearerToken();
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['error' => 'Authorization token required']);
                return;
            }
            
            // Validate token
            $payload = $this->jwtService->validateToken($token);
            
            if (!$payload) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid or expired token']);
                return;
            }
            
            // Get user
            $user = $this->userModel->findById($payload['user_id']);
            
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }
            
            // Remove sensitive data
            unset($user['password_hash']);
            
            echo json_encode(['user' => $user]);
            
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
    
    /**
     * Store refresh token in database
     */
    private function storeRefreshToken($userId, $token)
    {
        try {
            // Create tokens table if not exists
            $sql = "CREATE TABLE IF NOT EXISTS refresh_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token TEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id)
            )";
            $this->db->exec($sql);
            
            // Insert token
            $expiry = time() + (7 * 24 * 60 * 60); // 7 days
            $sql = "INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (:user_id, :token, FROM_UNIXTIME(:expires_at))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':expires_at' => $expiry
            ]);
            
        } catch (PDOException $e) {
            error_log("Store refresh token error: " . $e->getMessage());
        }
    }
    
    /**
     * Remove refresh token from database
     */
    private function removeRefreshToken($userId, $token)
    {
        try {
            $sql = "DELETE FROM refresh_tokens WHERE user_id = :user_id AND token = :token";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token
            ]);
            
        } catch (PDOException $e) {
            error_log("Remove refresh token error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if refresh token is valid
     */
    private function isValidRefreshToken($userId, $token)
    {
        try {
            $sql = "SELECT id FROM refresh_tokens WHERE user_id = :user_id AND token = :token AND expires_at > NOW() LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token
            ]);
            
            return $stmt->fetch() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Validate refresh token error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId)
    {
        try {
            $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $userId]);
            
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    /**
     * Get Bearer token from Authorization header
     */
    private function getBearerToken()
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

// Handle the request
$auth = new AuthController();

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Route handling
if (strpos($uri, '/register') !== false) {
    $auth->register();
} elseif (strpos($uri, '/login') !== false) {
    $auth->login();
} elseif (strpos($uri, '/refresh') !== false) {
    $auth->refresh();
} elseif (strpos($uri, '/logout') !== false) {
    $auth->logout();
} elseif (strpos($uri, '/me') !== false) {
    $auth->me();
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
?>