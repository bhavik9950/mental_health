<?php
/**
 * JWT Configuration
 * 
 * JSON Web Token configuration for secure authentication
 * 
 * @author Mental Health Platform Team
 */

// JWT Configuration
return [
    'secret_key' => $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production',
    'algorithm' => 'HS256',
    'expiration_time' => 3600, // 1 hour in seconds
    'refresh_expiration' => 604800, // 7 days in seconds
    
    // TODO: Implement key rotation strategy
    // TODO: Add JWT blacklisting for logout functionality
    // TODO: Consider RS256 for better security (requires key pair)
    // TODO: Implement refresh token rotation
];
?>