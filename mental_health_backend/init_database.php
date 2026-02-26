<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Mental Health Platform - Database Initialization</h2>";

// Include database configuration
require_once 'config.php';

try {
    // Create connection without database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Database connection successful</p>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Database '" . DB_NAME . "' created or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Create contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        query TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Contacts table created or already exists</p>";
    } else {
        throw new Exception("Error creating contacts table: " . $conn->error);
    }
    
    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Posts table created or already exists</p>";
    } else {
        throw new Exception("Error creating posts table: " . $conn->error);
    }
    
    // Create responses table
    $sql = "CREATE TABLE IF NOT EXISTS responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Responses table created or already exists</p>";
    } else {
        throw new Exception("Error creating responses table: " . $conn->error);
    }
    
    // Check table contents
    $result = $conn->query("SELECT COUNT(*) as count FROM posts");
    $row = $result->fetch_assoc();
    echo "<p>📊 Current posts in database: " . $row['count'] . "</p>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM contacts");
    $row = $result->fetch_assoc();
    echo "<p>📊 Current contact submissions: " . $row['count'] . "</p>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM responses");
    $row = $result->fetch_assoc();
    echo "<p>📊 Current responses in database: " . $row['count'] . "</p>";
    
    echo "<hr>";
    echo "<h3>🎉 Database initialization completed successfully!</h3>";
    echo "<p>You can now use the Mental Health Support Platform.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>