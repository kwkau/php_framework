<?php

class ServerDat{
    private $server_vals= array();
    public function __construct(){
        $this->load_config();
        $this->set_constants();
    }

    private function load_config(){
        try{
            // Initialize secure configuration
            SecureConfig::init();
            
            // Use secure configuration values
            $this->server_vals = [
                'DB_TYPE' => SecureConfig::get('DB_TYPE'),
                'HOST_NAME' => SecureConfig::get('HOST_NAME'),
                'DB_USER' => SecureConfig::get('DB_USER'),
                'DB_PASS' => SecureConfig::get('DB_PASS'),
                'DB_NAME' => SecureConfig::get('DB_NAME'),
                'HOST_URL' => SecureConfig::get('HOST_URL'),
                'DOMAIN_NAME' => SecureConfig::get('DOMAIN_NAME'),
                'DIGEST' => SecureConfig::get('DIGEST') ?: SecureConfig::generateSecureDigest(),
                'ADMIN_EMAIL' => SecureConfig::get('ADMIN_EMAIL'),
                'PORT' => SecureConfig::get('PORT'),
                'ENCRYPT' => SecureConfig::get('ENCRYPT') === 'true'
            ];
            
        }catch (Exception $ex){
            error_log("Configuration loading failed: " . $ex->getMessage());
            die("We are sorry for the inconvenience caused but we are having problems with our server, please try again later");
        }
   }

    private function set_constants () {
        foreach ($this->server_vals as $key => $val) {
            defined($key) ? null : define($key, $val);
        }
    }


}