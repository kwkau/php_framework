<?php

/**
 * Class CsrfToken
 * Handles CSRF token generation and validation
 */
class CsrfToken {
    
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_SESSION_KEY = '_csrf_token';
    
    /**
     * Generate a CSRF token
     * @return string
     */
    public static function generate() {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_SESSION_KEY] = $token;
        return $token;
    }
    
    /**
     * Get the current CSRF token
     * @return string|null
     */
    public static function get() {
        return $_SESSION[self::TOKEN_SESSION_KEY] ?? null;
    }
    
    /**
     * Validate a CSRF token
     * @param string $token
     * @return bool
     */
    public static function validate($token) {
        $sessionToken = self::get();
        
        if (!$sessionToken || !$token) {
            return false;
        }
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Generate HTML hidden input field with CSRF token
     * @return string
     */
    public static function field() {
        $token = self::generate();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Get CSRF token from request
     * @param Request $request
     * @return string|null
     */
    public static function getFromRequest(Request $request) {
        return $request->input(self::TOKEN_NAME, false);
    }
    
    /**
     * Verify CSRF token from request
     * @param Request $request
     * @return bool
     */
    public static function verifyRequest(Request $request) {
        $token = self::getFromRequest($request);
        return self::validate($token);
    }
}