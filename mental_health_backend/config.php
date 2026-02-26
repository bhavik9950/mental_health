<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', ''); // No password
define('DB_NAME', 'mental_health');

// Function to get database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
?>