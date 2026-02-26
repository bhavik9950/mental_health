<?php
/**
 * JWT Service
 * 
 * Handles JSON Web Token generation, validation, and refresh
 * Provides secure authentication token management
 * 
 * @author Mental Health Platform Team
 * @version 2.0
 */

require_once __DIR__ . '/../config/jwt.php';

class JWTService
{
    private $secretKey;
    private $algorithm;
    private $expirationTime;
    private $refreshExpiration;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../config/jwt.php';
        $this->secretKey = $config['secret_key'];
        $this->algorithm = $config['algorithm'];
        $this->expirationTime = $config['expiration_time'];
        $this->refreshExpiration = $config['refresh_expiration'];
    }
    
    /**
     * Generate JWT token
     * 
     * @param array $payload Data to encode in token
     * @return string Generated JWT token
     */
    public function generateToken($payload)
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expirationTime;
        
        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => 'mental_health_platform'
        ]);
        
        return $this->encode($tokenPayload);
    }
    
    /**
     * Generate refresh token
     * 
     * @param int $userId User ID
     * @return string Refresh token
     */
    public function generateRefreshToken($userId)
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->refreshExpiration;
        
        $payload = [
            'user_id' => $userId,
            'type' => 'refresh',
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => 'mental_health_platform'
        ];
        
        return $this->encode($payload);
    }
    
    /**
     * Validate JWT token
     * 
     * @param string $token Token to validate
     * @return array|false Decoded token payload or false if invalid
     */
    public function validateToken($token)
    {
        try {
            $decoded = $this->decode($token);
            
            if (!$decoded) {
                return false;
            }
            
            // Check expiration
            if (isset($decoded['exp']) && $decoded['exp'] < time()) {
                return false;
            }
            
            // Check issuer
            if (isset($decoded['iss']) && $decoded['iss'] !== 'mental_health_platform') {
                return false;
            }
            
            return $decoded;
            
        } catch (Exception $e) {
            error_log("JWT validation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate refresh token
     * 
     * @param string $token Refresh token
     * @return array|false Token payload or false if invalid
     */
    public function validateRefreshToken($token)
    {
        $decoded = $this->validateToken($token);
        
        if (!$decoded || !isset($decoded['type']) || $decoded['type'] !== 'refresh') {
            return false;
        }
        
        return $decoded;
    }
    
    /**
     * Encode payload to JWT
     * 
     * @param array $payload Data to encode
     * @return string Encoded token
     */
    private function encode($payload)
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];
        
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->sign($headerEncoded . "." . $payloadEncoded);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }
    
    /**
     * Decode JWT to payload
     * 
     * @param string $token Token to decode
     * @return array|false Decoded payload or false if invalid
     */
    private function decode($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // Verify signature
        $signature = $this->sign($headerEncoded . "." . $payloadEncoded);
        $expectedSignature = $this->base64UrlEncode($signature);
        
        if (!hash_equals($signatureEncoded, $expectedSignature)) {
            return false;
        }
        
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        return $payload;
    }
    
    /**
     * Sign data with HMAC
     * 
     * @param string $data Data to sign
     * @return string Signature
     */
    private function sign($data)
    {
        return hash_hmac('sha256', $data, $this->secretKey, true);
    }
    
    /**
     * Base64 URL encode
     * 
     * @param string $data Data to encode
     * @return string Encoded data
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     * 
     * @param string $data Data to decode
     * @return string Decoded data
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Get token expiration time
     * 
     * @return int Expiration time in seconds
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }
    
    /**
     * Get refresh token expiration time
     * 
     * @return int Expiration time in seconds
     */
    public function getRefreshExpiration()
    {
        return $this->refreshExpiration;
    }
}
?>