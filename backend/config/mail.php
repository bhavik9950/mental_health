<?php
/**
 * Email Configuration
 * 
 * Email service configuration for notifications and verification
 * 
 * @author Mental Health Platform Team
 */

return [
    'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
    'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
    'port' => $_ENV['MAIL_PORT'] ?? 587,
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@mentalhealth.com',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Mental Health Platform',
    
    // TODO: Implement email templates
    // TODO: Add email queue for better performance
    // TODO: Set up email delivery tracking
    // TODO: Implement rate limiting for email sending
];
?>