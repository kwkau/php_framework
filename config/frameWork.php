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
        foreach ($this->frameWork_vals as $key => $val) {
            defined($key) ? null : define($key, $val);
        }
    }


}