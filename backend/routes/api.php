<?php
/**
 * API Routes Configuration
 * 
 * Defines all API endpoints for the Mental Health Platform
 * Handles request routing to appropriate controllers
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

// Base path for API
define('API_BASE', '/api/v1');

// Authentication routes
$routes = [
    // Authentication
    API_BASE . '/auth/register' => ['controller' => 'AuthController', 'method' => 'register'],
    API_BASE . '/auth/login' => ['controller' => 'AuthController', 'method' => 'login'],
    API_BASE . '/auth/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    API_BASE . '/auth/refresh' => ['controller' => 'AuthController', 'method' => 'refresh'],
    API_BASE . '/auth/me' => ['controller' => 'AuthController', 'method' => 'me'],
    
    // Users (requires authentication)
    API_BASE . '/users' => ['controller' => 'UserController', 'method' => 'getAll', 'auth' => true],
    API_BASE . '/users/{id}' => ['controller' => 'UserController', 'method' => 'getById', 'auth' => true],
    
    // Admin routes (requires admin role)
    API_BASE . '/admin/dashboard' => ['controller' => 'AdminController', 'method' => 'dashboard', 'auth' => true, 'role' => 'admin'],
    API_BASE . '/admin/users' => ['controller' => 'AdminController', 'method' => 'users', 'auth' => true, 'role' => 'admin'],
    API_BASE . '/admin/moderation' => ['controller' => 'AdminController', 'method' => 'moderation', 'auth' => true, 'role' => 'admin'],
    API_BASE . '/admin/analytics' => ['controller' => 'AdminController', 'method' => 'analytics', 'auth' => true, 'role' => 'admin'],
    API_BASE . '/admin/settings' => ['controller' => 'AdminController', 'method' => 'settings', 'auth' => true, 'role' => 'admin'],
    
    // Posts (existing endpoints)
    '/post.php' => ['file' => true],
    '/fetch_posts.php' => ['file' => true],
    '/fetch_responses.php' => ['file' => true],
    '/submit_response.php' => ['file' => true],
    '/contact.php' => ['file' => true],
];

/**
 * Route the request to the appropriate handler
 * 
 * @param string $uri Request URI
 * @param string $method Request method
 */
function routeRequest($uri, $method)
{
    global $routes;
    
    // Check for exact match
    if (isset($routes[$uri])) {
        $route = $routes[$uri];
        
        // Check request method
        $allowedMethods = isset($route['methods']) ? $route['methods'] : ['GET', 'POST', 'PUT', 'DELETE'];
        if (!in_array($method, $allowedMethods)) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Handle file-based routes
        if (isset($route['file'])) {
            // Include the PHP file
            $file = __DIR__ . '/../mental_health_backend' . $uri;
            if (file_exists($file)) {
                include $file;
                return;
            }
        }
        
        // Handle controller-based routes
        if (isset($route['controller'])) {
            // Check authentication
            if (isset($route['auth']) && $route['auth']) {
                $token = getBearerToken();
                if (!$token) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Authentication required']);
                    return;
                }
                
                // TODO: Validate token and check role
                // For now, allow all authenticated requests
            }
            
            // Include and instantiate controller
            $controllerFile = __DIR__ . '/controllers/' . $route['controller'] . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controller = new $route['controller']();
                $methodName = $route['method'];
                $controller->$methodName();
                return;
            }
        }
    }
    
    // 404 Not Found
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found', 'uri' => $uri]);
}

/**
 * Get Bearer token from Authorization header
 */
function getBearerToken()
{
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

// Get request info
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = strtok($requestUri, '?');

// Route the request
routeRequest($uri, $requestMethod);
?>