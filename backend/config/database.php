<?php
/**
 * Database Connection Class
 * 
 * Singleton database connection manager for the Mental Health Platform
 * Provides secure and efficient database access
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

class Database
{
    private static $instance = null;
    private $connection;
    
    private $host = '127.0.0.1';
    private $username = 'root';
    private $password = '';
    private $database = 'mental_health';
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Load environment configuration
        $this->loadConfig();
        
        // Create database connection
        $this->connect();
    }
    
    /**
     * Load configuration from environment or config file
     */
    private function loadConfig()
    {
        // Check for environment variables
        if (getenv('DB_HOST')) {
            $this->host = getenv('DB_HOST');
            $this->username = getenv('DB_USER');
            $this->password = getenv('DB_PASS');
            $this->database = getenv('DB_NAME');
        } else {
            // Load from config file if available
            $configFile = __DIR__ . '/database.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
                $env = $config['environment'] ?? 'development';
                if (isset($config[$env])) {
                    $this->host = $config[$env]['host'];
                    $this->username = $config[$env]['username'];
                    $this->password = $config[$env]['password'];
                    $this->database = $config[$env]['database'];
                }
            }
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database Instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Connect to database
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Get database connection
     * 
     * @return PDO Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Prepare SQL statement
     * 
     * @param string $sql SQL query
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }
    
    /**
     * Execute SQL query
     * 
     * @param string $sql SQL query
     * @return PDOStatement
     */
    public function query($sql)
    {
        return $this->connection->query($sql);
    }
    
    /**
     * Get last inserted ID
     * 
     * @return string Last inserted ID
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->connection->rollBack();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>