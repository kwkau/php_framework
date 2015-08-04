<?php

class errorCodes{

    private $codes = array();

public function __construct(){
    $this->load_config();
    $this->set_contants();

}

    private function load_config(){
        try{
            $config_json = fread(fopen( __DIR__.'/config.json','r'),4*1024);
        }catch (Exception $ex){
            die("We are very sorry for the inconvenience caused but we are having problems with our server, please try again later");
        }
        $obj = (array)json_decode($config_json);
        $this->codes = (array)$obj['error_codes'];
    }

    private function set_contants(){
        /*---------------------------
         * REGISTRATION ERROR CODES
         *-------------------------*/
        foreach ($this->codes as $key => $val) {
            defined($key) ? null : define($key, $val);
        }

        //defined('') ? null:  define('', );
        //defined('') ? null:  define('', );
        //defined('') ? null:  define('', );
        //defined('') ? null:  define('', );
    }

}
