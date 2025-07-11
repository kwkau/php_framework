<?php

/**
 * Class SecureConfig
 * Handles secure configuration management with environment variables
 */
class SecureConfig {
    
    private static $config = [];
    
    /**
     * Load configuration from environment variables with fallback to JSON
     */
    public static function init() {
        // Load from environment variables first
        self::loadFromEnvironment();
        
        // Fallback to JSON config if environment variables not set
        if (empty(self::$config)) {
            self::loadFromJson();
        }
    }
    
    /**
     * Load configuration from environment variables
     */
    private static function loadFromEnvironment() {
        $envVars = [
            'DB_TYPE' => 'mysql',
            'HOST_NAME' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_NAME' => 'hbcmis',
            'HOST_URL' => 'http://localhost/agile',
            'DOMAIN_NAME' => 'http://localhost/agile',
            'DIGEST' => null,
            'ADMIN_EMAIL' => 'admin@example.com',
            'PORT' => '1250',
            'ENCRYPT' => 'false'
        ];
        
        foreach ($envVars as $key => $default) {
            $value = getenv($key);
            if ($value !== false) {
                self::$config[$key] = $value;
            } elseif ($default !== null) {
                self::$config[$key] = $default;
            }
        }
    }
    
    /**
     * Load configuration from JSON file (fallback)
     */
    private static function loadFromJson() {
        $configFile = __DIR__ . '/../config/config.json';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['server'])) {
                self::$config = array_merge(self::$config, $config['server']);
            }
        }
    }
    
    /**
     * Get configuration value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Check if configuration key exists
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        return isset(self::$config[$key]);
    }
    
    /**
     * Set configuration value (for testing purposes)
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::$config[$key] = $value;
    }
    
    /**
     * Generate a secure digest if not provided
     * @return string
     */
    public static function generateSecureDigest() {
        return bin2hex(random_bytes(64));
    }
}