<?php

/**
 * Class SecurityHeaders
 * Handles security headers for the application
 */
class SecurityHeaders {
    
    /**
     * Set security headers to prevent common attacks
     */
    public static function setHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent content-type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy - basic policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
        
        // Prevent exposing server information
        header('Server: ');
        header('X-Powered-By: ');
        
        // HSTS - only if using HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Set CORS headers if needed
     * @param array $allowedOrigins
     * @param array $allowedMethods
     * @param array $allowedHeaders
     */
    public static function setCorsHeaders($allowedOrigins = ['*'], $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE'], $allowedHeaders = ['Content-Type', 'Authorization']) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
        }
        
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
}