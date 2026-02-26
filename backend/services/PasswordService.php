<?php
/**
 * Password Service
 * 
 * Handles secure password hashing and verification
 * Uses PHP's built-in password hashing functions
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

class PasswordService
{
    /**
     * Hash a password
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => 12
        ]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing
     * 
     * @param string $hash Hashed password
     * @return bool True if needs rehashing
     */
    public function needsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, [
            'cost' => 12
        ]);
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return array Validation result with errors
     */
    public function validateStrength($password)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate a random secure password
     * 
     * @param int $length Password length
     * @return string Generated password
     */
    public function generateRandom($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $charLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charLength - 1)];
        }
        
        return $password;
    }
    
    /**
     * Generate password reset token
     * 
     * @return string Reset token
     */
    public function generateResetToken()
    {
        return bin2hex(random_bytes(32));
    }
}
?>