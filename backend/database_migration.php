<?php
/**
 * Database Migration Script
 * 
 * Creates enhanced database schema with users table for authentication
 * Run this script to set up the required database structure
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

require_once __DIR__ . '/config/Database.php';

echo "<h2>Database Migration - Mental Health Platform</h2>";

try {
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    echo "<p>✅ Database connection successful</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        avatar_url VARCHAR(255),
        bio TEXT,
        role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
        is_verified BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->exec($sql) === FALSE) {
        throw new Exception("Error creating users table: " . implode(", ", $db->errorInfo()));
    }
    echo "<p>✅ Users table created successfully</p>";
    
    // Create refresh_tokens table
    $sql = "CREATE TABLE IF NOT EXISTS refresh_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->exec($sql) === FALSE) {
        throw new Exception("Error creating refresh_tokens table: " . implode(", ", $db->errorInfo()));
    }
    echo "<p>✅ Refresh tokens table created successfully</p>";
    
    // Create user_profiles table
    $sql = "CREATE TABLE IF NOT EXISTS user_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        bio TEXT,
        mental_health_tags JSON,
        preferred_help_type JSON,
        privacy_settings JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->exec($sql) === FALSE) {
        throw new Exception("Error creating user_profiles table: " . implode(", ", $db->errorInfo()));
    }
    echo "<p>✅ User profiles table created successfully</p>";
    
    // Create admin_logs table
    $sql = "CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        target_type VARCHAR(50),
        target_id INT,
        details JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->exec($sql) === FALSE) {
        throw new Exception("Error creating admin_logs table: " . implode(", ", $db->errorInfo()));
    }
    echo "<p>✅ Admin logs table created successfully</p>";
    
    // Add columns to posts table if they don't exist
    try {
        $sql = "ALTER TABLE posts ADD COLUMN IF NOT EXISTS (
            category ENUM('anxiety', 'depression', 'stress', 'relationships', 'work', 'general') DEFAULT 'general',
            severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
            is_anonymous BOOLEAN DEFAULT TRUE,
            is_urgent BOOLEAN DEFAULT FALSE,
            tags JSON,
            view_count INT DEFAULT 0,
            user_id INT,
            status ENUM('active', 'hidden', 'deleted', 'under_review') DEFAULT 'active'
        )";
        $db->exec($sql);
        echo "<p>✅ Posts table enhanced with new columns</p>";
    } catch (Exception $e) {
        // Columns might already exist, continue
    }
    
    // Create password_resets table
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reset_token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token (reset_token),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($db->exec($sql) === FALSE) {
        throw new Exception("Error creating password_resets table: " . implode(", ", $db->errorInfo()));
    }
    echo "<p>✅ Password resets table created successfully</p>";
    
    // Insert default admin user (password: Admin@123)
    $passwordHash = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
    
    $sql = "INSERT IGNORE INTO users (username, email, password_hash, full_name, role, is_verified, is_active) 
            VALUES ('admin', 'admin@mentalhealth.com', :password_hash, 'System Administrator', 'admin', 1, 1)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':password_hash' => $passwordHash]);
    
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Default admin user created (admin@mentalhealth.com / Admin@123)</p>";
    } else {
        echo "<p>ℹ️ Admin user already exists</p>";
    }
    
    echo "<hr>";
    echo "<h3>🎉 Database migration completed successfully!</h3>";
    echo "<p>You can now use the authentication system.</p>";
    echo "<h4>Login Credentials:</h4>";
    echo "<ul>";
    echo "<li>Admin: admin@mentalhealth.com / Admin@123</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>