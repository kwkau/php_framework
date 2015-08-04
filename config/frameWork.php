<?php

class frameWork{
    private $frameWork_vals= array();
    public function __construct(){
        $this->load_config();
        $this->set_constants();
    }

    private function load_config(){
        try{
            $config_json = fread(fopen( __DIR__.'/config.json','r'),4*1024);
        }catch (Exception $ex){
            die("We are very sorry for the inconvenience caused but we are having problems with our server, please try again later");
        }
        $obj = (array)json_decode($config_json);
        $this->frameWork_vals = (array)$obj['framework'];
    }

    private function set_constants () {
        defined('HCMS') ? null : define('HCMS', $this->frameWork_vals['HCMS']);
        /*defined('HOST_NAME') ? null : define('HOST_NAME', $this->frameWork_vals['HOST_NAME']);
        defined('DB_USER') ? null : define('DB_USER', $this->frameWork_vals['DB_USER']);
        defined('DB_PASS') ? null : define('DB_PASS', $this->frameWork_vals['DB_PASS']);
        defined('DB_NAME') ? null : define('DB_NAME', $this->frameWork_vals['DB_NAME']);
        defined('HOST_URL') ? null : define('HOST_URL', $this->frameWork_vals['HOST_URL']);
        defined('DOMAIN_NAME') ? null : define('DOMAIN_NAME', $this->frameWork_vals['DOMAIN_NAME']);*/
    }


}